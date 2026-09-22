<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice - Vaishnavi Tours - {{ $booking->booking_id }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #F59E0B;
            --primary-dark: #D97706;
            --dark-950: #0F172A;
            --dark-900: #1E293B;
            --slate-600: #475569;
            --slate-500: #64748B;
            --slate-200: #E2E8F0;
            --slate-100: #F1F5F9;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--dark-900);
            background-color: #F8FAFC;
            padding: 2.5rem 1rem;
            line-height: 1.5;
        }

        .invoice-wrapper {
            max-width: 820px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            border: 1px solid var(--slate-200);
            overflow: hidden;
        }

        .no-print-bar {
            max-width: 820px;
            margin: 0 auto 1.5rem auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 0.6rem 1.2rem;
            font-size: 0.9rem;
            font-weight: 700;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }

        .btn-print {
            background: var(--dark-950);
            color: #ffffff;
        }

        .btn-print:hover {
            background: #000000;
        }

        .btn-back {
            background: #ffffff;
            color: var(--dark-900);
            border: 1px solid var(--slate-200);
        }

        .invoice-header {
            padding: 2.5rem 2.5rem 2rem;
            background: linear-gradient(135deg, var(--dark-950) 0%, #1a2233 100%);
            color: #ffffff;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 4px solid var(--primary);
        }

        .logo-box img {
            width: 54px;
            height: 54px;
            object-fit: contain;
            border-radius: 50%;
            border: 2px solid var(--primary);
            background: #000000;
            margin-bottom: 0.5rem;
        }

        .brand-title {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--primary);
            letter-spacing: -0.5px;
        }

        .brand-sub {
            font-size: 0.85rem;
            color: #CBD5E1;
            line-height: 1.4;
        }

        .invoice-meta {
            text-align: right;
        }

        .invoice-tag {
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--primary);
            margin-bottom: 4px;
        }

        .invoice-number {
            font-size: 1.35rem;
            font-weight: 800;
            font-family: 'JetBrains Mono', monospace;
            color: #ffffff;
        }

        .invoice-body {
            padding: 2.5rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2.5rem;
            padding-bottom: 2rem;
            border-bottom: 1.5px solid var(--slate-100);
        }

        .section-label {
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.75px;
            color: var(--slate-500);
            margin-bottom: 0.5rem;
        }

        .info-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--dark-950);
            margin-bottom: 0.25rem;
        }

        .info-detail {
            font-size: 0.9rem;
            color: var(--slate-600);
            line-height: 1.45;
        }

        .badge-pill {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            font-size: 0.8rem;
            font-weight: 800;
            border-radius: 9999px;
            text-transform: uppercase;
        }

        .badge-paid {
            background: #DEF7EC;
            color: #03543F;
        }

        .badge-partial {
            background: #FEF08A;
            color: #854D0E;
        }

        .badge-pending {
            background: #FEE2E2;
            color: #991B1B;
        }

        table.invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }

        table.invoice-table th {
            text-align: left;
            padding: 0.85rem 1rem;
            background: var(--slate-100);
            font-size: 0.8rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--slate-600);
            border-top: 1px solid var(--slate-200);
            border-bottom: 1px solid var(--slate-200);
        }

        table.invoice-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--slate-100);
            font-size: 0.925rem;
            vertical-align: top;
        }

        .table-totals {
            margin-left: auto;
            width: 320px;
            margin-bottom: 2.5rem;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            font-size: 0.95rem;
            color: var(--slate-600);
        }

        .total-row.grand {
            border-top: 2px solid var(--dark-950);
            padding-top: 0.75rem;
            margin-top: 0.5rem;
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--dark-950);
        }

        .total-row.due {
            font-weight: 800;
            color: #DC2626;
            border-top: 1px dashed var(--slate-200);
            padding-top: 0.5rem;
        }

        .invoice-footer {
            padding: 1.5rem 2.5rem;
            background: var(--slate-100);
            border-top: 1px solid var(--slate-200);
            font-size: 0.825rem;
            color: var(--slate-500);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }

            .no-print-bar {
                display: none;
            }

            .invoice-wrapper {
                box-shadow: none;
                border: none;
                max-width: 100%;
                border-radius: 0;
            }

            .invoice-header {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <a href="javascript:history.back()" class="btn btn-back">
            ← Back
        </a>
        <button onclick="window.print()" class="btn btn-print">
            🖨️ Print / Download PDF
        </button>
    </div>

    <div class="invoice-wrapper">
        <!-- Header -->
        <div class="invoice-header">
            <div>
                <div class="logo-box">
                    <img src="{{ asset('assets/branding/vaishnavi-tours-logo.png') }}" alt="Vaishnavi Tours Logo">
                </div>
                <div class="brand-title">VAISHNAVI TOURS</div>
                <div class="brand-sub">
                    Premium Taxi & Outstation Travel Services<br>
                    B.N City Colony, Jonki Road, Mangla Chowk, Bilaspur (C.G.) - 495001<br>
                    ✉️ info@vaishnavitours.in | Contact us for assistance
                </div>
            </div>
            <div class="invoice-meta">
                <div class="invoice-tag">TAX INVOICE / RECEIPT</div>
                <div class="invoice-number">
                    {{ $booking->invoice->invoice_number ?? ('INV-' . $booking->booking_id) }}
                </div>
                <div style="font-size: 0.85rem; color: #CBD5E1; margin-top: 4px;">
                    Date: {{ $booking->invoice->issued_at ? $booking->invoice->issued_at->format('d M, Y') : date('d M, Y') }}
                </div>
                <div style="margin-top: 8px;">
                    <span class="badge-pill {{ $booking->payment_status === 'Paid' ? 'badge-paid' : ($booking->payment_status === 'Partial' ? 'badge-partial' : 'badge-pending') }}">
                        {{ $booking->payment_status }}
                    </span>
                </div>
            </div>
        </div>

        <div class="invoice-body">
            <!-- 2-Column Info -->
            <div class="info-grid">
                <div>
                    <div class="section-label">Billed To (Customer Information)</div>
                    <div class="info-title">{{ $booking->customer->name ?? 'Valued Customer' }}</div>
                    <div class="info-detail">
                        Mobile: <strong>{{ $booking->customer->phone ?? 'N/A' }}</strong><br>
                        Email: {{ $booking->customer->email ?? 'N/A' }}<br>
                        Address: {{ $booking->customer->customer->address ?? 'Bilaspur, Chhattisgarh' }}
                    </div>
                </div>
                <div>
                    <div class="section-label">Booking Particulars</div>
                    <div class="info-title">Booking ID: {{ $booking->booking_id }}</div>
                    <div class="info-detail">
                        Trip Route: <strong>{{ $booking->pickup_location }} ➔ {{ $booking->destination }}</strong><br>
                        Travel Date: <strong>{{ $booking->travel_date->format('d M, Y') }}</strong> at <strong>{{ date('h:i A', strtotime($booking->travel_time)) }}</strong><br>
                        Trip Category: {{ $booking->trip_type }} ({{ $booking->booking_status }})
                    </div>
                </div>
            </div>

            <!-- Vehicle & Driver Allocation Details -->
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>Trip Route & Service</th>
                        <th>Assigned Vehicle</th>
                        <th>Assigned Chauffeur</th>
                        <th style="text-align: right;">Amount (INR)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>{{ $booking->trip_type }} Travel Service</strong><br>
                            <span style="font-size: 0.85rem; color: var(--slate-600);">
                                From: {{ $booking->pickup_location }}<br>
                                To: {{ $booking->destination }}
                            </span>
                        </td>
                        <td>
                            @if($booking->vehicle)
                                <strong>{{ $booking->vehicle->name }}</strong><br>
                                <span style="font-size: 0.85rem; color: var(--slate-600);">
                                    Reg: {{ $booking->vehicle->registration_number }}<br>
                                    {{ $booking->vehicle->vehicle_type }} • {{ $booking->vehicle->ac_non_ac }}
                                </span>
                            @else
                                <span style="color: var(--slate-500); font-style: italic;">Standard Taxi Allocation</span>
                            @endif
                        </td>
                        <td>
                            @if($booking->driver)
                                <strong>{{ $booking->driver->name }}</strong><br>
                                <span style="font-size: 0.85rem; color: var(--slate-600);">
                                    📞 {{ $booking->driver->mobile }}<br>
                                    Lic: {{ $booking->driver->license_number }}
                                </span>
                            @else
                                <span style="color: var(--slate-500); font-style: italic;">Fleet Chauffeur</span>
                            @endif
                        </td>
                        <td style="text-align: right; font-weight: 800; font-size: 1.05rem;">
                            ₹{{ number_format($booking->total_amount, 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Totals Breakdown -->
            <div class="table-totals">
                <div class="total-row">
                    <span>Total Fare:</span>
                    <strong>₹{{ number_format($booking->total_amount, 2) }}</strong>
                </div>
                <div class="total-row">
                    <span>Paid Amount:</span>
                    <strong style="color: #059669;">₹{{ number_format($booking->paid_amount, 2) }}</strong>
                </div>
                <div class="total-row due">
                    <span>Balance Due:</span>
                    <strong>₹{{ number_format($booking->balance_amount, 2) }}</strong>
                </div>
                <div class="total-row grand">
                    <span>Payment Status:</span>
                    <span>{{ $booking->payment_status }}</span>
                </div>
            </div>

            <!-- Notes -->
            @if($booking->notes)
                <div style="background: var(--slate-100); padding: 1rem; border-radius: 6px; font-size: 0.85rem; color: var(--slate-600); margin-bottom: 2rem;">
                    <strong>Special Instructions / Passenger Notes:</strong> {{ $booking->notes }}
                </div>
            @endif

            <!-- Terms -->
            <div style="font-size: 0.775rem; color: var(--slate-500); border-top: 1px solid var(--slate-200); padding-top: 1rem;">
                <strong>Terms & Conditions:</strong><br>
                1. Toll taxes, state permit taxes and parking charges are payable as per actual receipts unless explicitly included.<br>
                2. Kilometers and hours calculation commences from pickup point to final drop destination.<br>
                3. This is a computer-generated invoice and does not require a physical signature.
            </div>
        </div>

        <div class="invoice-footer">
            <div>Thank you for choosing <strong>Vaishnavi Tours</strong>. Have a safe journey!</div>
            <div>Support: support@vaishnavitours.com | Contact us for assistance</div>
        </div>
    </div>

</body>
</html>
