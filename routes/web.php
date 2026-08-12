<?php

use App\Http\Controllers\CylinderController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CylinderTrackingController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\IncomeExpenseController;
use App\Http\Controllers\GasProductController;
use App\Http\Controllers\HRMController;
use App\Http\Controllers\AccountingController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/refresh', [DashboardController::class, 'refresh'])->name('dashboard.refresh');
    
    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/change-password', [ProfileController::class, 'showChangePasswordForm'])->name('profile.change-password');
    Route::put('/profile/update-password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
    Route::get('/profile/security', [ProfileController::class, 'security'])->name('profile.security');
    Route::delete('/profile/destroy', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Customer Routes
    Route::resource('customers', CustomerController::class);
    Route::patch('/customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');
    Route::get('/customers/{customer}/statement', [CustomerController::class, 'statement'])->name('customers.statement');
    Route::get('/customers/export', [CustomerController::class, 'export'])->name('customers.export');
    
    // ============================================
    // CYLINDER OUTSTANDING ROUTES
    // ============================================
/*    Route::get('/cylinders/customer-outstanding/{customer}', [CylinderController::class, 'customerOutstanding'])->name('cylinders.customer-outstanding');
    Route::get('/cylinders/customer-outstanding-detailed/{customer}', [CylinderController::class, 'customerOutstandingDetailed'])->name('cylinders.customer-outstanding-detailed');
    Route::get('/cylinders/customer-summary/{customer}', [CylinderController::class, 'customerSummary'])->name('cylinders.customer-summary');
    Route::get('/cylinders/all-outstanding', [CylinderController::class, 'allOutstanding'])->name('cylinders.all-outstanding');
    Route::get('/cylinders/stock', [CylinderController::class, 'stock'])->name('cylinders.stock');
    Route::post('/cylinders/update-quantity', [CylinderController::class, 'updateQuantity'])->name('cylinders.update-quantity');
    Route::post('/cylinders/update-stock', [CylinderController::class, 'updateStock'])->name('cylinders.update-stock');*/
    
    // Sale Routes
    Route::resource('sales', SaleController::class);
    Route::get('/sales/{sale}/print', [SaleController::class, 'print'])->name('sales.print');
    Route::post('/sales/{sale}/payment', [SaleController::class, 'recordPayment'])->name('sales.payment');
    Route::post('/sales/{sale}/return-cylinder', [SaleController::class, 'returnCylinder'])->name('sales.return-cylinder');
    Route::get('/sales/customer-cylinders/{customer}', [SaleController::class, 'getCustomerCylinders'])->name('sales.customer-cylinders');


    
    // Purchase Routes
    Route::resource('purchases', PurchaseController::class);
    Route::get('/purchases/{purchase}/print', [PurchaseController::class, 'print'])->name('purchases.print');
    Route::patch('/purchases/{purchase}/approve', [PurchaseController::class, 'approve'])->name('purchases.approve');
    Route::patch('/purchases/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('purchases.cancel');
    Route::post('/purchases/{purchase}/payment', [PurchaseController::class, 'recordPayment'])->name('purchases.payment');
    
   /* // Cylinder Routes
    Route::resource('cylinders', CylinderController::class);
    Route::post('/cylinders/issue', [CylinderController::class, 'issue'])->name('cylinders.issue');
    Route::post('/cylinders/return', [CylinderController::class, 'returnCylinder'])->name('cylinders.return');
    Route::get('/cylinders/available', [CylinderController::class, 'available'])->name('cylinders.available');
    Route::get('/cylinders/export', [CylinderController::class, 'export'])->name('cylinders.export');
    Route::post('/cylinders/import', [CylinderController::class, 'import'])->name('cylinders.import');*/

     // Cylinder Routes
    Route::resource('cylinders', CylinderController::class);
    Route::get('/cylinders/stock', [CylinderController::class, 'stock'])->name('cylinders.stock');
    Route::post('/cylinders/update-stock', [CylinderController::class, 'updateStock'])->name('cylinders.update-stock');
    Route::post('/cylinders/issue', [CylinderController::class, 'issue'])->name('cylinders.issue');
    Route::post('/cylinders/return', [CylinderController::class, 'returnCylinder'])->name('cylinders.return');
    Route::get('/cylinders/available', [CylinderController::class, 'available'])->name('cylinders.available');
    Route::get('/cylinders/customer-outstanding/{customer}', [CylinderController::class, 'customerOutstanding'])->name('cylinders.customer-outstanding');
    Route::get('/cylinders/export', [CylinderController::class, 'export'])->name('cylinders.export');

    // Cylinder Tracking Routes
    Route::get('cylinder/tracking', [CylinderTrackingController::class, 'index'])->name('cylinders.tracking');
    Route::post('/cylinders/tracking/track', [CylinderTrackingController::class, 'track'])->name('cylinders.tracking.track');
    Route::get('/cylinders/tracking/customer', [CylinderTrackingController::class, 'trackByCustomer'])->name('cylinders.tracking.customer');
    Route::get('/cylinders/tracking/export', [CylinderTrackingController::class, 'export'])->name('cylinders.tracking.export');
    Route::get('/cylinders/tracking/customer-report', [CylinderTrackingController::class, 'getCustomerReport'])->name('cylinders.tracking.customer-report');
    Route::get('/cylinders/tracking/history', [CylinderTrackingController::class, 'getHistory'])->name('cylinders.tracking.history');



    // Cylinder History Routes
    Route::get('/cylinder/history', [CylinderTrackingController::class, 'history'])->name('cylinders.history');
    Route::get('/cylinders/tracking/history/{cylinderId}', [CylinderTrackingController::class, 'getCylinderHistory'])->name('cylinders.tracking.history');
    Route::get('/cylinders/tracking/history/export', [CylinderTrackingController::class, 'exportHistory'])->name('cylinders.tracking.history.export');
    Route::get('/cylinders/tracking/history-report/{cylinderId}', [CylinderTrackingController::class, 'historyReport'])->name('cylinders.tracking.history-report');
    Route::get('/cylinders/tracking/timeline/{cylinderId}', [CylinderTrackingController::class, 'getTimeline'])->name('cylinders.tracking.timeline');
    Route::get('/cylinders/tracking/stats', [CylinderTrackingController::class, 'getStats'])->name('cylinders.tracking.stats');



    // Supplier Routes
    Route::resource('suppliers', SupplierController::class);
    Route::patch('/suppliers/{supplier}/toggle-status', [SupplierController::class, 'toggleStatus'])->name('suppliers.toggle-status');
    Route::get('/suppliers/{supplier}/statement', [SupplierController::class, 'statement'])->name('suppliers.statement');
    Route::get('/suppliers/export', [SupplierController::class, 'export'])->name('suppliers.export');
    Route::get('/suppliers/statement-data', [SupplierController::class, 'getStatement'])->name('suppliers.statement-data');


    // Account Routes
    Route::resource('accounts', AccountController::class);
    
    // Income & Expense Routes
    Route::get('/income-expense', [IncomeExpenseController::class, 'index'])->name('income-expense.index');
    Route::get('/income-expense/create-income', [IncomeExpenseController::class, 'createIncome'])->name('income-expense.create-income');
    Route::get('/income-expense/create-expense', [IncomeExpenseController::class, 'createExpense'])->name('income-expense.create-expense');
    Route::post('/income-expense/store-income', [IncomeExpenseController::class, 'storeIncome'])->name('income-expense.store-income');
    Route::post('/income-expense/store-expense', [IncomeExpenseController::class, 'storeExpense'])->name('income-expense.store-expense');
    Route::get('/income-expense/report', [IncomeExpenseController::class, 'report'])->name('income-expense.report');
    Route::get('/income-expense/chart-data', [IncomeExpenseController::class, 'chartData'])->name('income-expense.chart-data');


    // Gas Products Routes
    Route::resource('gas-products', GasProductController::class);
    Route::patch('/gas-products/{gasProduct}/toggle-status', [GasProductController::class, 'toggleStatus'])->name('gas-products.toggle-status');
    Route::get('/gas-products/export', [GasProductController::class, 'export'])->name('gas-products.export');
    Route::post('/gas-products/{gasProduct}/update-stock', [GasProductController::class, 'updateStock'])->name('gas-products.update-stock');


  /*   // HRM Routes
    Route::get('/hrm/employees', [HRMController::class, 'employees'])->name('hrm.employees');
    Route::post('/hrm/employees', [HRMController::class, 'createEmployee'])->name('hrm.employees.store');
    
    // ✅ Employee CRUD
    Route::get('/hrm/employees/{id}/view', [HRMController::class, 'showEmployee'])->name('hrm.employees.view');
    Route::get('/hrm/employees/{id}/edit', [HRMController::class, 'editEmployee'])->name('hrm.employees.edit');
    Route::put('/hrm/employees/{id}', [HRMController::class, 'updateEmployee'])->name('hrm.employees.update');
    Route::delete('/hrm/employees/{id}', [HRMController::class, 'deleteEmployee'])->name('hrm.employees.delete');
    
    // ✅ Salary Routes
    Route::get('/hrm/salaries', [HRMController::class, 'salaries'])->name('hrm.salaries');
    Route::get('/hrm/salaries/create', [HRMController::class, 'createSalaryForm'])->name('hrm.salaries.create');
    Route::post('/hrm/salaries/process', [HRMController::class, 'processSalary'])->name('hrm.salaries.process');
    Route::post('/hrm/salaries/{salary}/pay', [HRMController::class, 'paySalary'])->name('hrm.salaries.pay');
    
    Route::get('/hrm/advances', [HRMController::class, 'advances'])->name('hrm.advances');
    Route::post('/hrm/advances', [HRMController::class, 'createAdvance'])->name('hrm.advances.store');
    Route::post('/hrm/advances/{advance}/approve', [HRMController::class, 'approveAdvance'])->name('hrm.advances.approve');
    
    Route::get('/hrm/attendance', [HRMController::class, 'attendance'])->name('hrm.attendance');
    Route::post('/hrm/attendance', [HRMController::class, 'markAttendance'])->name('hrm.attendance.store');
    //Route::post('/hrm/salaries/process', [HRMController::class, 'processSalary'])->name('hrm.salaries.process');
    // Accounting Routes*/


     // ============================================
    // HRM ROUTES
    // ============================================
    
    // Employees
    Route::get('/hrm/employees', [HRMController::class, 'employees'])->name('hrm.employees');
    Route::get('/hrm/employees/create', [HRMController::class, 'createEmployee'])->name('hrm.employees.create');
    Route::post('/hrm/employees', [HRMController::class, 'storeEmployee'])->name('hrm.employees.store');
    Route::get('/hrm/employees/{id}', [HRMController::class, 'showEmployee'])->name('hrm.employees.show');
    Route::get('/hrm/employees/{id}/edit', [HRMController::class, 'editEmployee'])->name('hrm.employees.edit');
    Route::put('/hrm/employees/{id}', [HRMController::class, 'updateEmployee'])->name('hrm.employees.update');
    Route::delete('/hrm/employees/{id}', [HRMController::class, 'deleteEmployee'])->name('hrm.employees.delete');
    
    // Salaries
    Route::get('/hrm/salaries', [HRMController::class, 'salaries'])->name('hrm.salaries');
    Route::get('/hrm/salaries/create', [HRMController::class, 'createSalary'])->name('hrm.salaries.create');
    Route::post('/hrm/salaries/process', [HRMController::class, 'processSalary'])->name('hrm.salaries.process');
    Route::post('/hrm/salaries/{salary}/pay', [HRMController::class, 'paySalary'])->name('hrm.salaries.pay');
    
    // Attendance
    Route::get('/hrm/attendance', [HRMController::class, 'attendance'])->name('hrm.attendance');
    Route::post('/hrm/attendance', [HRMController::class, 'markAttendance'])->name('hrm.attendance.store');
    Route::delete('/hrm/attendance/{id}', [HRMController::class, 'deleteAttendance'])->name('hrm.attendance.delete');
    
    // Advances
    Route::get('/hrm/advances', [HRMController::class, 'advances'])->name('hrm.advances');
    Route::get('/hrm/advances/create', [HRMController::class, 'createAdvance'])->name('hrm.advances.create');
    Route::post('/hrm/advances', [HRMController::class, 'storeAdvance'])->name('hrm.advances.store');
    Route::post('/hrm/advances/{advance}/approve', [HRMController::class, 'approveAdvance'])->name('hrm.advances.approve');
    Route::post('/hrm/advances/{advance}/reject', [HRMController::class, 'rejectAdvance'])->name('hrm.advances.reject');
    
    // Leaves
    Route::get('/hrm/leaves', [HRMController::class, 'leaves'])->name('hrm.leaves');
    Route::get('/hrm/leaves/create', [HRMController::class, 'createLeave'])->name('hrm.leaves.create');
    Route::post('/hrm/leaves', [HRMController::class, 'storeLeave'])->name('hrm.leaves.store');
    Route::post('/hrm/leaves/{leave}/approve', [HRMController::class, 'approveLeave'])->name('hrm.leaves.approve');
    Route::post('/hrm/leaves/{leave}/reject', [HRMController::class, 'rejectLeave'])->name('hrm.leaves.reject');
    Route::get('/accounting/trial-balance', [AccountingController::class, 'trialBalance'])->name('accounting.trial-balance');
    Route::get('/accounting/income-statement', [AccountingController::class, 'incomeStatement'])->name('accounting.income-statement');
    Route::get('/accounting/balance-sheet', [AccountingController::class, 'balanceSheet'])->name('accounting.balance-sheet');

    // ============================================
    // ROLES & PERMISSIONS (admin only)
    // ============================================
    Route::middleware('permission:roles.manage')->group(function () {
        Route::resource('roles', RoleController::class)->except(['show']);
    });

    // ============================================
    // USER MANAGEMENT (admin only)
    // ============================================
    Route::middleware('permission:users.manage')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
    });
});


require __DIR__.'/auth.php';