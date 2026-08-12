<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run()
    {
        // ============================================
        // ASSETS (1xxx)
        // ============================================
        $cash = Account::create([
            'account_code' => '1001',
            'account_name' => 'Cash',
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

        $receivable = Account::create([
            'account_code' => '1003',
            'account_name' => 'Accounts Receivable',
            'account_type' => 'asset',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Customer receivables'
        ]);

        $cylinderAsset = Account::create([
            'account_code' => '1004',
            'account_name' => 'Cylinder Asset',
            'account_type' => 'asset',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Cylinder fixed assets'
        ]);

        $inventory = Account::create([
            'account_code' => '1005',
            'account_name' => 'Inventory - Gas',
            'account_type' => 'asset',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Gas inventory stock'
        ]);

        // ============================================
        // LIABILITIES (2xxx)
        // ============================================
        $payable = Account::create([
            'account_code' => '2001',
            'account_name' => 'Accounts Payable',
            'account_type' => 'liability',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Supplier payables'
        ]);

        $cylinderDeposit = Account::create([
            'account_code' => '2002',
            'account_name' => 'Cylinder Deposit Liability',
            'account_type' => 'liability',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Customer cylinder security deposits'
        ]);

        $taxLiability = Account::create([
            'account_code' => '2003',
            'account_name' => 'Tax Payable',
            'account_type' => 'liability',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Sales tax payable'
        ]);

        // ============================================
        // EQUITY (3xxx)
        // ============================================
        $ownerEquity = Account::create([
            'account_code' => '3001',
            'account_name' => 'Owner\'s Equity',
            'account_type' => 'equity',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Owner\'s capital'
        ]);

        $retainedEarnings = Account::create([
            'account_code' => '3002',
            'account_name' => 'Retained Earnings',
            'account_type' => 'equity',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Retained earnings'
        ]);

        // ============================================
        // INCOME (4xxx)
        // ============================================
        $salesRevenue = Account::create([
            'account_code' => '4001',
            'account_name' => 'Sales Revenue',
            'account_type' => 'income',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Revenue from gas sales'
        ]);

        $cylinderSales = Account::create([
            'account_code' => '4002',
            'account_name' => 'Cylinder Sales Revenue',
            'account_type' => 'income',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Revenue from cylinder sales'
        ]);

        $serviceIncome = Account::create([
            'account_code' => '4003',
            'account_name' => 'Service Income',
            'account_type' => 'income',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Income from services'
        ]);

        $otherIncome = Account::create([
            'account_code' => '4004',
            'account_name' => 'Other Income',
            'account_type' => 'income',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Other miscellaneous income'
        ]);

        $damageIncome = Account::create([
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
        $cogs = Account::create([
            'account_code' => '5001',
            'account_name' => 'Cost of Goods Sold',
            'account_type' => 'expense',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Cost of gas sold'
        ]);

        $salaryExpense = Account::create([
            'account_code' => '5002',
            'account_name' => 'Salary Expense',
            'account_type' => 'expense',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Employee salaries'
        ]);

        $rentExpense = Account::create([
            'account_code' => '5003',
            'account_name' => 'Rent Expense',
            'account_type' => 'expense',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Office/warehouse rent'
        ]);

        $utilitiesExpense = Account::create([
            'account_code' => '5004',
            'account_name' => 'Utilities Expense',
            'account_type' => 'expense',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Electricity, water, gas bills'
        ]);

        $transportExpense = Account::create([
            'account_code' => '5005',
            'account_name' => 'Transport Expense',
            'account_type' => 'expense',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Vehicle and delivery expenses'
        ]);

        $cylinderMaintenance = Account::create([
            'account_code' => '5006',
            'account_name' => 'Cylinder Maintenance',
            'account_type' => 'expense',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Cylinder repair and maintenance'
        ]);

        $insuranceExpense = Account::create([
            'account_code' => '5007',
            'account_name' => 'Insurance Expense',
            'account_type' => 'expense',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Insurance premiums'
        ]);

        $marketingExpense = Account::create([
            'account_code' => '5008',
            'account_name' => 'Marketing Expense',
            'account_type' => 'expense',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Advertising and marketing'
        ]);

        $officeExpense = Account::create([
            'account_code' => '5009',
            'account_name' => 'Office Expense',
            'account_type' => 'expense',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Stationery and office supplies'
        ]);

        $depreciationExpense = Account::create([
            'account_code' => '5010',
            'account_name' => 'Depreciation Expense',
            'account_type' => 'expense',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Asset depreciation'
        ]);

        $bankCharges = Account::create([
            'account_code' => '5011',
            'account_name' => 'Bank Charges',
            'account_type' => 'expense',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Bank service charges'
        ]);

        $otherExpense = Account::create([
            'account_code' => '5012',
            'account_name' => 'Other Expense',
            'account_type' => 'expense',
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Other miscellaneous expenses'
        ]);

        // ============================================
        // SUB-ACCOUNTS (Optional)
        // ============================================
        
        // Cash sub-accounts
        Account::create([
            'account_code' => '1001-01',
            'account_name' => 'Petty Cash',
            'account_type' => 'asset',
            'parent_id' => $cash->id,
            'opening_balance' => 0,
            'is_active' => true,
            'description' => 'Petty cash in hand'
        ]);

        // Bank sub-accounts
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

        $this->command->info('✅ Default accounts seeded successfully!');
        $this->command->info('📊 Total Accounts: ' . Account::count());
        $this->command->info('📊 Asset: ' . Account::where('account_type', 'asset')->count());
        $this->command->info('📊 Liability: ' . Account::where('account_type', 'liability')->count());
        $this->command->info('📊 Equity: ' . Account::where('account_type', 'equity')->count());
        $this->command->info('📊 Income: ' . Account::where('account_type', 'income')->count());
        $this->command->info('📊 Expense: ' . Account::where('account_type', 'expense')->count());
    }
}