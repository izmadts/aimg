<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order #{{ $purchase->purchase_invoice_no }}</title>
    <style>
        /* ============================================
           PRINT STYLES
           ============================================ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 12px;
            color: #1a1a2e;
            background: #ffffff;
            padding: 20px;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px;
            border: 1px solid #e5e7eb;
        }

        /* ============================================
           HEADER
           ============================================ */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px double #1a1a2e;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .company-info h1 {
            font-size: 24px;
            color: #1a1a2e;
            margin-bottom: 5px;
        }

        .company-info p {
            color: #4b5563;
            font-size: 11px;
            line-height: 1.5;
        }

        .invoice-title {
            text-align: right;
        }

        .invoice-title h2 {
            font-size: 22px;
            color: #1a1a2e;
            margin-bottom: 5px;
        }

        .invoice-title .invoice-no {
            font-size: 14px;
            color: #4b5563;
        }

        /* ============================================
           BILL TO / DETAILS
           ============================================ */
        .details-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
        }

        .bill-to h3,
        .invoice-details h3 {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .bill-to p {
            font-size: 13px;
            line-height: 1.6;
        }

        .invoice-details .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
        }

        .invoice-details .label {
            color: #6b7280;
            margin-right: 30px;
        }

        .invoice-details .value {
            font-weight: 600;
        }

        /* ============================================
           ITEMS TABLE
           ============================================ */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table thead th {
            background: #f3f4f6;
            padding: 10px 12px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #4b5563;
            border-bottom: 2px solid #d1d5db;
        }

        .items-table thead th.text-right {
            text-align: right;
        }

        .items-table tbody td {
            padding: 8px 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .items-table tbody td.text-right {
            text-align: right;
        }

        .items-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* ============================================
           TOTALS
           ============================================ */
        .totals-section {
            display: flex;
            justify-content: flex-end;
            margin-top: 10px;
        }

        .totals-table {
            width: 300px;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 5px 10px;
            text-align: right;
        }

        .totals-table .label {
            color: #4b5563;
            padding-right: 20px;
        }

        .totals-table .amount {
            font-weight: 600;
        }

        .totals-table .grand-total td {
            font-size: 16px;
            font-weight: 700;
            padding-top: 10px;
            border-top: 2px solid #1a1a2e;
            color: #1a1a2e;
        }

        .totals-table .grand-total .label {
            color: #1a1a2e;
        }

        /* ============================================
           FOOTER
           ============================================ */
        .invoice-footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
        }

        .footer-left {
            font-size: 11px;
            color: #6b7280;
            line-height: 1.6;
        }

        .footer-right {
            text-align: right;
        }

        .footer-right .signature-line {
            width: 150px;
            border-top: 1px solid #1a1a2e;
            margin-top: 25px;
            padding-top: 5px;
            font-size: 11px;
            color: #4b5563;
        }

        /* ============================================
           STATUS BADGES (Print)
           ============================================ */
        .badge-print {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-print.confirmed {
            background: #dcfce7;
            color: #166534;
        }

        .badge-print.draft {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-print.cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-print.paid {
            background: #dcfce7;
            color: #166534;
        }

        .badge-print.unpaid {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-print.partial {
            background: #fef3c7;
            color: #92400e;
        }

        /* ============================================
           PRINT HIDE
           ============================================ */
        .no-print {
            display: none !important;
        }

        @media print {
            body {
                padding: 0;
                margin: 0;
            }
            .invoice-container {
                border: none;
                padding: 20px;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
            .items-table thead th {
                background: #f3f4f6 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .badge-print {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">

        <!-- ==========================================
        HEADER
        ========================================== -->
        <div class="invoice-header">
            <div class="company-info">
                <h1>🏥 Medical Gas ERP</h1>
                <p>123 Main Street, Lahore, Pakistan</p>
                <p>Phone: +92 42 1234567 | Email: info@medicalgas.com</p>
                <p>NTN: 1234567-8 | GST: 9876543-2</p>
            </div>
            <div class="invoice-title">
                <h2>PURCHASE ORDER</h2>
                <p class="invoice-no">#{{ $purchase->purchase_invoice_no }}</p>
                <div style="margin-top: 8px;">
                    <span class="badge-print {{ $purchase->status }}">
                        {{ ucfirst($purchase->status) }}
                    </span>
                    <span class="badge-print {{ $purchase->payment_status }}">
                        {{ ucfirst($purchase->payment_status) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- ==========================================
        DETAILS
        ========================================== -->
        <div class="details-section">
            <div class="bill-to">
                <h3>Supplier</h3>
                <p>
                    <strong>{{ $purchase->supplier->name ?? 'N/A' }}</strong><br>
                    {{ $purchase->supplier->address ?? '' }}<br>
                    Phone: {{ $purchase->supplier->phone ?? 'N/A' }}<br>
                    Email: {{ $purchase->supplier->email ?? 'N/A' }}
                </p>
            </div>
            <div class="invoice-details">
                <h3>Purchase Details</h3>
                <div class="detail-row">
                    <span class="label">PO Date</span>
                    <span class="value">{{ $purchase->date->format('d-m-Y') }}</span>
                </div>
                @if($purchase->delivery_date)
                <div class="detail-row">
                    <span class="label">Delivery Date</span>
                    <span class="value">{{ $purchase->delivery_date->format('d-m-Y') }}</span>
                </div>
                @endif
                <div class="detail-row">
                    <span class="label">Purchase Type</span>
                    <span class="value">{{ $purchase->purchase_type_label }}</span>
                </div>
                @if($purchase->reference_no)
                <div class="detail-row">
                    <span class="label">Ref #</span>
                    <span class="value">{{ $purchase->reference_no }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- ==========================================
        ITEMS TABLE
        ========================================== -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Product / Cylinder</th>
                    <th style="width: 80px; text-align: right;">Qty</th>
                    <th style="width: 100px; text-align: right;">Rate (Rs.)</th>
                    <th style="width: 120px; text-align: right;">Total (Rs.)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchase->items as $index => $item)
                @if($item->gas_product_id)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        {{ $item->gasProduct->name ?? 'N/A' }}
                        <span style="color: #6b7280; font-size: 10px;">
                            ({{ $item->gasProduct->code ?? '' }} {{ $item->gasProduct->uom ?? '' }})
                        </span>
                    </td>
                    <td style="text-align: right;">{{ number_format($item->gas_quantity, 2) }}</td>
                    <td style="text-align: right;">{{ number_format($item->gas_price, 2) }}</td>
                    <td style="text-align: right; font-weight: 600;">{{ number_format($item->gas_total, 2) }}</td>
                </tr>
                @endif
                @if($item->cylinder_id)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        Cylinder: {{ $item->cylinder->cylinder_number ?? 'N/A' }}
                        <span style="color: #6b7280; font-size: 10px;">
                            ({{ $item->cylinder_action_label }})
                        </span>
                    </td>
                    <td style="text-align: right;">{{ $item->cylinder_quantity }}</td>
                    <td style="text-align: right;">{{ number_format($item->cylinder_unit_price, 2) }}</td>
                    <td style="text-align: right; font-weight: 600;">{{ number_format($item->cylinder_total, 2) }}</td>
                </tr>
                @endif
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #6b7280; padding: 20px 0;">
                        No items found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- ==========================================
        TOTALS
        ========================================== -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td class="label">Gas Subtotal</td>
                    <td class="amount">Rs. {{ number_format($purchase->subtotal, 2) }}</td>
                </tr>
                @if($purchase->cylinder_total > 0)
                <tr>
                    <td class="label">Cylinder Total</td>
                    <td class="amount" style="color: #7c3aed;">Rs. {{ number_format($purchase->cylinder_total, 2) }}</td>
                </tr>
                @endif
                @if($purchase->discount > 0)
                <tr>
                    <td class="label" style="color: #dc2626;">Discount</td>
                    <td class="amount" style="color: #dc2626;">- Rs. {{ number_format($purchase->discount, 2) }}</td>
                </tr>
                @endif
                @if($purchase->tax > 0)
                <tr>
                    <td class="label">Tax</td>
                    <td class="amount">Rs. {{ number_format($purchase->tax, 2) }}</td>
                </tr>
                @endif
                <tr class="grand-total">
                    <td class="label">GRAND TOTAL</td>
                    <td class="amount">Rs. {{ number_format($purchase->grand_total, 2) }}</td>
                </tr>
                @if($purchase->amount_paid > 0)
                <tr>
                    <td class="label" style="color: #2563eb;">Amount Paid</td>
                    <td class="amount" style="color: #2563eb;">Rs. {{ number_format($purchase->amount_paid, 2) }}</td>
                </tr>
                <tr>
                    <td class="label" style="color: #dc2626; font-weight: 700;">Balance Due</td>
                    <td class="amount" style="color: #dc2626; font-weight: 700;">Rs. {{ number_format($purchase->balance_due, 2) }}</td>
                </tr>
                @endif
            </table>
        </div>

        <!-- ==========================================
        NOTES
        ========================================== -->
        @if($purchase->notes)
        <div style="margin-top: 15px; padding: 10px 15px; background: #f9fafb; border-radius: 6px;">
            <p style="font-size: 11px; color: #4b5563;">
                <strong>Notes:</strong> {{ $purchase->notes }}
            </p>
        </div>
        @endif

        <!-- ==========================================
        FOOTER
        ========================================== -->
        <div class="invoice-footer">
            <div class="footer-left">
                <p><strong>Medical Gas ERP</strong></p>
                <p>Phone: +92 42 1234567</p>
                <p>Email: info@medicalgas.com</p>
                <p>Website: www.medicalgas.com</p>
                <p style="margin-top: 5px; color: #9ca3af; font-size: 9px;">
                    Generated on: {{ now()->format('d-m-Y h:i A') }}
                </p>
            </div>
            <div class="footer-right">
                <div style="margin-bottom: 10px;">
                    <p style="font-size: 11px; color: #4b5563;">For Medical Gas ERP</p>
                </div>
                <div class="signature-line">
                    <p style="text-align: center;">Authorized Signature</p>
                </div>
                <p style="font-size: 10px; color: #9ca3af; margin-top: 8px;">This is a computer generated document.</p>
            </div>
        </div>

        <!-- ==========================================
        PRINT BUTTON (Hidden in print)
        ========================================== -->
        <div style="text-align: center; margin-top: 30px;" class="no-print">
            <button onclick="window.print()" style="
                background: #2563eb;
                color: #ffffff;
                border: none;
                padding: 12px 40px;
                border-radius: 8px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                margin: 0 10px;
            ">
                🖨️ Print Purchase Order
            </button>
            <button onclick="window.close()" style="
                background: #6b7280;
                color: #ffffff;
                border: none;
                padding: 12px 40px;
                border-radius: 8px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                margin: 0 10px;
            ">
                Close
            </button>
        </div>
    </div>
</body>
</html>
