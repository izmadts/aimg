<?php

namespace App\Http\Controllers;

use App\Services\AccountingService;
use Illuminate\Http\Request;

class AccountingController extends Controller
{
    protected $accountingService;

    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    public function trialBalance()
    {
        $trialBalance = $this->accountingService->getTrialBalance();
        return view('accounting.trial-balance', compact('trialBalance'));
    }

    public function incomeStatement(Request $request)
    {
        $startDate = $request->filled('start_date') ? $request->start_date : now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->filled('end_date') ? $request->end_date : now()->format('Y-m-d');

        $incomeStatement = $this->accountingService->getIncomeStatement($startDate, $endDate);

        return view('accounting.income-statement', compact('incomeStatement', 'startDate', 'endDate'));
    }

    public function balanceSheet()
    {
        $balanceSheet = $this->accountingService->getBalanceSheet();
        return view('accounting.balance-sheet', compact('balanceSheet'));
    }
}