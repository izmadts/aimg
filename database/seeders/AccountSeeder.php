<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

/**
 * Standard Chart of Accounts for a Pakistani gas/cylinder trading business.
 * Numbering follows local convention: 1xxx Assets, 2xxx Liabilities,
 * 3xxx Equity, 4xxx Income, 5xxx Expenses.
 *
 * The core codes (1001-1005, 2001-2002, 4001-4002, 4005, 5001-5002) are relied
 * on directly by App\Services\AccountingService — do not renumber them.
 */
class AccountSeeder extends Seeder
{
    public function run()
    {
        // ============================================
        // ASSETS (1xxx)
        // ============================================
        $cash = Account::create([
            'account_code' => '1001',
            'account_name' => 'Cash in Hand',
            'account_type' => 'asset',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Physical cash in hand'
        ]);

        $bank = Account::create([
            'account_code' => '1002',
            'account_name' => 'Bank Account',
            'account_type' => 'asset',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Bank current account'
        ]);

        Account::create([
            'account_code' => '1003',
            'account_name' => 'Accounts Receivable / Trade Debtors',
            'account_type' => 'asset',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Customer receivables'
        ]);

        Account::create([
            'account_code' => '1004',
            'account_name' => 'Cylinder Asset (Fixed Asset)',
            'account_type' => 'asset',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Cylinders owned by the business, held in warehouse or issued to customers'
        ]);

        Account::create([
            'account_code' => '1005',
            'account_name' => 'Stock in Trade - Gas Inventory',
            'account_type' => 'asset',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Gas inventory stock'
        ]);

        Account::create([
            'account_code' => '1006',
            'account_name' => 'Advances to Suppliers',
            'account_type' => 'asset',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Advance payments made to suppliers against future purchases'
        ]);

        Account::create([
            'account_code' => '1007',
            'account_name' => 'Prepaid Expenses',
            'account_type' => 'asset',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Rent, insurance and other expenses paid in advance'
        ]);

        Account::create([
            'account_code' => '1008',
            'account_name' => 'Furniture & Fixtures',
            'account_type' => 'asset',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Office furniture and fixtures'
        ]);

        Account::create([
            'account_code' => '1009',
            'account_name' => 'Vehicles',
            'account_type' => 'asset',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Delivery vehicles and fleet'
        ]);

        // ============================================
        // LIABILITIES (2xxx)
        // ============================================
        Account::create([
            'account_code' => '2001',
            'account_name' => 'Accounts Payable / Trade Creditors',
            'account_type' => 'liability',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Supplier payables'
        ]);

        Account::create([
            'account_code' => '2002',
            'account_name' => 'Cylinder Deposit Liability',
            'account_type' => 'liability',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Customer cylinder security deposits held'
        ]);

        Account::create([
            'account_code' => '2003',
            'account_name' => 'Sales Tax Payable (GST)',
            'account_type' => 'liability',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'General sales tax collected, payable to FBR'
        ]);

        Account::create([
            'account_code' => '2004',
            'account_name' => 'Withholding Tax Payable',
            'account_type' => 'liability',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Income tax withheld/deducted, payable to FBR'
        ]);

        Account::create([
            'account_code' => '2005',
            'account_name' => 'Advance from Customers',
            'account_type' => 'liability',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Advance payments received against future sales'
        ]);

        Account::create([
            'account_code' => '2006',
            'account_name' => 'Accrued Salaries Payable',
            'account_type' => 'liability',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Employee salaries accrued but not yet paid'
        ]);

        // ============================================
        // EQUITY (3xxx)
        // ============================================
        Account::create([
            'account_code' => '3001',
            'account_name' => "Owner's Capital Account",
            'account_type' => 'equity',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => "Owner's capital invested in the business"
        ]);

        Account::create([
            'account_code' => '3002',
            'account_name' => 'Retained Earnings',
            'account_type' => 'equity',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Accumulated retained earnings'
        ]);

        Account::create([
            'account_code' => '3003',
            'account_name' => "Owner's Drawings",
            'account_type' => 'equity',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Cash/goods withdrawn by the owner for personal use'
        ]);

        // ============================================
        // INCOME (4xxx)
        // ============================================
        Account::create([
            'account_code' => '4001',
            'account_name' => 'Gas Sales Revenue',
            'account_type' => 'income',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Revenue from gas sales'
        ]);

        Account::create([
            'account_code' => '4002',
            'account_name' => 'Cylinder Sales Revenue',
            'account_type' => 'income',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Revenue from cylinder sales'
        ]);

        Account::create([
            'account_code' => '4003',
            'account_name' => 'Service Income',
            'account_type' => 'income',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Income from services (refilling, transport, etc.)'
        ]);

        Account::create([
            'account_code' => '4004',
            'account_name' => 'Other Income',
            'account_type' => 'income',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Other miscellaneous income'
        ]);

        Account::create([
            'account_code' => '4005',
            'account_name' => 'Damage Income',
            'account_type' => 'income',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Income from cylinder damage charges'
        ]);

        // ============================================
        // EXPENSES (5xxx)
        // ============================================
        Account::create([
            'account_code' => '5001',
            'account_name' => 'Cost of Goods Sold',
            'account_type' => 'expense',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Cost of gas and cylinders sold'
        ]);

        Account::create([
            'account_code' => '5002',
            'account_name' => 'Salaries & Wages Expense',
            'account_type' => 'expense',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Employee salaries and wages'
        ]);

        Account::create([
            'account_code' => '5003',
            'account_name' => 'Rent Expense',
            'account_type' => 'expense',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Office/warehouse rent'
        ]);

        Account::create([
            'account_code' => '5004',
            'account_name' => 'Utilities Expense (Electricity, Gas, Water)',
            'account_type' => 'expense',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Electricity, water, gas bills'
        ]);

        Account::create([
            'account_code' => '5005',
            'account_name' => 'Fuel & Transport Expense',
            'account_type' => 'expense',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Vehicle fuel and delivery expenses'
        ]);

        Account::create([
            'account_code' => '5006',
            'account_name' => 'Cylinder Maintenance Expense',
            'account_type' => 'expense',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Cylinder repair, hydro-testing and maintenance'
        ]);

        Account::create([
            'account_code' => '5007',
            'account_name' => 'Insurance Expense',
            'account_type' => 'expense',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Insurance premiums'
        ]);

        Account::create([
            'account_code' => '5008',
            'account_name' => 'Marketing & Advertising Expense',
            'account_type' => 'expense',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Advertising and marketing'
        ]);

        Account::create([
            'account_code' => '5009',
            'account_name' => 'Office & Stationery Expense',
            'account_type' => 'expense',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Stationery and office supplies'
        ]);

        Account::create([
            'account_code' => '5010',
            'account_name' => 'Depreciation Expense',
            'account_type' => 'expense',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Depreciation on fixed assets (cylinders, vehicles, furniture)'
        ]);

        Account::create([
            'account_code' => '5011',
            'account_name' => 'Bank Charges',
            'account_type' => 'expense',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Bank service charges'
        ]);

        Account::create([
            'account_code' => '5012',
            'account_name' => 'Miscellaneous Expense',
            'account_type' => 'expense',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Other miscellaneous expenses'
        ]);

        Account::create([
            'account_code' => '5013',
            'account_name' => 'Legal & Professional Charges',
            'account_type' => 'expense',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Legal fees, audit fees, and professional consultancy'
        ]);

        // ============================================
        // SUB-ACCOUNTS
        // ============================================
        Account::create([
            'account_code' => '1001-01',
            'account_name' => 'Petty Cash',
            'account_type' => 'asset',
            'parent_id' => $cash->id,
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Petty cash in hand'
        ]);

        Account::create([
            'account_code' => '1002-01',
            'account_name' => 'Main Bank Account',
            'account_type' => 'asset',
            'parent_id' => $bank->id,
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Primary bank account'
        ]);

        Account::create([
            'account_code' => '1002-02',
            'account_name' => 'Savings Account',
            'account_type' => 'asset',
            'parent_id' => $bank->id,
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Savings bank account'
        ]);

        $this->command->info('✅ Standard Chart of Accounts (Pakistan) seeded successfully!');
        $this->command->info('📊 Total Accounts: ' . Account::count());
        $this->command->info('📊 Asset: ' . Account::where('account_type', 'asset')->count());
        $this->command->info('📊 Liability: ' . Account::where('account_type', 'liability')->count());
        $this->command->info('📊 Equity: ' . Account::where('account_type', 'equity')->count());
        $this->command->info('📊 Income: ' . Account::where('account_type', 'income')->count());
        $this->command->info('📊 Expense: ' . Account::where('account_type', 'expense')->count());
    }
}
