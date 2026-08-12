<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cylinder History Report - {{ $cylinder->cylinder_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 22px; }
        .header p { margin: 5px 0; color: #666; }
        .cylinder-info { background: #f5f5f5; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .cylinder-info table { width: 100%; }
        .cylinder-info td { padding: 5px 10px; }
        .cylinder-info .label { font-weight: bold; width: 150px; }
        table.history { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table.history th { background: #333; color: #fff; padding: 8px 10px; text-align: left; }
        table.history td { padding: 6px 10px; border-bottom: 1px solid #ddd; }
        table.history tr:nth-child(even) { background: #f9f9f9; }
        .journey { margin-top: 20px; }
        .journey-item { display: flex; align-items: center; padding: 5px 0; border-bottom: 1px solid #eee; }
        .journey-item .date { width: 150px; color: #666; }
        .journey-item .type { width: 150px; font-weight: bold; }
        .journey-item .party { flex: 1; }
        .footer { text-align: center; margin-top: 30px; padding-top: 15px; border-top: 1px solid #ddd; color: #999; font-size: 10px; }
        .badge { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 10px; font-weight: bold; }
        .badge-issued { background: #fef3c7; color: #92400e; }
        .badge-returned { background: #d1fae5; color: #065f46; }
        .badge-sold { background: #dbeafe; color: #1e40af; }
        .badge-purchased { background: #f3e8ff; color: #6d28d9; }
        .badge-maintenance { background: #fed7aa; color: #9a3412; }
        .badge-scrapped { background: #fee2e2; color: #991b1b; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>📜 Cylinder History Report</h1>
        <p>Generated on: {{ now()->format('d-m-Y H:i:s') }}</p>
    </div>

    <!-- Cylinder Information -->
    <div class="cylinder-info">
        <h2 style="margin-top: 0;">Cylinder Information</h2>
        <table>
            <tr>
                <td class="label">Cylinder Number:</td>
                <td><strong>{{ $cylinder->cylinder_number }}</strong></td>
                <td class="label">Status:</td>
                <td><span class="badge badge-{{ $cylinder->status }}">{{ $cylinder->status_label }}</span></td>
            </tr>
            <tr>
                <td class="label">Gas Product:</td>
                <td>{{ $cylinder->gasProduct->name ?? 'N/A' }}</td>
                <td class="label">Type:</td>
                <td>{{ $cylinder->type ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Capacity:</td>
                <td>{{ $cylinder->capacity }} KG</td>
                <td class="label">Current Gas:</td>
                <td>{{ $cylinder->current_gas_quantity }} KG</td>
            </tr>
            <tr>
                <td class="label">Purchase Price:</td>
                <td>Rs. {{ number_format($cylinder->purchase_price ?? 0, 2) }}</td>
                <td class="label">Purchase Date:</td>
                <td>{{ $cylinder->purchase_date?->format('d-m-Y') ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Last Hydro Test:</td>
                <td>{{ $cylinder->last_hydro_test_date?->format('d-m-Y') ?? 'N/A' }}</td>
                <td class="label">Next Hydro Test:</td>
                <td>{{ $cylinder->next_hydro_test_date?->format('d-m-Y') ?? 'N/A' }}</td>
            </tr>
            @if($cylinder->currentCustomer)
            <tr>
                <td class="label">Current Customer:</td>
                <td colspan="3"><strong>{{ $cylinder->currentCustomer->name }}</strong> ({{ $cylinder->currentCustomer->phone ?? 'N/A' }})</td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Journey Timeline -->
    @if($journey->count() > 0)
    <h2>🔄 Cylinder Journey Timeline</h2>
    <div class="journey">
        @foreach($journey as $item)
        <div class="journey-item">
            <span class="date">{{ $item->date_formatted }}</span>
            <span class="type"><span class="badge badge-{{ $item->type }}">{{ $item->type_label }}</span></span>
            <span class="party">{{ $item->party_name }}</span>
            @if($item->remarks)
                <span style="color: #666; font-size: 11px;">- {{ $item->remarks }}</span>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    <!-- Transaction History -->
    <h2>📋 Transaction History</h2>
    <table class="history">
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Party</th>
                <th>User</th>
                <th style="text-align: right;">Gas Qty</th>
                <th style="text-align: right;">Deposit</th>
                <th style="text-align: right;">Damage</th>
                <th>Reference</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $transaction)
            <tr>
                <td>{{ $transaction->created_at->format('d-m-Y H:i') }}</td>
                <td><span class="badge badge-{{ $transaction->transaction_type }}">{{ $transaction->type_label }}</span></td>
                <td>{{ $transaction->customer->name ?? ($transaction->supplier->name ?? 'System') }}</td>
                <td>{{ $transaction->user->name ?? 'N/A' }}</td>
                <td style="text-align: right;">{{ $transaction->gas_quantity_at_transaction ?? 0 }}</td>
                <td style="text-align: right;">{{ number_format($transaction->security_deposit_charged ?? 0, 2) }}</td>
                <td style="text-align: right;">{{ number_format($transaction->damage_charge ?? 0, 2) }}</td>
                <td>{{ $transaction->reference_document ?? '—' }}</td>
                <td>{{ $transaction->remarks ?? '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align: center;">No transactions found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Summary -->
    <div style="margin-top: 20px; background: #f5f5f5; padding: 15px; border-radius: 5px;">
        <h3>📊 Summary</h3>
        <table style="width: 50%;">
            <tr>
                <td><strong>Total Transactions:</strong></td>
                <td>{{ $transactions->count() }}</td>
            </tr>
            <tr>
                <td><strong>Total Issues:</strong></td>
                <td>{{ $transactions->where('transaction_type', 'issued')->count() }}</td>
            </tr>
            <tr>
                <td><strong>Total Returns:</strong></td>
                <td>{{ $transactions->where('transaction_type', 'returned')->count() }}</td>
            </tr>
            <tr>
                <td><strong>Total Security Deposits:</strong></td>
                <td>Rs. {{ number_format($transactions->sum('security_deposit_charged'), 2) }}</td>
            </tr>
            <tr>
                <td><strong>Total Damage Charges:</strong></td>
                <td>Rs. {{ number_format($transactions->sum('damage_charge'), 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Generated by Medical Gas ERP System</p>
        <p>This is a computer generated report.</p>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 30px; background: #2563eb; color: #fff; border: none; border-radius: 5px; cursor: pointer; font-size: 14px;">
            🖨️ Print Report
        </button>
        <button onclick="window.close()" style="padding: 10px 30px; background: #6b7280; color: #fff; border: none; border-radius: 5px; cursor: pointer; font-size: 14px;">
            Close
        </button>
    </div>
</body>
</html>