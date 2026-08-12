<?php

namespace App\Http\Controllers;

use App\Models\Cylinder;
use App\Models\Customer;
use App\Models\Sale;
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
        // Get statistics
        $stats = [
            'total_issued' => Cylinder::where('status', 'issued')->count(),
            'pending_return_7' => Cylinder::pendingReturn(7)->count(),
            'pending_return_14' => Cylinder::pendingReturn(14)->count(),
            'pending_return_30' => Cylinder::pendingReturn(30)->count(),
            'total_customers' => Customer::whereHas('cylinders', function ($q) {
                $q->where('status', 'issued');
            })->count(),
        ];

        // Get all issued cylinders with customer details
        $issuedCylinders = Cylinder::with(['currentCustomer', 'gasProduct'])
            ->where('status', 'issued')
            ->orderBy('updated_at', 'asc')
            ->get()
            ->map(function ($cylinder) {
                $details = $cylinder->getCurrentCustomerWithDetails();
                return (object) [
                    'cylinder' => $cylinder,
                    'customer' => $details->customer ?? null,
                    'issued_date' => $details->issued_date ?? null,
                    'days_out' => $details->days_out ?? 0,
                    'security_deposit' => $details->security_deposit ?? 0,
                    'sale' => $details->sale ?? null,
                ];
            });

        return view('cylinders.tracking', compact('stats', 'issuedCylinders'));
    }

    /**
     * Track cylinder by number
     */
    public function track(Request $request)
    {
        $request->validate([
            'cylinder_number' => 'required|string|exists:cylinders,cylinder_number'
        ]);

        $cylinder = Cylinder::with(['gasProduct', 'currentCustomer', 'supplier'])
            ->where('cylinder_number', $request->cylinder_number)
            ->first();

        if (!$cylinder) {
            return redirect()->back()->with('error', 'Cylinder not found!');
        }

        // Get current customer details
        $currentDetails = $cylinder->getCurrentCustomerWithDetails();
        
        // Get full history
        $history = $cylinder->getFullHistory();
        
        // Get journey
        $journey = $cylinder->getJourney();

        // Get all sales related to this cylinder
        $sales = Sale::whereHas('items', function ($q) use ($cylinder) {
            $q->where('cylinder_id', $cylinder->id);
        })->with(['customer'])->get();

        return view('cylinders.track-result', compact(
            'cylinder', 
            'currentDetails', 
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

        $customer = Customer::with(['cylinders' => function ($q) {
            $q->where('status', 'issued')->with(['gasProduct', 'transactions' => function ($tq) {
                $tq->where('transaction_type', 'issued')->latest()->first();
            }]);
        }])->find($request->customer_id);

        $cylinders = $customer->cylinders->map(function ($cylinder) {
            $lastIssue = $cylinder->transactions->first();
            return (object) [
                'cylinder' => $cylinder,
                'issued_date' => $lastIssue ? $lastIssue->created_at : null,
                'days_out' => $lastIssue ? $lastIssue->created_at->diffInDays(now()) : 0,
                'security_deposit' => $lastIssue ? $lastIssue->security_deposit_charged : 0,
                'reference_document' => $lastIssue ? $lastIssue->reference_document : null
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
        $status = $request->status ?? 'all';

        $query = Cylinder::with(['gasProduct'])
            ->where('current_customer_id', $customerId);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $cylinders = $query->get()->map(function ($cylinder) {
            $lastIssue = $cylinder->transactions()
                ->where('transaction_type', 'issued')
                ->latest()
                ->first();

            return [
                'id' => $cylinder->id,
                'cylinder_number' => $cylinder->cylinder_number,
                'gas_name' => $cylinder->gasProduct->name ?? 'N/A',
                'status' => $cylinder->status,
                'status_label' => $cylinder->status_label,
                'issued_date' => $lastIssue ? $lastIssue->created_at->format('d-m-Y') : 'N/A',
                'days_out' => $lastIssue ? $lastIssue->created_at->diffInDays(now()) : 0,
                'reference_document' => $lastIssue ? $lastIssue->reference_document : 'N/A',
                'security_deposit' => $lastIssue ? $lastIssue->security_deposit_charged : 0,
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
        $cylinders = Cylinder::with(['currentCustomer', 'gasProduct'])
            ->where('status', 'issued')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="cylinder_tracking_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($cylinders) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Cylinder #', 'Gas Type', 'Customer', 'Phone', 
                'Issued Date', 'Days Out', 'Security Deposit', 'Sale Invoice'
            ]);

            foreach ($cylinders as $cylinder) {
                $details = $cylinder->getCurrentCustomerWithDetails();
                fputcsv($file, [
                    $cylinder->cylinder_number,
                    $cylinder->gasProduct->name ?? 'N/A',
                    $details->customer->name ?? 'N/A',
                    $details->customer->phone ?? 'N/A',
                    $details->issued_date ? $details->issued_date->format('d-m-Y') : 'N/A',
                    $details->days_out ?? 0,
                    $details->security_deposit ?? 0,
                    $details->sale->invoice_no ?? 'N/A'
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
        // Get all cylinders with their transactions
        $cylinders = Cylinder::with(['gasProduct', 'currentCustomer', 'transactions' => function ($q) {
            $q->orderBy('created_at', 'desc');
        }])->get();

        // Get statistics
        $stats = [
            'total_cylinders' => Cylinder::count(),
            'issued_cylinders' => Cylinder::where('status', 'issued')->count(),
            'sold_cylinders' => Cylinder::where('status', 'sold')->count(),
            'scrapped_cylinders' => Cylinder::where('status', 'scrapped')->count(),
            'total_transactions' => CylinderTransaction::count(),
            'total_issues' => CylinderTransaction::where('transaction_type', 'issued')->count(),
            'total_returns' => CylinderTransaction::where('transaction_type', 'returned')->count(),
        ];

        // Get recent transactions
        $recentTransactions = CylinderTransaction::with(['cylinder', 'customer', 'supplier', 'user'])
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
        $cylinder = Cylinder::with(['gasProduct', 'currentCustomer', 'supplier'])
            ->find($cylinderId);

        if (!$cylinder) {
            return response()->json([
                'success' => false,
                'message' => 'Cylinder not found'
            ], 404);
        }

        // Get all transactions with details
        $transactions = $cylinder->transactions()
            ->with(['customer', 'supplier', 'user'])
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
                    'supplier' => $transaction->supplier ? [
                        'id' => $transaction->supplier->id,
                        'name' => $transaction->supplier->name,
                        'phone' => $transaction->supplier->phone,
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

        // Get journey timeline
        $journey = $cylinder->getJourney();

        // Get current status details
        $currentDetails = $cylinder->getCurrentCustomerWithDetails();

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
                'current_gas_quantity' => $cylinder->current_gas_quantity,
                'purchase_price' => $cylinder->purchase_price,
                'purchase_date' => $cylinder->purchase_date?->format('d-m-Y'),
                'last_hydro_test' => $cylinder->last_hydro_test_date?->format('d-m-Y'),
                'next_hydro_test' => $cylinder->next_hydro_test_date?->format('d-m-Y'),
            ],
            'transactions' => $transactions,
            'journey' => $journey,
            'current_details' => $currentDetails,
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
        $cylinder = Cylinder::with(['gasProduct', 'currentCustomer', 'supplier'])
            ->find($cylinderId);

        if (!$cylinder) {
            return redirect()->back()->with('error', 'Cylinder not found');
        }

        $transactions = $cylinder->transactions()
            ->with(['customer', 'supplier', 'user'])
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
            ->with(['customer', 'supplier', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="cylinder_history_' . $cylinder->cylinder_number . '_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($cylinder, $transactions) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, [
                'Cylinder History Report',
                'Cylinder: ' . $cylinder->cylinder_number,
                'Gas: ' . ($cylinder->gasProduct->name ?? 'N/A'),
                'Generated: ' . now()->format('d-m-Y H:i:s')
            ]);
            fputcsv($file, []);
            
            // Column headers
            fputcsv($file, [
                'Date', 'Type', 'Customer/Supplier', 'User', 
                'Gas Quantity', 'Security Deposit', 'Damage Charge', 'Reference', 'Remarks'
            ]);

            // Data
            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->created_at->format('d-m-Y H:i:s'),
                    $transaction->type_label,
                    $transaction->customer->name ?? ($transaction->supplier->name ?? 'System'),
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

        // Check if it's a sale reference
        if (str_starts_with($transaction->reference_document, 'INV-')) {
            $sale = Sale::where('invoice_no', $transaction->reference_document)->first();
            if ($sale) {
                return route('sales.show', $sale);
            }
        }

        // Check if it's a purchase reference
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
            'total' => Cylinder::count(),
            'by_status' => [
                'in_house_empty' => Cylinder::where('status', 'in_house_empty')->count(),
                'in_house_filled' => Cylinder::where('status', 'in_house_filled')->count(),
                'issued' => Cylinder::where('status', 'issued')->count(),
                'sold' => Cylinder::where('status', 'sold')->count(),
                'under_maintenance' => Cylinder::where('status', 'under_maintenance')->count(),
                'scrapped' => Cylinder::where('status', 'scrapped')->count(),
            ],
            'by_gas' => Cylinder::select('gas_product_id', DB::raw('count(*) as total'))
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
            'pending_returns' => Cylinder::where('status', 'issued')
                ->where('updated_at', '<', now()->subDays(7))
                ->count(),
            'expired_hydro_tests' => Cylinder::where('next_hydro_test_date', '<', now())->count(),
        ];

        return response()->json($stats);
    }
}