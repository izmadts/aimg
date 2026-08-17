<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Supplier;
use App\Models\GasProduct;
use App\Models\Cylinder;
use App\Models\CylinderIssuedDetail;
use App\Models\CylinderTransaction;
use App\Models\Sale;
use App\Models\Purchase;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected $accountingService;

    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    public function index()
    {
        $today = now()->toDateString();
        $currentMonth = now()->startOfMonth();

        // ============================================
        // 1. QUICK STATS
        // ============================================
        $stats = (object) [
            'total_customers' => Customer::where('is_active', true)->count(),
            'total_suppliers' => Supplier::where('is_active', true)->count(),
            'total_cylinders' => Cylinder::sum('stock_quantity'),
            'issued_cylinders' => Cylinder::sum('issued_quantity'),
            'available_cylinders' => Cylinder::whereIn('status', ['in_house', 'partial_issued'])->sum(DB::raw('stock_quantity - issued_quantity')),

            'today_sales' => Sale::whereDate('date', $today)->where('status', '!=', 'cancelled')->sum('grand_total'),
            'today_sales_count' => Sale::whereDate('date', $today)->where('status', '!=', 'cancelled')->count(),

            'month_sales' => Sale::where('date', '>=', $currentMonth)->where('status', '!=', 'cancelled')->sum('grand_total'),

            'gas_stock_value' => GasProduct::where('is_active', true)->get()->sum(function ($p) {
                return $p->current_stock * $p->purchase_price;
            }),

            'cylinder_asset_value' => Cylinder::where('status', '!=', 'scrapped')->sum(DB::raw('purchase_price * stock_quantity')),

            'pending_receivables' => Sale::where('payment_status', '!=', 'paid')->where('status', '!=', 'cancelled')->sum('balance_due') + Customer::sum('opening_balance'),
            'pending_payables' => Purchase::where('payment_status', '!=', 'paid')->where('status', '!=', 'cancelled')->sum('balance_due'),
        ];

        // ============================================
        // 2. INCOME & EXPENSE
        // ============================================
        $incomeExpense = $this->getIncomeExpense();

        // ============================================
        // 3. ISSUED CYLINDERS WITH CUSTOMER DETAILS
        // ============================================
        $issuedCylinders = CylinderIssuedDetail::with(['cylinder.gasProduct', 'customer'])
            ->where('status', 'issued')
            ->latest('issue_date')
            ->limit(10)
            ->get()
            ->map(function ($detail) {
                return (object) [
                    'id' => $detail->cylinder_id,
                    'cylinder_number' => $detail->cylinder->cylinder_number ?? 'N/A',
                    'gas_name' => $detail->cylinder->gasProduct->name ?? 'N/A',
                    'customer_name' => $detail->customer->name ?? 'N/A',
                    'customer_phone' => $detail->customer->phone ?? 'N/A',
                    'quantity' => $detail->quantity,
                    'issued_date' => $detail->issue_date ? $detail->issue_date->format('d-m-Y') : 'N/A',
                    'days_out' => $detail->days_out,
                    'security_deposit' => $detail->security_deposit,
                ];
            });

        // ============================================
        // 4. CUSTOMER LIST WITH ISSUED CYLINDERS
        // ============================================
        $customersWithCylinders = Customer::whereHas('activeCylinderIssues')
            ->with(['activeCylinderIssues.cylinder.gasProduct'])
            ->get()
            ->map(function ($customer) {
                return (object) [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'address' => $customer->address,
                    'issued_cylinders' => $customer->activeCylinderIssues->map(function ($detail) {
                        return (object) [
                            'number' => $detail->cylinder->cylinder_number ?? 'N/A',
                            'gas' => $detail->cylinder->gasProduct->name ?? 'N/A',
                            'quantity' => $detail->quantity,
                            'days_out' => $detail->days_out,
                        ];
                    }),
                    'total_issued' => $customer->activeCylinderIssues->sum('quantity'),
                    'total_deposit' => $customer->activeCylinderIssues->sum('security_deposit'),
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
        // 9. PENDING RETURNS (issued 7+ days, not yet returned)
        // ============================================
        $pendingReturns = CylinderIssuedDetail::with(['cylinder', 'customer'])
            ->where('status', 'issued')
            ->where('issue_date', '<', now()->subDays(7))
            ->get()
            ->groupBy('customer_id')
            ->map(function ($details) {
                $customer = $details->first()->customer;
                return (object) [
                    'customer_name' => $customer ? $customer->name : 'Unknown',
                    'customer_phone' => $customer ? $customer->phone : 'N/A',
                    'cylinder_count' => $details->sum('quantity'),
                    'cylinders' => $details->pluck('cylinder.cylinder_number')->filter()->unique()->join(', '),
                    'days_out' => $details->max('days_out'),
                    'deposit_held' => $details->sum('security_deposit'),
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
     * Get Income & Expense data (from the accounting ledger)
     */
    private function getIncomeExpense()
    {
        $today = now()->toDateString();
        $currentMonth = now()->startOfMonth();
        $currentYear = now()->year;

        $overall = $this->accountingService->getIncomeStatement();
        $today_ = $this->accountingService->getIncomeStatement($today, $today);
        $month = $this->accountingService->getIncomeStatement($currentMonth->format('Y-m-d'), now()->format('Y-m-d'));

        $incomeByAccount = \App\Models\AccountingEntry::approved()
            ->whereIn('transaction_type', ['sale', 'income', 'damage'])
            ->whereYear('date', $currentYear)
            ->select('account_id', DB::raw('SUM(credit) as total'))
            ->groupBy('account_id')
            ->having('total', '>', 0)
            ->with('account')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($item) => (object) [
                'account_name' => $item->account->account_name ?? 'N/A',
                'total' => $item->total,
            ]);

        $expenseByAccount = \App\Models\AccountingEntry::approved()
            ->whereIn('transaction_type', ['purchase', 'expense', 'salary'])
            ->whereYear('date', $currentYear)
            ->select('account_id', DB::raw('SUM(debit) as total'))
            ->groupBy('account_id')
            ->having('total', '>', 0)
            ->with('account')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($item) => (object) [
                'account_name' => $item->account->account_name ?? 'N/A',
                'total' => $item->total,
            ]);

        return (object) [
            'total_income' => $overall->total_income,
            'total_expense' => $overall->total_expense,
            'net_profit' => $overall->net_profit,
            'today_income' => $today_->total_income,
            'today_expense' => $today_->total_expense,
            'month_income' => $month->total_income,
            'month_expense' => $month->total_expense,
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

            $statement = $this->accountingService->getIncomeStatement($startDate->format('Y-m-d'), $endDate->format('Y-m-d'));

            $salesRevenue = Sale::whereBetween('date', [$startDate, $endDate])
                ->where('status', '!=', 'cancelled')
                ->sum('grand_total');

            $months->push([
                'month' => $startDate->format('M'),
                'income' => $statement->total_income,
                'expense' => $statement->total_expense,
                'profit' => $statement->net_profit,
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

            $issued = CylinderTransaction::whereDate('transaction_date', $date)
                ->where('transaction_type', 'issued')
                ->count();

            $returned = CylinderTransaction::whereDate('transaction_date', $date)
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

        $cylinderTrans = CylinderTransaction::with(['cylinder', 'customer'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($transaction) {
                return (object) [
                    'type' => 'cylinder',
                    'title' => 'Cylinder ' . ucfirst($transaction->transaction_type),
                    'description' => ($transaction->cylinder->cylinder_number ?? 'N/A') . ' - ' . ($transaction->customer->name ?? 'N/A'),
                    'amount' => $transaction->damage_charge ?? 0,
                    'date' => $transaction->created_at,
                    'icon' => 'fa-cylinder',
                    'color' => 'yellow',
                    'url' => route('cylinders.show', $transaction->cylinder_id)
                ];
            });

        return $sales->concat($purchases)->concat($cylinderTrans)
            ->sortByDesc('date')
            ->take(10);
    }

    /**
     * Refresh Dashboard Data (AJAX)
     */
    public function refresh(Request $request)
    {
        return response()->json([
            'stats' => $this->getQuickStats(),
            'incomeExpense' => $this->getIncomeExpense(),
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
            'total_cylinders' => Cylinder::sum('stock_quantity'),
            'issued_cylinders' => Cylinder::sum('issued_quantity'),
            'today_sales' => Sale::whereDate('date', $today)->where('status', '!=', 'cancelled')->sum('grand_total'),
            'month_sales' => Sale::where('date', '>=', now()->startOfMonth())->where('status', '!=', 'cancelled')->sum('grand_total'),
        ];
    }
}
