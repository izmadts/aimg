<?php

namespace App\Http\Controllers;

use App\Models\Cylinder;
use App\Models\Customer;
use App\Models\CylinderIssuedDetail;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\CylinderTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CylinderTrackingController extends Controller
{
    /**
     * Display cylinder tracking dashboard
     */
    public function index()
    {
        $stats = [
            'total_issued' => Cylinder::sum('issued_quantity'),
            'pending_return_7' => $this->pendingReturnQuery(7)->count(),
            'pending_return_14' => $this->pendingReturnQuery(14)->count(),
            'pending_return_30' => $this->pendingReturnQuery(30)->count(),
            'total_customers' => Customer::whereHas('activeCylinderIssues')->count(),
        ];

        $issuedCylinders = CylinderIssuedDetail::with(['cylinder.gasProduct', 'customer'])
            ->where('status', 'issued')
            ->orderBy('issue_date', 'asc')
            ->get()
            ->map(function ($detail) {
                return (object) [
                    'cylinder' => $detail->cylinder,
                    'customer' => $detail->customer,
                    'quantity' => $detail->quantity,
                    'issued_date' => $detail->issue_date,
                    'days_out' => $detail->days_out,
                    'security_deposit' => $detail->security_deposit,
                ];
            });

        return view('cylinders.tracking', compact('stats', 'issuedCylinders'));
    }

    private function pendingReturnQuery($days)
    {
        return CylinderIssuedDetail::where('status', 'issued')
            ->where('issue_date', '<', now()->subDays($days));
    }

    /**
     * Track cylinder type by code, showing everyone currently holding units of it
     */
    public function track(Request $request)
    {
        $request->validate([
            'cylinder_number' => 'required|string|exists:cylinders,cylinder_number'
        ]);

        $cylinder = Cylinder::with(['gasProduct', 'supplier'])
            ->where('cylinder_number', $request->cylinder_number)
            ->first();

        if (!$cylinder) {
            return redirect()->back()->with('error', 'Cylinder not found!');
        }

        $currentHolders = $cylinder->activeIssuedDetails()->with('customer')->get();
        $history = $cylinder->getFullHistory();
        $journey = $cylinder->getJourney();

        $sales = Sale::whereHas('items', function ($q) use ($cylinder) {
            $q->where('cylinder_id', $cylinder->id);
        })->with(['customer'])->get();

        return view('cylinders.track-result', compact(
            'cylinder',
            'currentHolders',
            'history',
            'journey',
            'sales'
        ));
    }

    /**
     * Track by customer
     */
    public function trackByCustomer(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id'
        ]);

        $customer = Customer::with(['activeCylinderIssues.cylinder.gasProduct'])
            ->find($request->customer_id);

        $cylinders = $customer->activeCylinderIssues->map(function ($detail) {
            return (object) [
                'cylinder' => $detail->cylinder,
                'quantity' => $detail->quantity,
                'issued_date' => $detail->issue_date,
                'days_out' => $detail->days_out,
                'security_deposit' => $detail->security_deposit,
                'reference_document' => $detail->reference_document,
            ];
        });

        return view('cylinders.track-customer', compact('customer', 'cylinders'));
    }

    /**
     * Get customer cylinder report (AJAX)
     */
    public function getCustomerReport(Request $request)
    {
        $customerId = $request->customer_id;
        $status = $request->status ?? 'issued';

        $query = CylinderIssuedDetail::with(['cylinder.gasProduct'])
            ->where('customer_id', $customerId);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $cylinders = $query->get()->map(function ($detail) {
            return [
                'id' => $detail->cylinder_id,
                'cylinder_number' => $detail->cylinder->cylinder_number ?? 'N/A',
                'gas_name' => $detail->cylinder->gasProduct->name ?? 'N/A',
                'quantity' => $detail->quantity,
                'status' => $detail->status,
                'issued_date' => $detail->issue_date ? $detail->issue_date->format('d-m-Y') : 'N/A',
                'days_out' => $detail->days_out,
                'reference_document' => $detail->reference_document ?? 'N/A',
                'security_deposit' => $detail->security_deposit,
            ];
        });

        return response()->json([
            'success' => true,
            'cylinders' => $cylinders,
            'total' => $cylinders->count()
        ]);
    }

    /**
     * Get cylinder history (AJAX)
     */
    public function getHistory(Request $request)
    {
        $cylinderId = $request->cylinder_id;

        $cylinder = Cylinder::find($cylinderId);
        if (!$cylinder) {
            return response()->json(['success' => false, 'message' => 'Cylinder not found'], 404);
        }

        $history = $cylinder->getFullHistory();

        return response()->json([
            'success' => true,
            'cylinder_number' => $cylinder->cylinder_number,
            'history' => $history
        ]);
    }

    /**
     * Export cylinder tracking report
     */
    public function export(Request $request)
    {
        $details = CylinderIssuedDetail::with(['cylinder.gasProduct', 'customer'])
            ->where('status', 'issued')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="cylinder_tracking_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($details) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Cylinder Type', 'Gas Type', 'Customer', 'Phone', 'Quantity',
                'Issued Date', 'Days Out', 'Security Deposit', 'Reference'
            ]);

            foreach ($details as $detail) {
                fputcsv($file, [
                    $detail->cylinder->cylinder_number ?? 'N/A',
                    $detail->cylinder->gasProduct->name ?? 'N/A',
                    $detail->customer->name ?? 'N/A',
                    $detail->customer->phone ?? 'N/A',
                    $detail->quantity,
                    $detail->issue_date ? $detail->issue_date->format('d-m-Y') : 'N/A',
                    $detail->days_out,
                    $detail->security_deposit,
                    $detail->reference_document ?? 'N/A',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Display cylinder history tracking page
     */
    public function history()
    {
        $cylinders = Cylinder::with(['gasProduct', 'transactions' => function ($q) {
            $q->orderBy('created_at', 'desc');
        }])->get();

        $stats = [
            'total_cylinders' => Cylinder::sum('stock_quantity'),
            'issued_cylinders' => Cylinder::sum('issued_quantity'),
            'under_maintenance' => Cylinder::where('status', 'under_maintenance')->count(),
            'scrapped_cylinders' => Cylinder::where('status', 'scrapped')->count(),
            'total_transactions' => CylinderTransaction::count(),
            'total_issues' => CylinderTransaction::where('transaction_type', 'issued')->count(),
            'total_returns' => CylinderTransaction::where('transaction_type', 'returned')->count(),
        ];

        $recentTransactions = CylinderTransaction::with(['cylinder', 'customer', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('cylinders.history', compact('cylinders', 'stats', 'recentTransactions'));
    }

    /**
     * Get cylinder full history (AJAX)
     */
    public function getCylinderHistory($cylinderId)
    {
        $cylinder = Cylinder::with(['gasProduct', 'supplier'])->find($cylinderId);

        if (!$cylinder) {
            return response()->json([
                'success' => false,
                'message' => 'Cylinder not found'
            ], 404);
        }

        $transactions = $cylinder->transactions()
            ->with(['customer', 'user'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'type' => $transaction->transaction_type,
                    'type_label' => $transaction->type_label,
                    'type_color' => $transaction->type_color,
                    'date' => $transaction->created_at->format('Y-m-d H:i:s'),
                    'date_formatted' => $transaction->created_at->format('d-m-Y h:i A'),
                    'customer' => $transaction->customer ? [
                        'id' => $transaction->customer->id,
                        'name' => $transaction->customer->name,
                        'phone' => $transaction->customer->phone,
                    ] : null,
                    'user' => $transaction->user ? [
                        'id' => $transaction->user->id,
                        'name' => $transaction->user->name,
                    ] : null,
                    'gas_quantity' => $transaction->gas_quantity_at_transaction,
                    'security_deposit_charged' => $transaction->security_deposit_charged,
                    'security_deposit_refunded' => $transaction->security_deposit_refunded,
                    'damage_charge' => $transaction->damage_charge,
                    'remarks' => $transaction->remarks,
                    'reference_document' => $transaction->reference_document,
                    'reference_url' => $this->getReferenceUrl($transaction),
                ];
            });

        $journey = $cylinder->getJourney();
        $currentHolders = $cylinder->activeIssuedDetails()->with('customer')->get();

        return response()->json([
            'success' => true,
            'cylinder' => [
                'id' => $cylinder->id,
                'number' => $cylinder->cylinder_number,
                'gas_name' => $cylinder->gasProduct->name ?? 'N/A',
                'type' => $cylinder->type,
                'capacity' => $cylinder->capacity,
                'status' => $cylinder->status,
                'status_label' => $cylinder->status_label,
                'status_color' => $cylinder->status_color,
                'stock_quantity' => $cylinder->stock_quantity,
                'issued_quantity' => $cylinder->issued_quantity,
                'purchase_price' => $cylinder->purchase_price,
                'purchase_date' => $cylinder->purchase_date?->format('d-m-Y'),
            ],
            'transactions' => $transactions,
            'journey' => $journey,
            'current_holders' => $currentHolders,
            'total_transactions' => $transactions->count(),
            'total_issues' => $transactions->where('type', 'issued')->count(),
            'total_returns' => $transactions->where('type', 'returned')->count(),
        ]);
    }

    /**
     * Get cylinder history report (PDF/Print)
     */
    public function historyReport($cylinderId)
    {
        $cylinder = Cylinder::with(['gasProduct', 'supplier'])->find($cylinderId);

        if (!$cylinder) {
            return redirect()->back()->with('error', 'Cylinder not found');
        }

        $transactions = $cylinder->transactions()
            ->with(['customer', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        $journey = $cylinder->getJourney();

        return view('cylinders.history-report', compact('cylinder', 'transactions', 'journey'));
    }

    /**
     * Export cylinder history to CSV
     */
    public function exportHistory(Request $request)
    {
        $cylinderId = $request->cylinder_id;
        $cylinder = Cylinder::find($cylinderId);

        if (!$cylinder) {
            return redirect()->back()->with('error', 'Cylinder not found');
        }

        $transactions = $cylinder->transactions()
            ->with(['customer', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="cylinder_history_' . $cylinder->cylinder_number . '_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($cylinder, $transactions) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Cylinder History Report',
                'Cylinder: ' . $cylinder->cylinder_number,
                'Gas: ' . ($cylinder->gasProduct->name ?? 'N/A'),
                'Generated: ' . now()->format('d-m-Y H:i:s')
            ]);
            fputcsv($file, []);

            fputcsv($file, [
                'Date', 'Type', 'Customer', 'User',
                'Gas Quantity', 'Security Deposit', 'Damage Charge', 'Reference', 'Remarks'
            ]);

            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->created_at->format('d-m-Y H:i:s'),
                    $transaction->type_label,
                    $transaction->customer->name ?? 'System',
                    $transaction->user->name ?? 'N/A',
                    $transaction->gas_quantity_at_transaction ?? 0,
                    $transaction->security_deposit_charged ?? 0,
                    $transaction->damage_charge ?? 0,
                    $transaction->reference_document ?? 'N/A',
                    $transaction->remarks ?? 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get reference URL for transaction
     */
    private function getReferenceUrl($transaction)
    {
        if (!$transaction->reference_document) {
            return null;
        }

        if (str_starts_with($transaction->reference_document, 'INV-')) {
            $sale = Sale::where('invoice_no', $transaction->reference_document)->first();
            if ($sale) {
                return route('sales.show', $sale);
            }
        }

        if (str_starts_with($transaction->reference_document, 'PO-')) {
            $purchase = Purchase::where('purchase_invoice_no', $transaction->reference_document)->first();
            if ($purchase) {
                return route('purchases.show', $purchase);
            }
        }

        return null;
    }

    /**
     * Get cylinder timeline data for chart
     */
    public function getTimeline($cylinderId)
    {
        $cylinder = Cylinder::find($cylinderId);

        if (!$cylinder) {
            return response()->json(['success' => false, 'message' => 'Cylinder not found'], 404);
        }

        $timeline = $cylinder->transactions()
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($transaction) {
                return [
                    'date' => $transaction->created_at->format('Y-m-d'),
                    'type' => $transaction->transaction_type,
                    'type_label' => $transaction->type_label,
                    'color' => $transaction->type_color,
                ];
            });

        return response()->json([
            'success' => true,
            'cylinder_number' => $cylinder->cylinder_number,
            'timeline' => $timeline
        ]);
    }

    /**
     * Get cylinder summary statistics
     */
    public function getStats()
    {
        $stats = [
            'total' => Cylinder::sum('stock_quantity'),
            'by_status' => [
                'in_house' => Cylinder::where('status', 'in_house')->count(),
                'partial_issued' => Cylinder::where('status', 'partial_issued')->count(),
                'all_issued' => Cylinder::where('status', 'all_issued')->count(),
                'out_of_stock' => Cylinder::where('status', 'out_of_stock')->count(),
                'under_maintenance' => Cylinder::where('status', 'under_maintenance')->count(),
                'scrapped' => Cylinder::where('status', 'scrapped')->count(),
            ],
            'by_gas' => Cylinder::select('gas_product_id', DB::raw('sum(stock_quantity) as total'))
                ->groupBy('gas_product_id')
                ->with('gasProduct')
                ->get()
                ->map(function ($item) {
                    return [
                        'gas_name' => $item->gasProduct->name ?? 'N/A',
                        'total' => $item->total
                    ];
                }),
            'transactions' => [
                'total' => CylinderTransaction::count(),
                'issued' => CylinderTransaction::where('transaction_type', 'issued')->count(),
                'returned' => CylinderTransaction::where('transaction_type', 'returned')->count(),
                'sold' => CylinderTransaction::where('transaction_type', 'sold')->count(),
                'purchased' => CylinderTransaction::where('transaction_type', 'purchased')->count(),
            ],
            'pending_returns' => $this->pendingReturnQuery(7)->count(),
        ];

        return response()->json($stats);
    }
}
