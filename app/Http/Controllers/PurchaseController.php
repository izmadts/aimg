<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\GasProduct;
use App\Models\Cylinder;
use App\Models\Account;
use App\Models\AccountingEntry;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $query = Purchase::with(['supplier', 'gasProduct', 'cylinder']);

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
        $cylinders = Cylinder::where('stock_quantity', '>', 0)
            ->with('gasProduct')
            ->get();
        $accounts = Account::where('is_active', true)->orderBy('account_code')->get();

        return view('purchases.create', compact('suppliers', 'gasProducts', 'cylinders', 'accounts'));
    }

    // ============================================
    // STORE - Save Purchase
    // ============================================

    public function store(Request $request)
{
    $validated = $request->validate([
        'supplier_id' => 'required|exists:suppliers,id',
        'date' => 'required|date',
        'purchase_type' => 'required|in:gas_only,gas_with_cylinder,cylinder_only,exchange',
        'gas_product_id' => 'nullable|exists:gas_products,id',
        'gas_quantity' => 'nullable|numeric|min:0',
        'gas_price' => 'nullable|numeric|min:0',
        'cylinder_id' => 'nullable|exists:cylinders,id',
        'cylinder_quantity' => 'nullable|integer|min:0',
        'cylinder_purchase_price' => 'nullable|numeric|min:0',
        'cylinder_sale_price' => 'nullable|numeric|min:0',
        'discount' => 'nullable|numeric|min:0',
        'tax' => 'nullable|numeric|min:0',
        'amount_paid' => 'nullable|numeric|min:0',
        'debit_account_id' => 'required|exists:accounts,id',
        'credit_account_id' => 'required|exists:accounts,id',
        'notes' => 'nullable|string'
    ]);

    // Validate debit and credit accounts are not same
    if ($validated['debit_account_id'] == $validated['credit_account_id']) {
        return redirect()->back()
            ->with('error', 'Debit and Credit accounts cannot be the same.')
            ->withInput();
    }

    // ✅ Calculate totals
    $validated['gas_total'] = ($validated['gas_quantity'] ?? 0) * ($validated['gas_price'] ?? 0);
    $validated['cylinder_total'] = ($validated['cylinder_quantity'] ?? 0) * ($validated['cylinder_purchase_price'] ?? 0);
    $validated['subtotal'] = $validated['gas_total'] + $validated['cylinder_total'];
    $validated['grand_total'] = $validated['subtotal'] - ($validated['discount'] ?? 0) + ($validated['tax'] ?? 0);
    $validated['balance_due'] = $validated['grand_total'] - ($validated['amount_paid'] ?? 0);
    $validated['payment_status'] = $this->getPaymentStatus($validated['grand_total'], $validated['amount_paid'] ?? 0);
    $validated['status'] = 'confirmed';
    $validated['created_by'] = auth()->id();

    DB::transaction(function () use ($validated) {
        $purchase = Purchase::create($validated);

        // ✅ Update gas stock
        if ($validated['gas_product_id'] && $validated['gas_quantity'] > 0) {
            $gasProduct = GasProduct::find($validated['gas_product_id']);
            if ($gasProduct) {
                $gasProduct->increment('current_stock', $validated['gas_quantity']);
            }
        }

        // ✅ Update cylinder stock
        if ($validated['cylinder_id'] && $validated['cylinder_quantity'] > 0) {
            $cylinder = Cylinder::find($validated['cylinder_id']);
            if ($cylinder) {
                $cylinder->increment('stock_quantity', $validated['cylinder_quantity']);
                if ($validated['cylinder_sale_price'] > 0) {
                    $cylinder->update(['sale_price' => $validated['cylinder_sale_price']]);
                }
                $cylinder->updateStatus();
            }
        }

        // ✅ Auto Accounting
        $this->recordAccounting($purchase, $validated);

        // ✅ Update supplier balance
        $supplier = Supplier::find($validated['supplier_id']);
        if ($supplier) {
            $supplier->increment('opening_balance', $purchase->balance_due);
        }
    });

    return redirect()->route('purchases.index')
        ->with('success', 'Purchase created successfully!');
}

    /*public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'purchase_type' => 'required|in:gas_only,gas_with_cylinder,cylinder_only,exchange',
            'gas_product_id' => 'nullable|exists:gas_products,id',
            'gas_quantity' => 'nullable|numeric|min:0',
            'gas_price' => 'nullable|numeric|min:0',
            'cylinder_id' => 'nullable|exists:cylinders,id',
            'cylinder_quantity' => 'nullable|integer|min:0',
            'cylinder_purchase_price' => 'nullable|numeric|min:0',
            'cylinder_sale_price' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'debit_account_id' => 'required|exists:accounts,id',
            'credit_account_id' => 'required|exists:accounts,id',
            'notes' => 'nullable|string'
        ]);

        $validated['gas_total'] = ($validated['gas_quantity'] ?? 0) * ($validated['gas_price'] ?? 0);
        $validated['cylinder_total'] = ($validated['cylinder_quantity'] ?? 0) * ($validated['cylinder_purchase_price'] ?? 0);
        $validated['subtotal'] = $validated['gas_total'] + $validated['cylinder_total'];
        $validated['grand_total'] = $validated['subtotal'] - ($validated['discount'] ?? 0) + ($validated['tax'] ?? 0);
        $validated['balance_due'] = $validated['grand_total'] - ($validated['amount_paid'] ?? 0);
        $validated['payment_status'] = $this->getPaymentStatus($validated['grand_total'], $validated['amount_paid'] ?? 0);
        $validated['status'] = 'confirmed';
        $validated['created_by'] = auth()->id();

        DB::transaction(function () use ($validated) {
            $purchase = Purchase::create($validated);

            // Update gas stock
            if ($validated['gas_product_id'] && $validated['gas_quantity'] > 0) {
                $gasProduct = GasProduct::find($validated['gas_product_id']);
                if ($gasProduct) {
                    $gasProduct->increment('current_stock', $validated['gas_quantity']);
                }
            }

            // Update cylinder stock
            if ($validated['cylinder_id'] && $validated['cylinder_quantity'] > 0) {
                $cylinder = Cylinder::find($validated['cylinder_id']);
                if ($cylinder) {
                    $cylinder->increment('stock_quantity', $validated['cylinder_quantity']);
                    if ($validated['cylinder_sale_price'] > 0) {
                        $cylinder->update(['sale_price' => $validated['cylinder_sale_price']]);
                    }
                    $cylinder->updateStatus();
                }
            }

            // Auto Accounting
            $this->recordAccounting($purchase, $validated);

            // Update supplier balance
            $supplier = Supplier::find($validated['supplier_id']);
            if ($supplier) {
                $supplier->increment('opening_balance', $purchase->balance_due);
            }
        });

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase created successfully!');
    }*/

    // ============================================
    // SHOW - View Purchase
    // ============================================
    /*public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'gasProduct', 'cylinder', 'creator', 'debitAccount', 'creditAccount']);

        $journalEntries = $purchase->journalEntries()
            ->with(['account', 'oppositeAccount'])
            ->get();

        return view('purchases.show', compact('purchase', 'journalEntries'));
    }*/

    /**
 * Display the specified purchase.
 */
