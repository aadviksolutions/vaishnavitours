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
@php
    $invoiceUrl = url()->current();
    $custPhone = preg_replace('/\D/', '', $document['customer']['phone'] ?? '');
    $waPhone = (strlen($custPhone) === 10) ? '91' . $custPhone : $custPhone;

    $waMessage = "Namaste *" . ($document['customer']['name'] ?? 'Customer') . "*,\n\n"
        . "Here is your Tax Invoice from *Vaishnavi Tours & Travels*:\n"
        . "• Invoice No: " . $invoice->invoice_number . "\n"
        . "• Booking Ref: #" . $booking->booking_id . "\n"
        . "• Route: " . ($document['service']['pickup'] ?? 'Bilaspur') . " to " . ($document['service']['drop'] ?? 'Drop Location') . "\n"
        . "• Total Amount: Rs. " . number_format($document['totals']['total_amount'] ?? 0, 2) . "\n"
        . "• Received: Rs. " . number_format($document['totals']['received_amount'] ?? 0, 2) . "\n"
        . "• Balance Due: Rs. " . number_format($document['totals']['balance_amount'] ?? 0, 2) . "\n\n"
        . "View & Download Invoice:\n" . $invoiceUrl . "\n\n"
        . "24/7 Support: +91 9244784443 / +91 9179484443\nThank you for travelling with us!";

    $waUrl = "https://wa.me/" . ($waPhone ? $waPhone : '') . "?text=" . urlencode($waMessage);

    $mailSubject = "Tax Invoice " . $invoice->invoice_number . " - Vaishnavi Tours";
    $mailBody = "Dear " . ($document['customer']['name'] ?? 'Customer') . ",\n\n"
        . "Please find your tax invoice details from Vaishnavi Tours below:\n\n"
        . "Invoice Number: " . $invoice->invoice_number . "\n"
        . "Booking ID: #" . $booking->booking_id . "\n"
        . "Route: " . ($document['service']['pickup'] ?? '') . " to " . ($document['service']['drop'] ?? '') . "\n"
        . "Total Amount: Rs. " . number_format($document['totals']['total_amount'] ?? 0, 2) . "\n"
        . "Paid Amount: Rs. " . number_format($document['totals']['received_amount'] ?? 0, 2) . "\n"
        . "Balance Due: Rs. " . number_format($document['totals']['balance_amount'] ?? 0, 2) . "\n\n"
        . "You can view and print your complete invoice online at:\n" . $invoiceUrl . "\n\n"
        . "Warm regards,\nVaishnavi Tours Team\nBilaspur, Chhattisgarh\nSupport: +91 9244784443";
    $mailUrl = "mailto:" . ($document['customer']['email'] ?? '') . "?subject=" . urlencode($mailSubject) . "&body=" . urlencode($mailBody);
