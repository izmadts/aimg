<?php

namespace App\Http\Controllers;

use App\Models\Cylinder;
use App\Models\GasProduct;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\Account;
use App\Models\AccountingEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CylinderController extends Controller
{
    // ============================================
    // INDEX - List Cylinders
    // ============================================
    public function index(Request $request)
    {
        $query = Cylinder::with(['gasProduct', 'currentCustomer', 'supplier']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('cylinder_number', 'LIKE', "%{$search}%")
                  ->orWhere('type', 'LIKE', "%{$search}%")
                  ->orWhere('manufacturer', 'LIKE', "%{$search}%")
                  ->orWhereHas('gasProduct', function ($sq) use ($search) {
                      $sq->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('gas_product_id')) {
            $query->where('gas_product_id', $request->gas_product_id);
        }

        $cylinders = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total' => Cylinder::count(),
            'in_house' => Cylinder::where('status', 'in_house')->count(),
            'partial_issued' => Cylinder::where('status', 'partial_issued')->count(),
            'all_issued' => Cylinder::where('status', 'all_issued')->count(),
            'issued' => Cylinder::where('status', 'issued')->count(),
            'sold' => Cylinder::where('status', 'sold')->count(),
            'under_maintenance' => Cylinder::where('status', 'under_maintenance')->count(),
            'scrapped' => Cylinder::where('status', 'scrapped')->count(),
            'out_of_stock' => Cylinder::where('status', 'out_of_stock')->count(),
            'total_stock' => Cylinder::sum('stock_quantity'),
            'total_issued' => Cylinder::sum('issued_quantity'),
            'total_value' => Cylinder::sum(DB::raw('purchase_price * stock_quantity')),
        ];

        $gasProducts = GasProduct::where('is_active', true)->get();

        return view('cylinders.index', compact('cylinders', 'stats', 'gasProducts'));
    }

    // ============================================
    // CREATE - Show Form
    // ============================================
    public function create()
    {
        $gasProducts = GasProduct::where('is_active', true)->get();
        $suppliers = Supplier::where('is_active', true)->get();
        $customers = Customer::where('is_active', true)->get();

        return view('cylinders.create', compact('gasProducts', 'suppliers', 'customers'));
    }

    // ============================================
    // STORE - Save Cylinder
    // ============================================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cylinder_number' => 'nullable|string|max:50|unique:cylinders,cylinder_number',
            'gas_product_id' => 'required|exists:gas_products,id',
            'type' => 'required|string|max:50',
            'manufacturer' => 'nullable|string|max:100',
            'tare_weight' => 'nullable|numeric|min:0',
            'capacity' => 'required|numeric|min:0.01',
            'current_gas_quantity' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:1',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'purchase_date' => 'nullable|date',
            'last_hydro_test_date' => 'nullable|date',
            'next_hydro_test_date' => 'nullable|date|after:last_hydro_test_date',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validated['sale_price'] < $validated['purchase_price']) {
            return redirect()->back()
                ->with('error', 'Sale price must be greater than purchase price')
                ->withInput();
        }

        $validated['issued_quantity'] = 0;
        $validated['status'] = 'in_house';

        DB::transaction(function () use ($validated) {
            $cylinder = Cylinder::create($validated);

            $cylinder->transactions()->create([
                'transaction_type' => 'purchased',
                'transaction_date' => $validated['purchase_date'] ?? now(),
                'gas_quantity_at_transaction' => $validated['current_gas_quantity'] ?? 0,
                'remarks' => 'Cylinder added to system',
                'user_id' => auth()->id()
            ]);

            $this->recordCylinderAsset($cylinder);

            if ($validated['current_gas_quantity'] > 0) {
                $gasProduct = GasProduct::find($validated['gas_product_id']);
                if ($gasProduct) {
                    $gasProduct->increment('current_stock', $validated['current_gas_quantity']);
                }
            }
        });

        return redirect()->route('cylinders.index')
            ->with('success', 'Cylinder created successfully!');
    }

    // ============================================
    // SHOW - View Cylinder
    // ============================================
    public function show(Cylinder $cylinder)
    {
        $cylinder->load(['gasProduct', 'currentCustomer', 'supplier', 'purchase']);

        $transactions = $cylinder->transactions()
            ->with(['customer', 'supplier', 'user'])
            ->latest()
            ->paginate(20);

        $issuedDetails = $cylinder->issuedDetails()
            ->with('customer')
            ->where('status', 'issued')
            ->get();

        $customerHistory = $cylinder->issuedDetails()
            ->with('customer')
            ->orderBy('created_at', 'desc')
            ->get();

        $customers = Customer::where('is_active', true)->get();

        return view('cylinders.show', compact(
            'cylinder',
            'transactions',
            'issuedDetails',
            'customerHistory',
            'customers'
        ));
    }

    // ============================================
    // EDIT - Show Edit Form
    // ============================================
    public function edit(Cylinder $cylinder)
    {
        if (in_array($cylinder->status, ['issued', 'sold', 'all_issued'])) {
            return redirect()->route('cylinders.show', $cylinder)
                ->with('error', 'Cannot edit issued or sold cylinders.');
        }

        $gasProducts = GasProduct::where('is_active', true)->get();
        $suppliers = Supplier::where('is_active', true)->get();
        $customers = Customer::where('is_active', true)->get();

        return view('cylinders.edit', compact('cylinder', 'gasProducts', 'suppliers', 'customers'));
    }

    // ============================================
    // UPDATE - Update Cylinder
    // ============================================
    public function update(Request $request, Cylinder $cylinder)
    {
        if (in_array($cylinder->status, ['issued', 'sold', 'all_issued'])) {
            return redirect()->route('cylinders.show', $cylinder)
                ->with('error', 'Cannot update issued or sold cylinders.');
        }

        $validated = $request->validate([
            'cylinder_number' => 'required|string|max:50|unique:cylinders,cylinder_number,' . $cylinder->id,
            'gas_product_id' => 'required|exists:gas_products,id',
            'type' => 'required|string|max:50',
            'manufacturer' => 'nullable|string|max:100',
            'tare_weight' => 'nullable|numeric|min:0',
            'capacity' => 'required|numeric|min:0.01',
            'current_gas_quantity' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'purchase_date' => 'nullable|date',
            'last_hydro_test_date' => 'nullable|date',
            'next_hydro_test_date' => 'nullable|date|after:last_hydro_test_date',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validated['sale_price'] < $validated['purchase_price']) {
            return redirect()->back()
                ->with('error', 'Sale price must be greater than purchase price')
                ->withInput();
        }

        DB::transaction(function () use ($validated, $cylinder) {
            $oldStatus = $cylinder->status;
            $oldGasProductId = $cylinder->gas_product_id;
            $oldGasQuantity = $cylinder->current_gas_quantity;

            $cylinder->update($validated);
            $cylinder->updateStatus();

            if ($oldStatus === 'in_house' && $oldGasQuantity > 0) {
                $oldGasProduct = GasProduct::find($oldGasProductId);
                if ($oldGasProduct) {
                    $oldGasProduct->decrement('current_stock', $oldGasQuantity);
                }
            }

            if ($cylinder->status === 'in_house' && $cylinder->current_gas_quantity > 0) {
                $newGasProduct = GasProduct::find($cylinder->gas_product_id);
                if ($newGasProduct) {
                    $newGasProduct->increment('current_stock', $cylinder->current_gas_quantity);
                }
            }
        });

        return redirect()->route('cylinders.index')
            ->with('success', 'Cylinder updated successfully!');
    }

    // ============================================
    // DESTROY - Delete Cylinder
    // ============================================
    public function destroy(Cylinder $cylinder)
    {
        if (in_array($cylinder->status, ['issued', 'sold', 'all_issued'])) {
            return redirect()->route('cylinders.index')
                ->with('error', 'Cannot delete issued or sold cylinders.');
        }

        DB::transaction(function () use ($cylinder) {
            if ($cylinder->current_gas_quantity > 0) {
                $gasProduct = GasProduct::find($cylinder->gas_product_id);
                if ($gasProduct) {
                    $gasProduct->decrement('current_stock', $cylinder->current_gas_quantity);
                }
            }

            $cylinder->transactions()->delete();
            $cylinder->issuedDetails()->delete();
            $cylinder->delete();
        });

        return redirect()->route('cylinders.index')
            ->with('success', 'Cylinder deleted successfully!');
    }

    // ============================================
    // STOCK - Stock Management
    // ============================================
    public function stock(Request $request)
    {
        $query = Cylinder::with(['gasProduct', 'currentCustomer']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('cylinder_number', 'LIKE', "%{$search}%")
                  ->orWhere('type', 'LIKE', "%{$search}%")
                  ->orWhereHas('gasProduct', function ($sq) use ($search) {
                      $sq->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $cylinders = $query->paginate(20);

        $stockSummary = [
            'total_types' => Cylinder::count(),
            'total_stock' => Cylinder::sum('stock_quantity'),
            'total_issued' => Cylinder::sum('issued_quantity'),
            'available' => Cylinder::sum(DB::raw('stock_quantity - issued_quantity')),
            'total_value' => Cylinder::sum(DB::raw('purchase_price * stock_quantity')),
            'in_house' => Cylinder::where('status', 'in_house')->count(),
            'partial_issued' => Cylinder::where('status', 'partial_issued')->count(),
            'all_issued' => Cylinder::where('status', 'all_issued')->count(),
        ];

        $gasProducts = GasProduct::where('is_active', true)->get();
        $customers = Customer::where('is_active', true)->get();

        return view('cylinders.stock', compact('cylinders', 'stockSummary', 'gasProducts', 'customers'));
    }

    // ============================================
    // UPDATE STOCK (AJAX)
    // ============================================
    public function updateStock(Request $request)
    {
        $request->validate([
            'cylinder_id' => 'required|exists:cylinders,id',
            'action' => 'required|in:add,remove,set',
            'quantity' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:500'
        ]);

        $cylinder = Cylinder::find($request->cylinder_id);

        try {
            switch ($request->action) {
                case 'add':
                    $cylinder->addStock($request->quantity);
                    $message = "Added {$request->quantity} pieces to stock.";
                    break;
                case 'remove':
                    $cylinder->removeStock($request->quantity);
                    $message = "Removed {$request->quantity} pieces from stock.";
                    break;
                case 'set':
                    $cylinder->update(['stock_quantity' => $request->quantity]);
                    $cylinder->updateStatus();
                    $message = "Set stock to {$request->quantity} pieces.";
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'stock_quantity' => $cylinder->stock_quantity,
                'issued_quantity' => $cylinder->issued_quantity,
                'available_quantity' => $cylinder->available_quantity,
                'status' => $cylinder->status,
                'status_label' => $cylinder->status_label
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    // ============================================
    // ISSUE CYLINDER (AJAX)
    // ============================================
    public function issue(Request $request)
    {
        $request->validate([
            'cylinder_id' => 'required|exists:cylinders,id',
            'customer_id' => 'required|exists:customers,id',
            'security_deposit' => 'nullable|numeric|min:0',
            'gas_quantity' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500'
        ]);

        $cylinder = Cylinder::find($request->cylinder_id);

        if (!$cylinder->is_in_stock) {
            return response()->json([
                'success' => false,
                'message' => 'Cylinder is not available for issue.'
            ], 400);
        }

        try {
            $cylinder->issueToCustomer(
                $request->customer_id,
                1,
                $request->security_deposit ?? 0,
                $request->notes
            );

            return response()->json([
                'success' => true,
                'message' => "Cylinder issued successfully!",
                'available_quantity' => $cylinder->available_quantity
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    // ============================================
    // RETURN CYLINDER (AJAX)
    // ============================================
    public function returnCylinder(Request $request)
    {
        $request->validate([
            'cylinder_id' => 'required|exists:cylinders,id',
            'customer_id' => 'required|exists:customers,id',
            'damage_charge' => 'nullable|numeric|min:0',
            'security_deposit_refund' => 'nullable|numeric|min:0',
            'cylinder_condition' => 'required|in:good,damaged,expired',
            'notes' => 'nullable|string|max:500'
        ]);

        $cylinder = Cylinder::find($request->cylinder_id);

        try {
            $cylinder->returnFromCustomer(
                $request->customer_id,
                1,
                $request->damage_charge ?? 0,
                $request->security_deposit_refund ?? 0,
                $request->notes
            );

            return response()->json([
                'success' => true,
                'message' => "Cylinder returned successfully!",
                'available_quantity' => $cylinder->available_quantity
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    // ============================================
    // GET AVAILABLE CYLINDERS (AJAX)
    // ============================================
    public function available(Request $request)
    {
        $cylinders = Cylinder::where('stock_quantity', '>', 0)
            ->where('issued_quantity', '<', DB::raw('stock_quantity'))
            ->with('gasProduct')
            ->when($request->filled('gas_product_id'), function ($q) use ($request) {
                return $q->where('gas_product_id', $request->gas_product_id);
            })
            ->get()
            ->map(function ($cylinder) {
                return [
                    'id' => $cylinder->id,
                    'cylinder_number' => $cylinder->cylinder_number,
                    'gasProduct' => $cylinder->gasProduct,
                    'type' => $cylinder->type,
                    'stock_quantity' => $cylinder->stock_quantity,
                    'issued_quantity' => $cylinder->issued_quantity,
                    'available_quantity' => $cylinder->available_quantity,
                    'sale_price' => $cylinder->sale_price,
                    'purchase_price' => $cylinder->purchase_price,
                    'status' => $cylinder->status,
                ];
            });

        return response()->json($cylinders);
    }

    // ============================================
    // GET CUSTOMER OUTSTANDING (AJAX)
    // ============================================
    public function customerOutstanding(Customer $customer)
    {
        $cylinders = $customer->cylinders()
            ->where('status', 'issued')
            ->with(['gasProduct'])
            ->get()
            ->map(function ($cylinder) {
                $issuedDetail = $cylinder->issuedDetails()
                    ->where('customer_id', $customer->id)
                    ->where('status', 'issued')
                    ->first();

                return [
                    'id' => $cylinder->id,
                    'cylinder_number' => $cylinder->cylinder_number,
                    'gas_product' => $cylinder->gasProduct,
                    'type' => $cylinder->type,
                    'issued_date' => $issuedDetail ? $issuedDetail->issue_date->format('d-m-Y') : 'N/A',
                    'days_out' => $issuedDetail ? $issuedDetail->issue_date->diffInDays(now()) : 0,
                    'security_deposit' => $issuedDetail ? $issuedDetail->security_deposit : 0,
                    'quantity' => $issuedDetail ? $issuedDetail->quantity : 1,
                ];
            });

        return response()->json([
            'cylinders' => $cylinders,
            'total_deposit' => $customer->security_deposit,
            'count' => $cylinders->count()
        ]);
    }

    // ============================================
    // EXPORT CYLINDERS
    // ============================================
    public function export()
    {
        $cylinders = Cylinder::with(['gasProduct', 'currentCustomer'])->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="cylinders_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($cylinders) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Cylinder #', 'Gas', 'Type', 'Stock', 'Issued',
                'Available', 'Purchase Price', 'Sale Price', 'Status', 'Location'
            ]);

            foreach ($cylinders as $cylinder) {
                fputcsv($file, [
                    $cylinder->cylinder_number,
                    $cylinder->gasProduct->name ?? 'N/A',
                    $cylinder->type,
                    $cylinder->stock_quantity,
                    $cylinder->issued_quantity,
                    $cylinder->available_quantity,
                    $cylinder->purchase_price,
                    $cylinder->sale_price,
                    $cylinder->status_label,
                    $cylinder->currentCustomer->name ?? ($cylinder->supplier->name ?? 'In House'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ============================================
    // ACCOUNTING HELPER
    // ============================================
    private function recordCylinderAsset($cylinder)
    {
        try {
            $assetAccount = Account::where('account_code', '1004')->first();
            $cashAccount = Account::where('account_code', '1001')->first();

            if ($assetAccount && $cashAccount) {
                $totalValue = $cylinder->purchase_price * $cylinder->stock_quantity;

                AccountingEntry::create([
                    'entry_no' => AccountingEntry::generateEntryNo(),
                    'date' => now(),
                    'description' => "Cylinder Asset: {$cylinder->cylinder_number}",
                    'transaction_type' => 'purchase',
                    'reference_type' => get_class($cylinder),
                    'reference_id' => $cylinder->id,
                    'account_id' => $assetAccount->id,
                    'opposite_account_id' => $cashAccount->id,
                    'debit' => $totalValue,
                    'credit' => 0,
                    'status' => 'approved',
                    'created_by' => auth()->id(),
                ]);

                $assetAccount->updateBalance();
                $cashAccount->updateBalance();
            }
        } catch (\Exception $e) {
            Log::error('Cylinder asset accounting error: ' . $e->getMessage());
        }
    }
}