public function show(Purchase $purchase)
{
    $purchase->load([
        'supplier', 
        'gasProduct', 
        'cylinder', 
        'creator', 
        'debitAccount', 
        'creditAccount',
        'payments'  // ✅ Load payments
    ]);

    $journalEntries = $purchase->journalEntries()
        ->with(['account', 'oppositeAccount'])
        ->get();

    // ✅ Get cylinder transactions (if any)
    $cylinderTransactions = $purchase->cylinderTransactions()
        ->with(['cylinder', 'supplier'])
        ->get();

    return view('purchases.show', compact('purchase', 'journalEntries', 'cylinderTransactions'));
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
        $cylinders = Cylinder::where('stock_quantity', '>', 0)
            ->with('gasProduct')
            ->get();
        $accounts = Account::where('is_active', true)->orderBy('account_code')->get();

        return view('purchases.edit', compact('purchase', 'suppliers', 'gasProducts', 'cylinders', 'accounts'));
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

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'purchase_type' => 'required|in:gas_only,gas_with_cylinder,cylinder_only,exchange',
            'gas_product_id' => 'nullable|exists:gas_products,id',
            'gas_quantity' => 'nullable|numeric|min:0',
            'gas_price' => 'nullable|numeric|min:0',
            'cylinder_id' => 'nullable|exists:cylinders,id',
            'cylinder_quantity' => 'nullable|integer|min:0',
            'cylinder_purchase_price' => 'nullable|numeric|min:0',
            'cylinder_sale_price' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'debit_account_id' => 'required|exists:accounts,id',
            'credit_account_id' => 'required|exists:accounts,id',
            'notes' => 'nullable|string'
        ]);

        $validated['gas_total'] = ($validated['gas_quantity'] ?? 0) * ($validated['gas_price'] ?? 0);
        $validated['cylinder_total'] = ($validated['cylinder_quantity'] ?? 0) * ($validated['cylinder_purchase_price'] ?? 0);
        $validated['subtotal'] = $validated['gas_total'] + $validated['cylinder_total'];
        $validated['grand_total'] = $validated['subtotal'] - ($validated['discount'] ?? 0) + ($validated['tax'] ?? 0);
        $validated['balance_due'] = $validated['grand_total'] - ($validated['amount_paid'] ?? 0);
        $validated['payment_status'] = $this->getPaymentStatus($validated['grand_total'], $validated['amount_paid'] ?? 0);

        $purchase->update($validated);

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase updated successfully!');
    }

    // ============================================
    // DESTROY - Delete Purchase
    // ============================================
    public function destroy(Purchase $purchase)
    {
        if ($purchase->status !== 'draft') {
            return redirect()->route('purchases.index')
                ->with('error', 'Only draft purchases can be deleted.');
        }

        $purchase->delete();

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase deleted successfully!');
    }

    // ============================================
    // RECORD PAYMENT
    // ============================================
   /* public function recordPayment(Request $request, Purchase $purchase)
    {
        $request->validate([
            'payment_amount' => 'required|numeric|min:0.01|max:' . $purchase->balance_due,
            'payment_method' => 'required|in:cash,bank_transfer,cheque,online',
            'payment_date' => 'required|date',
            'payment_notes' => 'nullable|string|max:500'
        ]);

        DB::transaction(function () use ($request, $purchase) {
            $purchase->amount_paid += $request->payment_amount;
            $purchase->balance_due = $purchase->grand_total - $purchase->amount_paid;
            $purchase->updatePaymentStatus();
            $purchase->save();

            // Update supplier balance
            $supplier = $purchase->supplier;
            if ($supplier) {
                $supplier->decrement('opening_balance', $request->payment_amount);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Payment recorded successfully!',
            'balance_due' => $purchase->balance_due,
            'payment_status' => $purchase->payment_status
        ]);
    }*/

    /**
 * Record Payment for Purchase
 */
