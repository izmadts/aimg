<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use App\Models\GasProduct;
use App\Models\Cylinder;
use App\Models\Account;
use App\Models\AccountingEntry;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
   /* public function create()
    {
        $customers = Customer::where('is_active', true)->get();
        $gasProducts = GasProduct::where('is_active', true)->get();
        
        $availableCylinders = Cylinder::where('stock_quantity', '>', 0)
            ->where('issued_quantity', '<', DB::raw('stock_quantity'))
            ->with('gasProduct')
            ->get()
            ->map(function ($cylinder) {
                return [
                    'id' => $cylinder->id,
                    'cylinder_number' => $cylinder->cylinder_number,
                    'gasProduct' => $cylinder->gasProduct,
                    'type' => $cylinder->type,
                    'stock_quantity' => $cylinder->stock_quantity,
                    'issued_quantity' => $cylinder->issued_quantity ?? 0,
                    'available_quantity' => $cylinder->stock_quantity - ($cylinder->issued_quantity ?? 0),
                    'current_gas_quantity' => $cylinder->current_gas_quantity,
                    'status' => $cylinder->status,
                    'capacity' => $cylinder->capacity,
                    'purchase_price' => $cylinder->purchase_price,
                    'sale_price' => $cylinder->sale_price,
                ];
            });

        $accounts = Account::where('is_active', true)->orderBy('account_code')->get();

        return view('sales.create', compact('customers', 'gasProducts', 'availableCylinders', 'accounts'));
    }
*/

    public function create()
{
    $customers = Customer::where('is_active', true)->get();
    $gasProducts = GasProduct::where('is_active', true)->get();
    
    $availableCylinders = Cylinder::where('stock_quantity', '>', 0)
        ->where('issued_quantity', '<', DB::raw('stock_quantity'))
        ->with('gasProduct')
        ->get()
        ->map(function ($cylinder) {
            return [
                'id' => $cylinder->id,
                'cylinder_number' => $cylinder->cylinder_number,
                'gasProduct' => $cylinder->gasProduct,
                'type' => $cylinder->type,
                'stock_quantity' => $cylinder->stock_quantity,
                'issued_quantity' => $cylinder->issued_quantity ?? 0,
                'available_quantity' => $cylinder->stock_quantity - ($cylinder->issued_quantity ?? 0),
                'current_gas_quantity' => $cylinder->current_gas_quantity,
                'status' => $cylinder->status,
                'capacity' => $cylinder->capacity,
                'purchase_price' => $cylinder->purchase_price,
                'sale_price' => $cylinder->sale_price,
            ];
        });

    // ✅ Only Income Accounts for Credit
    $accounts = Account::where('is_active', true)
        ->where('account_type', 'income')
        ->orderBy('account_code')
        ->get();

    return view('sales.create', compact('customers', 'gasProducts', 'availableCylinders', 'accounts'));
}
    // ============================================
    // STORE - Save Sale
    // ============================================
   /* public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'date' => 'required|date',
            'delivery_date' => 'nullable|date|after_or_equal:date',
            'items' => 'required|array|min:1',
            'items.*.gas_product_id' => 'required|exists:gas_products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
            'cylinder_ids' => 'nullable|array',
            'cylinder_ids.*' => 'exists:cylinders,id',
            'cylinder_actions' => 'nullable|array',
            'cylinder_actions.*' => 'in:issue,sell',
            'cylinder_prices' => 'nullable|array',
            'cylinder_prices.*' => 'nullable|numeric|min:0',
            'cylinder_quantities' => 'nullable|array',
            'cylinder_quantities.*' => 'nullable|integer|min:1',
            'security_deposit' => 'nullable|numeric|min:0',
            'debit_account_id' => 'required|exists:accounts,id',
            'credit_account_id' => 'required|exists:accounts,id',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validated['debit_account_id'] == $validated['credit_account_id']) {
            return redirect()->back()
                ->with('error', 'Debit and Credit accounts cannot be the same.')
                ->withInput();
        }

        DB::transaction(function () use ($validated) {
            
            // 1. Create Sale
            $sale = Sale::create([
                'customer_id' => $validated['customer_id'],
                'date' => $validated['date'],
                'delivery_date' => $validated['delivery_date'] ?? null,
                'subtotal' => 0,
                'cylinder_deposit_total' => 0,
                'cylinder_sale_total' => 0,
                'discount' => $validated['discount'] ?? 0,
                'tax' => $validated['tax'] ?? 0,
                'grand_total' => 0,
                'amount_paid' => $validated['amount_paid'] ?? 0,
                'balance_due' => 0,
                'payment_status' => 'unpaid',
                'status' => 'confirmed',
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id()
            ]);

            // 2. Save Sale Items
            $subtotal = 0;
            foreach ($validated['items'] as $itemData) {
                $total = $itemData['quantity'] * $itemData['price'];
                $subtotal += $total;
                
                $sale->items()->create([
                    'gas_product_id' => $itemData['gas_product_id'],
                    'quantity' => $itemData['quantity'],
                    'price' => $itemData['price'],
                    'total' => $total,
                ]);

                // Update gas stock
                $gasProduct = GasProduct::find($itemData['gas_product_id']);
                if ($gasProduct) {
                    $gasProduct->decrement('current_stock', $itemData['quantity']);
                }
            }

            // 3. Handle Cylinders
            $cylinderDepositTotal = 0;
            $cylinderSaleTotal = 0;

            if (isset($validated['cylinder_ids']) && count($validated['cylinder_ids']) > 0) {
                foreach ($validated['cylinder_ids'] as $index => $cylinderId) {
                    $cylinder = Cylinder::find($cylinderId);
                    if (!$cylinder) continue;

                    $action = $validated['cylinder_actions'][$index] ?? 'issue';
                    $price = $validated['cylinder_prices'][$index] ?? 0;
                    $quantity = $validated['cylinder_quantities'][$index] ?? 1;

                    $availableQty = $cylinder->stock_quantity - ($cylinder->issued_quantity ?? 0);
                    if ($availableQty < $quantity) {
                        throw new \Exception("Insufficient stock for cylinder {$cylinder->cylinder_number}. Available: {$availableQty}");
                    }

                    // Update cylinder
                    $cylinder->increment('issued_quantity', $quantity);
                    $cylinder->updateStatus();

                    // Create transaction
                    $dbAction = $action === 'issue' ? 'issued' : 'sold';
                    $cylinder->transactions()->create([
                        'customer_id' => $validated['customer_id'],
                        'user_id' => auth()->id(),
                        'transaction_type' => $dbAction,
                        'transaction_date' => now(),
                        'remarks' => "Against sale: {$sale->invoice_no} - {$quantity} piece(s)",
                        'reference_document' => $sale->invoice_no
                    ]);

                    // Create issued detail
                    $cylinder->issuedDetails()->create([
                        'customer_id' => $validated['customer_id'],
                        'quantity' => $quantity,
                        'issue_date' => now(),
                        'security_deposit' => $action === 'issue' ? ($validated['security_deposit'] ?? 0) : 0,
                        'reference_document' => $sale->invoice_no,
                        'status' => 'issued',
                        'created_by' => auth()->id()
                    ]);

                    // Update sale item with cylinder reference
                    $saleItem = $sale->items()->first();
                    if ($saleItem) {
                        $saleItem->update([
                            'cylinder_id' => $cylinder->id,
                            'cylinder_sale_price' => $price,
                            'cylinder_action' => $dbAction
                        ]);
                    }

                    if ($action === 'issue') {
                        $cylinderDepositTotal += $validated['security_deposit'] ?? 0;
                    } else {
                        $cylinderSaleTotal += $price;
                    }
                }
            }

            // 4. Update Sale Totals
            $sale->subtotal = $subtotal;
            $sale->cylinder_deposit_total = $cylinderDepositTotal;
            $sale->cylinder_sale_total = $cylinderSaleTotal;
            $sale->grand_total = $subtotal + $cylinderDepositTotal + $cylinderSaleTotal - ($validated['discount'] ?? 0) + ($validated['tax'] ?? 0);
            $sale->balance_due = $sale->grand_total - ($validated['amount_paid'] ?? 0);
            $sale->updatePaymentStatus();
            $sale->save();

            // 5. Auto Accounting
            $this->recordSaleAccounting($sale, $validated);
        });

        return redirect()->route('sales.index')
            ->with('success', 'Sale created successfully!');
    }*/


 /*   public function store(Request $request)
{
    $validated = $request->validate([
        'customer_id' => 'required|exists:customers,id',
        'date' => 'required|date',
        'delivery_date' => 'nullable|date|after_or_equal:date',
        'items' => 'required|array|min:1',
        'items.*.gas_product_id' => 'required|exists:gas_products,id',
        'items.*.quantity' => 'required|numeric|min:0.01',
        'items.*.price' => 'required|numeric|min:0',
        'cylinder_ids' => 'nullable|array',
        'cylinder_ids.*' => 'exists:cylinders,id',
        'cylinder_actions' => 'nullable|array',
        'cylinder_actions.*' => 'in:issue,sell',
        'cylinder_prices' => 'nullable|array',
        'cylinder_prices.*' => 'nullable|numeric|min:0',
        'security_deposit' => 'nullable|numeric|min:0',
        'credit_account_id' => 'required|exists:accounts,id',  // ✅ Only Credit Account
        'notes' => 'nullable|string|max:1000'
    ]);

    DB::transaction(function () use ($validated) {
        
        // 1. Create Sale
        $sale = Sale::create([
            'customer_id' => $validated['customer_id'],
            'date' => $validated['date'],
            'delivery_date' => $validated['delivery_date'] ?? null,
            'subtotal' => 0,
            'cylinder_deposit_total' => 0,
            'cylinder_sale_total' => 0,
            'discount' => $validated['discount'] ?? 0,
            'tax' => $validated['tax'] ?? 0,
            'grand_total' => 0,
            'amount_paid' => $validated['amount_paid'] ?? 0,
            'balance_due' => 0,
            'payment_status' => 'unpaid',
            'status' => 'confirmed',
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id()
        ]);

        // 2. Save Sale Items
        $subtotal = 0;
        foreach ($validated['items'] as $itemData) {
            $total = $itemData['quantity'] * $itemData['price'];
            $subtotal += $total;
            
            $sale->items()->create([
                'gas_product_id' => $itemData['gas_product_id'],
                'quantity' => $itemData['quantity'],
                'price' => $itemData['price'],
                'total' => $total,
            ]);

            $gasProduct = GasProduct::find($itemData['gas_product_id']);
            if ($gasProduct) {
                $gasProduct->decrement('current_stock', $itemData['quantity']);
            }
        }

        // 3. Handle Cylinders
        $cylinderDepositTotal = 0;
        $cylinderSaleTotal = 0;

        if (isset($validated['cylinder_ids']) && count($validated['cylinder_ids']) > 0) {
            foreach ($validated['cylinder_ids'] as $index => $cylinderId) {
                $cylinder = Cylinder::find($cylinderId);
                if (!$cylinder) continue;

                $action = $validated['cylinder_actions'][$index] ?? 'issue';
                $price = $validated['cylinder_prices'][$index] ?? 0;
                $quantity = 1;

                $availableQty = $cylinder->stock_quantity - ($cylinder->issued_quantity ?? 0);
                if ($availableQty < $quantity) {
                    throw new \Exception("Insufficient stock for cylinder {$cylinder->cylinder_number}.");
                }

                $cylinder->increment('issued_quantity', $quantity);
                $cylinder->updateStatus();

                $dbAction = $action === 'issue' ? 'issued' : 'sold';
                $cylinder->transactions()->create([
                    'customer_id' => $validated['customer_id'],
                    'user_id' => auth()->id(),
                    'transaction_type' => $dbAction,
                    'transaction_date' => now(),
                    'remarks' => "Against sale: {$sale->invoice_no}",
                    'reference_document' => $sale->invoice_no
                ]);

                $cylinder->issuedDetails()->create([
                    'customer_id' => $validated['customer_id'],
                    'quantity' => $quantity,
                    'issue_date' => now(),
                    'security_deposit' => $action === 'issue' ? ($validated['security_deposit'] ?? 0) : 0,
                    'reference_document' => $sale->invoice_no,
                    'status' => 'issued',
                    'created_by' => auth()->id()
                ]);

                if ($action === 'issue') {
                    $cylinderDepositTotal += $validated['security_deposit'] ?? 0;
                } else {
                    $cylinderSaleTotal += $price;
                }
            }
        }

        // 4. Update Sale Totals
        $sale->subtotal = $subtotal;
        $sale->cylinder_deposit_total = $cylinderDepositTotal;
        $sale->cylinder_sale_total = $cylinderSaleTotal;
        $sale->grand_total = $subtotal + $cylinderDepositTotal + $cylinderSaleTotal - ($validated['discount'] ?? 0) + ($validated['tax'] ?? 0);
        $sale->balance_due = $sale->grand_total - ($validated['amount_paid'] ?? 0);
        $sale->updatePaymentStatus();
        $sale->save();

        // 5. ✅ Auto Accounting (Only Credit Account)
        $this->recordSaleAccounting($sale, $validated);
    });

    return redirect()->route('sales.index')
        ->with('success', 'Sale created successfully!');
}*/

