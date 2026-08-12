<?php

namespace App\Http\Controllers;

use App\Models\Cylinder;
use App\Models\CylinderIssuedDetail;
use App\Models\CylinderType;
use App\Models\GasProduct;
use App\Models\Supplier;
use App\Models\Customer;
use App\Http\Requests\StoreCylinderRequest;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CylinderController extends Controller
{
    protected $accountingService;

    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    // ============================================
    // INDEX - List Cylinders
    // ============================================
    public function index(Request $request)
    {
        $query = Cylinder::with(['gasProduct', 'supplier']);

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
            'under_maintenance' => Cylinder::where('status', 'under_maintenance')->count(),
            'scrapped' => Cylinder::where('status', 'scrapped')->count(),
            'out_of_stock' => Cylinder::where('status', 'out_of_stock')->count(),
            'total_stock' => Cylinder::sum('stock_quantity'),
            'total_issued' => Cylinder::sum('issued_quantity'),
            'total_value' => Cylinder::sum(DB::raw('purchase_price * stock_quantity')),
        ];

        $gasProducts = GasProduct::where('is_active', true)->get();
        $customers = Customer::where('is_active', true)->get();

        return view('cylinders.index', compact('cylinders', 'stats', 'gasProducts', 'customers'));
    }

    // ============================================
    // CREATE - Show Form
    // ============================================
    public function create()
    {
        $gasProducts = GasProduct::where('is_active', true)->get();
        $suppliers = Supplier::where('is_active', true)->get();
        $cylinderTypes = CylinderType::active()->orderBy('name')->get();

        return view('cylinders.create', compact('gasProducts', 'suppliers', 'cylinderTypes'));
    }

    // ============================================
    // STORE - Save Cylinder Type
    // ============================================
    public function store(StoreCylinderRequest $request)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $cylinder = Cylinder::create($validated);

            $cylinder->transactions()->create([
                'transaction_type' => 'purchased',
                'transaction_date' => $validated['purchase_date'] ?? now(),
                'remarks' => "Registered with {$cylinder->stock_quantity} piece(s) in stock",
                'user_id' => auth()->id()
            ]);

            $totalValue = round(($validated['purchase_price'] ?? 0) * ($validated['stock_quantity'] ?? 0), 2);
            $this->accountingService->recordCylinderAsset($cylinder, $totalValue, 'cash');
        });

        return redirect()->route('cylinders.index')
            ->with('success', 'Cylinder type created successfully!');
    }

    // ============================================
    // SHOW - View Cylinder
    // ============================================
    public function show(Cylinder $cylinder)
    {
        $cylinder->load(['gasProduct', 'supplier']);

        $transactions = $cylinder->transactions()
            ->with(['customer', 'user'])
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
        if ($cylinder->issued_quantity > 0) {
            return redirect()->route('cylinders.show', $cylinder)
                ->with('error', 'Cannot edit a cylinder type while units are issued to customers.');
        }

        $gasProducts = GasProduct::where('is_active', true)->get();
        $suppliers = Supplier::where('is_active', true)->get();
        $cylinderTypes = CylinderType::active()->orderBy('name')->get();

        // Keep the cylinder's current type selectable even if it was since deactivated/renamed away.
        if ($cylinder->type && ! $cylinderTypes->contains('name', $cylinder->type)) {
            $cylinderTypes->push(new CylinderType(['name' => $cylinder->type, 'price_premium' => 1500]));
        }

        return view('cylinders.edit', compact('cylinder', 'gasProducts', 'suppliers', 'cylinderTypes'));
    }

    // ============================================
    // UPDATE - Update Cylinder
    // ============================================
    public function update(StoreCylinderRequest $request, Cylinder $cylinder)
    {
        if ($cylinder->issued_quantity > 0) {
            return redirect()->route('cylinders.show', $cylinder)
                ->with('error', 'Cannot update a cylinder type while units are issued to customers.');
        }

        $cylinder->update($request->validated());
        $cylinder->updateStatus();

        return redirect()->route('cylinders.index')
            ->with('success', 'Cylinder updated successfully!');
    }

    // ============================================
    // DESTROY - Delete Cylinder
    // ============================================
    public function destroy(Cylinder $cylinder)
    {
        if ($cylinder->issued_quantity > 0) {
            return redirect()->route('cylinders.index')
                ->with('error', 'Cannot delete a cylinder type while units are issued to customers.');
        }

        DB::transaction(function () use ($cylinder) {
            $cylinder->transactions()->delete();
            $cylinder->issuedDetails()->delete();
            $cylinder->delete();
        });

        return redirect()->route('cylinders.index')
            ->with('success', 'Cylinder deleted successfully!');
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
                    if ($request->quantity < $cylinder->issued_quantity) {
                        throw new \Exception("Cannot set stock below the {$cylinder->issued_quantity} pieces currently issued.");
                    }
                    $cylinder->update(['stock_quantity' => $request->quantity]);
                    $cylinder->updateStatus();
                    $message = "Set stock to {$request->quantity} pieces.";
                    break;
                default:
                    throw new \Exception('Invalid stock action.');
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
            'notes' => 'nullable|string|max:500'
        ]);

        $cylinder = Cylinder::find($request->cylinder_id);

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
            'quantity' => 'nullable|integer|min:1',
            'damage_charge' => 'nullable|numeric|min:0',
            'security_deposit_refund' => 'nullable|numeric|min:0',
            'cylinder_condition' => 'required|in:good,damaged,expired',
            'notes' => 'nullable|string|max:500'
        ]);

        $cylinder = Cylinder::find($request->cylinder_id);

        try {
            DB::transaction(function () use ($request, $cylinder) {
                $cylinder->returnFromCustomer(
                    $request->customer_id,
                    $request->quantity ?? 1,
                    $request->damage_charge ?? 0,
                    $request->security_deposit_refund ?? 0,
                    $request->notes,
                    $request->cylinder_condition
                );
            });

            return response()->json([
                'success' => true,
                'message' => "Cylinder returned successfully!",
                'available_quantity' => $cylinder->fresh()->available_quantity
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
        $cylinders = Cylinder::whereColumn('stock_quantity', '>', 'issued_quantity')
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
        $details = $customer->activeCylinderIssues()->with('cylinder.gasProduct')->get();

        $cylinders = $details->map(function ($detail) {
            return [
                'id' => $detail->cylinder_id,
                'cylinder_number' => $detail->cylinder->cylinder_number ?? 'N/A',
                'gas_product' => $detail->cylinder->gasProduct ?? null,
                'type' => $detail->cylinder->type ?? 'N/A',
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

    // ============================================
    // EXPORT CYLINDERS
    // ============================================
    public function export()
    {
        $cylinders = Cylinder::with(['gasProduct', 'supplier'])->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="cylinders_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($cylinders) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Cylinder #', 'Gas', 'Type', 'Stock', 'Issued',
                'Available', 'Purchase Price', 'Sale Price', 'Status', 'Supplier'
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
                    $cylinder->supplier->name ?? 'N/A',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