public function recordPayment(Request $request, Purchase $purchase)
{
    $request->validate([
        'amount' => 'required|numeric|min:0.01|max:' . $purchase->balance_due,
        'payment_method' => 'required|in:cash,bank_transfer,cheque,online',
        'payment_date' => 'required|date',
        'notes' => 'nullable|string|max:500'
    ]);

    try {
        DB::transaction(function () use ($request, $purchase) {
            // Update purchase
            $purchase->amount_paid += $request->amount;
            $purchase->balance_due = $purchase->grand_total - $purchase->amount_paid;
            $purchase->updatePaymentStatus();
            $purchase->save();

            // ✅ Create payment record
            $payment = $purchase->payments()->create([
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'payment_date' => $request->payment_date,
                'transaction_no' => 'PAY-' . time() . '-' . rand(100, 999),
                'notes' => $request->notes,
                'created_by' => auth()->id(),
                'status' => 'completed'
            ]);

            // Update supplier balance
            $supplier = $purchase->supplier;
            if ($supplier) {
                $supplier->decrement('opening_balance', $request->amount);
            }

            // ✅ Auto Accounting for Payment
            $this->recordPaymentAccounting($purchase, $request);
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

/**
 * Record Payment Accounting
 */
private function recordPaymentAccounting($purchase, $request)
{
    try {
        $payableAccount = Account::where('account_code', '2001')->first();
        $cashAccount = Account::where('account_code', '1001')->first();

        if ($payableAccount && $cashAccount) {
            // 1. Debit: Accounts Payable
            AccountingEntry::create([
                'entry_no' => AccountingEntry::generateEntryNo(),
                'date' => $request->payment_date,
                'description' => "Payment against purchase: {$purchase->purchase_invoice_no}",
                'transaction_type' => 'payment',
                'reference_type' => get_class($purchase),
                'reference_id' => $purchase->id,
                'account_id' => $payableAccount->id,
                'opposite_account_id' => $cashAccount->id,
                'debit' => $request->amount,
                'credit' => 0,
                'status' => 'approved',
                'created_by' => auth()->id(),
            ]);

            // 2. Credit: Cash
            AccountingEntry::create([
                'entry_no' => AccountingEntry::generateEntryNo(),
                'date' => $request->payment_date,
                'description' => "Cash payment for purchase: {$purchase->purchase_invoice_no}",
                'transaction_type' => 'payment',
                'reference_type' => get_class($purchase),
                'reference_id' => $purchase->id,
                'account_id' => $cashAccount->id,
                'opposite_account_id' => $payableAccount->id,
                'debit' => 0,
                'credit' => $request->amount,
                'status' => 'approved',
                'created_by' => auth()->id(),
            ]);

            $payableAccount->updateBalance();
            $cashAccount->updateBalance();
        }
    } catch (\Exception $e) {
        Log::error('Payment accounting error: ' . $e->getMessage());
    }
}

    // ============================================
    // PRINT - Print Purchase
    // ============================================
    public function print(Purchase $purchase)
    {
        $purchase->load(['supplier', 'gasProduct', 'cylinder']);
        return view('purchases.print', compact('purchase'));
    }

    // ============================================
    // HELPER METHODS
    // ============================================
    private function getPaymentStatus($total, $paid)
    {
        if ($paid >= $total) return 'paid';
        if ($paid > 0) return 'partial';
        return 'unpaid';
    }

    // ============================================
    // ACCOUNTING
    // ============================================
    private function recordAccounting($purchase, $validated)
    {
        try {
            $entries = [];
            $accounts = [];

            $debitAccount = Account::find($validated['debit_account_id']);
            $creditAccount = Account::find($validated['credit_account_id']);
            $cashAccount = Account::where('account_code', '1001')->first();

            // 1. Debit Entry
            $debitTotal = $validated['gas_total'] + $validated['cylinder_total'];
            if ($debitTotal > 0 && $debitAccount) {
                $entries[] = [
                    'account_id' => $debitAccount->id,
                    'opposite_account_id' => $creditAccount->id,
                    'debit' => $debitTotal,
                    'credit' => 0,
                    'description' => "Purchase: {$purchase->purchase_invoice_no} - Debit",
                ];
                $accounts[] = $debitAccount;
            }

            // 2. Credit Entry
            if ($validated['grand_total'] > 0 && $creditAccount) {
                $entries[] = [
                    'account_id' => $creditAccount->id,
                    'opposite_account_id' => $debitAccount->id,
                    'debit' => 0,
                    'credit' => $validated['grand_total'],
                    'description' => "Purchase: {$purchase->purchase_invoice_no} - Credit",
                ];
                $accounts[] = $creditAccount;
            }

            // 3. Payment Entry
            if ($validated['amount_paid'] > 0 && $cashAccount) {
                $entries[] = [
                    'account_id' => $creditAccount->id,
                    'opposite_account_id' => $cashAccount->id,
                    'debit' => $validated['amount_paid'],
                    'credit' => 0,
                    'description' => "Payment against: {$purchase->purchase_invoice_no}",
                ];
                $entries[] = [
                    'account_id' => $cashAccount->id,
                    'opposite_account_id' => $creditAccount->id,
                    'debit' => 0,
                    'credit' => $validated['amount_paid'],
                    'description' => "Cash Payment: {$purchase->purchase_invoice_no}",
                ];
                $accounts[] = $cashAccount;
            }

            foreach ($entries as $entry) {
                AccountingEntry::create([
                    'entry_no' => AccountingEntry::generateEntryNo(),
                    'date' => now(),
                    'description' => $entry['description'],
                    'transaction_type' => 'purchase',
                    'reference_type' => get_class($purchase),
                    'reference_id' => $purchase->id,
                    'account_id' => $entry['account_id'],
                    'opposite_account_id' => $entry['opposite_account_id'],
                    'debit' => $entry['debit'],
                    'credit' => $entry['credit'],
                    'status' => 'approved',
                    'created_by' => auth()->id(),
                ]);
            }

            foreach ($accounts as $account) {
                $account->updateBalance();
            }

            Log::info('Purchase accounting recorded: ' . $purchase->purchase_invoice_no);

        } catch (\Exception $e) {
            Log::error('Purchase accounting error: ' . $e->getMessage());
        }
    }
}