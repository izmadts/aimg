@extends('layouts.app')

@section('title', 'Help / User Guide')

@section('content')
<div class="space-y-6">

    <!-- Page Header -->
    <div>
        <h1 class="text-2xl font-bold text-gray-900">❓ Help / User Guide</h1>
        <p class="text-sm text-gray-500">A quick, plain-English guide to using this system.</p>
    </div>

    <!-- Intro -->
    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-sm text-gray-700 leading-relaxed">
            This system helps you run your medical gas and cylinder business day to day: recording what you buy and sell,
            keeping track of which cylinders are in your warehouse or out with customers, and keeping your books
            (accounting) accurate automatically. You don't need any accounting background — the system posts the
            correct accounting entries for you every time you record a sale, purchase, payment, or expense.
        </p>
    </div>

    <!-- Getting Around -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-3"><i class="fas fa-compass text-blue-600 mr-2"></i>Getting Around</h2>
        <ul class="space-y-2 text-sm text-gray-700 list-disc list-inside">
            <li>The menu on the left is grouped by what you're trying to do: <strong>Operations</strong> (day-to-day sales, purchases, stock), <strong>Parties</strong> (customers &amp; suppliers), <strong>Finance</strong> (accounting reports), <strong>Human Resources</strong>, and <strong>Administration</strong> (users, roles, settings — only visible if you have access).</li>
            <li>Click the arrow at the top of the sidebar to collapse it to icons-only if you want more screen space. Hover an icon to see its name.</li>
            <li>Most list pages (Sales, Cylinders, Customers, etc.) have a search box and filters at the top, and colored count cards showing quick totals.</li>
            <li>The small colored badges next to a sidebar item (e.g. "Sales 12") show how many records exist in that section.</li>
        </ul>
    </div>

    <!-- Understanding actions -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-3"><i class="fas fa-icons text-blue-600 mr-2"></i>What the Icons Mean</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm">
            <div class="flex items-center gap-2"><i class="fas fa-eye text-blue-600 w-5"></i> View details</div>
            <div class="flex items-center gap-2"><i class="fas fa-edit text-yellow-600 w-5"></i> Edit</div>
            <div class="flex items-center gap-2"><i class="fas fa-trash text-red-600 w-5"></i> Delete</div>
            <div class="flex items-center gap-2"><i class="fas fa-check-circle text-green-600 w-5"></i> Approve</div>
            <div class="flex items-center gap-2"><i class="fas fa-times-circle text-red-600 w-5"></i> Reject / Cancel</div>
            <div class="flex items-center gap-2"><i class="fas fa-hand-holding text-green-600 w-5"></i> Issue to a customer</div>
            <div class="flex items-center gap-2"><i class="fas fa-boxes text-teal-600 w-5"></i> Update stock quantity</div>
            <div class="flex items-center gap-2"><i class="fas fa-file-export text-gray-600 w-5"></i> Export to a file</div>
        </div>
        <p class="text-xs text-gray-500 mt-4">
            <strong>A note on Delete:</strong> deleting a record that already affected your accounts (a paid salary, an
            approved advance, an income/expense entry) doesn't just erase the money trail — the system automatically
            reverses the accounting entries first, so your books always stay correct and balanced. You'll usually see a
            confirmation message before anything with money attached is removed.
        </p>
    </div>

    <!-- Modules -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4"><i class="fas fa-th-large text-blue-600 mr-2"></i>What Each Section Does</h2>
        <div class="space-y-5">

            <div>
                <h3 class="font-semibold text-gray-900"><i class="fas fa-flask text-indigo-500 mr-2"></i>Gas Products</h3>
                <p class="text-sm text-gray-600 mt-1">The types of gas you sell (Oxygen, Nitrogen, Argon, CO2, etc.), with their purchase price, sale price, and unit of measure (KG, Cubic Meter, Liters...). Each gas product also shows the cylinder sizes currently holding it.</p>
            </div>

            <div>
                <h3 class="font-semibold text-gray-900"><i class="fas fa-gas-pump text-yellow-600 mr-2"></i>Cylinders</h3>
                <p class="text-sm text-gray-600 mt-1">Cylinders are tracked by <em>type</em> (e.g. "Oxygen — Large"), not one by one. Each type shows how many you own (Stock), how many are currently with customers (Issued), and how many are free in your warehouse (Available). From here you can issue a cylinder to a customer, record one coming back, or adjust stock.</p>
            </div>

            <div>
                <h3 class="font-semibold text-gray-900"><i class="fas fa-file-invoice text-blue-500 mr-2"></i>Sales</h3>
                <p class="text-sm text-gray-600 mt-1">Create an invoice for a customer. A sale can be gas only, a cylinder only, or both together. Record payments against it as the customer pays, and the accounting entries (revenue, cash/receivable) are posted automatically.</p>
            </div>

            <div>
                <h3 class="font-semibold text-gray-900"><i class="fas fa-shopping-cart text-purple-500 mr-2"></i>Purchases</h3>
                <p class="text-sm text-gray-600 mt-1">Record what you buy from a supplier — gas refills, new cylinders, or cylinder exchanges. Approve a purchase once it's confirmed, and record payments the same way as sales.</p>
            </div>

            <div>
                <h3 class="font-semibold text-gray-900"><i class="fas fa-users text-green-600 mr-2"></i>Customers &amp; <i class="fas fa-truck text-orange-500 mr-1"></i>Suppliers</h3>
                <p class="text-sm text-gray-600 mt-1">Your contact list of who you sell to and who you buy from. Each one has a statement showing their full sales/purchase and payment history with you.</p>
            </div>

            <div>
                <h3 class="font-semibold text-gray-900"><i class="fas fa-balance-scale text-gray-700 mr-2"></i>Accounting, Accounts &amp; Income/Expense</h3>
                <p class="text-sm text-gray-600 mt-1">You never need to manually pick which accounts to debit or credit — the system figures that out from what actually happened (a cash sale, a bank payment, a cylinder deposit, etc.). Use <strong>Accounting</strong> to see the Trial Balance and Income Statement, <strong>Accounts</strong> to see or manage your chart of accounts, and <strong>Income/Expense</strong> to record money in or out that isn't a sale or purchase (e.g. rent, utilities, other income).</p>
            </div>

            <div>
                <h3 class="font-semibold text-gray-900"><i class="fas fa-users-cog text-blue-700 mr-2"></i>Human Resources (HRM)</h3>
                <p class="text-sm text-gray-600 mt-1">Manage your employees, mark daily attendance, process and pay monthly salaries, and handle advance/leave requests. Approving an advance or paying a salary automatically records the expense in your accounts.</p>
            </div>

            <div>
                <h3 class="font-semibold text-gray-900"><i class="fas fa-ruler text-pink-500 mr-2"></i>Units &amp; <i class="fas fa-shapes text-pink-500 mr-1"></i>Cylinder Types</h3>
                <p class="text-sm text-gray-600 mt-1">These control the dropdown lists used elsewhere. <strong>Units</strong> is the list of measurement units gas can be sold in. <strong>Cylinder Types</strong> is the list of cylinder sizes (e.g. Small, Medium, Large) with their default capacity and price premium. Add a new one here and it's instantly available when creating a gas product or cylinder — no need to edit anything else.</p>
            </div>

            <div>
                <h3 class="font-semibold text-gray-900"><i class="fas fa-user-shield text-red-600 mr-2"></i>Users, Roles &amp; Settings</h3>
                <p class="text-sm text-gray-600 mt-1">Only visible to administrators. <strong>Users</strong> creates staff logins. <strong>Roles &amp; Permissions</strong> controls exactly what each type of staff member can see and do (view, add, edit, delete — per section). <strong>System Settings</strong> currently controls whether new users can sign themselves up at the login page.</p>
            </div>

        </div>
    </div>

    <!-- Quick reference -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900"><i class="fas fa-bolt text-blue-600 mr-2"></i>I want to... quick reference</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <tbody class="divide-y divide-gray-200">
                    <tr><td class="px-6 py-3 text-gray-700">Sell gas or a cylinder to a customer</td><td class="px-6 py-3 font-medium">Sales → Add Sale</td></tr>
                    <tr><td class="px-6 py-3 text-gray-700">Record money received from a customer</td><td class="px-6 py-3 font-medium">Sales → open the invoice → Record Payment</td></tr>
                    <tr><td class="px-6 py-3 text-gray-700">Hand a cylinder to a customer / take one back</td><td class="px-6 py-3 font-medium">Cylinders → Issue / Return</td></tr>
                    <tr><td class="px-6 py-3 text-gray-700">Add new stock after buying from a supplier</td><td class="px-6 py-3 font-medium">Purchases → Add Purchase</td></tr>
                    <tr><td class="px-6 py-3 text-gray-700">Add a new gas type or cylinder size</td><td class="px-6 py-3 font-medium">Gas Products / Cylinders → Add, using Units / Cylinder Types to add new dropdown options first if needed</td></tr>
                    <tr><td class="px-6 py-3 text-gray-700">Pay staff salary</td><td class="px-6 py-3 font-medium">HRM → Salaries → Process Salary, then Pay</td></tr>
                    <tr><td class="px-6 py-3 text-gray-700">Record rent, utilities, or other expenses</td><td class="px-6 py-3 font-medium">Income/Expense → Add Expense</td></tr>
                    <tr><td class="px-6 py-3 text-gray-700">See if the business is profitable this month</td><td class="px-6 py-3 font-medium">Accounting → Income Statement</td></tr>
                    <tr><td class="px-6 py-3 text-gray-700">Give a staff member access to the system</td><td class="px-6 py-3 font-medium">Users → Add User (pick their Role)</td></tr>
                    <tr><td class="px-6 py-3 text-gray-700">Change what a role is allowed to do</td><td class="px-6 py-3 font-medium">Roles &amp; Permissions → Edit</td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
