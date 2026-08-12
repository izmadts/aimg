<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\Cylinder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    /**
     * Display a listing of suppliers.
     */
    public function index(Request $request)
    {
        $query = Supplier::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('company_name', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('contact_person', 'LIKE', "%{$search}%")
                  ->orWhere('erp_supplier_id', 'LIKE', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->status == 'active');
        }

        // Sort
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $suppliers = $query->paginate(15)->withQueryString();

        // Get statistics
        $stats = [
            'total' => Supplier::count(),
            'active' => Supplier::where('is_active', true)->count(),
            'inactive' => Supplier::where('is_active', false)->count(),
            'with_pending_payments' => Supplier::withPendingPayments()->count(),
            'total_payable' => Supplier::all()->sum('total_payable'),
        ];

        return view('suppliers.index', compact('suppliers', 'stats'));
    }

    /**
     * Show the form for creating a new supplier.
     */
    public function create()
    {
        return view('suppliers.create');
    }

    /**
     * Store a newly created supplier in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'erp_supplier_id' => 'nullable|string|max:50|unique:suppliers',
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:suppliers',
            'address' => 'nullable|string|max:500',
            'ntn_number' => 'nullable|string|max:50',
            'cnic' => 'nullable|string|max:20|unique:suppliers',
            'contact_person' => 'nullable|string|max:100',
            'contact_person_phone' => 'nullable|string|max:20',
            'opening_balance' => 'nullable|numeric|min:0',
            'balance_type' => 'nullable|in:payable,receivable',
            'cylinder_deposit_paid' => 'nullable|numeric|min:0',
            'cylinder_return_days' => 'nullable|integer|min:1|max:365',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000'
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['opening_balance'] = $validated['opening_balance'] ?? 0;
        $validated['cylinder_deposit_paid'] = $validated['cylinder_deposit_paid'] ?? 0;
        $validated['cylinder_return_days'] = $validated['cylinder_return_days'] ?? 7;
        $validated['balance_type'] = $validated['balance_type'] ?? 'payable';

        $supplier = Supplier::create($validated);

        return redirect()->route('suppliers.index')
            ->with('success', "Supplier '{$supplier->name}' created successfully!");
    }

    /**
     * Display the specified supplier.
     */
    public function show(Supplier $supplier)
    {
        $supplier->load(['purchases' => function ($q) {
            $q->latest()->limit(10);
        }, 'cylinders' => function ($q) {
            $q->limit(10);
        }]);

        // Get statistics
        $stats = [
            'total_purchases' => $supplier->purchases()->count(),
            'total_purchase_amount' => $supplier->purchases()->where('status', '!=', 'cancelled')->sum('grand_total'),
            'total_cylinders' => $supplier->cylinders()->count(),
            'total_cylinder_transactions' => $supplier->cylinderTransactions()->count(),
            'pending_payable' => $supplier->total_payable,
        ];

        // Get recent purchases
        $recentPurchases = $supplier->purchases()
            ->with(['items.gasProduct'])
            ->latest()
            ->limit(10)
            ->get();

        // Get cylinder transactions
        $cylinderTransactions = $supplier->cylinderTransactions()
            ->with(['cylinder'])
            ->latest()
            ->limit(20)
            ->get();

        return view('suppliers.show', compact('supplier', 'stats', 'recentPurchases', 'cylinderTransactions'));
    }

    /**
     * Show the form for editing the specified supplier.
     */
    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified supplier in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'erp_supplier_id' => 'nullable|string|max:50|unique:suppliers,erp_supplier_id,' . $supplier->id,
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:suppliers,email,' . $supplier->id,
            'address' => 'nullable|string|max:500',
            'ntn_number' => 'nullable|string|max:50',
            'cnic' => 'nullable|string|max:20|unique:suppliers,cnic,' . $supplier->id,
            'contact_person' => 'nullable|string|max:100',
            'contact_person_phone' => 'nullable|string|max:20',
            'opening_balance' => 'nullable|numeric|min:0',
            'balance_type' => 'nullable|in:payable,receivable',
            'cylinder_deposit_paid' => 'nullable|numeric|min:0',
            'cylinder_return_days' => 'nullable|integer|min:1|max:365',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000'
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['opening_balance'] = $validated['opening_balance'] ?? 0;
        $validated['cylinder_deposit_paid'] = $validated['cylinder_deposit_paid'] ?? 0;
        $validated['cylinder_return_days'] = $validated['cylinder_return_days'] ?? 7;
        $validated['balance_type'] = $validated['balance_type'] ?? 'payable';

        $supplier->update($validated);

        return redirect()->route('suppliers.index')
            ->with('success', "Supplier '{$supplier->name}' updated successfully!");
    }

    /**
     * Remove the specified supplier from storage.
     */
    public function destroy(Supplier $supplier)
    {
        // Check if supplier has any purchases
        if ($supplier->purchases()->exists()) {
            return redirect()->route('suppliers.index')
                ->with('error', "Cannot delete '{$supplier->name}' because they have purchase records.");
        }

        // Check if supplier has any cylinders
        if ($supplier->cylinders()->exists()) {
            return redirect()->route('suppliers.index')
                ->with('error', "Cannot delete '{$supplier->name}' because they have cylinder records.");
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', "Supplier '{$supplier->name}' deleted successfully!");
    }

    /**
     * Toggle supplier status (active/inactive).
     */
    public function toggleStatus(Supplier $supplier)
    {
        $supplier->is_active = !$supplier->is_active;
        $supplier->save();

        $status = $supplier->is_active ? 'activated' : 'deactivated';

        return redirect()->route('suppliers.index')
            ->with('success', "Supplier '{$supplier->name}' {$status} successfully!");
    }

    /**
     * Display supplier statement.
     */
    public function statement(Supplier $supplier)
    {
        $purchases = $supplier->purchases()
            ->where('status', '!=', 'cancelled')
            ->orderBy('date', 'desc')
            ->get();

        $balance = $supplier->total_payable;

        return view('suppliers.statement', compact('supplier', 'purchases', 'balance'));
    }

    /**
     * Export suppliers to CSV.
     */
    public function export()
    {
        $suppliers = Supplier::where('is_active', true)->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="suppliers_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($suppliers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'ID', 'Name', 'Company', 'Phone', 'Email', 
                'Contact Person', 'NTN', 'CNIC', 'Balance', 'Balance Type', 'Status'
            ]);

            foreach ($suppliers as $supplier) {
                fputcsv($file, [
                    $supplier->id,
                    $supplier->name,
                    $supplier->company_name,
                    $supplier->phone,
                    $supplier->email,
                    $supplier->contact_person,
                    $supplier->ntn_number,
                    $supplier->cnic,
                    $supplier->opening_balance,
                    $supplier->balance_type,
                    $supplier->is_active ? 'Active' : 'Inactive'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get supplier statement for AJAX.
     */
    public function getStatement(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|integer',
        ]);

        $supplierId = $request->supplier_id;
        $supplier = Supplier::find($supplierId);

        if (!$supplier) {
            return response()->json(['success' => false, 'message' => 'Supplier not found']);
        }

        $purchases = $supplier->purchases()
            ->where('status', '!=', 'cancelled')
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($purchase) {
                return [
                    'id' => $purchase->id,
                    'invoice_no' => $purchase->purchase_invoice_no,
                    'date' => $purchase->date->format('d-m-Y'),
                    'total' => $purchase->grand_total,
                    'paid' => $purchase->amount_paid,
                    'balance' => $purchase->balance_due,
                    'status' => $purchase->status,
                    'payment_status' => $purchase->payment_status,
                    'url' => route('purchases.show', $purchase)
                ];
            });

        return response()->json([
            'success' => true,
            'supplier' => [
                'id' => $supplier->id,
                'name' => $supplier->name,
            ],
            'purchases' => $purchases,
            'total_purchases' => $purchases->sum('total'),
            'total_paid' => $purchases->sum('paid'),
            'total_balance' => $purchases->sum('balance'),
        ]);
    }


    /**
 * Display the specified supplier.
 */
/*public function show(Supplier $supplier)
{
    $supplier->load(['purchases' => function ($q) {
        $q->latest()->limit(10);
    }, 'cylinders' => function ($q) {
        $q->limit(10);
    }]);

    // Get statistics
    $stats = [
        'total_purchases' => $supplier->purchases()->count(),
        'total_purchase_amount' => $supplier->purchases()->where('status', '!=', 'cancelled')->sum('grand_total'),
        'total_cylinders' => $supplier->cylinders()->count(),
        'total_cylinder_transactions' => $supplier->cylinderTransactions()->count(),
        'pending_payable' => $supplier->total_payable,
    ];

    // Get recent purchases
    $recentPurchases = $supplier->purchases()
        ->with(['items.gasProduct'])
        ->latest()
        ->limit(10)
        ->get();

    // Get cylinder transactions
    $cylinderTransactions = $supplier->cylinderTransactions()
        ->with(['cylinder'])
        ->latest()
        ->limit(20)
        ->get();

    return view('suppliers.show', compact('supplier', 'stats', 'recentPurchases', 'cylinderTransactions'));
}*/
}