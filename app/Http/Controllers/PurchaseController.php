<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\GasProduct;
use App\Models\Cylinder;
use App\Models\SupplierCylinderTransaction;
use App\Http\Requests\StorePurchaseRequest;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    protected $accountingService;

    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    // ============================================
    // INDEX - List Purchases
    // ============================================
    public function index(Request $request)
    {
        $query = Purchase::with(['supplier']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('purchase_invoice_no', 'LIKE', "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $purchases = $query->orderBy('date', 'desc')->paginate(15);

        $stats = [
            'total' => Purchase::where('status', '!=', 'cancelled')->count(),
            'total_amount' => Purchase::where('status', '!=', 'cancelled')->sum('grand_total'),
            'pending_payments' => Purchase::where('payment_status', '!=', 'paid')->where('status', '!=', 'cancelled')->count(),
            'pending_amount' => Purchase::where('payment_status', '!=', 'paid')->where('status', '!=', 'cancelled')->sum('balance_due'),
        ];

        return view('purchases.index', compact('purchases', 'stats'));
    }

    // ============================================
    // CREATE - Show Form
    // ============================================
    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->get();
        $gasProducts = GasProduct::where('is_active', true)->get();
        $cylinders = Cylinder::with('gasProduct')->get();

        return view('purchases.create', compact('suppliers', 'gasProducts', 'cylinders'));
    }

    // ============================================
    // STORE - Save Purchase
    // ============================================
    public function store(StorePurchaseRequest $request)
    {
        $validated = $request->validated();

        $purchase = DB::transaction(function () use ($validated) {
            $purchase = Purchase::create([
                'supplier_id' => $validated['supplier_id'],
                'date' => $validated['date'],
                'delivery_date' => $validated['delivery_date'] ?? null,
                'purchase_type' => $validated['purchase_type'],
                'discount' => $validated['discount'] ?? 0,
                'tax' => $validated['tax'] ?? 0,
                'amount_paid' => $validated['amount_paid'] ?? 0,
                'payment_method' => $validated['payment_method'],
                'status' => 'confirmed',
                'reference_no' => $validated['reference_no'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $returnCredits = [];

            foreach ($validated['items'] as $itemData) {
                $purchase->items()->create($this->buildPurchaseItemFields($purchase, $itemData, $returnCredits));
            }

            $purchase->calculateTotals();
            $purchase->updatePaymentStatus();

            $supplier = Supplier::find($purchase->supplier_id);
            $supplier?->increment('opening_balance', $purchase->balance_due);

            $this->accountingService->recordPurchase($purchase);

            foreach ($returnCredits as $credit) {
                $this->accountingService->recordCylinderReturnToSupplier($purchase, $credit, $validated['payment_method']);
            }

            return $purchase;
        });

        return redirect()->route('purchases.index')
            ->with('success', "Purchase {$purchase->purchase_invoice_no} created successfully!");
    }

    /**
     * Apply a purchase line item's stock effects and return the attributes to save on it.
     * Any credit owed back for a supplier return is appended to $returnCredits by reference.
     */
    private function buildPurchaseItemFields(Purchase $purchase, array $itemData, array &$returnCredits): array
    {
        $fields = [
            'notes' => $itemData['notes'] ?? null,
            'gas_total' => 0,
            'cylinder_total' => 0,
        ];

        if (!empty($itemData['gas_product_id'])) {
            $gasProduct = GasProduct::where('id', $itemData['gas_product_id'])->lockForUpdate()->first();
            $gasProduct->increment('current_stock', $itemData['gas_quantity']);

            $fields['gas_product_id'] = $itemData['gas_product_id'];
            $fields['gas_quantity'] = $itemData['gas_quantity'];
            $fields['gas_price'] = $itemData['gas_price'];
            $fields['gas_total'] = round($itemData['gas_quantity'] * $itemData['gas_price'], 2);
        }

        if (!empty($itemData['cylinder_id'])) {
            $cylinder = Cylinder::findOrFail($itemData['cylinder_id']);
            $quantity = (int) $itemData['cylinder_quantity'];
            $unitPrice = (float) ($itemData['cylinder_unit_price'] ?? 0);
            $action = $itemData['cylinder_action'];
            $lineTotal = 0;

            if ($action === 'purchase') {
                $cylinder->addStock($quantity);
                $lineTotal = round($quantity * $unitPrice, 2);
                if ($unitPrice > 0) {
                    $cylinder->update(['purchase_price' => $unitPrice]);
                }
                $this->logSupplierCylinderTransaction($purchase, $cylinder, 'purchased_new', $quantity, $unitPrice * $quantity);
            } elseif ($action === 'exchange') {
                $this->logSupplierCylinderTransaction($purchase, $cylinder, 'exchanged', $quantity, 0);
            } elseif ($action === 'return_to_supplier') {
                $cylinder->removeStock($quantity);
                $creditAmount = round($quantity * $unitPrice, 2);
                $returnCredits[] = $creditAmount;
                $this->logSupplierCylinderTransaction($purchase, $cylinder, 'returned_empty', $quantity, $creditAmount);
            }

            $fields['cylinder_id'] = $itemData['cylinder_id'];
            $fields['cylinder_action'] = $action;
            $fields['cylinder_quantity'] = $quantity;
            $fields['cylinder_unit_price'] = $unitPrice;
            $fields['cylinder_total'] = $lineTotal;
        }

        return $fields;
    }

    private function logSupplierCylinderTransaction(Purchase $purchase, Cylinder $cylinder, string $type, int $quantity, float $depositAdjustment): void
    {
        SupplierCylinderTransaction::create([
            'supplier_id' => $purchase->supplier_id,
            'cylinder_id' => $cylinder->id,
            'purchase_id' => $purchase->id,
            'user_id' => auth()->id(),
            'transaction_type' => $type,
            'transaction_date' => $purchase->date,
            'deposit_adjustment' => $depositAdjustment,
            'reference_document' => $purchase->purchase_invoice_no,
            'remarks' => "{$quantity} piece(s) via {$purchase->purchase_invoice_no}",
        ]);
    }

    // ============================================
    // SHOW - View Purchase
    // ============================================
    public function show(Purchase $purchase)
    {
        $purchase->load([
            'supplier',
            'items.gasProduct',
            'items.cylinder',
            'creator',
            'payments',
        ]);

        $accountingEntries = $purchase->accountingEntries()
            ->with(['account', 'oppositeAccount'])
            ->get();

        $cylinderTransactions = $purchase->cylinderTransactions()
            ->with(['cylinder', 'supplier'])
            ->get();

        return view('purchases.show', compact('purchase', 'accountingEntries', 'cylinderTransactions'));
    }

    // ============================================
    // EDIT - Show Edit Form
    // ============================================
    public function edit(Purchase $purchase)
    {
        if ($purchase->status !== 'draft') {
            return redirect()->route('purchases.show', $purchase)
                ->with('error', 'Only draft purchases can be edited.');
        }

        $suppliers = Supplier::where('is_active', true)->get();
        $gasProducts = GasProduct::where('is_active', true)->get();
        $cylinders = Cylinder::with('gasProduct')->get();

        return view('purchases.edit', compact('purchase', 'suppliers', 'gasProducts', 'cylinders'));
    }

    // ============================================
    // UPDATE - Update Purchase
    // ============================================
    public function update(Request $request, Purchase $purchase)
    {
        if ($purchase->status !== 'draft') {
            return redirect()->route('purchases.show', $purchase)
                ->with('error', 'Only draft purchases can be updated.');
        }

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase updated successfully!');
    }

    // ============================================
    // DESTROY - Cancel Purchase (reverses stock and accounting, never hard-deletes posted entries)
    // ============================================
    public function destroy(Purchase $purchase)
    {
        if ($purchase->status === 'cancelled') {
            return redirect()->route('purchases.index')->with('error', 'Purchase is already cancelled.');
        }

        DB::transaction(function () use ($purchase) {
            foreach ($purchase->items as $item) {
                if ($item->gas_product_id && $item->gas_quantity > 0) {
                    GasProduct::where('id', $item->gas_product_id)->decrement('current_stock', $item->gas_quantity);
                }

                if ($item->cylinder_id && $item->cylinder_quantity > 0) {
                    $cylinder = Cylinder::find($item->cylinder_id);
                    if ($cylinder) {
                        if ($item->cylinder_action === 'purchase') {
                            $cylinder->decrement('stock_quantity', $item->cylinder_quantity);
                        } elseif ($item->cylinder_action === 'return_to_supplier') {
                            $cylinder->increment('stock_quantity', $item->cylinder_quantity);
                        }
                        $cylinder->updateStatus();
                    }
                }
            }

            $this->accountingService->reverseEntries($purchase, 'purchase', "Cancelled purchase: {$purchase->purchase_invoice_no}");

            $supplier = $purchase->supplier;
            $supplier?->decrement('opening_balance', $purchase->balance_due);

            $purchase->update(['status' => 'cancelled']);
        });

        return redirect()->route('purchases.index')
            ->with('success', "Purchase {$purchase->purchase_invoice_no} cancelled and reversed successfully!");
    }

    // ============================================
    // APPROVE / CANCEL
    // ============================================
    public function approve(Purchase $purchase)
    {
        if ($purchase->status !== 'draft') {
            return redirect()->route('purchases.show', $purchase)
                ->with('error', "Purchase {$purchase->purchase_invoice_no} is already {$purchase->status} and cannot be approved again.");
        }

        $purchase->approve();

        return redirect()->route('purchases.show', $purchase)
            ->with('success', "Purchase {$purchase->purchase_invoice_no} approved.");
    }

    public function cancel(Purchase $purchase)
    {
        return $this->destroy($purchase);
    }

    // ============================================
    // RECORD PAYMENT
    // ============================================
    public function recordPayment(Request $request, Purchase $purchase)
    {
        if ($purchase->status === 'cancelled') {
            return response()->json(['success' => false, 'message' => 'Cannot record a payment against a cancelled purchase.'], 400);
        }

        if ($purchase->balance_due <= 0) {
            return response()->json(['success' => false, 'message' => 'This purchase has no outstanding balance.'], 400);
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $purchase->balance_due,
            'payment_method' => 'required|in:cash,bank_transfer,cheque,online',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::transaction(function () use ($request, $purchase) {
                $purchase->amount_paid += $request->amount;
                $purchase->balance_due = $purchase->grand_total - $purchase->amount_paid;
                $purchase->updatePaymentStatus();
                $purchase->save();

                $purchase->payments()->create([
                    'amount' => $request->amount,
                    'payment_method' => $request->payment_method,
                    'payment_date' => $request->payment_date,
                    'transaction_no' => 'PAY-PUR-' . time() . '-' . rand(100, 999),
                    'notes' => $request->notes,
                    'created_by' => auth()->id(),
                    'status' => 'completed'
                ]);

                $purchase->supplier?->decrement('opening_balance', $request->amount);

                $this->accountingService->recordPurchasePayment($purchase, (float) $request->amount, $request->payment_method, $request->payment_date);
            });

            return response()->json([
                'success' => true,
                'message' => "Payment of Rs. " . number_format($request->amount, 2) . " recorded successfully!",
                'balance_due' => $purchase->balance_due,
                'payment_status' => $purchase->payment_status,
                'amount_paid' => $purchase->amount_paid
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    // ============================================
    // PRINT - Print Purchase
    // ============================================
    public function print(Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.gasProduct', 'items.cylinder']);
        return view('purchases.print', compact('purchase'));
    }
}
