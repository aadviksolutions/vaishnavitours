@extends('layouts.admin')

@section('title', 'Record New Payment')
@section('page_title', 'Record Payment')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.payments.index') }}" style="font-size: 0.875rem; color: var(--slate-600); font-weight: 600;">
        ← Back to Payments Ledger
    </a>
    <h1 style="font-size: 1.65rem; font-weight: 800; margin: 4px 0 0 0;">Record Customer Payment</h1>
    <p style="color: var(--slate-500); font-size: 0.875rem; margin: 2px 0 0 0;">Log offline cash collections, driver trip advances, UPI payments, and auto-sync invoices.</p>
</div>

@if($errors->any())
    <div class="alert alert-danger mb-4">
        <ul style="margin: 0; padding-left: 1.25rem;">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('admin.payments.store') }}" method="POST">
    @csrf

    <div class="grid grid-3 gap-4">
        <!-- Left 2 Cols: Form -->
        <div style="grid-column: span 2;">
            <div class="card mb-4">
                <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1.25rem;">💳 Payment Transaction Details</h3>

                <div class="form-group mb-3">
                    <label for="booking_id" class="form-label">Select Booking with Due Balance *</label>
                    <select name="booking_id" id="booking_id" class="form-select" required onchange="updateDueAmount(this)">
                        <option value="">-- Choose Booking --</option>
                        @foreach($bookings as $b)
                            <option value="{{ $b->id }}" data-due="{{ $b->balance_amount }}" {{ (old('booking_id') == $b->id || $selectedBookingId == $b->id) ? 'selected' : '' }}>
                                #{{ $b->booking_id }} - {{ $b->customer->name ?? 'Guest' }} ({{ $b->pickup_location }} ➔ {{ $b->destination }}) - Due: ₹{{ number_format($b->balance_amount, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-2 gap-3 mb-3">
                    <div class="form-group">
                        <label for="amount" class="form-label">Payment Amount (₹) *</label>
                        <input type="number" step="0.01" min="1" name="amount" id="amount" class="form-control" value="{{ old('amount') }}" placeholder="e.g. 1500.00" required>
                    </div>

                    <div class="form-group">
                        <label for="payment_method" class="form-label">Payment Mode *</label>
                        <select name="payment_method" id="payment_method" class="form-select" required>
                            @foreach(['Cash', 'UPI / QR', 'Bank Transfer', 'Card', 'Online Gateway'] as $method)
                                <option value="{{ $method }}" {{ old('payment_method', 'UPI / QR') == $method ? 'selected' : '' }}>{{ $method }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-2 gap-3 mb-3">
                    <div class="form-group">
                        <label for="transaction_id" class="form-label">Transaction Ref / UTR / Receipt No.</label>
                        <input type="text" name="transaction_id" id="transaction_id" class="form-control" value="{{ old('transaction_id') }}" placeholder="e.g. UPI/423589102456">
                    </div>

                    <div class="form-group">
                        <label for="status" class="form-label">Transaction Status *</label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="Success" {{ old('status', 'Success') == 'Success' ? 'selected' : '' }}>Success (Verified & Received)</option>
                            <option value="Pending" {{ old('status') == 'Pending' ? 'selected' : '' }}>Pending Verification</option>
                            <option value="Failed" {{ old('status') == 'Failed' ? 'selected' : '' }}>Failed / Rejected</option>
                        </select>
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label for="notes" class="form-label">Payment Remarks</label>
                    <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Received by Bilaspur office cashier, advance for trip, driver handover...">{{ old('notes') }}</textarea>
                </div>

                <div class="d-flex justify-end gap-2">
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary" style="min-width: 180px;">💾 Record & Sync</button>
                </div>
            </div>
        </div>

        <!-- Right 1 Col: Info -->
        <div>
            <div class="card mb-4" style="background: var(--slate-50);">
                <h4 style="font-size: 0.95rem; font-weight: 800; margin-bottom: 0.5rem;">ℹ️ Automated Financial Sync</h4>
                <p style="font-size: 0.825rem; color: var(--slate-600); line-height: 1.5; margin-bottom: 0.75rem;">
                    When a payment is marked as <strong>Success</strong>:
                </p>
                <ul style="font-size: 0.8rem; color: var(--slate-600); padding-left: 1.2rem; line-height: 1.5; margin: 0;">
                    <li>Booking <code>paid_amount</code> increases automatically.</li>
                    <li>Booking <code>balance_amount</code> decreases instantly.</li>
                    <li>Payment status auto-transitions to <code>Paid</code> or <code>Partial</code>.</li>
                    <li>Customer's official GST Tax Invoice is updated.</li>
                </ul>
            </div>
        </div>
    </div>
</form>

<script>
function updateDueAmount(select) {
    const selectedOption = select.options[select.selectedIndex];
    const due = selectedOption.getAttribute('data-due');
    if (due && !document.getElementById('amount').value) {
        document.getElementById('amount').value = parseFloat(due).toFixed(2);
    }
}
</script>
@endsection