/*public function store(Request $request)
{
    // ✅ Debug: Check incoming data
    \Log::info('Sale Request Data:', $request->all());

    $validated = $request->validate([
        'customer_id' => 'required|exists:customers,id',
        'date' => 'required|date',
        'delivery_date' => 'nullable|date|after_or_equal:date',
        'items' => 'required|array|min:1',
        'items.*.gas_product_id' => 'required|exists:gas_products,id',
        'items.*.quantity' => 'required|numeric|min:0.01',
        'items.*.price' => 'required|numeric|min:0',
        'cylinder_ids' => 'nullable|array',
        'cylinder_ids.*' => 'exists:cylinders,id',
        'cylinder_actions' => 'nullable|array',
        'cylinder_actions.*' => 'in:issue,sell',
        'cylinder_prices' => 'nullable|array',
        'cylinder_prices.*' => 'nullable|numeric|min:0',
        'security_deposit' => 'nullable|numeric|min:0',
        'payment_method' => 'required|in:cash,bank_transfer,cheque,online,credit',
        'credit_account_id' => 'required|exists:accounts,id',
        'discount' => 'nullable|numeric|min:0',
        'tax' => 'nullable|numeric|min:0',
        'amount_paid' => 'nullable|numeric|min:0',
        'notes' => 'nullable|string|max:1000'
    ]);

    // ✅ Debug: Check validated data
    \Log::info('Validated Data:', $validated);

    DB::transaction(function () use ($validated) {
        
        // 1. Create Sale with initial values
        $sale = Sale::create([
            'customer_id' => $validated['customer_id'],
            'date' => $validated['date'],
            'delivery_date' => $validated['delivery_date'] ?? null,
            'subtotal' => 0,
            'cylinder_deposit_total' => 0,
            'cylinder_sale_total' => 0,
            'discount' => $validated['discount'] ?? 0,
            'tax' => $validated['tax'] ?? 0,
            'grand_total' => 0,
            'amount_paid' => $validated['amount_paid'] ?? 0,
            'balance_due' => 0,
            'payment_status' => 'unpaid',
            'payment_method' => $validated['payment_method'],
            'status' => 'confirmed',
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id()
        ]);

        // 2. Save Sale Items
        $subtotal = 0;
        foreach ($validated['items'] as $itemData) {
            $total = $itemData['quantity'] * $itemData['price'];
            $subtotal += $total;
            
            $sale->items()->create([
                'gas_product_id' => $itemData['gas_product_id'],
                'quantity' => $itemData['quantity'],
                'price' => $itemData['price'],
                'total' => $total,
            ]);

            // Update gas stock
            $gasProduct = GasProduct::find($itemData['gas_product_id']);
            if ($gasProduct) {
                $gasProduct->decrement('current_stock', $itemData['quantity']);
            }
        }

        // 3. Handle Cylinders
        $cylinderDepositTotal = 0;
        $cylinderSaleTotal = 0;

        if (isset($validated['cylinder_ids']) && count($validated['cylinder_ids']) > 0) {
            foreach ($validated['cylinder_ids'] as $index => $cylinderId) {
                $cylinder = Cylinder::find($cylinderId);
                if (!$cylinder) continue;

                $action = $validated['cylinder_actions'][$index] ?? 'issue';
                $price = $validated['cylinder_prices'][$index] ?? 0;
                $quantity = 1;

                $availableQty = $cylinder->stock_quantity - ($cylinder->issued_quantity ?? 0);
                if ($availableQty < $quantity) {
                    throw new \Exception("Insufficient stock for cylinder {$cylinder->cylinder_number}.");
                }

                $cylinder->increment('issued_quantity', $quantity);
                $cylinder->updateStatus();

                $dbAction = $action === 'issue' ? 'issued' : 'sold';
                $cylinder->transactions()->create([
                    'customer_id' => $validated['customer_id'],
                    'user_id' => auth()->id(),
                    'transaction_type' => $dbAction,
                    'transaction_date' => now(),
                    'remarks' => "Against sale: {$sale->invoice_no}",
                    'reference_document' => $sale->invoice_no
                ]);

                $cylinder->issuedDetails()->create([
                    'customer_id' => $validated['customer_id'],
                    'quantity' => $quantity,
                    'issue_date' => now(),
                    'security_deposit' => $action === 'issue' ? ($validated['security_deposit'] ?? 0) : 0,
                    'reference_document' => $sale->invoice_no,
                    'status' => 'issued',
                    'created_by' => auth()->id()
                ]);

                if ($action === 'issue') {
                    $cylinderDepositTotal += $validated['security_deposit'] ?? 0;
                } else {
                    $cylinderSaleTotal += $price;
                }
            }
        }

        // ✅ 4. Calculate ALL totals properly
        $sale->subtotal = $subtotal;
        $sale->cylinder_deposit_total = $cylinderDepositTotal;
        $sale->cylinder_sale_total = $cylinderSaleTotal;
        
        // ✅ Grand Total = Subtotal + Deposit + Cylinder Sale - Discount + Tax
        $grandTotal = $subtotal + $cylinderDepositTotal + $cylinderSaleTotal - ($validated['discount'] ?? 0) + ($validated['tax'] ?? 0);
        $sale->grand_total = $grandTotal;
        
        // ✅ Amount Paid from form
        $amountPaid = $validated['amount_paid'] ?? 0;
        $sale->amount_paid = $amountPaid;
        
        // ✅ Balance Due
        $sale->balance_due = $grandTotal - $amountPaid;
        
        // ✅ Update Payment Status
        if ($amountPaid >= $grandTotal && $grandTotal > 0) {
            $sale->payment_status = 'paid';
        } elseif ($amountPaid > 0) {
            $sale->payment_status = 'partial';
        } else {
            $sale->payment_status = 'unpaid';
        }
        
        $sale->save();

        // ✅ Debug: Check final values
        \Log::info('Sale Final Data:', [
            'invoice_no' => $sale->invoice_no,
            'subtotal' => $sale->subtotal,
            'cylinder_deposit_total' => $sale->cylinder_deposit_total,
            'cylinder_sale_total' => $sale->cylinder_sale_total,
            'discount' => $sale->discount,
            'tax' => $sale->tax,
            'grand_total' => $sale->grand_total,
            'amount_paid' => $sale->amount_paid,
            'balance_due' => $sale->balance_due,
            'payment_status' => $sale->payment_status
        ]);

        // 5. Auto Accounting
        $this->recordSaleAccounting($sale, $validated);
    });

    return redirect()->route('sales.index')
        ->with('success', 'Sale created successfully!');
}*/

