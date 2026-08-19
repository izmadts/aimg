@extends('layouts.app')

@section('title', 'Help / User Guide')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;600;700&display=swap" rel="stylesheet">
<style>
    .font-urdu { font-family: 'Noto Nastaliq Urdu', 'Noto Sans Arabic', Tahoma, sans-serif; line-height: 2.1; }
</style>
@endpush

@section('content')
<div class="space-y-6">

    <!-- Page Header + Language Toggle -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">❓ Help / User Guide</h1>
            <p class="text-sm text-gray-500">A plain-language guide to using this system — in English or Urdu.</p>
        </div>
        <div class="inline-flex rounded-lg shadow-sm border border-gray-300 overflow-hidden" role="group">
            <button type="button" id="langBtnEn" onclick="setGuideLang('en')"
                    class="px-4 py-2 text-sm font-medium transition">English</button>
            <button type="button" id="langBtnUr" onclick="setGuideLang('ur')"
                    class="px-4 py-2 text-sm font-medium transition">اردو</button>
        </div>
    </div>

    <!-- ============================================
         ENGLISH
         ============================================ -->
    <div data-lang="en" class="space-y-6">

        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-700 leading-relaxed">
                This system helps you run your medical gas and cylinder business day to day: recording what you buy and sell,
                keeping track of which cylinders are in your warehouse or out with customers, and keeping your books
                (accounting) accurate automatically. You don't need any accounting background — the system posts the
                correct accounting entries for you every time you record a sale, purchase, payment, or expense.
            </p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-3"><i class="fas fa-compass text-blue-600 mr-2"></i>Getting Around</h2>
            <ul class="space-y-2 text-sm text-gray-700 list-disc list-inside">
                <li>The menu on the left is grouped by what you're trying to do: <strong>Operations</strong> (day-to-day sales, purchases, stock), <strong>Parties</strong> (customers &amp; suppliers), <strong>Finance</strong> (accounting reports), <strong>Human Resources</strong>, and <strong>Administration</strong> (users, roles, settings — only visible if you have access).</li>
                <li>Click the arrow at the top of the sidebar to collapse it to icons-only if you want more screen space. Hover an icon to see its name.</li>
                <li>Most list pages (Sales, Cylinders, Customers, etc.) have a search box and filters at the top, and colored count cards showing quick totals.</li>
                <li>The small colored badges next to a sidebar item (e.g. "Sales 12") show how many records exist in that section.</li>
            </ul>
        </div>

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
                <div class="flex items-center gap-2"><i class="fas fa-undo text-green-600 w-5"></i> Return a cylinder</div>
                <div class="flex items-center gap-2"><i class="fas fa-truck-loading text-blue-600 w-5"></i> Gas transfer (bulk → cylinder)</div>
                <div class="flex items-center gap-2"><i class="fas fa-tools text-purple-600 w-5"></i> Repair / scrap a cylinder</div>
            </div>
            <p class="text-xs text-gray-500 mt-4">
                <strong>A note on Delete:</strong> deleting a record that already affected your accounts (a paid salary, an
                approved advance, an income/expense entry) doesn't just erase the money trail — the system automatically
                reverses the accounting entries first, so your books always stay correct and balanced. You'll usually see a
                confirmation message before anything with money attached is removed.
            </p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4"><i class="fas fa-th-large text-blue-600 mr-2"></i>What Each Section Does</h2>
            <div class="space-y-5">

                <div>
                    <h3 class="font-semibold text-gray-900"><i class="fas fa-flask text-indigo-500 mr-2"></i>Gas Products</h3>
                    <p class="text-sm text-gray-600 mt-1">The types of gas you sell (Oxygen, Nitrogen, Argon, CO2, etc.), with their purchase price, sale price, and unit of measure (KG, Cubic Meter, Liters...). Each gas product also shows the cylinder sizes currently holding it. If a gas is measured in Cubic Meter and you've entered its density, the system automatically shows the stock in KG too.</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900"><i class="fas fa-gas-pump text-yellow-600 mr-2"></i>Cylinders</h3>
                    <p class="text-sm text-gray-600 mt-1">Cylinders are tracked by <em>type</em> (e.g. "Oxygen — Large"), not one by one. Each type shows: <strong>Total</strong> you own, <strong>Issued</strong> with customers, <strong>Filled</strong> ready to sell/issue, <strong>Empty</strong> in the warehouse waiting for gas, <strong>Under Repair</strong> being fixed, and <strong>Scrapped</strong> flagged as junk pending disposal. From here you can issue a cylinder, record one coming back, adjust stock, or manage repair/scrap.</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900"><i class="fas fa-tools text-purple-500 mr-2"></i>Repair &amp; Scrap</h3>
                    <p class="text-sm text-gray-600 mt-1">Use the 🔧 button on a cylinder row to send empty units for repair, bring repaired units back to the Empty pool, mark units as scrap (from either the Empty or Under-Repair pool — no accounting impact yet, still an owned asset), or formally <strong>Dispose</strong> scrapped units. Disposing is the only step that permanently removes them from your fleet and posts a write-off loss in your accounts.</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900"><i class="fas fa-truck-loading text-blue-500 mr-2"></i>Gas Transfers</h3>
                    <p class="text-sm text-gray-600 mt-1">When you buy gas in bulk (a "bowser"/"bonser" delivery) and then load it into your saleable cylinders, use <strong>Cylinders → Gas Transfers</strong> to record it: pick the gas, pick which cylinder size received it, and how much gas moved. This reduces your bulk stock total and keeps a permanent record of which cylinder sizes were filled. It doesn't change how many cylinders you own — only gas moving from bulk storage into your cylinder stock.</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900"><i class="fas fa-file-invoice text-blue-500 mr-2"></i>Sales</h3>
                    <p class="text-sm text-gray-600 mt-1">Create an invoice for a customer. A sale can be gas only, a cylinder only, or both together. If you use a pre-printed Khata receipt book, type its number into the <strong>ECR #</strong> field — the system's own invoice number keeps auto-generating separately either way. You can combine a return and a new issue/sale in the same invoice: add one line per cylinder type/size and pick the right Action (Return / Issue / Sell) — e.g. a customer returns 10 empty small cylinders and takes 11 filled large ones, all on one invoice. Record payments as the customer pays, and the accounting entries (revenue, cash/receivable, deposit refunds) are posted automatically.</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900"><i class="fas fa-shopping-cart text-purple-500 mr-2"></i>Purchases</h3>
                    <p class="text-sm text-gray-600 mt-1">Record what you buy from a supplier — gas refills, new cylinders, or cylinder exchanges. While typing a gas quantity, if the product has a density set, the system shows the equivalent in the other unit automatically (type in KG and see Cubic Meters, or the reverse) — handy when your supplier bills in one unit but you sell in another. Approve a purchase once it's confirmed, and record payments the same way as sales.</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900"><i class="fas fa-users text-green-600 mr-2"></i>Customers &amp; <i class="fas fa-truck text-orange-500 mr-1"></i>Suppliers</h3>
                    <p class="text-sm text-gray-600 mt-1">Your contact list of who you sell to and who you buy from. Each one has a statement showing their full sales/purchase and payment history with you. Every new customer/supplier gets an ID automatically (e.g. <code class="bg-gray-100 px-1 rounded">CUST-000001</code>) — you don't type one in. Phone number is required; everything else is optional. If a customer or supplier already had a balance before you started using this system, enter it as their <strong>Opening Balance</strong> when adding them. If a customer is already holding some of your cylinders from before (e.g. moving over from a paper record), list them under <strong>Cylinders Already With This Customer</strong> on the same form so your stock stays accurate.</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900"><i class="fas fa-balance-scale text-gray-700 mr-2"></i>Accounting, Accounts &amp; Income/Expense</h3>
                    <p class="text-sm text-gray-600 mt-1">You never need to manually pick which accounts to debit or credit — the system figures that out from what actually happened (a cash sale, a bank payment, a cylinder deposit, a scrapped cylinder write-off, etc.). Use <strong>Accounting</strong> to see the Trial Balance and Income Statement, <strong>Accounts</strong> to see or manage your chart of accounts, and <strong>Income/Expense</strong> to record money in or out that isn't a sale or purchase (e.g. rent, utilities, other income).</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900"><i class="fas fa-users-cog text-blue-700 mr-2"></i>Human Resources (HRM)</h3>
                    <p class="text-sm text-gray-600 mt-1">Manage your employees, mark daily attendance, process and pay monthly salaries, and handle advance/leave requests. Approving an advance or paying a salary automatically records the expense in your accounts.</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900"><i class="fas fa-ruler text-pink-500 mr-2"></i>Units &amp; <i class="fas fa-shapes text-pink-500 mr-1"></i>Cylinder Types</h3>
                    <p class="text-sm text-gray-600 mt-1">These control the dropdown lists used elsewhere. <strong>Units</strong> is the list of measurement units gas can be sold in. <strong>Cylinder Types</strong> is the list of cylinder sizes (e.g. Small, Medium, Large) with their default capacity. Add a new one here and it's instantly available when creating a gas product or cylinder — no need to edit anything else.</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900"><i class="fas fa-user-shield text-red-600 mr-2"></i>Users, Roles &amp; Settings</h3>
                    <p class="text-sm text-gray-600 mt-1">Only visible to administrators. <strong>Users</strong> creates staff logins. <strong>Roles &amp; Permissions</strong> controls exactly what each type of staff member can see and do (view, add, edit, delete — per section). <strong>System Settings</strong> currently controls whether new users can sign themselves up at the login page.</p>
                </div>

            </div>
        </div>

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
                        <tr><td class="px-6 py-3 text-gray-700">Return old cylinders and issue new ones in one invoice</td><td class="px-6 py-3 font-medium">Sales → Add Sale → one line per size, Action = Return / Issue / Sell</td></tr>
                        <tr><td class="px-6 py-3 text-gray-700">Move gas from bulk/bowser stock into cylinders</td><td class="px-6 py-3 font-medium">Cylinders → Gas Transfers → New Transfer</td></tr>
                        <tr><td class="px-6 py-3 text-gray-700">Send a cylinder for repair, or scrap it</td><td class="px-6 py-3 font-medium">Cylinders → 🔧 button on the row</td></tr>
                        <tr><td class="px-6 py-3 text-gray-700">Add new stock after buying from a supplier</td><td class="px-6 py-3 font-medium">Purchases → Add Purchase</td></tr>
                        <tr><td class="px-6 py-3 text-gray-700">Add a new gas type or cylinder size</td><td class="px-6 py-3 font-medium">Gas Products / Cylinders → Add, using Units / Cylinder Types to add new dropdown options first if needed</td></tr>
                        <tr><td class="px-6 py-3 text-gray-700">Onboard a customer who already owes money or holds cylinders</td><td class="px-6 py-3 font-medium">Customers → Add Customer → fill in Opening Balance and/or Cylinders Already With This Customer</td></tr>
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

    <!-- ============================================
         اردو (URDU)
         ============================================ -->
    <div data-lang="ur" dir="rtl" class="hidden font-urdu space-y-6">

        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-700">
                یہ سسٹم آپ کے میڈیکل گیس اور سلنڈر کے کاروبار کو روزانہ کی بنیاد پر چلانے میں مدد کرتا ہے: آپ کیا خریدتے اور بیچتے ہیں اس کا ریکارڈ رکھنا، کون سے سلنڈر گودام میں ہیں یا کسٹمرز کے پاس ہیں اس کا حساب رکھنا، اور آپ کا حساب کتاب (اکاؤنٹنگ) خود بخود درست رکھنا۔ آپ کو اکاؤنٹنگ کی کوئی معلومات کی ضرورت نہیں — جب بھی آپ سیل، خریداری، ادائیگی یا خرچہ درج کرتے ہیں، سسٹم خود درست اکاؤنٹنگ اندراج کر دیتا ہے۔
            </p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-3"><i class="fas fa-compass text-blue-600 ml-2"></i>سسٹم میں کیسے چلیں</h2>
            <ul class="space-y-2 text-sm text-gray-700 list-disc list-inside">
                <li>بائیں طرف کا مینیو اس بنیاد پر گروپ کیا گیا ہے کہ آپ کیا کرنا چاہتے ہیں: <strong>آپریشنز</strong> (روزمرہ کی سیلز، خریداری، اسٹاک)، <strong>پارٹیز</strong> (کسٹمرز اور سپلائرز)، <strong>فنانس</strong> (اکاؤنٹنگ رپورٹس)، <strong>ہیومن ریسورسز</strong>، اور <strong>ایڈمنسٹریشن</strong> (یوزرز، رولز، سیٹنگز — صرف اگر آپ کو رسائی حاصل ہو)۔</li>
                <li>سائڈبار کے اوپر تیر پر کلک کر کے اسے صرف آئیکنز تک سکیڑا جا سکتا ہے تاکہ اسکرین پر زیادہ جگہ ملے۔ نام دیکھنے کے لیے آئیکن پر ماؤس رکھیں۔</li>
                <li>زیادہ تر فہرست والے صفحات (سیلز، سلنڈرز، کسٹمرز وغیرہ) کے اوپر سرچ باکس اور فلٹرز ہوتے ہیں، اور رنگین کارڈز فوری ٹوٹل دکھاتے ہیں۔</li>
                <li>سائڈبار آئٹم کے ساتھ چھوٹے رنگین بیج (مثلاً "Sales 12") اس سیکشن میں موجود ریکارڈز کی تعداد دکھاتے ہیں۔</li>
            </ul>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-3"><i class="fas fa-icons text-blue-600 ml-2"></i>آئیکنز کا مطلب</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm">
                <div class="flex items-center gap-2"><i class="fas fa-eye text-blue-600 w-5"></i> تفصیل دیکھیں</div>
                <div class="flex items-center gap-2"><i class="fas fa-edit text-yellow-600 w-5"></i> ترمیم کریں</div>
                <div class="flex items-center gap-2"><i class="fas fa-trash text-red-600 w-5"></i> حذف کریں</div>
                <div class="flex items-center gap-2"><i class="fas fa-check-circle text-green-600 w-5"></i> منظور کریں</div>
                <div class="flex items-center gap-2"><i class="fas fa-times-circle text-red-600 w-5"></i> مسترد / منسوخ کریں</div>
                <div class="flex items-center gap-2"><i class="fas fa-hand-holding text-green-600 w-5"></i> کسٹمر کو جاری کریں</div>
                <div class="flex items-center gap-2"><i class="fas fa-boxes text-teal-600 w-5"></i> اسٹاک کی مقدار اپڈیٹ کریں</div>
                <div class="flex items-center gap-2"><i class="fas fa-file-export text-gray-600 w-5"></i> فائل میں ایکسپورٹ کریں</div>
                <div class="flex items-center gap-2"><i class="fas fa-undo text-green-600 w-5"></i> سلنڈر واپس لیں</div>
                <div class="flex items-center gap-2"><i class="fas fa-truck-loading text-blue-600 w-5"></i> گیس ٹرانسفر (بلک سے سلنڈر)</div>
                <div class="flex items-center gap-2"><i class="fas fa-tools text-purple-600 w-5"></i> سلنڈر کی مرمت / اسکریپ</div>
            </div>
            <p class="text-xs text-gray-500 mt-4">
                <strong>حذف کرنے کے بارے میں ایک نوٹ:</strong> ایسا ریکارڈ حذف کرنا جو پہلے سے آپ کے اکاؤنٹس پر اثر ڈال چکا ہے (ادا شدہ تنخواہ، منظور شدہ ایڈوانس، آمدنی/خرچہ اندراج) صرف پیسوں کا ریکارڈ نہیں مٹاتا — سسٹم خود بخود پہلے اکاؤنٹنگ اندراجات کو ریورس کرتا ہے، تاکہ آپ کا حساب کتاب ہمیشہ درست اور متوازن رہے۔ عام طور پر پیسوں سے جڑی کوئی چیز ہٹانے سے پہلے آپ کو تصدیقی پیغام نظر آئے گا۔
            </p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4"><i class="fas fa-th-large text-blue-600 ml-2"></i>ہر سیکشن کیا کرتا ہے</h2>
            <div class="space-y-5">

                <div>
                    <h3 class="font-semibold text-gray-900"><i class="fas fa-flask text-indigo-500 ml-2"></i>گیس پروڈکٹس</h3>
                    <p class="text-sm text-gray-600 mt-1">آپ جو گیسیں بیچتے ہیں ان کی اقسام (آکسیجن، نائٹروجن، آرگن، CO2 وغیرہ)، ان کی خریداری قیمت، فروخت قیمت، اور پیمائش کی اکائی (KG، کیوبک میٹر، لیٹرز...) کے ساتھ۔ ہر گیس پروڈکٹ اُن سلنڈر سائزز کو بھی دکھاتا ہے جو فی الحال یہ گیس رکھتے ہیں۔ اگر گیس کیوبک میٹر میں ماپی جاتی ہے اور آپ نے اس کی کثافت (density) درج کر دی ہے، تو سسٹم خود بخود اسٹاک KG میں بھی دکھا دیتا ہے۔</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900"><i class="fas fa-gas-pump text-yellow-600 ml-2"></i>سلنڈرز</h3>
                    <p class="text-sm text-gray-600 mt-1">سلنڈرز کو <em>قسم</em> کے حساب سے ٹریک کیا جاتا ہے (مثلاً "آکسیجن — بڑا")، ایک ایک کر کے نہیں۔ ہر قسم دکھاتی ہے: <strong>کل</strong> کتنے آپ کے پاس ہیں، <strong>جاری</strong> کتنے کسٹمرز کے پاس ہیں، <strong>بھرے ہوئے</strong> کتنے فروخت/جاری کرنے کے لیے تیار ہیں، <strong>خالی</strong> کتنے گودام میں گیس بھرنے کے منتظر ہیں، <strong>مرمت میں</strong> کتنے ٹھیک ہو رہے ہیں، اور <strong>اسکریپ شدہ</strong> کتنے ضائع ہونے کے منتظر ہیں۔ یہاں سے آپ سلنڈر جاری کر سکتے ہیں، واپسی درج کر سکتے ہیں، اسٹاک ایڈجسٹ کر سکتے ہیں، یا مرمت/اسکریپ کا انتظام کر سکتے ہیں۔</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900"><i class="fas fa-tools text-purple-500 ml-2"></i>مرمت اور اسکریپ</h3>
                    <p class="text-sm text-gray-600 mt-1">سلنڈر کی قطار میں 🔧 بٹن استعمال کریں: خالی سلنڈر مرمت کے لیے بھیجیں، ٹھیک ہونے والے سلنڈر واپس خالی پول میں لائیں، سلنڈروں کو اسکریپ کے طور پر نشان زد کریں (خالی یا مرمت میں والے پول سے — ابھی اکاؤنٹس پر کوئی اثر نہیں، اثاثہ اب بھی آپ کی ملکیت ہے)، یا اسکریپ شدہ سلنڈروں کو باقاعدہ طور پر <strong>ضائع (Dispose)</strong> کریں۔ ضائع کرنا واحد قدم ہے جو انہیں مستقل طور پر آپ کے بیڑے سے ہٹاتا ہے اور آپ کے اکاؤنٹس میں نقصان درج کرتا ہے۔</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900"><i class="fas fa-truck-loading text-blue-500 ml-2"></i>گیس ٹرانسفرز</h3>
                    <p class="text-sm text-gray-600 mt-1">جب آپ بلک میں گیس خریدتے ہیں ("بونسر"/"باؤزر" ڈیلیوری) اور پھر اسے اپنے فروخت کے قابل سلنڈروں میں بھرتے ہیں، تو Cylinders → Gas Transfers استعمال کریں: گیس منتخب کریں، کون سا سلنڈر سائز بھرا گیا وہ منتخب کریں، اور کتنی گیس منتقل ہوئی۔ اس سے آپ کا بلک اسٹاک ٹوٹل کم ہو جاتا ہے اور یہ مستقل ریکارڈ رہتا ہے کہ کون سے سلنڈر سائز بھرے گئے۔ اس سے آپ کے پاس موجود سلنڈروں کی تعداد نہیں بدلتی — صرف گیس بلک اسٹوریج سے سلنڈر اسٹاک میں منتقل ہوتی ہے۔</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900"><i class="fas fa-file-invoice text-blue-500 ml-2"></i>سیلز</h3>
                    <p class="text-sm text-gray-600 mt-1">کسٹمر کے لیے انوائس بنائیں۔ ایک سیل صرف گیس کی، صرف سلنڈر کی، یا دونوں کی ہو سکتی ہے۔ اگر آپ کے پاس پہلے سے نمبر چھپی ہوئی کھاتہ رسید بک ہے، تو وہی نمبر <strong>ECR #</strong> فیلڈ میں لکھیں — سسٹم کا اپنا انوائس نمبر الگ سے خود بخود بنتا رہے گا۔ ایک ہی انوائس میں واپسی اور نیا اجراء/فروخت بھی ملا سکتے ہیں: ہر سلنڈر سائز کے لیے الگ لائن شامل کریں اور صحیح Action (Return / Issue / Sell) منتخب کریں — مثلاً کسٹمر 10 خالی چھوٹے سلنڈر واپس کرتا ہے اور 11 بھرے بڑے سلنڈر لے جاتا ہے، سب ایک ہی انوائس پر۔ جیسے جیسے کسٹمر ادائیگی کرے، ادائیگیاں درج کریں — اکاؤنٹنگ اندراجات (آمدنی، نقدی/وصولی، ڈپازٹ ریفنڈ) خود بخود ہو جاتے ہیں۔</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900"><i class="fas fa-shopping-cart text-purple-500 ml-2"></i>خریداری</h3>
                    <p class="text-sm text-gray-600 mt-1">سپلائر سے جو کچھ آپ خریدتے ہیں وہ درج کریں — گیس ری فل، نئے سلنڈر، یا سلنڈر ایکسچینج۔ گیس کی مقدار لکھتے وقت، اگر پروڈکٹ کی کثافت درج ہے، تو سسٹم خود دوسری اکائی میں مقدار دکھا دیتا ہے (KG لکھیں تو کیوبک میٹر نظر آ جائے، یا اُلٹا) — یہ اس وقت کارآمد ہے جب سپلائر ایک اکائی میں بل دے مگر آپ دوسری اکائی میں بیچتے ہوں۔ خریداری کی تصدیق ہونے پر اسے منظور کریں، اور سیلز کی طرح ادائیگیاں درج کریں۔</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900"><i class="fas fa-users text-green-600 ml-2"></i>کسٹمرز اور <i class="fas fa-truck text-orange-500 ml-1"></i>سپلائرز</h3>
                    <p class="text-sm text-gray-600 mt-1">آپ کے خریداروں اور بیچنے والوں کی رابطہ فہرست۔ ہر ایک کا ایک بیان (statement) ہوتا ہے جو آپ کے ساتھ ان کی مکمل سیل/خریداری اور ادائیگی کی تاریخ دکھاتا ہے۔ ہر نئے کسٹمر/سپلائر کو خود بخود ایک ID مل جاتی ہے (مثلاً <code class="bg-gray-100 px-1 rounded">CUST-000001</code>) — آپ کو خود لکھنے کی ضرورت نہیں۔ فون نمبر لازمی ہے؛ باقی سب اختیاری ہے۔ اگر کسی کسٹمر یا سپلائر کا اس سسٹم کے استعمال سے پہلے پہلے سے بیلنس تھا، تو اسے شامل کرتے وقت <strong>Opening Balance</strong> میں درج کریں۔ اگر کوئی کسٹمر پہلے ہی آپ کے کچھ سلنڈر رکھے ہوئے ہے (مثلاً کاغذی رجسٹر سے سسٹم میں منتقل ہو رہے ہیں)، تو اسی فارم میں <strong>Cylinders Already With This Customer</strong> کے تحت درج کریں تاکہ آپ کا اسٹاک درست رہے۔</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900"><i class="fas fa-balance-scale text-gray-700 ml-2"></i>اکاؤنٹنگ، اکاؤنٹس اور آمدنی/اخراجات</h3>
                    <p class="text-sm text-gray-600 mt-1">آپ کو کبھی بھی خود سے یہ منتخب کرنے کی ضرورت نہیں کہ کون سا اکاؤنٹ ڈیبٹ یا کریڈٹ ہو — سسٹم خود سمجھ لیتا ہے کہ اصل میں کیا ہوا (نقدی سیل، بینک ادائیگی، سلنڈر ڈپازٹ، اسکریپ شدہ سلنڈر کا نقصان وغیرہ)۔ <strong>Accounting</strong> میں ٹرائل بیلنس اور انکم اسٹیٹمنٹ دیکھیں، <strong>Accounts</strong> میں اپنا chart of accounts دیکھیں یا منظم کریں، اور <strong>Income/Expense</strong> میں وہ رقم درج کریں جو سیل یا خریداری نہیں ہے (مثلاً کرایہ، بجلی، دیگر آمدنی)۔</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900"><i class="fas fa-users-cog text-blue-700 ml-2"></i>ہیومن ریسورسز (HRM)</h3>
                    <p class="text-sm text-gray-600 mt-1">اپنے ملازمین کا انتظام کریں، روزانہ حاضری لگائیں، ماہانہ تنخواہیں بنائیں اور ادا کریں، اور ایڈوانس/چھٹی کی درخواستیں سنبھالیں۔ ایڈوانس منظور کرنا یا تنخواہ ادا کرنا خود بخود آپ کے اکاؤنٹس میں خرچہ درج کر دیتا ہے۔</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900"><i class="fas fa-ruler text-pink-500 ml-2"></i>یونٹس اور <i class="fas fa-shapes text-pink-500 ml-1"></i>سلنڈر ٹائپس</h3>
                    <p class="text-sm text-gray-600 mt-1">یہ دوسری جگہوں پر استعمال ہونے والی ڈراپ ڈاؤن فہرستوں کو کنٹرول کرتے ہیں۔ <strong>Units</strong> وہ اکائیاں ہیں جن میں گیس بیچی جا سکتی ہے۔ <strong>Cylinder Types</strong> سلنڈر سائزز کی فہرست ہے (مثلاً چھوٹا، درمیانہ، بڑا) اپنی ڈیفالٹ گنجائش (capacity) کے ساتھ۔ یہاں نیا شامل کریں تو وہ فوراً گیس پروڈکٹ یا سلنڈر بناتے وقت دستیاب ہو جاتا ہے — کہیں اور کچھ بدلنے کی ضرورت نہیں۔</p>
                </div>

                <div>
                    <h3 class="font-semibold text-gray-900"><i class="fas fa-user-shield text-red-600 ml-2"></i>یوزرز، رولز اور سیٹنگز</h3>
                    <p class="text-sm text-gray-600 mt-1">صرف ایڈمنسٹریٹرز کو نظر آتا ہے۔ <strong>Users</strong> عملے کے لاگ ان بناتا ہے۔ <strong>Roles &amp; Permissions</strong> یہ کنٹرول کرتا ہے کہ ہر قسم کا عملہ کیا دیکھ اور کر سکتا ہے (دیکھنا، شامل کرنا، ترمیم، حذف — ہر سیکشن کے لیے الگ)۔ <strong>System Settings</strong> فی الحال یہ کنٹرول کرتی ہے کہ کیا نئے یوزرز لاگ ان پیج پر خود سائن اپ کر سکتے ہیں۔</p>
                </div>

            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900"><i class="fas fa-bolt text-blue-600 ml-2"></i>مجھے یہ کرنا ہے...</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-gray-200">
                        <tr><td class="px-6 py-3 text-gray-700">کسٹمر کو گیس یا سلنڈر بیچنا</td><td class="px-6 py-3 font-medium">Sales → Add Sale</td></tr>
                        <tr><td class="px-6 py-3 text-gray-700">کسٹمر سے موصول رقم درج کرنا</td><td class="px-6 py-3 font-medium">Sales → انوائس کھولیں → Record Payment</td></tr>
                        <tr><td class="px-6 py-3 text-gray-700">کسٹمر کو سلنڈر دینا / واپس لینا</td><td class="px-6 py-3 font-medium">Cylinders → Issue / Return</td></tr>
                        <tr><td class="px-6 py-3 text-gray-700">ایک ہی انوائس میں پرانے سلنڈر واپس لینا اور نئے جاری کرنا</td><td class="px-6 py-3 font-medium">Sales → Add Sale → ہر سائز کے لیے الگ لائن، Action = Return / Issue / Sell</td></tr>
                        <tr><td class="px-6 py-3 text-gray-700">بلک/بونسر اسٹاک سے سلنڈروں میں گیس منتقل کرنا</td><td class="px-6 py-3 font-medium">Cylinders → Gas Transfers → New Transfer</td></tr>
                        <tr><td class="px-6 py-3 text-gray-700">سلنڈر کو مرمت کے لیے بھیجنا یا اسکریپ کرنا</td><td class="px-6 py-3 font-medium">Cylinders → قطار میں 🔧 بٹن</td></tr>
                        <tr><td class="px-6 py-3 text-gray-700">سپلائر سے خریداری کے بعد نیا اسٹاک شامل کرنا</td><td class="px-6 py-3 font-medium">Purchases → Add Purchase</td></tr>
                        <tr><td class="px-6 py-3 text-gray-700">نئی گیس قسم یا سلنڈر سائز شامل کرنا</td><td class="px-6 py-3 font-medium">Gas Products / Cylinders → Add، ضرورت ہو تو پہلے Units / Cylinder Types میں نئی آپشن شامل کریں</td></tr>
                        <tr><td class="px-6 py-3 text-gray-700">پرانے کسٹمر کو شامل کرنا جس پر پہلے سے رقم یا سلنڈر واجب ہیں</td><td class="px-6 py-3 font-medium">Customers → Add Customer → Opening Balance اور/یا Cylinders Already With This Customer پُر کریں</td></tr>
                        <tr><td class="px-6 py-3 text-gray-700">عملے کی تنخواہ ادا کرنا</td><td class="px-6 py-3 font-medium">HRM → Salaries → Process Salary، پھر Pay</td></tr>
                        <tr><td class="px-6 py-3 text-gray-700">کرایہ، بجلی یا دیگر اخراجات درج کرنا</td><td class="px-6 py-3 font-medium">Income/Expense → Add Expense</td></tr>
                        <tr><td class="px-6 py-3 text-gray-700">دیکھنا کہ کاروبار اس مہینے منافع بخش ہے یا نہیں</td><td class="px-6 py-3 font-medium">Accounting → Income Statement</td></tr>
                        <tr><td class="px-6 py-3 text-gray-700">عملے کے رکن کو سسٹم تک رسائی دینا</td><td class="px-6 py-3 font-medium">Users → Add User (Role منتخب کریں)</td></tr>
                        <tr><td class="px-6 py-3 text-gray-700">کوئی رول کیا کر سکتا ہے وہ بدلنا</td><td class="px-6 py-3 font-medium">Roles &amp; Permissions → Edit</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
    function setGuideLang(lang) {
        document.querySelectorAll('[data-lang]').forEach(function (el) {
            el.classList.toggle('hidden', el.getAttribute('data-lang') !== lang);
        });

        const isEn = lang === 'en';
        document.getElementById('langBtnEn').className = 'px-4 py-2 text-sm font-medium transition ' +
            (isEn ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50');
        document.getElementById('langBtnUr').className = 'px-4 py-2 text-sm font-medium transition ' +
            (!isEn ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50');

        localStorage.setItem('guideLang', lang);
    }

    document.addEventListener('DOMContentLoaded', function () {
        setGuideLang(localStorage.getItem('guideLang') || 'en');
    });
</script>
@endpush
@endsection