@endphp

    <div class="invoice-actions no-print">
        <a href="{{ $backUrl }}" class="invoice-button">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 5px;"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg> Back
        </a>

        <div class="invoice-action-group">
            <!-- Share on WhatsApp -->
            <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="invoice-button" style="background: #16A34A; color: #fff; border-color: #15803D;">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><path d="M3 21l1.65-3.8a9 9 0 1 1 3.4 2.9L3 21"/><path d="M9 10a.5.5 0 0 0 1 0V9a.5.5 0 0 0-1 0v1a5 5 0 0 0 5 5h1a.5.5 0 0 0 0-1h-1a.5.5 0 0 0 0 1"/></svg> Share via WhatsApp
            </a>

            <!-- Share via Email -->
            <a href="{{ $mailUrl }}" class="invoice-button" style="background: #2563EB; color: #fff; border-color: #1D4ED8;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg> Email Invoice
            </a>

            <!-- Copy Link -->
            <button type="button" class="invoice-button" onclick="copyInvoiceLink()" id="btnCopyLink" title="Copy public invoice URL">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg> <span id="copyLinkText">Copy Link</span>
            </button>

            <!-- Native Share for Mobile -->
            <button type="button" class="invoice-button" onclick="shareNativeInvoice()" id="btnNativeShare" style="display: none;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" x2="15.42" y1="13.51" y2="17.49"/><line x1="15.41" x2="8.59" y1="6.51" y2="10.49"/></svg> More Share
            </button>

            <!-- Print & PDF -->
            <button type="button" class="invoice-button invoice-button-primary" onclick="window.print()">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg> Print
            </button>
            <button type="button" class="invoice-button" onclick="window.print()" title="Choose Save as PDF in the print dialog">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg> Download PDF
            </button>
        </div>
    </div>

    <script>
        if (navigator.share) {
            const btn = document.getElementById('btnNativeShare');
            if (btn) btn.style.display = 'inline-flex';
        }

        function copyInvoiceLink() {
            const url = window.location.href;
            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(showCopiedState);
            } else {
                const ta = document.createElement('textarea');
                ta.value = url;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                showCopiedState();
            }
        }

        function showCopiedState() {
            const txt = document.getElementById('copyLinkText');
            if (txt) {
                txt.textContent = 'Copied!';
                setTimeout(() => { txt.textContent = 'Copy Link'; }, 2000);
            }
        }

        function shareNativeInvoice() {
            if (navigator.share) {
                navigator.share({
                    title: 'Tax Invoice {{ $invoice->invoice_number }} - Vaishnavi Tours',
                    text: 'Tax invoice for booking #{{ $booking->booking_id }} from Vaishnavi Tours.',
                    url: window.location.href
                }).catch(() => {});
            }
        }
    </script>

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
                <div class="invoice-meta-item"><strong>Booking ID:</strong><span>{{ $booking->booking_id }}</span></div>
                <div class="invoice-meta-item"><strong>Category:</strong><span style="font-weight:700; background:#e0f2fe; color:#0369a1; padding:2px 8px; border-radius:3px;">{{ $document['invoice']['category_label'] }} ({{ $document['invoice']['category'] }})</span></div>
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
                    <div class="invoice-detail-line"><strong>Booking Ref:</strong> {{ $booking->booking_id }} [{{ $document['invoice']['category'] }}]</div>
                    @if(!empty($document['booking']['via']))
                        <div class="invoice-detail-line"><strong>Via Route:</strong> {{ $document['booking']['via'] }}</div>
                    @endif
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
                    <div class="invoice-total-row"><span>Taxable Value</span><strong>₹{{ number_format($document['totals']['taxable_amount'], 2) }}</strong></div>
                    <div class="invoice-total-row"><span>GST Rate</span><strong>{{ number_format($document['totals']['gst_rate'], 1) }}%</strong></div>
                    @if(!empty($document['totals']['igst_amount']) && $document['totals']['igst_amount'] > 0)
                        <div class="invoice-total-row"><span>IGST (5%)</span><strong>₹{{ number_format($document['totals']['igst_amount'], 2) }}</strong></div>
                    @else
                        <div class="invoice-total-row">
                            <span>CGST (2.5%)</span>
                            <strong>
                                @if ($document['totals']['tax_recorded'])
                                    ₹{{ number_format($document['totals']['cgst_amount'] ?? 0, 2) }}
                                @else
                                    Not recorded
                                @endif
                            </strong>
                        </div>
                        <div class="invoice-total-row">
                            <span>SGST (2.5%)</span>
                            <strong>
                                @if ($document['totals']['tax_recorded'])
                                    ₹{{ number_format($document['totals']['sgst_amount'] ?? 0, 2) }}
                                @else
                                    Not recorded
                                @endif
                            </strong>
                        </div>
                    @endif
                    @if (($document['service']['tax']['gst_applicable'] ?? false) && ! ($document['totals']['tax_recorded'] ?? true))
                        <div class="invoice-tax-note">GST is marked applicable for this booking, but no tax amount was saved with its rate details. No tax has been estimated.</div>
                    @endif
                    <div class="invoice-total-row"><span>Total Tax</span><strong>₹{{ number_format($document['totals']['gst_amount'], 2) }}</strong></div>
                    <div class="invoice-total-row invoice-total-grand"><span>Invoice Total</span><strong>₹{{ number_format($document['totals']['total_amount'], 2) }}</strong></div>
                    <div class="invoice-total-row"><span>Received Amount</span><strong>₹{{ number_format($document['totals']['received_amount'], 2) }}</strong></div>
                    <div class="invoice-total-row invoice-total-balance"><span>Balance Due</span><strong>₹{{ number_format($document['totals']['balance_amount'], 2) }}</strong></div>
                </div>
            </section>

            <div class="invoice-words"><strong>Total Amount (in words):</strong> {{ $document['totals']['amount_in_words'] }}</div>

            <footer class="invoice-footer" style="display:flex; flex-direction:column; gap:16px;">
                <div>
                    <h2 class="invoice-section-heading">Terms &amp; Conditions</h2>
                    @if ($document['terms'])
                        <div class="invoice-terms">{{ $document['terms'] }}</div>
                    @else
                        <div class="invoice-terms">Refer to the <a href="{{ route('terms-and-conditions') }}">Vaishnavi Tours Terms &amp; Conditions</a>.</div>
                    @endif
                </div>
                <div style="padding:10px; border:1px dashed #176a78; background:#edf5f6; text-align:center; font-weight:800; font-size:11px; letter-spacing:0.5px; color:#176a78; border-radius:4px;">
                    THIS IS A SYSTEM-GENERATED INVOICE. SIGNATURE NOT REQUIRED.
                </div>
            </footer>
        </article>
    </main>
</body>
</html>