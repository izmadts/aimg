<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Supplier;
use App\Models\GasProduct;
use App\Models\Cylinder;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Transaction;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        $currentMonth = now()->startOfMonth();
        $currentYear = now()->year;

        // ============================================
        // 1. QUICK STATS
        // ============================================
        $stats = (object) [
            'total_customers' => Customer::where('is_active', true)->count(),
            'total_suppliers' => Supplier::where('is_active', true)->count(),
            'total_cylinders' => Cylinder::count(),
            'issued_cylinders' => Cylinder::where('status', 'issued')->count(),
            'available_cylinders' => Cylinder::whereIn('status', ['in_house_empty', 'in_house_filled'])->count(),
            
            // Today's Sales
            'today_sales' => Sale::whereDate('date', $today)->where('status', '!=', 'cancelled')->sum('grand_total'),
            'today_sales_count' => Sale::whereDate('date', $today)->where('status', '!=', 'cancelled')->count(),
            
            // Month's Sales
            'month_sales' => Sale::where('date', '>=', $currentMonth)->where('status', '!=', 'cancelled')->sum('grand_total'),
            
            // Gas Stock Value
            'gas_stock_value' => GasProduct::where('is_active', true)->get()->sum(function ($p) { 
                return $p->current_stock * $p->purchase_price; 
            }),
            
            // Cylinder Asset Value
            'cylinder_asset_value' => Cylinder::whereNotIn('status', ['sold', 'scrapped'])->sum('purchase_price'),
            
            // Pending Receivables & Payables
            'pending_receivables' => Sale::where('payment_status', '!=', 'paid')->where('status', '!=', 'cancelled')->sum('balance_due'),
            'pending_payables' => Purchase::where('payment_status', '!=', 'paid')->where('status', '!=', 'cancelled')->sum('balance_due'),
        ];

        // ============================================
        // 2. INCOME & EXPENSE (Using Transactions)
        // ============================================
        $incomeExpense = $this->getIncomeExpense();

        // ============================================
        // 3. ISSUED CYLINDERS WITH CUSTOMER DETAILS
        // ============================================
        $issuedCylinders = Cylinder::with(['currentCustomer', 'gasProduct'])
            ->where('status', 'issued')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($cylinder) {
                $lastIssue = $cylinder->transactions()
                    ->where('transaction_type', 'issued')
                    ->latest()
                    ->first();
                
                return (object) [
                    'id' => $cylinder->id,
                    'cylinder_number' => $cylinder->cylinder_number,
                    'gas_name' => $cylinder->gasProduct->name ?? 'N/A',
                    'customer_name' => $cylinder->currentCustomer->name ?? 'N/A',
                    'customer_phone' => $cylinder->currentCustomer->phone ?? 'N/A',
                    'issued_date' => $lastIssue ? $lastIssue->created_at->format('d-m-Y') : 'N/A',
                    'days_out' => $lastIssue ? $lastIssue->created_at->diffInDays(now()) : 0,
                    'security_deposit' => $lastIssue ? $lastIssue->security_deposit_charged : 0,
                ];
            });

        // ============================================
        // 4. CUSTOMER LIST WITH ISSUED CYLINDERS
        // ============================================
        $customersWithCylinders = Customer::whereHas('cylinders', function ($q) {
            $q->where('status', 'issued');
        })
        ->with(['cylinders' => function ($q) {
            $q->where('status', 'issued')->with('gasProduct');
        }])
        ->get()
        ->map(function ($customer) {
            return (object) [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'address' => $customer->address,
                'issued_cylinders' => $customer->cylinders->map(function ($cyl) {
                    return (object) [
                        'number' => $cyl->cylinder_number,
                        'gas' => $cyl->gasProduct->name ?? 'N/A',
                        'days_out' => $cyl->updated_at->diffInDays(now()),
                    ];
                }),
                'total_issued' => $customer->cylinders->count(),
                'total_deposit' => $customer->security_deposit,
                'pending_balance' => $customer->sales()
                    ->where('payment_status', '!=', 'paid')
                    ->where('status', '!=', 'cancelled')
                    ->sum('balance_due'),
            ];
        });

        // ============================================
        // 5. MONTHLY REVENUE CHART
        // ============================================
        $monthlyRevenue = $this->getMonthlyRevenue();

        // ============================================
        // 6. CYLINDER MOVEMENT CHART
        // ============================================
        $cylinderMovement = $this->getCylinderMovement();

        // ============================================
        // 7. RECENT ACTIVITY
        // ============================================
        $recentActivity = $this->getRecentActivity();

        // ============================================
        // 8. LOW STOCK ALERT
        // ============================================
        $lowStockProducts = GasProduct::where('current_stock', '<=', DB::raw('minimum_stock_level'))
            ->where('is_active', true)
            ->get();

        // ============================================
        // 9. PENDING RETURNS
        // ============================================
        $pendingReturns = Cylinder::where('status', 'issued')
            ->where('updated_at', '<', now()->subDays(7))
            ->with(['currentCustomer', 'gasProduct'])
            ->get()
            ->groupBy('current_customer_id')
            ->map(function ($cylinders) {
                $customer = $cylinders->first()->currentCustomer;
                return (object) [
                    'customer_name' => $customer ? $customer->name : 'Unknown',
                    'customer_phone' => $customer ? $customer->phone : 'N/A',
                    'cylinder_count' => $cylinders->count(),
                    'cylinders' => $cylinders->pluck('cylinder_number')->join(', '),
                    'days_out' => $cylinders->first()->updated_at->diffInDays(now()),
                    'deposit_held' => $customer ? $customer->security_deposit : 0,
                ];
            })
            ->sortByDesc('days_out')
            ->take(10);

        return view('dashboard', compact(
            'stats',
            'incomeExpense',
            'issuedCylinders',
            'customersWithCylinders',
            'monthlyRevenue',
            'cylinderMovement',
            'recentActivity',
            'lowStockProducts',
            'pendingReturns'
        ));
    }

    /**
     * Get Income & Expense data
     */
    private function getIncomeExpense()
    {
        $today = now()->toDateString();
        $currentMonth = now()->startOfMonth();
        $currentYear = now()->year;

        // Total Income (debit from income accounts)
        $totalIncome = Transaction::income()
            ->where('status', 'approved')
            ->sum('debit');

        // Total Expense (credit from expense accounts)
        $totalExpense = Transaction::expense()
            ->where('status', 'approved')
            ->sum('credit');

        // Today's Income
        $todayIncome = Transaction::income()
            ->whereDate('date', $today)
            ->where('status', 'approved')
            ->sum('debit');

        // Today's Expense
        $todayExpense = Transaction::expense()
            ->whereDate('date', $today)
            ->where('status', 'approved')
            ->sum('credit');

        // This Month Income
        $monthIncome = Transaction::income()
            ->where('date', '>=', $currentMonth)
            ->where('status', 'approved')
            ->sum('debit');

        // This Month Expense
        $monthExpense = Transaction::expense()
            ->where('date', '>=', $currentMonth)
            ->where('status', 'approved')
            ->sum('credit');

        // Income by Account (Top 5)
        $incomeByAccount = Transaction::income()
            ->where('status', 'approved')
            ->whereYear('date', $currentYear)
            ->select('account_id', DB::raw('SUM(debit) as total'))
            ->groupBy('account_id')
            ->with('account')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return (object) [
                    'account_name' => $item->account->account_name ?? 'N/A',
                    'total' => $item->total
                ];
            });

        // Expense by Account (Top 5)
        $expenseByAccount = Transaction::expense()
            ->where('status', 'approved')
            ->whereYear('date', $currentYear)
            ->select('account_id', DB::raw('SUM(credit) as total'))
            ->groupBy('account_id')
            ->with('account')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return (object) [
                    'account_name' => $item->account->account_name ?? 'N/A',
                    'total' => $item->total
                ];
            });

        return (object) [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_profit' => $totalIncome - $totalExpense,
            'today_income' => $todayIncome,
            'today_expense' => $todayExpense,
            'month_income' => $monthIncome,
            'month_expense' => $monthExpense,
            'income_by_account' => $incomeByAccount,
            'expense_by_account' => $expenseByAccount,
        ];
    }

    /**
     * Get Monthly Revenue for Chart
     */
    private function getMonthlyRevenue()
    {
        $months = collect();
        $currentYear = now()->year;
        
        for ($month = 1; $month <= 12; $month++) {
            $startDate = now()->setDate($currentYear, $month, 1)->startOfMonth();
            $endDate = now()->setDate($currentYear, $month, 1)->endOfMonth();
            
            // Income from transactions
            $income = Transaction::income()
                ->whereBetween('date', [$startDate, $endDate])
                ->where('status', 'approved')
                ->sum('debit');
            
            // Expense from transactions
            $expense = Transaction::expense()
                ->whereBetween('date', [$startDate, $endDate])
                ->where('status', 'approved')
                ->sum('credit');
            
            // Sales revenue (for comparison)
            $salesRevenue = Sale::whereBetween('date', [$startDate, $endDate])
                ->where('status', '!=', 'cancelled')
                ->sum('grand_total');
            
            $months->push([
                'month' => $startDate->format('M'),
                'income' => $income,
                'expense' => $expense,
                'profit' => $income - $expense,
                'sales' => $salesRevenue,
                'count' => Sale::whereBetween('date', [$startDate, $endDate])
                    ->where('status', '!=', 'cancelled')
                    ->count()
            ]);
        }
        
        return $months;
    }

    /**
     * Get Cylinder Movement (Last 7 Days)
     */
    private function getCylinderMovement()
    {
        $days = collect();
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            
            $issued = \App\Models\CylinderTransaction::whereDate('transaction_date', $date)
                ->where('transaction_type', 'issued')
                ->count();
            
            $returned = \App\Models\CylinderTransaction::whereDate('transaction_date', $date)
                ->where('transaction_type', 'returned')
                ->count();
            
            $days->push([
                'date' => now()->subDays($i)->format('D, d M'),
                'issued' => $issued,
                'returned' => $returned,
                'net' => $issued - $returned
            ]);
        }
        
        return $days;
    }

    /**
     * Get Recent Activity
     */
    private function getRecentActivity()
    {
        $activities = collect();

        // Recent Sales
        $sales = Sale::with(['customer'])
            ->where('status', '!=', 'cancelled')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($sale) {
                return (object) [
                    'type' => 'sale',
                    'title' => 'Sale Invoice Created',
                    'description' => $sale->invoice_no . ' - ' . ($sale->customer->name ?? 'N/A'),
                    'amount' => $sale->grand_total,
                    'date' => $sale->created_at,
                    'icon' => 'fa-file-invoice',
                    'color' => 'green',
                    'url' => route('sales.show', $sale)
                ];
            });

        // Recent Purchases
        $purchases = Purchase::with(['supplier'])
            ->where('status', '!=', 'cancelled')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($purchase) {
                return (object) [
                    'type' => 'purchase',
                    'title' => 'Purchase Order Created',
                    'description' => $purchase->purchase_invoice_no . ' - ' . ($purchase->supplier->name ?? 'N/A'),
                    'amount' => $purchase->grand_total,
                    'date' => $purchase->created_at,
                    'icon' => 'fa-shopping-cart',
                    'color' => 'blue',
                    'url' => route('purchases.show', $purchase)
                ];
            });

        // Recent Cylinder Transactions
        $cylinderTrans = \App\Models\CylinderTransaction::with(['cylinder', 'customer'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($transaction) {
                return (object) [
                    'type' => 'cylinder',
                    'title' => 'Cylinder ' . ucfirst($transaction->transaction_type),
                    'description' => $transaction->cylinder->cylinder_number . ' - ' . ($transaction->customer->name ?? 'N/A'),
                    'amount' => $transaction->damage_charge ?? 0,
                    'date' => $transaction->created_at,
                    'icon' => 'fa-cylinder',
                    'color' => 'yellow',
                    'url' => route('cylinders.show', $transaction->cylinder)
                ];
            });

        // Merge and sort
        $activities = $sales->concat($purchases)->concat($cylinderTrans)
            ->sortByDesc('date')
            ->take(10);

        return $activities;
    }

    /**
     * Refresh Dashboard Data (AJAX)
     */
    public function refresh(Request $request)
    {
        return response()->json([
            'stats' => $this->getQuickStats(),
            'incomeExpense' => $this->getIncomeExpense(),
            'issuedCylinders' => $this->getIssuedCylinders(),
            'customersWithCylinders' => $this->getCustomersWithCylinders(),
            'monthlyRevenue' => $this->getMonthlyRevenue(),
            'cylinderMovement' => $this->getCylinderMovement(),
        ]);
    }

    private function getQuickStats()
    {
        $today = now()->toDateString();
        
        return (object) [
            'total_customers' => Customer::where('is_active', true)->count(),
            'total_suppliers' => Supplier::where('is_active', true)->count(),
            'total_cylinders' => Cylinder::count(),
            'issued_cylinders' => Cylinder::where('status', 'issued')->count(),
            'today_sales' => Sale::whereDate('date', $today)->where('status', '!=', 'cancelled')->sum('grand_total'),
            'month_sales' => Sale::where('date', '>=', now()->startOfMonth())->where('status', '!=', 'cancelled')->sum('grand_total'),
        ];
    }

    private function getIssuedCylinders()
    {
        return Cylinder::with(['currentCustomer', 'gasProduct'])
            ->where('status', 'issued')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($cylinder) {
                $lastIssue = $cylinder->transactions()
                    ->where('transaction_type', 'issued')
                    ->latest()
                    ->first();
                
                return (object) [
                    'cylinder_number' => $cylinder->cylinder_number,
                    'gas_name' => $cylinder->gasProduct->name ?? 'N/A',
                    'customer_name' => $cylinder->currentCustomer->name ?? 'N/A',
                    'days_out' => $lastIssue ? $lastIssue->created_at->diffInDays(now()) : 0,
                ];
            });
    }

    private function getCustomersWithCylinders()
    {
        return Customer::whereHas('cylinders', function ($q) {
            $q->where('status', 'issued');
        })
        ->with(['cylinders' => function ($q) {
            $q->where('status', 'issued')->with('gasProduct');
        }])
        ->get()
        ->map(function ($customer) {
            return (object) [
                'name' => $customer->name,
                'phone' => $customer->phone,
                'total_issued' => $customer->cylinders->count(),
                'total_deposit' => $customer->security_deposit,
            ];
        });
    }
}