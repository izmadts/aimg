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
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\CylinderTypeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/refresh', [DashboardController::class, 'refresh'])->name('dashboard.refresh');

    // Help / User Guide (every authenticated user)
    Route::view('/help', 'help.index')->name('help.index');

    // Profile Routes (every authenticated user manages their own profile)
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/change-password', [ProfileController::class, 'showChangePasswordForm'])->name('profile.change-password');
    Route::put('/profile/update-password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
    Route::get('/profile/security', [ProfileController::class, 'security'])->name('profile.security');
    Route::delete('/profile/destroy', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ============================================
    // CUSTOMERS
    // ============================================
    // Static routes must be registered before the resource's {customer} wildcard,
    // otherwise e.g. GET /customers/export matches customers.show with customer="export" and 404s.
    Route::get('/customers/export', [CustomerController::class, 'export'])->name('customers.export')->middleware('permission:customers.view');
    Route::resource('customers', CustomerController::class)
        ->middlewareFor(['index', 'show'], 'permission:customers.view')
        ->middlewareFor(['create', 'store'], 'permission:customers.create')
        ->middlewareFor(['edit', 'update'], 'permission:customers.edit')
        ->middlewareFor('destroy', 'permission:customers.delete');
    Route::patch('/customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status')->middleware('permission:customers.edit');
    Route::get('/customers/{customer}/statement', [CustomerController::class, 'statement'])->name('customers.statement')->middleware('permission:customers.view');

    // ============================================
    // CYLINDERS
    // ============================================
    // Static routes must be registered before the resource's {cylinder} wildcard,
    // otherwise e.g. GET /cylinders/available matches cylinders.show with cylinder="available" and 404s.
    Route::middleware('permission:cylinders.view')->group(function () {
        Route::get('/cylinders/available', [CylinderController::class, 'available'])->name('cylinders.available');
        Route::get('/cylinders/export', [CylinderController::class, 'export'])->name('cylinders.export');
        Route::get('/cylinders/customer-outstanding/{customer}', [CylinderController::class, 'customerOutstanding'])->name('cylinders.customer-outstanding');
    });
    Route::middleware('permission:cylinders.edit')->group(function () {
        Route::post('/cylinders/update-stock', [CylinderController::class, 'updateStock'])->name('cylinders.update-stock');
        Route::post('/cylinders/issue', [CylinderController::class, 'issue'])->name('cylinders.issue');
        Route::post('/cylinders/return', [CylinderController::class, 'returnCylinder'])->name('cylinders.return');
    });
    Route::resource('cylinders', CylinderController::class)
        ->middlewareFor(['index', 'show'], 'permission:cylinders.view')
        ->middlewareFor(['create', 'store'], 'permission:cylinders.create')
        ->middlewareFor(['edit', 'update'], 'permission:cylinders.edit')
        ->middlewareFor('destroy', 'permission:cylinders.delete');

    // Cylinder Tracking & History (read-only reporting over cylinder data)
    Route::middleware('permission:cylinders.view')->group(function () {
        Route::get('cylinder/tracking', [CylinderTrackingController::class, 'index'])->name('cylinders.tracking');
        Route::post('/cylinders/tracking/track', [CylinderTrackingController::class, 'track'])->name('cylinders.tracking.track');
        Route::get('/cylinders/tracking/customer', [CylinderTrackingController::class, 'trackByCustomer'])->name('cylinders.tracking.customer');
        Route::get('/cylinders/tracking/export', [CylinderTrackingController::class, 'export'])->name('cylinders.tracking.export');
        Route::get('/cylinders/tracking/customer-report', [CylinderTrackingController::class, 'getCustomerReport'])->name('cylinders.tracking.customer-report');
        Route::get('/cylinders/tracking/history', [CylinderTrackingController::class, 'getHistory'])->name('cylinders.tracking.history');
        Route::get('/cylinder/history', [CylinderTrackingController::class, 'history'])->name('cylinders.history');
        // The literal "export" route must be registered before the {cylinderId} wildcard
        // below it, otherwise /cylinders/tracking/history/export matches the wildcard
        // with cylinderId="export" instead. Also named off 'cylinders.tracking.history'
        // (already used above) since a duplicate route name silently makes route()
        // resolve to whichever is registered last.
        Route::get('/cylinders/tracking/history/export', [CylinderTrackingController::class, 'exportHistory'])->name('cylinders.tracking.history.export');
        Route::get('/cylinders/tracking/history/{cylinderId}', [CylinderTrackingController::class, 'getCylinderHistory'])->name('cylinders.tracking.history.detail');
        Route::get('/cylinders/tracking/history-report/{cylinderId}', [CylinderTrackingController::class, 'historyReport'])->name('cylinders.tracking.history-report');
        Route::get('/cylinders/tracking/timeline/{cylinderId}', [CylinderTrackingController::class, 'getTimeline'])->name('cylinders.tracking.timeline');
        Route::get('/cylinders/tracking/stats', [CylinderTrackingController::class, 'getStats'])->name('cylinders.tracking.stats');
    });

    // ============================================
    // SALES
    // ============================================
    Route::resource('sales', SaleController::class)
        ->middlewareFor(['index', 'show'], 'permission:sales.view')
        ->middlewareFor(['create', 'store'], 'permission:sales.create')
        ->middlewareFor(['edit', 'update'], 'permission:sales.edit')
        ->middlewareFor('destroy', 'permission:sales.delete');
    Route::get('/sales/{sale}/print', [SaleController::class, 'print'])->name('sales.print')->middleware('permission:sales.view');
    Route::post('/sales/{sale}/payment', [SaleController::class, 'recordPayment'])->name('sales.payment')->middleware('permission:sales.edit');
    Route::post('/sales/{sale}/return-cylinder', [SaleController::class, 'returnCylinder'])->name('sales.return-cylinder')->middleware('permission:sales.edit');
    Route::get('/sales/customer-cylinders/{customer}', [SaleController::class, 'getCustomerCylinders'])->name('sales.customer-cylinders')->middleware('permission:sales.view');

    // ============================================
    // PURCHASES
    // ============================================
    Route::resource('purchases', PurchaseController::class)
        ->middlewareFor(['index', 'show'], 'permission:purchases.view')
        ->middlewareFor(['create', 'store'], 'permission:purchases.create')
        ->middlewareFor(['edit', 'update'], 'permission:purchases.edit')
        ->middlewareFor('destroy', 'permission:purchases.delete');
    Route::get('/purchases/{purchase}/print', [PurchaseController::class, 'print'])->name('purchases.print')->middleware('permission:purchases.view');
    Route::patch('/purchases/{purchase}/approve', [PurchaseController::class, 'approve'])->name('purchases.approve')->middleware('permission:purchases.edit');
    Route::patch('/purchases/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('purchases.cancel')->middleware('permission:purchases.delete');
    Route::post('/purchases/{purchase}/payment', [PurchaseController::class, 'recordPayment'])->name('purchases.payment')->middleware('permission:purchases.edit');

    // ============================================
    // SUPPLIERS
    // ============================================
    // Static routes must be registered before the resource's {supplier} wildcard,
    // otherwise e.g. GET /suppliers/export matches suppliers.show with supplier="export" and 404s.
    Route::get('/suppliers/export', [SupplierController::class, 'export'])->name('suppliers.export')->middleware('permission:suppliers.view');
    Route::get('/suppliers/statement-data', [SupplierController::class, 'getStatement'])->name('suppliers.statement-data')->middleware('permission:suppliers.view');
    Route::resource('suppliers', SupplierController::class)
        ->middlewareFor(['index', 'show'], 'permission:suppliers.view')
        ->middlewareFor(['create', 'store'], 'permission:suppliers.create')
        ->middlewareFor(['edit', 'update'], 'permission:suppliers.edit')
        ->middlewareFor('destroy', 'permission:suppliers.delete');
    Route::patch('/suppliers/{supplier}/toggle-status', [SupplierController::class, 'toggleStatus'])->name('suppliers.toggle-status')->middleware('permission:suppliers.edit');
    Route::get('/suppliers/{supplier}/statement', [SupplierController::class, 'statement'])->name('suppliers.statement')->middleware('permission:suppliers.view');

    // ============================================
    // CHART OF ACCOUNTS
    // ============================================
    Route::resource('accounts', AccountController::class)
        ->middlewareFor(['index', 'show'], 'permission:accounting.view')
        ->middlewareFor(['create', 'store', 'edit', 'update', 'destroy'], 'permission:accounting.manage');

    // ============================================
    // INCOME & EXPENSE
    // ============================================
    Route::middleware('permission:income_expense.view')->group(function () {
        Route::get('/income-expense', [IncomeExpenseController::class, 'index'])->name('income-expense.index');
        Route::get('/income-expense/report', [IncomeExpenseController::class, 'report'])->name('income-expense.report');
        Route::get('/income-expense/chart-data', [IncomeExpenseController::class, 'chartData'])->name('income-expense.chart-data');
    });
    Route::middleware('permission:income_expense.create')->group(function () {
        Route::get('/income-expense/create-income', [IncomeExpenseController::class, 'createIncome'])->name('income-expense.create-income');
        Route::get('/income-expense/create-expense', [IncomeExpenseController::class, 'createExpense'])->name('income-expense.create-expense');
        Route::post('/income-expense/store-income', [IncomeExpenseController::class, 'storeIncome'])->name('income-expense.store-income');
        Route::post('/income-expense/store-expense', [IncomeExpenseController::class, 'storeExpense'])->name('income-expense.store-expense');
    });
    Route::delete('/income-expense/{entry}', [IncomeExpenseController::class, 'destroy'])
        ->name('income-expense.destroy')
        ->middleware('permission:income_expense.delete');

    // ============================================
    // GAS PRODUCTS
    // ============================================
    // Static routes must be registered before the resource's {gas_product} wildcard,
    // otherwise e.g. GET /gas-products/export matches gas-products.show and 404s.
    Route::get('/gas-products/export', [GasProductController::class, 'export'])->name('gas-products.export')->middleware('permission:gas_products.view');
    Route::resource('gas-products', GasProductController::class)
        ->middlewareFor(['index', 'show'], 'permission:gas_products.view')
        ->middlewareFor(['create', 'store'], 'permission:gas_products.create')
        ->middlewareFor(['edit', 'update'], 'permission:gas_products.edit')
        ->middlewareFor('destroy', 'permission:gas_products.delete');
    Route::patch('/gas-products/{gasProduct}/toggle-status', [GasProductController::class, 'toggleStatus'])->name('gas-products.toggle-status')->middleware('permission:gas_products.edit');
    Route::post('/gas-products/{gasProduct}/update-stock', [GasProductController::class, 'updateStock'])->name('gas-products.update-stock')->middleware('permission:gas_products.edit');

    // ============================================
    // HRM
    // ============================================
    // hrm.employees.create must be registered before the {id} wildcard below it,
    // otherwise GET /hrm/employees/create matches hrm.employees.show with id="create" and 404s.
    Route::get('/hrm/employees/create', [HRMController::class, 'createEmployee'])->name('hrm.employees.create')->middleware('permission:hrm.manage');
    Route::middleware('permission:hrm.view')->group(function () {
        Route::get('/hrm/employees', [HRMController::class, 'employees'])->name('hrm.employees');
        Route::get('/hrm/employees/{id}', [HRMController::class, 'showEmployee'])->name('hrm.employees.show');
        Route::get('/hrm/salaries', [HRMController::class, 'salaries'])->name('hrm.salaries');
        Route::get('/hrm/attendance', [HRMController::class, 'attendance'])->name('hrm.attendance');
        Route::get('/hrm/advances', [HRMController::class, 'advances'])->name('hrm.advances');
        Route::get('/hrm/leaves', [HRMController::class, 'leaves'])->name('hrm.leaves');
    });
    Route::middleware('permission:hrm.manage')->group(function () {
        Route::post('/hrm/employees', [HRMController::class, 'storeEmployee'])->name('hrm.employees.store');
        Route::get('/hrm/employees/{id}/edit', [HRMController::class, 'editEmployee'])->name('hrm.employees.edit');
        Route::put('/hrm/employees/{id}', [HRMController::class, 'updateEmployee'])->name('hrm.employees.update');
        Route::delete('/hrm/employees/{id}', [HRMController::class, 'deleteEmployee'])->name('hrm.employees.delete');

        Route::get('/hrm/salaries/create', [HRMController::class, 'createSalary'])->name('hrm.salaries.create');
        Route::post('/hrm/salaries/process', [HRMController::class, 'processSalary'])->name('hrm.salaries.process');
        Route::post('/hrm/salaries/{salary}/pay', [HRMController::class, 'paySalary'])->name('hrm.salaries.pay');
        Route::delete('/hrm/salaries/{salary}', [HRMController::class, 'deleteSalary'])->name('hrm.salaries.delete');

        Route::post('/hrm/attendance', [HRMController::class, 'markAttendance'])->name('hrm.attendance.store');
        Route::delete('/hrm/attendance/{id}', [HRMController::class, 'deleteAttendance'])->name('hrm.attendance.delete');

        Route::get('/hrm/advances/create', [HRMController::class, 'createAdvance'])->name('hrm.advances.create');
        Route::post('/hrm/advances', [HRMController::class, 'storeAdvance'])->name('hrm.advances.store');
        Route::post('/hrm/advances/{advance}/approve', [HRMController::class, 'approveAdvance'])->name('hrm.advances.approve');
        Route::post('/hrm/advances/{advance}/reject', [HRMController::class, 'rejectAdvance'])->name('hrm.advances.reject');
        Route::delete('/hrm/advances/{advance}', [HRMController::class, 'deleteAdvance'])->name('hrm.advances.delete');

        Route::get('/hrm/leaves/create', [HRMController::class, 'createLeave'])->name('hrm.leaves.create');
        Route::post('/hrm/leaves', [HRMController::class, 'storeLeave'])->name('hrm.leaves.store');
        Route::post('/hrm/leaves/{leave}/approve', [HRMController::class, 'approveLeave'])->name('hrm.leaves.approve');
        Route::post('/hrm/leaves/{leave}/reject', [HRMController::class, 'rejectLeave'])->name('hrm.leaves.reject');
        Route::delete('/hrm/leaves/{leave}', [HRMController::class, 'deleteLeave'])->name('hrm.leaves.delete');
    });

    // ============================================
    // ACCOUNTING REPORTS
    // ============================================
    Route::middleware('permission:accounting.view')->group(function () {
        Route::get('/accounting/trial-balance', [AccountingController::class, 'trialBalance'])->name('accounting.trial-balance');
        Route::get('/accounting/income-statement', [AccountingController::class, 'incomeStatement'])->name('accounting.income-statement');
        Route::get('/accounting/balance-sheet', [AccountingController::class, 'balanceSheet'])->name('accounting.balance-sheet');
    });

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

    // ============================================
    // SYSTEM SETTINGS (admin only)
    // ============================================
    Route::middleware('permission:settings.manage')->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    });

    // ============================================
    // UNITS OF MEASURE (master data used by Gas Products)
    // ============================================
    Route::middleware('permission:units.manage')->group(function () {
        Route::get('/units', [UnitController::class, 'index'])->name('units.index');
        Route::post('/units', [UnitController::class, 'store'])->name('units.store');
        Route::put('/units/{unit}', [UnitController::class, 'update'])->name('units.update');
        Route::delete('/units/{unit}', [UnitController::class, 'destroy'])->name('units.destroy');
    });

    // ============================================
    // CYLINDER TYPES (master data used by Cylinders)
    // ============================================
    Route::middleware('permission:cylinder_types.manage')->group(function () {
        Route::get('/cylinder-types', [CylinderTypeController::class, 'index'])->name('cylinder-types.index');
        Route::post('/cylinder-types', [CylinderTypeController::class, 'store'])->name('cylinder-types.store');
        Route::put('/cylinder-types/{cylinderType}', [CylinderTypeController::class, 'update'])->name('cylinder-types.update');
        Route::delete('/cylinder-types/{cylinderType}', [CylinderTypeController::class, 'destroy'])->name('cylinder-types.destroy');
    });
});

require __DIR__.'/auth.php';