public function store(Request $request)
{
    $validated = $request->validate([
        'customer_id' => 'required|exists:customers,id',
        'date' => 'required|date',
        'delivery_date' => 'nullable|date|after_or_equal:date',
        'items' => 'required|array|min:1',
        'items.*.gas_product_id' => 'required|exists:gas_products,id',
        'items.*.quantity' => 'required|numeric|min:0.01',
        'items.*.price' => 'required|numeric|min:0',
        'cylinder_ids' => 'nullable|array',
        'cylinder_ids.*' => 'exists:cylinders,id',
        'cylinder_actions' => 'nullable|array',
        'cylinder_actions.*' => 'in:issue,sell',
        'cylinder_prices' => 'nullable|array',
        'cylinder_prices.*' => 'nullable|numeric|min:0',
        'security_deposit' => 'nullable|numeric|min:0',
        'payment_method' => 'required|in:cash,bank_transfer,cheque,online,credit',
        'credit_account_id' => 'required|exists:accounts,id',
        'discount' => 'nullable|numeric|min:0',
        'tax' => 'nullable|numeric|min:0',
        'amount_paid' => 'nullable|numeric|min:0',
        'notes' => 'nullable|string|max:1000'
    ]);

    DB::transaction(function () use ($validated) {
        
        // 1. Create Sale
        $sale = Sale::create([
            'customer_id' => $validated['customer_id'],
            'date' => $validated['date'],
            'delivery_date' => $validated['delivery_date'] ?? null,
            'subtotal' => 0,
            'cylinder_deposit_total' => 0,
            'cylinder_sale_total' => 0,
            'discount' => $validated['discount'] ?? 0,
            'tax' => $validated['tax'] ?? 0,
            'grand_total' => 0,
            'amount_paid' => $validated['amount_paid'] ?? 0,
            'balance_due' => 0,
            'payment_status' => 'unpaid',
            'payment_method' => $validated['payment_method'], // ✅ Add this
            'status' => 'confirmed',
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id()
        ]);

        // 2. Save Sale Items
        $subtotal = 0;
        foreach ($validated['items'] as $itemData) {
            $total = $itemData['quantity'] * $itemData['price'];
            $subtotal += $total;
            
            $sale->items()->create([
                'gas_product_id' => $itemData['gas_product_id'],
                'quantity' => $itemData['quantity'],
                'price' => $itemData['price'],
                'total' => $total,
            ]);

            // Update gas stock
            $gasProduct = GasProduct::find($itemData['gas_product_id']);
            if ($gasProduct) {
                $gasProduct->decrement('current_stock', $itemData['quantity']);
            }
        }

        // 3. Handle Cylinders
        $cylinderDepositTotal = 0;
        $cylinderSaleTotal = 0;

        if (isset($validated['cylinder_ids']) && count($validated['cylinder_ids']) > 0) {
            foreach ($validated['cylinder_ids'] as $index => $cylinderId) {
                $cylinder = Cylinder::find($cylinderId);
                if (!$cylinder) continue;

                $action = $validated['cylinder_actions'][$index] ?? 'issue';
                $price = $validated['cylinder_prices'][$index] ?? 0;
                $quantity = 1;

                $availableQty = $cylinder->stock_quantity - ($cylinder->issued_quantity ?? 0);
                if ($availableQty < $quantity) {
                    throw new \Exception("Insufficient stock for cylinder {$cylinder->cylinder_number}.");
                }

                $cylinder->increment('issued_quantity', $quantity);
                $cylinder->updateStatus();

                $dbAction = $action === 'issue' ? 'issued' : 'sold';
                $cylinder->transactions()->create([
                    'customer_id' => $validated['customer_id'],
                    'user_id' => auth()->id(),
                    'transaction_type' => $dbAction,
                    'transaction_date' => now(),
                    'remarks' => "Against sale: {$sale->invoice_no}",
                    'reference_document' => $sale->invoice_no
                ]);

                $cylinder->issuedDetails()->create([
                    'customer_id' => $validated['customer_id'],
                    'quantity' => $quantity,
                    'issue_date' => now(),
                    'security_deposit' => $action === 'issue' ? ($validated['security_deposit'] ?? 0) : 0,
                    'reference_document' => $sale->invoice_no,
                    'status' => 'issued',
                    'created_by' => auth()->id()
                ]);

                if ($action === 'issue') {
                    $cylinderDepositTotal += $validated['security_deposit'] ?? 0;
                } else {
                    $cylinderSaleTotal += $price;
                }
            }
        }

        // ✅ 4. Calculate ALL totals properly
        $sale->subtotal = $subtotal;
        $sale->cylinder_deposit_total = $cylinderDepositTotal;
        $sale->cylinder_sale_total = $cylinderSaleTotal;
        
        $grandTotal = $subtotal + $cylinderDepositTotal + $cylinderSaleTotal - ($validated['discount'] ?? 0) + ($validated['tax'] ?? 0);
        $sale->grand_total = $grandTotal;
        
        $amountPaid = $validated['amount_paid'] ?? 0;
        $sale->amount_paid = $amountPaid;
        $sale->balance_due = $grandTotal - $amountPaid;
        
        // ✅ Update Payment Status
        $sale->updatePaymentStatus();
        $sale->save();

        // 5. Auto Accounting
        $this->recordSaleAccounting($sale, $validated);
    });

    return redirect()->route('sales.index')
        ->with('success', 'Sale created successfully!');
}



