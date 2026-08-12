<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\GasProduct;
use App\Models\Cylinder;
use App\Http\Requests\StoreSaleRequest;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    protected $accountingService;

    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    // ============================================
    // INDEX - List Sales
    // ============================================
    public function index(Request $request)
    {
        $query = Sale::with(['customer', 'creator']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'LIKE', "%{$search}%")
                  ->orWhereHas('customer', function ($sq) use ($search) {
                      $sq->where('name', 'LIKE', "%{$search}%")
                         ->orWhere('phone', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', $request->to_date);
        }

        $sales = $query->orderBy('date', 'desc')->paginate(15);

        $stats = [
            'total' => Sale::where('status', '!=', 'cancelled')->count(),
            'today_sales' => Sale::whereDate('date', now()->toDateString())->where('status', '!=', 'cancelled')->sum('grand_total'),
            'pending_payments' => Sale::where('payment_status', '!=', 'paid')->where('status', '!=', 'cancelled')->count(),
            'pending_amount' => Sale::where('payment_status', '!=', 'paid')->where('status', '!=', 'cancelled')->sum('balance_due'),
        ];

        return view('sales.index', compact('sales', 'stats'));
    }

    // ============================================
    // CREATE - Show Form
    // ============================================
    public function create()
    {
        $customers = Customer::where('is_active', true)->get();
        $gasProducts = GasProduct::where('is_active', true)->where('current_stock', '>', 0)->get();

        $availableCylinders = Cylinder::whereColumn('stock_quantity', '>', 'issued_quantity')
            ->with('gasProduct')
            ->get()
            ->map(function ($cylinder) {
                return [
                    'id' => $cylinder->id,
                    'cylinder_number' => $cylinder->cylinder_number,
                    'gasProduct' => $cylinder->gasProduct,
                    'type' => $cylinder->type,
                    'available_quantity' => $cylinder->available_quantity,
                    'sale_price' => $cylinder->sale_price,
                ];
            });

        return view('sales.create', compact('customers', 'gasProducts', 'availableCylinders'));
    }

    // ============================================
    // STORE - Save Sale
    // ============================================
    public function store(StoreSaleRequest $request)
    {
        $validated = $request->validated();

        $sale = DB::transaction(function () use ($validated) {
            $sale = Sale::create([
                'customer_id' => $validated['customer_id'],
                'date' => $validated['date'],
                'delivery_date' => $validated['delivery_date'] ?? null,
                'discount' => $validated['discount'] ?? 0,
                'tax' => $validated['tax'] ?? 0,
                'amount_paid' => $validated['amount_paid'] ?? 0,
                'payment_method' => $validated['payment_method'],
                'status' => 'confirmed',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $itemData) {
                $sale->items()->create($this->buildSaleItemFields($sale, $itemData));
            }

            $sale->calculateTotals();
            $sale->updatePaymentStatus();

            $this->accountingService->recordSale($sale);

            return $sale;
        });

        return redirect()->route('sales.index')
            ->with('success', "Sale {$sale->invoice_no} created successfully!");
    }

    /**
     * Apply a sale line item's stock effects and return the attributes to save on it.
     */
    private function buildSaleItemFields(Sale $sale, array $itemData): array
    {
        $fields = [
            'notes' => $itemData['notes'] ?? null,
            'gas_total' => 0,
            'cylinder_total' => 0,
        ];

        if (!empty($itemData['gas_product_id'])) {
            $gasProduct = GasProduct::where('id', $itemData['gas_product_id'])->lockForUpdate()->first();
            if (!$gasProduct || $gasProduct->current_stock < $itemData['gas_quantity']) {
                throw new \Exception('Insufficient gas stock for ' . ($gasProduct->name ?? 'the selected product') . '.');
            }

            $gasProduct->decrement('current_stock', $itemData['gas_quantity']);

            $fields['gas_product_id'] = $itemData['gas_product_id'];
            $fields['gas_quantity'] = $itemData['gas_quantity'];
            $fields['gas_price'] = $itemData['gas_price'];
            $fields['gas_total'] = round($itemData['gas_quantity'] * $itemData['gas_price'], 2);
        }

        if (!empty($itemData['cylinder_id'])) {
            $cylinder = Cylinder::findOrFail($itemData['cylinder_id']);
            $quantity = (int) $itemData['cylinder_quantity'];
            $unitPrice = (float) ($itemData['cylinder_unit_price'] ?? 0);
            $lineTotal = round($quantity * $unitPrice, 2);

            if ($itemData['cylinder_action'] === 'issue') {
                $cylinder->issueToCustomer($sale->customer_id, $quantity, $lineTotal, $sale->invoice_no);
            } else {
                $cylinder->sellToCustomer($sale->customer_id, $quantity, $sale->invoice_no);
            }

            $fields['cylinder_id'] = $itemData['cylinder_id'];
            $fields['cylinder_action'] = $itemData['cylinder_action'];
            $fields['cylinder_quantity'] = $quantity;
            $fields['cylinder_unit_price'] = $unitPrice;
            $fields['cylinder_total'] = $lineTotal;
        }

        return $fields;
    }

    // ============================================
    // SHOW - View Sale
    // ============================================
    public function show(Sale $sale)
    {
        $sale->load(['customer', 'items.gasProduct', 'items.cylinder', 'creator', 'payments']);

        $accountingEntries = $sale->accountingEntries()
            ->with(['account', 'oppositeAccount'])
            ->get();

        $cylinderTransactions = $sale->cylinderTransactions()
            ->with(['cylinder', 'customer'])
            ->get();

        return view('sales.show', compact('sale', 'accountingEntries', 'cylinderTransactions'));
    }

    // ============================================
    // EDIT - Show Edit Form
    // ============================================
    public function edit(Sale $sale)
    {
        if ($sale->status !== 'draft') {
            return redirect()->route('sales.show', $sale)
                ->with('error', 'Only draft sales can be edited.');
        }

        $customers = Customer::where('is_active', true)->get();
        $gasProducts = GasProduct::where('is_active', true)->get();
        $availableCylinders = Cylinder::whereColumn('stock_quantity', '>', 'issued_quantity')
            ->with('gasProduct')
            ->get();

        return view('sales.edit', compact('sale', 'customers', 'gasProducts', 'availableCylinders'));
    }

    // ============================================
    // UPDATE - Update Sale
    // ============================================
    public function update(Request $request, Sale $sale)
    {
        if ($sale->status !== 'draft') {
            return redirect()->route('sales.show', $sale)
                ->with('error', 'Only draft sales can be updated.');
        }

        return redirect()->route('sales.index')
            ->with('success', 'Sale updated successfully!');
    }

    // ============================================
    // DESTROY - Cancel Sale (reverses stock and accounting, never hard-deletes posted entries)
    // ============================================
    public function destroy(Sale $sale)
    {
        if ($sale->status === 'cancelled') {
            return redirect()->route('sales.index')->with('error', 'Sale is already cancelled.');
        }

        DB::transaction(function () use ($sale) {
            foreach ($sale->items as $item) {
                if ($item->gas_product_id && $item->gas_quantity > 0) {
                    GasProduct::where('id', $item->gas_product_id)->increment('current_stock', $item->gas_quantity);
                }

                if ($item->cylinder_id && $item->cylinder_quantity > 0) {
                    $cylinder = Cylinder::find($item->cylinder_id);
                    if ($cylinder) {
                        if ($item->cylinder_action === 'issue') {
                            $cylinder->decrement('issued_quantity', $item->cylinder_quantity);
                        } else {
                            $cylinder->increment('stock_quantity', $item->cylinder_quantity);
                        }
                        $cylinder->updateStatus();
                    }
                }
            }

            $this->accountingService->reverseEntries($sale, 'sale', "Cancelled sale: {$sale->invoice_no}");

            $sale->update(['status' => 'cancelled']);
        });

        return redirect()->route('sales.index')
            ->with('success', "Sale {$sale->invoice_no} cancelled and reversed successfully!");
    }

    // ============================================
    // PRINT - Print Sale Invoice
    // ============================================
    public function print(Sale $sale)
    {
        $sale->load(['customer', 'items.gasProduct', 'items.cylinder', 'creator']);
        return view('sales.print', compact('sale'));
    }

    // ============================================
    // RECORD PAYMENT
    // ============================================
    public function recordPayment(Request $request, Sale $sale)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $sale->balance_due,
            'payment_method' => 'required|in:cash,bank_transfer,cheque,online',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::transaction(function () use ($request, $sale) {
                $sale->recordPayment(
                    $request->amount,
                    $request->payment_method,
                    $request->payment_date,
                    $request->notes
                );

                $this->accountingService->recordSalePayment($sale, (float) $request->amount, $request->payment_method, $request->payment_date);
            });

            return response()->json([
                'success' => true,
                'message' => "Payment of Rs. " . number_format($request->amount, 2) . " recorded successfully!",
                'balance_due' => $sale->balance_due,
                'payment_status' => $sale->payment_status
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    // ============================================
    // RETURN CYLINDER
    // ============================================
    public function returnCylinder(Request $request, Sale $sale)
    {
        $request->validate([
            'cylinder_id' => 'required|exists:cylinders,id',
            'customer_id' => 'required|exists:customers,id',
            'damage_charge' => 'nullable|numeric|min:0',
            'security_deposit_refund' => 'nullable|numeric|min:0',
            'cylinder_condition' => 'required|in:good,damaged,expired',
            'notes' => 'nullable|string|max:500'
        ]);

        $cylinder = Cylinder::findOrFail($request->cylinder_id);

        try {
            DB::transaction(function () use ($request, $sale, $cylinder) {
                $cylinder->returnFromCustomer(
                    $request->customer_id,
                    1,
                    $request->damage_charge ?? 0,
                    $request->security_deposit_refund ?? 0,
                    $sale->invoice_no
                );

                if ($request->security_deposit_refund > 0) {
                    $sale->cylinder_deposit_total = max(0, $sale->cylinder_deposit_total - $request->security_deposit_refund);
                    $sale->grand_total = $sale->subtotal + $sale->cylinder_deposit_total + $sale->cylinder_sale_total - $sale->discount + $sale->tax;
                    $sale->balance_due = $sale->grand_total - $sale->amount_paid;
                    $sale->save();

                    $this->accountingService->recordDepositRefund($sale, (float) $request->security_deposit_refund);
                }

                if ($request->damage_charge > 0) {
                    $this->accountingService->recordDamageCharge($sale, (float) $request->damage_charge);
                }
            });

            return response()->json([
                'success' => true,
                'message' => "Cylinder returned successfully!"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    // ============================================
    // GET CUSTOMER CYLINDERS (AJAX)
    // ============================================
    public function getCustomerCylinders(Customer $customer)
    {
        $details = $customer->activeCylinderIssues()->with('cylinder.gasProduct')->get();

        $cylinders = $details->map(function ($detail) {
            return [
                'id' => $detail->cylinder_id,
                'cylinder_number' => $detail->cylinder->cylinder_number ?? 'N/A',
                'gas_name' => $detail->cylinder->gasProduct->name ?? 'N/A',
                'issued_date' => $detail->issue_date ? $detail->issue_date->format('d-m-Y') : 'N/A',
                'days_out' => $detail->days_out,
                'security_deposit' => $detail->security_deposit,
                'quantity' => $detail->quantity,
            ];
        });

        return response()->json([
            'cylinders' => $cylinders,
            'total_deposit' => $details->sum('security_deposit'),
            'count' => $cylinders->count()
        ]);
    }
}
