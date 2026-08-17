<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeAdvance;
use App\Models\EmployeeLeave;
use App\Models\Account;
use App\Models\AccountingEntry;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HRMController extends Controller
{
    protected $accountingService;

    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    // ============================================
    // EMPLOYEES
    // ============================================

    public function employees(Request $request)
    {
        $query = Employee::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('employee_code', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status == 'active');
        }

        $employees = $query->orderBy('id', 'desc')->paginate(15);

        $stats = [
            'total' => Employee::count(),
            'active' => Employee::where('is_active', true)->count(),
            'inactive' => Employee::where('is_active', false)->count(),
            'total_salary' => Employee::sum('net_salary'),
            'total_advance' => EmployeeAdvance::where('status', 'approved')->sum('amount'),
        ];

        return view('hrm.employees.index', compact('employees', 'stats'));
    }

    public function createEmployee()
    {
        return view('hrm.employees.create');
    }

    public function storeEmployee(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:employees',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'cnic' => 'nullable|string|max:20|unique:employees',
            'designation' => 'required|string|max:100',
            'department' => 'required|string|max:100',
            'joining_date' => 'required|date',
            'basic_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:100',
            'bank_account' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string'
        ]);

        $validated['allowances'] = $validated['allowances'] ?? 0;
        $validated['deductions'] = $validated['deductions'] ?? 0;
        $validated['net_salary'] = ($validated['basic_salary'] ?? 0)
            + $validated['allowances']
            - $validated['deductions'];
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $employee = Employee::create($validated);

        return redirect()->route('hrm.employees')
            ->with('success', "Employee '{$employee->full_name}' created successfully!");
    }

    public function showEmployee($id)
    {
        $employee = Employee::with(['salaries' => function ($q) {
            $q->latest()->limit(5);
        }, 'advances' => function ($q) {
            $q->where('status', 'approved');
        }, 'attendances' => function ($q) {
            $q->latest()->limit(10);
        }, 'leaves' => function ($q) {
            $q->where('status', 'approved');
        }])->findOrFail($id);

        $stats = [
            'total_salary' => $employee->salaries()->sum('net_salary'),
            'total_advance' => $employee->advances()->where('status', 'approved')->sum('amount'),
            'total_attendance' => $employee->attendances()->count(),
            'present' => $employee->attendances()->where('status', 'present')->count(),
            'absent' => $employee->attendances()->where('status', 'absent')->count(),
            'late' => $employee->attendances()->where('status', 'late')->count(),
            'total_leaves' => $employee->leaves()->where('status', 'approved')->count(),
        ];

        return view('hrm.employees.show', compact('employee', 'stats'));
    }

    public function editEmployee($id)
    {
        $employee = Employee::findOrFail($id);
        return view('hrm.employees.edit', compact('employee'));
    }

    public function updateEmployee(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:employees,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'cnic' => 'nullable|string|max:20|unique:employees,cnic,' . $id,
            'designation' => 'required|string|max:100',
            'department' => 'required|string|max:100',
            'joining_date' => 'required|date',
            'basic_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:100',
            'bank_account' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string'
        ]);

        $validated['allowances'] = $validated['allowances'] ?? 0;
        $validated['deductions'] = $validated['deductions'] ?? 0;
        $validated['net_salary'] = ($validated['basic_salary'] ?? 0)
            + $validated['allowances']
            - $validated['deductions'];
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $employee->update($validated);

        return redirect()->route('hrm.employees')
            ->with('success', "Employee '{$employee->full_name}' updated successfully!");
    }

    public function deleteEmployee($id)
    {
        $employee = Employee::findOrFail($id);

        $hasRecords = $employee->salaries()->exists()
            || $employee->advances()->exists()
            || $employee->attendances()->exists()
            || $employee->leaves()->exists();

        if ($hasRecords && !auth()->user()->isAdmin()) {
            return redirect()->back()
                ->with('error', "Cannot delete '{$employee->full_name}' because they have salary, advance, attendance, or leave records. Only an administrator can delete an employee along with their history.");
        }

        DB::transaction(function () use ($employee) {
            // Reverse any posted accounting entries before the cascade below removes
            // the salary/advance rows those entries point to via reference_id.
            foreach ($employee->salaries as $salary) {
                $this->accountingService->reverseEntries($salary, 'salary', "Employee deleted: {$employee->full_name}");
            }
            foreach ($employee->advances as $advance) {
                $this->accountingService->reverseEntries($advance, 'advance', "Employee deleted: {$employee->full_name}");
            }

            // Salaries/advances/attendances/leaves cascade-delete via their
            // employee_id foreign key (onDelete('cascade')).
            $employee->delete();
        });

        return redirect()->route('hrm.employees')
            ->with('success', 'Employee deleted successfully' . ($hasRecords ? ', along with their related records. Accounting entries were reversed.' : '.'));
    }

    // ============================================
    // SALARIES
    // ============================================

    public function salaries(Request $request)
    {
        $query = EmployeeSalary::with('employee');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        $salaries = $query->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(15);

        $stats = [
            'total' => EmployeeSalary::count(),
            'pending' => EmployeeSalary::where('status', 'pending')->count(),
            'paid' => EmployeeSalary::where('status', 'paid')->count(),
            'total_amount' => EmployeeSalary::sum('net_salary'),
        ];

        $employees = Employee::where('is_active', true)->get();

        return view('hrm.salaries.index', compact('salaries', 'stats', 'employees'));
    }

    public function createSalary()
    {
        $employees = Employee::where('is_active', true)->get();
        $currentMonth = date('m');
        $currentYear = date('Y');

        return view('hrm.salaries.create', compact('employees', 'currentMonth', 'currentYear'));
    }

    public function processSalary(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        DB::transaction(function () use ($request) {
            $employee = Employee::find($request->employee_id);
            
            $existing = EmployeeSalary::where('employee_id', $employee->id)
                ->where('month', $request->month)
                ->where('year', $request->year)
                ->first();

            if ($existing) {
                throw new \Exception('Salary already processed for this month.');
            }

            $basicSalary = $employee->basic_salary;
            $allowances = $employee->allowances ?? 0;
            $deductions = $employee->deductions ?? 0;

            // ✅ Calculate advance deduction
            $advanceDeduction = EmployeeAdvance::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->where('deduction_start_month', '<=', now())
                ->sum('deduction_amount_per_month');

            $netSalary = $basicSalary + $allowances - $deductions - $advanceDeduction;

            $salary = EmployeeSalary::create([
                'employee_id' => $employee->id,
                'month' => $request->month,
                'year' => $request->year,
                'basic_salary' => $basicSalary,
                'allowances' => $allowances,
                'deductions' => $deductions,
                'advance_deduction' => $advanceDeduction,
                'net_salary' => $netSalary,
                'status' => 'pending',
                'created_by' => auth()->id(),
            ]);

            // ✅ Auto Accounting - Salary Expense
            $this->recordSalaryAccounting($employee, $salary);
        });

        return redirect()->route('hrm.salaries')
            ->with('success', 'Salary processed successfully!');
    }

    public function paySalary(EmployeeSalary $salary, Request $request)
    {
        $request->validate([
            'payment_method' => 'nullable|in:cash,bank_transfer,cheque,online',
        ]);

        if ($salary->status === 'paid') {
            $message = 'This salary was already paid.';
            return $request->ajax()
                ? response()->json(['success' => false, 'message' => $message], 400)
                : redirect()->route('hrm.salaries')->with('error', $message);
        }

        DB::transaction(function () use ($salary, $request) {
            $salary->update([
                'status' => 'paid',
                'paid_date' => now(),
                'payment_method' => $request->payment_method ?? 'cash',
                'transaction_no' => 'SAL-' . time(),
            ]);

            // ✅ Auto Accounting - Salary Payment
            $this->recordSalaryPayment($salary);
        });

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Salary paid successfully!'
            ]);
        }

        return redirect()->route('hrm.salaries')
            ->with('success', 'Salary paid successfully!');
    }

    public function deleteSalary($id)
    {
        $salary = EmployeeSalary::with('employee')->findOrFail($id);

        DB::transaction(function () use ($salary) {
            $this->accountingService->reverseEntries(
                $salary,
                'salary',
                "Deleted salary record: {$salary->employee->full_name} - {$salary->month_name} {$salary->year}"
            );
            $salary->delete();
        });

        return redirect()->route('hrm.salaries')
            ->with('success', 'Salary record deleted. Any related accounting entries were reversed.');
    }

    // ============================================
    // ATTENDANCE
    // ============================================

    public function attendance(Request $request)
    {
        $query = EmployeeAttendance::with('employee');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', $request->to_date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $attendances = $query->orderBy('date', 'desc')->paginate(15);

        // Today's stats
        $todayStats = [
            'present' => EmployeeAttendance::where('status', 'present')->whereDate('date', today())->count(),
            'absent' => EmployeeAttendance::where('status', 'absent')->whereDate('date', today())->count(),
            'late' => EmployeeAttendance::where('status', 'late')->whereDate('date', today())->count(),
            'leave' => EmployeeAttendance::where('status', 'leave')->whereDate('date', today())->count(),
            'holiday' => EmployeeAttendance::where('status', 'holiday')->whereDate('date', today())->count(),
        ];

        $employees = Employee::where('is_active', true)->get();

        return view('hrm.attendance.index', compact('attendances', 'todayStats', 'employees'));
    }

    public function markAttendance(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,late,leave,holiday',
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i|after:check_in_time',
            'notes' => 'nullable|string|max:1000'
        ]);

        $existing = EmployeeAttendance::where('employee_id', $request->employee_id)
            ->whereDate('date', $request->date)
            ->first();

        if ($existing) {
            return redirect()->back()
                ->with('error', 'Attendance already marked for this date.');
        }

        $lateMinutes = 0;
        if ($request->check_in_time && $request->status === 'late') {
            $checkIn = \Carbon\Carbon::parse($request->date . ' ' . $request->check_in_time);
            $officeStart = \Carbon\Carbon::parse($request->date . ' 09:00:00');
            if ($checkIn > $officeStart) {
                $lateMinutes = $checkIn->diffInMinutes($officeStart);
            }
        }

        EmployeeAttendance::create([
            'employee_id' => $request->employee_id,
            'date' => $request->date,
            'status' => $request->status,
            'check_in_time' => $request->check_in_time ? $request->date . ' ' . $request->check_in_time : null,
            'check_out_time' => $request->check_out_time ? $request->date . ' ' . $request->check_out_time : null,
            'late_minutes' => $lateMinutes,
            'created_by' => auth()->id(),
            'notes' => $request->notes,
        ]);

        return redirect()->route('hrm.attendance')
            ->with('success', 'Attendance marked successfully!');
    }

    public function deleteAttendance($id)
    {
        $attendance = EmployeeAttendance::findOrFail($id);
        $attendance->delete();

        return redirect()->route('hrm.attendance')
            ->with('success', 'Attendance record deleted!');
    }

    // ============================================
    // ADVANCES
    // ============================================

    public function advances(Request $request)
    {
        $query = EmployeeAdvance::with('employee');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $advances = $query->orderBy('created_at', 'desc')->paginate(15);

        $stats = [
            'total' => EmployeeAdvance::count(),
            'pending' => EmployeeAdvance::where('status', 'pending')->count(),
            'approved' => EmployeeAdvance::where('status', 'approved')->count(),
            'total_amount' => EmployeeAdvance::sum('amount'),
        ];

        $employees = Employee::where('is_active', true)->get();

        return view('hrm.advances.index', compact('advances', 'stats', 'employees'));
    }

    public function createAdvance(Request $request)
    {
        $employees = Employee::where('is_active', true)->get();
        return view('hrm.advances.create', compact('employees'));
    }

    public function storeAdvance(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:0.01',
            'advance_date' => 'required|date',
            'deduction_start_month' => 'required|date|after_or_equal:advance_date',
            'deduction_installments' => 'required|integer|min:1|max:12',
            'purpose' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000'
        ]);

        $validated['deduction_amount_per_month'] = $validated['amount'] / $validated['deduction_installments'];
        $validated['status'] = 'pending';
        $validated['created_by'] = auth()->id();

        $advance = EmployeeAdvance::create($validated);

        return redirect()->route('hrm.advances')
            ->with('success', 'Advance request created successfully!');
    }

    public function approveAdvance(EmployeeAdvance $advance, Request $request)
    {
        if ($advance->status !== 'pending') {
            $message = 'Only a pending advance can be approved.';
            return $request->ajax()
                ? response()->json(['success' => false, 'message' => $message], 400)
                : redirect()->route('hrm.advances')->with('error', $message);
        }

        DB::transaction(function () use ($advance) {
            $advance->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // ✅ Auto Accounting - Employee Advance
            $this->recordAdvanceAccounting($advance);
        });

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Advance approved successfully!'
            ]);
        }

        return redirect()->route('hrm.advances')
            ->with('success', 'Advance approved successfully!');
    }

    public function rejectAdvance(EmployeeAdvance $advance, Request $request)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($advance->status !== 'pending') {
            return redirect()->route('hrm.advances')->with('error', 'Only a pending advance can be rejected.');
        }

        $advance->update([
            'status' => 'rejected',
            'notes' => $request->notes ?? 'Rejected'
        ]);

        return redirect()->route('hrm.advances')
            ->with('success', 'Advance rejected successfully!');
    }

    public function deleteAdvance($id)
    {
        $advance = EmployeeAdvance::with('employee')->findOrFail($id);

        DB::transaction(function () use ($advance) {
            // No-ops safely if the advance was never approved (nothing was posted).
            $this->accountingService->reverseEntries(
                $advance,
                'advance',
                "Deleted advance record: {$advance->employee->full_name}"
            );
            $advance->delete();
        });

        return redirect()->route('hrm.advances')
            ->with('success', 'Advance record deleted. Any related accounting entries were reversed.');
    }

    // ============================================
    // LEAVES
    // ============================================

    public function leaves(Request $request)
    {
        $query = EmployeeLeave::with('employee');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $leaves = $query->orderBy('created_at', 'desc')->paginate(15);

        $stats = [
            'total' => EmployeeLeave::count(),
            'pending' => EmployeeLeave::where('status', 'pending')->count(),
            'approved' => EmployeeLeave::where('status', 'approved')->count(),
            'rejected' => EmployeeLeave::where('status', 'rejected')->count(),
        ];

        $employees = Employee::where('is_active', true)->get();

        return view('hrm.leaves.index', compact('leaves', 'stats', 'employees'));
    }

    public function createLeave()
    {
        $employees = Employee::where('is_active', true)->get();
        return view('hrm.leaves.create', compact('employees'));
    }

    public function storeLeave(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type' => 'required|in:annual,sick,casual,other',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000'
        ]);

        $start = \Carbon\Carbon::parse($request->start_date);
        $end = \Carbon\Carbon::parse($request->end_date);
        $days = $start->diffInDays($end) + 1;

        $leave = EmployeeLeave::create([
            'employee_id' => $request->employee_id,
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'days' => $days,
            'reason' => $request->reason,
            'status' => 'pending',
            'created_by' => auth()->id(),
            'notes' => $request->notes,
        ]);

        return redirect()->route('hrm.leaves')
            ->with('success', 'Leave request submitted successfully!');
    }

    public function approveLeave(EmployeeLeave $leave, Request $request)
    {
        if ($leave->status !== 'pending') {
            $message = 'Only a pending leave request can be approved.';
            return $request->ajax()
                ? response()->json(['success' => false, 'message' => $message], 400)
                : redirect()->route('hrm.leaves')->with('error', $message);
        }

        $leave->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Leave approved successfully!'
            ]);
        }

        return redirect()->route('hrm.leaves')
            ->with('success', 'Leave approved successfully!');
    }

    public function rejectLeave(EmployeeLeave $leave, Request $request)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($leave->status !== 'pending') {
            return redirect()->route('hrm.leaves')->with('error', 'Only a pending leave request can be rejected.');
        }

        $leave->update([
            'status' => 'rejected',
            'notes' => $request->notes ?? 'Rejected'
        ]);

        return redirect()->route('hrm.leaves')
            ->with('success', 'Leave rejected!');
    }

    public function deleteLeave($id)
    {
        $leave = EmployeeLeave::findOrFail($id);
        $leave->delete();

        return redirect()->route('hrm.leaves')
            ->with('success', 'Leave record deleted.');
    }

    // ============================================
    // ACCOUNTING HELPERS
    // ============================================

    private function recordSalaryAccounting($employee, $salary)
    {
        try {
            $salaryExpense = Account::where('account_code', '5002')->first();
            $payableAccount = Account::where('account_code', '2001')->first();

            if ($salaryExpense && $payableAccount) {
                // Debit: Salary Expense
                AccountingEntry::create([
                    'entry_no' => AccountingEntry::generateEntryNo(),
                    'date' => now(),
                    'description' => "Salary: {$employee->full_name} - {$salary->month_name} {$salary->year}",
                    'transaction_type' => 'salary',
                    'reference_type' => get_class($salary),
                    'reference_id' => $salary->id,
                    'account_id' => $salaryExpense->id,
                    'opposite_account_id' => $payableAccount->id,
                    'debit' => $salary->net_salary,
                    'credit' => 0,
                    'status' => 'approved',
                    'created_by' => auth()->id(),
                ]);

                // Credit: Salary Payable
                AccountingEntry::create([
                    'entry_no' => AccountingEntry::generateEntryNo(),
                    'date' => now(),
                    'description' => "Salary Payable: {$employee->full_name}",
                    'transaction_type' => 'salary',
                    'reference_type' => get_class($salary),
                    'reference_id' => $salary->id,
                    'account_id' => $payableAccount->id,
                    'opposite_account_id' => $salaryExpense->id,
                    'debit' => 0,
                    'credit' => $salary->net_salary,
                    'status' => 'approved',
                    'created_by' => auth()->id(),
                ]);

                $salaryExpense->updateBalance();
                $payableAccount->updateBalance();
            }
        } catch (\Exception $e) {
            Log::error('Salary accounting error: ' . $e->getMessage());
        }
    }

    private function recordSalaryPayment($salary)
    {
        try {
            $cashAccount = Account::where('account_code', '1001')->first();
            $payableAccount = Account::where('account_code', '2001')->first();

            if ($cashAccount && $payableAccount) {
                // Debit: Salary Payable
                AccountingEntry::create([
                    'entry_no' => AccountingEntry::generateEntryNo(),
                    'date' => now(),
                    'description' => "Salary Payment: {$salary->employee->full_name}",
                    'transaction_type' => 'payment',
                    'reference_type' => get_class($salary),
                    'reference_id' => $salary->id,
                    'account_id' => $payableAccount->id,
                    'opposite_account_id' => $cashAccount->id,
                    'debit' => $salary->net_salary,
                    'credit' => 0,
                    'status' => 'approved',
                    'created_by' => auth()->id(),
                ]);

                // Credit: Cash
                AccountingEntry::create([
                    'entry_no' => AccountingEntry::generateEntryNo(),
                    'date' => now(),
                    'description' => "Cash Payment: {$salary->employee->full_name}",
                    'transaction_type' => 'payment',
                    'reference_type' => get_class($salary),
                    'reference_id' => $salary->id,
                    'account_id' => $cashAccount->id,
                    'opposite_account_id' => $payableAccount->id,
                    'debit' => 0,
                    'credit' => $salary->net_salary,
                    'status' => 'approved',
                    'created_by' => auth()->id(),
                ]);

                $cashAccount->updateBalance();
                $payableAccount->updateBalance();
            }
        } catch (\Exception $e) {
            Log::error('Salary payment accounting error: ' . $e->getMessage());
        }
    }

    private function recordAdvanceAccounting($advance)
    {
        try {
            $advanceAsset = Account::where('account_code', '1006')->first();
            $cashAccount = Account::where('account_code', '1001')->first();

            if ($advanceAsset && $cashAccount) {
                // Debit: Employee Advances
                AccountingEntry::create([
                    'entry_no' => AccountingEntry::generateEntryNo(),
                    'date' => now(),
                    'description' => "Employee Advance: {$advance->employee->full_name}",
                    'transaction_type' => 'advance',
                    'reference_type' => get_class($advance),
                    'reference_id' => $advance->id,
                    'account_id' => $advanceAsset->id,
                    'opposite_account_id' => $cashAccount->id,
                    'debit' => $advance->amount,
                    'credit' => 0,
                    'status' => 'approved',
                    'created_by' => auth()->id(),
                ]);

                // Credit: Cash
                AccountingEntry::create([
                    'entry_no' => AccountingEntry::generateEntryNo(),
                    'date' => now(),
                    'description' => "Cash: Employee Advance {$advance->employee->full_name}",
                    'transaction_type' => 'advance',
                    'reference_type' => get_class($advance),
                    'reference_id' => $advance->id,
                    'account_id' => $cashAccount->id,
                    'opposite_account_id' => $advanceAsset->id,
                    'debit' => 0,
                    'credit' => $advance->amount,
                    'status' => 'approved',
                    'created_by' => auth()->id(),
                ]);

                $advanceAsset->updateBalance();
                $cashAccount->updateBalance();
            }
        } catch (\Exception $e) {
            Log::error('Advance accounting error: ' . $e->getMessage());
        }
    }
}