/**
 * ✅ Record Sale Accounting (Only Credit Account)
 */
private function recordSaleAccounting($sale, $validated)
{
    try {
        $creditAccount = Account::find($validated['credit_account_id']);
        
        if (!$creditAccount) {
            throw new \Exception('Invalid credit account selected.');
        }

        // ✅ Debit Account: Auto-assigned based on payment status
        $debitAccountId = $sale->payment_status === 'paid' 
            ? Account::where('account_code', '1001')->first()->id  // Cash
            : Account::where('account_code', '1003')->first()->id; // Accounts Receivable

        // 1. Debit Entry (Cash or Accounts Receivable)
        AccountingEntry::create([
            'entry_no' => AccountingEntry::generateEntryNo(),
            'date' => $sale->date,
            'description' => "Sale: {$sale->invoice_no} - Debit",
            'transaction_type' => 'sale',
            'reference_type' => get_class($sale),
            'reference_id' => $sale->id,
            'account_id' => $debitAccountId,
            'opposite_account_id' => $creditAccount->id,
            'debit' => $sale->grand_total,
            'credit' => 0,
            'status' => 'approved',
            'created_by' => auth()->id(),
        ]);

        // 2. Credit Entry (Sales Revenue)
        AccountingEntry::create([
            'entry_no' => AccountingEntry::generateEntryNo(),
            'date' => $sale->date,
            'description' => "Sale: {$sale->invoice_no} - Credit",
            'transaction_type' => 'sale',
            'reference_type' => get_class($sale),
            'reference_id' => $sale->id,
            'account_id' => $creditAccount->id,
            'opposite_account_id' => $debitAccountId,
            'debit' => 0,
            'credit' => $sale->grand_total,
            'status' => 'approved',
            'created_by' => auth()->id(),
        ]);

        // Update balances
        $creditAccount->updateBalance();
        $debitAccount = Account::find($debitAccountId);
        if ($debitAccount) {
            $debitAccount->updateBalance();
        }

    } catch (\Exception $e) {
        Log::error('Sale accounting error: ' . $e->getMessage());
    }
}

    // ============================================
    // SHOW - View Sale
    // ============================================
    public function show(Sale $sale)
    {
        $sale->load(['customer', 'items.gasProduct', 'items.cylinder', 'creator', 'payments']);
        
        $journalEntries = $sale->journalEntries()
            ->with(['account', 'oppositeAccount'])
            ->get();

        $cylinderTransactions = $sale->cylinderTransactions()
            ->with(['cylinder', 'customer'])
            ->get();

        return view('sales.show', compact('sale', 'journalEntries', 'cylinderTransactions'));
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
        $availableCylinders = Cylinder::where('stock_quantity', '>', 0)
            ->where('issued_quantity', '<', DB::raw('stock_quantity'))
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

        // Similar to store but with update logic
        // ... (can be implemented similarly)

        return redirect()->route('sales.index')
            ->with('success', 'Sale updated successfully!');
    }

    // ============================================
    // DESTROY - Delete Sale
    // ============================================
    public function destroy(Sale $sale)
    {
        if ($sale->status !== 'draft') {
            return redirect()->route('sales.index')
                ->with('error', 'Only draft sales can be deleted.');
        }

        DB::transaction(function () use ($sale) {
            // Delete items
            $sale->items()->delete();
            // Delete sale
            $sale->delete();
        });

        return redirect()->route('sales.index')
            ->with('success', 'Sale deleted successfully!');
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
                $payment = $sale->recordPayment(
                    $request->amount,
                    $request->payment_method,
                    $request->payment_date,
                    $request->notes
                );

                // Auto Accounting for Payment
                $this->recordPaymentAccounting($sale, $request);
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
            'returned_gas_quantity' => 'nullable|numeric|min:0',
            'cylinder_condition' => 'required|in:good,damaged,expired',
            'notes' => 'nullable|string|max:500'
        ]);

        $cylinder = Cylinder::find($request->cylinder_id);

        DB::transaction(function () use ($request, $sale, $cylinder) {
            
            $cylinder->returnFromCustomer(
                $request->customer_id,
                1,
                $request->damage_charge ?? 0,
                $request->security_deposit_refund ?? 0,
                $request->notes
            );

            // Update sale deposit
            if ($request->security_deposit_refund > 0) {
                $sale->cylinder_deposit_total -= $request->security_deposit_refund;
                $sale->grand_total = $sale->subtotal + $sale->cylinder_deposit_total + $sale->cylinder_sale_total - $sale->discount + $sale->tax;
                $sale->balance_due = $sale->grand_total - $sale->amount_paid;
                $sale->save();
            }

            // Record damage charge
            if ($request->damage_charge > 0) {
                $this->accountingService->recordDamageCharge($sale, $request->damage_charge);
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Cylinder returned successfully!"
        ]);
    }

    // ============================================
    // GET CUSTOMER CYLINDERS (AJAX)
    // ============================================
    public function getCustomerCylinders(Customer $customer)
    {
        $cylinders = $customer->cylinders()
            ->where('status', 'issued')
            ->with(['gasProduct'])
            ->get()
            ->map(function ($cylinder) use ($customer) {
                $issuedDetail = $cylinder->issuedDetails()
                    ->where('customer_id', $customer->id)
                    ->where('status', 'issued')
                    ->first();

                return [
                    'id' => $cylinder->id,
                    'cylinder_number' => $cylinder->cylinder_number,
                    'gas_name' => $cylinder->gasProduct->name ?? 'N/A',
                    'issued_date' => $issuedDetail ? $issuedDetail->issue_date->format('d-m-Y') : 'N/A',
                    'days_out' => $issuedDetail ? $issuedDetail->issue_date->diffInDays(now()) : 0,
                    'security_deposit' => $issuedDetail ? $issuedDetail->security_deposit : 0,
                ];
            });

        return response()->json([
            'cylinders' => $cylinders,
            'total_deposit' => $customer->security_deposit,
            'count' => $cylinders->count()
        ]);
    }

    // ============================================
    // ACCOUNTING METHODS
    // ============================================
   /* private function recordSaleAccounting($sale, $validated)
    {
        try {
            $debitAccount = Account::find($validated['debit_account_id']);
            $creditAccount = Account::find($validated['credit_account_id']);

            if (!$debitAccount || !$creditAccount) {
                throw new \Exception('Invalid accounts selected.');
            }

            // 1. Debit Entry (Accounts Receivable or Cash)
            $accountId = $sale->payment_status === 'paid' 
                ? Account::where('account_code', '1001')->first()->id  // Cash
                : $debitAccount->id;

            AccountingEntry::create([
                'entry_no' => AccountingEntry::generateEntryNo(),
                'date' => $sale->date,
                'description' => "Sale: {$sale->invoice_no} - Debit",
                'transaction_type' => 'sale',
                'reference_type' => get_class($sale),
                'reference_id' => $sale->id,
                'account_id' => $accountId,
                'opposite_account_id' => $creditAccount->id,
                'debit' => $sale->grand_total,
                'credit' => 0,
                'status' => 'approved',
                'created_by' => auth()->id(),
            ]);

            // 2. Credit Entry (Sales Revenue)
            AccountingEntry::create([
                'entry_no' => AccountingEntry::generateEntryNo(),
                'date' => $sale->date,
                'description' => "Sale: {$sale->invoice_no} - Credit",
                'transaction_type' => 'sale',
                'reference_type' => get_class($sale),
                'reference_id' => $sale->id,
                'account_id' => $creditAccount->id,
                'opposite_account_id' => $accountId,
                'debit' => 0,
                'credit' => $sale->grand_total,
                'status' => 'approved',
                'created_by' => auth()->id(),
            ]);

            // Update balances
            $debitAccount->updateBalance();
            $creditAccount->updateBalance();

        } catch (\Exception $e) {
            Log::error('Sale accounting error: ' . $e->getMessage());
        }
    }*/

    private function recordPaymentAccounting($sale, $request)
    {
        try {
            $receivableAccount = Account::where('account_code', '1003')->first();
            $cashAccount = Account::where('account_code', '1001')->first();

            if ($receivableAccount && $cashAccount) {
                // Debit: Cash, Credit: Accounts Receivable
                AccountingEntry::create([
                    'entry_no' => AccountingEntry::generateEntryNo(),
                    'date' => $request->payment_date,
                    'description' => "Payment received for sale: {$sale->invoice_no}",
                    'transaction_type' => 'payment',
                    'reference_type' => get_class($sale),
                    'reference_id' => $sale->id,
                    'account_id' => $cashAccount->id,
                    'opposite_account_id' => $receivableAccount->id,
                    'debit' => $request->amount,
                    'credit' => 0,
                    'status' => 'approved',
                    'created_by' => auth()->id(),
                ]);

                AccountingEntry::create([
                    'entry_no' => AccountingEntry::generateEntryNo(),
                    'date' => $request->payment_date,
                    'description' => "Payment received for sale: {$sale->invoice_no}",
                    'transaction_type' => 'payment',
                    'reference_type' => get_class($sale),
                    'reference_id' => $sale->id,
                    'account_id' => $receivableAccount->id,
                    'opposite_account_id' => $cashAccount->id,
                    'debit' => 0,
                    'credit' => $request->amount,
                    'status' => 'approved',
                    'created_by' => auth()->id(),
                ]);

                $cashAccount->updateBalance();
                $receivableAccount->updateBalance();
            }
        } catch (\Exception $e) {
            Log::error('Payment accounting error: ' . $e->getMessage());
        }
    }
}