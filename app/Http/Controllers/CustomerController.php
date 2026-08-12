<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\Cylinder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index(Request $request)
    {
        $query = Customer::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('cnic', 'LIKE', "%{$search}%")
                  ->orWhere('erp_customer_id', 'LIKE', "%{$search}%");
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

        $customers = $query->paginate(15)->withQueryString();

        // Get statistics
        $stats = [
            'total' => Customer::count(),
            'active' => Customer::where('is_active', true)->count(),
            'inactive' => Customer::where('is_active', false)->count(),
            'with_issued_cylinders' => Customer::whereHas('cylinders', function ($q) {
                $q->where('status', 'issued');
            })->count(),
        ];

        return view('customers.index', compact('customers', 'stats'));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'erp_customer_id' => 'nullable|string|max:50|unique:customers',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:customers',
            'address' => 'nullable|string|max:500',
            'ntn_number' => 'nullable|string|max:50',
            'cnic' => 'nullable|string|max:20|unique:customers',
            'security_deposit' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000'
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['security_deposit'] = $validated['security_deposit'] ?? 0;

        $customer = Customer::create($validated);

        return redirect()->route('customers.index')
            ->with('success', "Customer '{$customer->name}' created successfully!");
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer)
    {
        // Load relationships
        $customer->load(['sales' => function ($q) {
            $q->latest()->limit(10);
        }, 'cylinders' => function ($q) {
            $q->where('status', 'issued');
        }]);

        // Get statistics
        $stats = [
            'total_sales' => $customer->sales()->count(),
            'total_sales_amount' => $customer->sales()->where('status', '!=', 'cancelled')->sum('grand_total'),
            'total_issued_cylinders' => $customer->cylinders()->where('status', 'issued')->count(),
            'total_cylinder_transactions' => $customer->cylinderTransactions()->count(),
            'pending_balance' => $customer->sales()->where('payment_status', '!=', 'paid')->sum('balance_due'),
        ];

        // Get cylinder transaction history
        $cylinderHistory = $customer->cylinderTransactions()
            ->with(['cylinder', 'user'])
            ->latest()
            ->limit(20)
            ->get();

        // Get recent sales
        $recentSales = $customer->sales()
            ->with(['items.gasProduct'])
            ->latest()
            ->limit(10)
            ->get();

        return view('customers.show', compact('customer', 'stats', 'cylinderHistory', 'recentSales'));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'erp_customer_id' => 'nullable|string|max:50|unique:customers,erp_customer_id,' . $customer->id,
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:customers,email,' . $customer->id,
            'address' => 'nullable|string|max:500',
            'ntn_number' => 'nullable|string|max:50',
            'cnic' => 'nullable|string|max:20|unique:customers,cnic,' . $customer->id,
            'security_deposit' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000'
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['security_deposit'] = $validated['security_deposit'] ?? 0;

        $customer->update($validated);

        return redirect()->route('customers.index')
            ->with('success', "Customer '{$customer->name}' updated successfully!");
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy(Customer $customer)
    {
        // Check if customer has any sales or issued cylinders
        if ($customer->sales()->exists()) {
            return redirect()->route('customers.index')
                ->with('error', "Cannot delete '{$customer->name}' because they have sales records.");
        }

        if ($customer->cylinders()->where('status', 'issued')->exists()) {
            return redirect()->route('customers.index')
                ->with('error', "Cannot delete '{$customer->name}' because they have issued cylinders.");
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', "Customer '{$customer->name}' deleted successfully!");
    }

    /**
     * Toggle customer status (active/inactive).
     */
    public function toggleStatus(Customer $customer)
    {
        $customer->is_active = !$customer->is_active;
        $customer->save();

        $status = $customer->is_active ? 'activated' : 'deactivated';

        return redirect()->route('customers.index')
            ->with('success', "Customer '{$customer->name}' {$status} successfully!");
    }

    /**
     * Display customer statement (sales history with payments).
     */
    public function statement(Customer $customer)
    {
        $sales = $customer->sales()
            ->where('status', '!=', 'cancelled')
            ->orderBy('date', 'desc')
            ->get();

        $balance = $customer->sales()->where('payment_status', '!=', 'paid')->sum('balance_due');

        return view('customers.statement', compact('customer', 'sales', 'balance'));
    }

    /**
     * Export customers to CSV.
     */
    public function export()
    {
        $customers = Customer::where('is_active', true)->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="customers_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($customers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Phone', 'Email', 'Address', 'NTN', 'CNIC', 'Security Deposit', 'Status']);

            foreach ($customers as $customer) {
                fputcsv($file, [
                    $customer->id,
                    $customer->name,
                    $customer->phone,
                    $customer->email,
                    $customer->address,
                    $customer->ntn_number,
                    $customer->cnic,
                    $customer->security_deposit,
                    $customer->is_active ? 'Active' : 'Inactive'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}