<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice {{ $invoice->invoice_number }} | {{ $document['company']['name'] }}</title>
    <style>
        :root {
            color-scheme: light;
            --invoice-ink: #172a36;
            --invoice-muted: #536873;
            --invoice-line: #c8d8dc;
            --invoice-teal: #176a78;
            --invoice-pale: #edf5f6;
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; min-height: 100%; }
        body {
            background: #eef2f4;
            color: var(--invoice-ink);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            line-height: 1.35;
        }
        .invoice-actions {
            display: flex;
            max-width: 210mm;
            justify-content: space-between;
            gap: 0.6rem;
            margin: 16px auto;
            padding: 0 12px;
        }
        .invoice-action-group { display: flex; flex-wrap: wrap; gap: 0.5rem; }
        .invoice-button {
            display: inline-flex;
            min-height: 40px;
            align-items: center;
            justify-content: center;
            padding: 0.55rem 0.85rem;
            border: 1px solid #b8cbd0;
            border-radius: 4px;
            background: #fff;
            color: var(--invoice-ink);
            cursor: pointer;
            font: inherit;
            font-weight: 700;
            text-decoration: none;
        }
        .invoice-button-primary { border-color: var(--invoice-teal); background: var(--invoice-teal); color: #fff; }
        .invoice-print-root { width: min(100%, 210mm); margin: 0 auto 24px; }
        .invoice-paper {
            width: 100%;
            min-height: 267mm;
            padding: 10mm;
            border: 1px solid #d7e1e4;
            background: #fff;
            box-shadow: 0 5px 22px rgba(21, 46, 56, 0.1);
        }
        .invoice-header {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 1rem;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--invoice-teal);
        }
        .invoice-brand { display: flex; min-width: 0; align-items: center; gap: 12px; }
        .invoice-logo { width: 54px; height: 54px; flex: 0 0 54px; object-fit: contain; }
        .invoice-company-name { margin: 0 0 3px; color: var(--invoice-teal); font-size: 19px; font-weight: 800; }
        .invoice-company-details { color: var(--invoice-muted); font-size: 10px; line-height: 1.45; overflow-wrap: anywhere; }
        .invoice-title-block { min-width: 128px; text-align: right; }
        .invoice-title { color: var(--invoice-ink); font-size: 18px; font-weight: 800; }
        .invoice-copy-label { margin-top: 4px; color: var(--invoice-teal); font-size: 9px; font-weight: 800; letter-spacing: 0.4px; }
        .invoice-meta-bar {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin: 10px 0;
            padding: 8px 10px;
            border: 1px solid var(--invoice-line);
            border-left: 3px solid var(--invoice-teal);
            background: var(--invoice-pale);
        }
        .invoice-meta-item { display: flex; flex-wrap: wrap; gap: 5px; }
        .invoice-meta-item strong { color: var(--invoice-ink); }
        .invoice-section-heading {
            margin: 0 0 6px;
            color: var(--invoice-teal);
            font-size: 9px;
            font-weight: 800;
            letter-spacing: 0.55px;
            text-transform: uppercase;
        }
        .invoice-parties {
            display: grid;
            grid-template-columns: 1.25fr 0.75fr;
            gap: 16px;
            padding: 8px 0 10px;
            border-bottom: 1px solid var(--invoice-line);
        }
        .invoice-party { min-width: 0; }
        .invoice-customer-name { margin-bottom: 3px; font-size: 13px; font-weight: 800; overflow-wrap: anywhere; }
        .invoice-detail-line { margin: 2px 0; color: var(--invoice-muted); overflow-wrap: anywhere; }
        .invoice-detail-line strong { color: var(--invoice-ink); }
        .invoice-table-wrap { margin-top: 10px; }
        .invoice-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            border: 1px solid var(--invoice-line);
        }
        .invoice-table th {
            padding: 7px 5px;
            border-right: 1px solid #d8e5e8;
            background: var(--invoice-pale);
            color: var(--invoice-ink);
            font-size: 8.5px;
            font-weight: 800;
            text-align: left;
            text-transform: uppercase;
            vertical-align: middle;
        }
        .invoice-table td {
            padding: 8px 5px;
            border-top: 1px solid var(--invoice-line);
            border-right: 1px solid #e2eaec;
            font-size: 9px;
            vertical-align: top;
            overflow-wrap: anywhere;
        }
        .invoice-table th:last-child, .invoice-table td:last-child { border-right: 0; }
        .invoice-table .service-column { width: 33%; }
        .invoice-table .date-column { width: 15%; }
        .invoice-table .quantity-column { width: 11%; }
        .invoice-table .rate-column { width: 14%; }
        .invoice-table .tax-column { width: 13%; }
        .invoice-table .amount-column { width: 14%; }
        .invoice-align-right { text-align: right !important; }
        .invoice-bottom-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(190px, 0.72fr);
            gap: 16px;
            margin-top: 10px;
        }
        .invoice-support-column { display: grid; align-content: start; gap: 9px; }
        .invoice-support-block { min-width: 0; }
        .invoice-compact-details { display: grid; gap: 2px; color: var(--invoice-muted); font-size: 9px; }
        .invoice-totals { border-top: 1px solid var(--invoice-line); }
        .invoice-total-row { display: flex; justify-content: space-between; gap: 8px; padding: 4px 0; border-bottom: 1px solid #e5edef; }
        .invoice-total-row strong { text-align: right; }
        .invoice-total-grand { border-top: 1px solid var(--invoice-teal); color: var(--invoice-ink); font-size: 12px; font-weight: 800; }
        .invoice-total-balance { color: var(--invoice-teal); font-weight: 800; }
        .invoice-words {
            margin-top: 9px;
            padding: 7px 9px;
            border: 1px solid var(--invoice-line);
            background: #fbfdfd;
            font-size: 9px;
        }
        .invoice-footer {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(150px, 0.8fr);
            gap: 14px;
            margin-top: 10px;
            padding-top: 8px;
            border-top: 2px solid var(--invoice-teal);
        }
        .invoice-terms { color: var(--invoice-muted); font-size: 8.5px; line-height: 1.35; white-space: pre-line; }
        .invoice-terms a { color: var(--invoice-teal); }
        .invoice-signatory { align-self: end; min-height: 62px; text-align: right; }
        .invoice-signature-space { height: 30px; }
        .invoice-signatory-label { font-size: 8px; font-weight: 700; }
        .invoice-signatory-company { margin-top: 2px; color: var(--invoice-teal); font-size: 10px; font-weight: 800; }
        .invoice-tax-note { margin-top: 4px; color: #835f11; font-size: 8px; line-height: 1.3; }
        @media (max-width: 600px) {
            .invoice-actions { align-items: stretch; flex-direction: column; }
            .invoice-action-group { display: grid; grid-template-columns: 1fr 1fr; }
            .invoice-button { width: 100%; padding: 0.5rem; font-size: 11px; }
            .invoice-paper { min-height: 0; padding: 12px; }
            .invoice-header { grid-template-columns: minmax(0, 1fr) auto; gap: 8px; }
            .invoice-logo { width: 42px; height: 42px; flex-basis: 42px; }
            .invoice-brand { gap: 7px; }
            .invoice-company-name { font-size: 15px; }
            .invoice-company-details { font-size: 8px; }
            .invoice-title-block { min-width: 88px; }
            .invoice-title { font-size: 14px; }
            .invoice-copy-label { font-size: 7px; }
            .invoice-meta-bar { gap: 5px; padding: 7px; font-size: 9px; }
            .invoice-parties { grid-template-columns: 1fr 0.8fr; gap: 8px; }
            .invoice-customer-name { font-size: 11px; }
            .invoice-detail-line { font-size: 8px; }
            .invoice-table th { padding: 5px 3px; font-size: 7px; }
            .invoice-table td { padding: 6px 3px; font-size: 7.5px; }
            .invoice-table .service-column { width: 32%; }
            .invoice-table .date-column { width: 15%; }
            .invoice-table .quantity-column { width: 11%; }
            .invoice-table .rate-column { width: 14%; }
            .invoice-table .tax-column { width: 14%; }
            .invoice-table .amount-column { width: 14%; }
            .invoice-bottom-grid { grid-template-columns: 1fr 0.9fr; gap: 9px; }
            .invoice-compact-details, .invoice-total-row { font-size: 8px; }
            .invoice-footer { grid-template-columns: 1fr 0.75fr; gap: 8px; }
            .invoice-terms { font-size: 7.5px; }
        }
        @page { size: A4 portrait; margin: 7mm; }
        @media print {
            html, body { width: 100%; min-height: 0; margin: 0 !important; padding: 0 !important; background: #fff !important; }
            body * { visibility: hidden !important; }
            .invoice-print-root, .invoice-print-root * { visibility: visible !important; }
            .invoice-actions, .no-print { display: none !important; }
            .invoice-print-root { position: absolute; top: 0; left: 0; width: 100%; max-width: none; margin: 0; }
            .invoice-paper { width: 100%; min-height: 0; padding: 0; border: 0; box-shadow: none; }
            .invoice-header, .invoice-meta-bar, .invoice-table th, .invoice-words { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .invoice-table tr, .invoice-parties, .invoice-bottom-grid, .invoice-footer, .invoice-support-block { break-inside: avoid; page-break-inside: avoid; }
            .invoice-table thead { display: table-header-group; }
            .invoice-table tfoot { display: table-footer-group; }
            a { color: inherit; text-decoration: none; }
        }
    </style>
</head>
<body>
    <div class="invoice-actions no-print">
        <a href="{{ $backUrl }}" class="invoice-button">Back</a>
        <div class="invoice-action-group">
            <button type="button" class="invoice-button invoice-button-primary" onclick="window.print()">Print Invoice</button>
            <button type="button" class="invoice-button" onclick="window.print()" title="Choose Save as PDF in the print dialog">Download PDF</button>
        </div>
    </div>

    <main class="invoice-print-root">
        <article class="invoice-paper" aria-label="Tax invoice">
            <header class="invoice-header">
                <div class="invoice-brand">
                    <img class="invoice-logo" src="{{ asset('assets/branding/vaishnavi-tours-logo.png') }}" alt="Vaishnavi Tours logo">
                    <div>
                        <h1 class="invoice-company-name">{{ $document['company']['name'] }}</h1>
                        <div class="invoice-company-details">
                            @if ($document['company']['address'])<div>{{ $document['company']['address'] }}</div>@endif
                            @if ($document['company']['phone'])<div>Mobile: {{ $document['company']['phone'] }}</div>@endif
                            @if ($document['company']['gstin'])<div>GSTIN: {{ $document['company']['gstin'] }}</div>@endif
                            @if ($document['company']['email'])<div>{{ $document['company']['email'] }}</div>@endif
                        </div>
                    </div>
                </div>
                <div class="invoice-title-block">
                    <div class="invoice-title">TAX INVOICE</div>
                    <div class="invoice-copy-label">ORIGINAL FOR RECIPIENT</div>
                </div>
            </header>

            <section class="invoice-meta-bar" aria-label="Invoice information">
                <div class="invoice-meta-item"><strong>Invoice No.:</strong><span>{{ $document['invoice']['number'] ?? '' }}</span></div>
                <div class="invoice-meta-item"><strong>Invoice Date:</strong><span>{{ $document['invoice']['date']?->format('d/m/Y') ?? '' }}</span></div>
            </section>

            <section class="invoice-parties" aria-label="Customer and travel allocation">
                <div class="invoice-party">
                    <h2 class="invoice-section-heading">Bill To</h2>
                    @if ($document['customer']['name'])<div class="invoice-customer-name">{{ $document['customer']['name'] }}</div>@endif
                    @if ($document['customer']['address'])<div class="invoice-detail-line">{{ $document['customer']['address'] }}</div>@endif
                    @if ($document['customer']['mobile'])<div class="invoice-detail-line"><strong>Mobile:</strong> {{ $document['customer']['mobile'] }}</div>@endif
                    @if ($document['customer']['gstin'])<div class="invoice-detail-line"><strong>GSTIN:</strong> {{ $document['customer']['gstin'] }}</div>@endif
                    @if ($document['customer']['pan'])<div class="invoice-detail-line"><strong>PAN:</strong> {{ $document['customer']['pan'] }}</div>@endif
                    @if ($document['customer']['place_of_supply'])<div class="invoice-detail-line"><strong>Place of Supply:</strong> {{ $document['customer']['place_of_supply'] }}</div>@endif
                </div>
                <div class="invoice-party">
                    <h2 class="invoice-section-heading">Travel Allocation</h2>
                    @if ($document['driver']['name'])<div class="invoice-detail-line"><strong>Driver Name:</strong> {{ $document['driver']['name'] }}</div>@endif
                    @if ($document['vehicle']['number'])<div class="invoice-detail-line"><strong>Vehicle No.:</strong> {{ $document['vehicle']['number'] }}</div>@endif
                    <div class="invoice-detail-line"><strong>Booking Ref:</strong> {{ $booking->booking_id }}</div>
                </div>
            </section>

            <section class="invoice-table-wrap" aria-label="Invoice service lines">
                <table class="invoice-table">
                    <thead>
                        <tr>
                            <th class="service-column">Services</th>
                            <th class="date-column">Travel Date</th>
                            <th class="quantity-column">Qty.</th>
                            <th class="rate-column invoice-align-right">Rate</th>
                            <th class="tax-column invoice-align-right">Tax</th>
                            <th class="amount-column invoice-align-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $document['service']['description'] }}</td>
                            <td>{{ $document['service']['travel_date']?->format('d/m/Y') ?? '' }}</td>
                            <td>{{ $document['service']['quantity'] }}</td>
                            <td class="invoice-align-right">₹{{ number_format($document['service']['rate'], 2) }}</td>
                            <td class="invoice-align-right">
                                @if ($document['service']['tax']['tax_recorded'])
                                    ₹{{ number_format(($document['service']['tax']['cgst_amount'] ?? 0) + ($document['service']['tax']['sgst_amount'] ?? 0), 2) }}
                                @else
                                    Not recorded
                                @endif
                            </td>
                            <td class="invoice-align-right">₹{{ number_format($document['service']['amount'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section class="invoice-bottom-grid" aria-label="Payment and bank summary">
                <div class="invoice-support-column">
                    <div class="invoice-support-block">
                        <h2 class="invoice-section-heading">Bank Details</h2>
                        @php
                            $bankDetails = array_filter($document['bank'], static fn ($value) => filled($value));
                        @endphp
                        @if ($bankDetails)
                            <div class="invoice-compact-details">
                                @if ($document['bank']['account_name'])<div><strong>Name:</strong> {{ $document['bank']['account_name'] }}</div>@endif
                                @if ($document['bank']['ifsc_code'])<div><strong>IFSC Code:</strong> {{ $document['bank']['ifsc_code'] }}</div>@endif
                                @if ($document['bank']['account_number'])<div><strong>Account No.:</strong> {{ $document['bank']['account_number'] }}</div>@endif
                                @if ($document['bank']['bank_name'])<div><strong>Bank:</strong> {{ $document['bank']['bank_name'] }}</div>@endif
                            </div>
                        @else
                            <div class="invoice-compact-details">Bank details are not configured.</div>
                        @endif
                    </div>

                    @if ($document['notes'])
                        <div class="invoice-support-block">
                            <h2 class="invoice-section-heading">Notes</h2>
                            <div class="invoice-compact-details">
                                @foreach ($document['notes'] as $note)
                                    <div>{{ $note }}</div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="invoice-totals">
                    <div class="invoice-total-row"><span>Taxable Amount</span><strong>₹{{ number_format($document['totals']['taxable_amount'], 2) }}</strong></div>
                    <div class="invoice-total-row">
                        <span>CGST</span>
                        <strong>
                            @if ($document['totals']['tax_recorded'])
                                ₹{{ number_format($document['totals']['cgst_amount'] ?? 0, 2) }}
                            @else
                                Not recorded
                            @endif
                        </strong>
                    </div>
                    <div class="invoice-total-row">
                        <span>SGST</span>
                        <strong>
                            @if ($document['totals']['tax_recorded'])
                                ₹{{ number_format($document['totals']['sgst_amount'] ?? 0, 2) }}
                            @else
                                Not recorded
                            @endif
                        </strong>
                    </div>
                    @if ($document['service']['tax']['gst_applicable'] && ! $document['totals']['tax_recorded'])
                        <div class="invoice-tax-note">GST is marked applicable for this booking, but no tax amount was saved with its rate details. No tax has been estimated.</div>
                    @endif
                    <div class="invoice-total-row invoice-total-grand"><span>Total Amount</span><strong>₹{{ number_format($document['totals']['total_amount'], 2) }}</strong></div>
                    <div class="invoice-total-row"><span>Received Amount</span><strong>₹{{ number_format($document['totals']['received_amount'], 2) }}</strong></div>
                    <div class="invoice-total-row invoice-total-balance"><span>Balance</span><strong>₹{{ number_format($document['totals']['balance_amount'], 2) }}</strong></div>
                </div>
            </section>

            <div class="invoice-words"><strong>Total Amount (in words):</strong> {{ $document['totals']['amount_in_words'] }}</div>

            <footer class="invoice-footer">
                <div>
                    <h2 class="invoice-section-heading">Terms &amp; Conditions</h2>
                    @if ($document['terms'])
                        <div class="invoice-terms">{{ $document['terms'] }}</div>
                    @else
                        <div class="invoice-terms">Refer to the <a href="{{ route('terms-and-conditions') }}">Vaishnavi Tours Terms &amp; Conditions</a>.</div>
                    @endif
                </div>
                <div class="invoice-signatory">
                    <div class="invoice-signature-space"></div>
                    <div class="invoice-signatory-label">AUTHORISED SIGNATORY FOR</div>
                    <div class="invoice-signatory-company">{{ $document['company']['name'] }}</div>
                </div>
            </footer>
        </article>
    </main>
</body>
</html>