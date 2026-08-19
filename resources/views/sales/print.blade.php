<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $sale->invoice_no }}</title>
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
            @php
                $companyName = \App\Models\Setting::get('company_name', config('app.name', 'ERP System'));
                $companyAddress = \App\Models\Setting::get('company_address');
                $companyPhone = \App\Models\Setting::get('company_phone');
                $companyEmail = \App\Models\Setting::get('company_email');
                $companyTaxNumber = \App\Models\Setting::get('company_tax_number');
                $companyWebsite = \App\Models\Setting::get('company_website');
                $companyLogoUrl = \App\Models\Setting::url('company_logo');
            @endphp
            <div class="company-info">
                @if($companyLogoUrl)
                    <img src="{{ $companyLogoUrl }}" alt="{{ $companyName }}" style="max-height: 50px; margin-bottom: 6px;">
                @endif
                <h1>{{ $companyName }}</h1>
                @if($companyAddress)
                    <p>{{ $companyAddress }}</p>
                @endif
                @if($companyPhone || $companyEmail)
                    <p>{{ $companyPhone ? "Phone: {$companyPhone}" : '' }}{{ $companyPhone && $companyEmail ? ' | ' : '' }}{{ $companyEmail ? "Email: {$companyEmail}" : '' }}</p>
                @endif
                @if($companyTaxNumber)
                    <p>{{ $companyTaxNumber }}</p>
                @endif
            </div>
            <div class="invoice-title">
                <h2>SALE INVOICE</h2>
                <p class="invoice-no">#{{ $sale->invoice_no }}</p>
                @if($sale->ecr_number)
                    <p class="invoice-no">ECR #{{ $sale->ecr_number }}</p>
                @endif
                <div style="margin-top: 8px;">
                    <span class="badge-print {{ $sale->status }}">
                        {{ ucfirst($sale->status) }}
                    </span>
                    <span class="badge-print {{ $sale->payment_status }}">
                        {{ ucfirst($sale->payment_status) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- ==========================================
        DETAILS
        ========================================== -->
        <div class="details-section">
            <div class="bill-to">
                <h3>Bill To</h3>
                <p>
                    <strong>{{ $sale->customer->name ?? 'N/A' }}</strong><br>
                    {{ $sale->customer->address ?? '' }}<br>
                    Phone: {{ $sale->customer->phone ?? 'N/A' }}<br>
                    Email: {{ $sale->customer->email ?? 'N/A' }}<br>
                    @if($sale->customer->cnic)
                        CNIC: {{ $sale->customer->cnic }}<br>
                    @endif
                    @if($sale->customer->ntn_number)
                        NTN: {{ $sale->customer->ntn_number }}
                    @endif
                </p>
            </div>
            <div class="invoice-details">
                <h3>Invoice Details</h3>
                <div class="detail-row">
                    <span class="label">Invoice Date</span>
                    <span class="value">{{ $sale->date->format('d-m-Y') }}</span>
                </div>
                @if($sale->delivery_date)
                <div class="detail-row">
                    <span class="label">Delivery Date</span>
                    <span class="value">{{ $sale->delivery_date->format('d-m-Y') }}</span>
                </div>
                @endif
                <div class="detail-row">
                    <span class="label">Created By</span>
                    <span class="value">{{ $sale->creator->name ?? 'N/A' }}</span>
                </div>
                @if($sale->reference_no)
                <div class="detail-row">
                    <span class="label">Ref #</span>
                    <span class="value">{{ $sale->reference_no }}</span>
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
                @forelse($sale->items as $index => $item)
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
                    <td class="label">Subtotal</td>
                    <td class="amount">Rs. {{ number_format($sale->subtotal, 2) }}</td>
                </tr>
                @if($sale->cylinder_deposit_total > 0)
                <tr>
                    <td class="label">Cylinder Deposit</td>
                    <td class="amount" style="color: #d97706;">Rs. {{ number_format($sale->cylinder_deposit_total, 2) }}</td>
                </tr>
                @endif
                @if($sale->cylinder_sale_total > 0)
                <tr>
                    <td class="label">Cylinder Sale</td>
                    <td class="amount" style="color: #2563eb;">Rs. {{ number_format($sale->cylinder_sale_total, 2) }}</td>
                </tr>
                @endif
                @if($sale->discount > 0)
                <tr>
                    <td class="label" style="color: #dc2626;">Discount</td>
                    <td class="amount" style="color: #dc2626;">- Rs. {{ number_format($sale->discount, 2) }}</td>
                </tr>
                @endif
                @if($sale->tax > 0)
                <tr>
                    <td class="label">Tax</td>
                    <td class="amount">Rs. {{ number_format($sale->tax, 2) }}</td>
                </tr>
                @endif
                <tr class="grand-total">
                    <td class="label">GRAND TOTAL</td>
                    <td class="amount">Rs. {{ number_format($sale->grand_total, 2) }}</td>
                </tr>
                @if($sale->amount_paid > 0)
                <tr>
                    <td class="label" style="color: #2563eb;">Amount Paid</td>
                    <td class="amount" style="color: #2563eb;">Rs. {{ number_format($sale->amount_paid, 2) }}</td>
                </tr>
                <tr>
                    <td class="label" style="color: #dc2626; font-weight: 700;">Balance Due</td>
                    <td class="amount" style="color: #dc2626; font-weight: 700;">Rs. {{ number_format($sale->balance_due, 2) }}</td>
                </tr>
                @endif
                @if($sale->cylinder_return_refund_total > 0)
                <tr>
                    <td class="label" style="color: #dc2626;">Refunded for returned cylinders (paid separately)</td>
                    <td class="amount" style="color: #dc2626;">Rs. {{ number_format($sale->cylinder_return_refund_total, 2) }}</td>
                </tr>
                @endif
            </table>
        </div>

        <!-- ==========================================
        NOTES
        ========================================== -->
        @if($sale->notes)
        <div style="margin-top: 15px; padding: 10px 15px; background: #f9fafb; border-radius: 6px;">
            <p style="font-size: 11px; color: #4b5563;">
                <strong>Notes:</strong> {{ $sale->notes }}
            </p>
        </div>
        @endif

        <!-- ==========================================
        TERMS & CONDITIONS
        ========================================== -->
        <div style="margin-top: 20px; font-size: 10px; color: #6b7280; line-height: 1.6; border-top: 1px solid #e5e7eb; padding-top: 15px;">
            <p><strong>Terms & Conditions:</strong></p>
            <p>1. Payment is due within 15 days from the date of invoice.</p>
            <p>2. Cylinders remain the property of {{ $companyName }} until full payment is received.</p>
            <p>3. Any damage to cylinders will be charged to the customer.</p>
            <p>4. Please return empty cylinders within 7 days of delivery.</p>
        </div>

        <!-- ==========================================
        FOOTER
        ========================================== -->
        <div class="invoice-footer">
            <div class="footer-left">
                <p><strong>{{ $companyName }}</strong></p>
                @if($companyPhone)<p>Phone: {{ $companyPhone }}</p>@endif
                @if($companyEmail)<p>Email: {{ $companyEmail }}</p>@endif
                @if($companyWebsite)<p>Website: {{ $companyWebsite }}</p>@endif
                <p style="margin-top: 5px; color: #9ca3af; font-size: 9px;">
                    Generated on: {{ now()->format('d-m-Y h:i A') }}
                </p>
            </div>
            <div class="footer-right">
                <div style="margin-bottom: 10px;">
                    <p style="font-size: 11px; color: #4b5563;">For {{ $companyName }}</p>
                </div>
                <div class="signature-line">
                    <p style="text-align: center;">Authorized Signature</p>
                </div>
                <p style="font-size: 10px; color: #9ca3af; margin-top: 8px;">This is a computer generated invoice.</p>
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
                🖨️ Print Invoice
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