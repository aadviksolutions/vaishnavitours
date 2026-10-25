@extends('layouts.app')

@section('title', 'Complete Payment for #' . $booking->booking_id . ' | Vaishnavi Tours')

@section('content')
<section style="padding: 3.5rem 0 5.5rem; background: var(--slate-50); min-height: 80vh;">
    <div class="container" style="max-width: 780px;">
        <!-- Breadcrumb / Back -->
        <div style="margin-bottom: 1.5rem;">
            <a href="/" style="font-size: 0.875rem; color: var(--slate-600); font-weight: 600; text-decoration: none;">
                ← Back to Home
            </a>
        </div>

        <div class="card" style="padding: 2.5rem; box-shadow: var(--shadow-lg); border-radius: var(--radius-lg); border-top: 6px solid var(--primary); background: #ffffff;">
            
            <div class="d-flex justify-between align-center flex-wrap gap-3" style="border-bottom: 1.5px solid var(--slate-100); padding-bottom: 1.25rem; margin-bottom: 1.75rem;">
                <div>
                    <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 800; color: var(--slate-500); letter-spacing: 0.5px;">SECURE PAYMENT PORTAL</span>
                    <h1 style="font-size: 1.85rem; font-weight: 900; margin: 0.2rem 0 0 0; color: var(--dark-950);">
                        Booking #{{ $booking->booking_id }}
                    </h1>
                </div>
                <div style="text-align: right;">
                    <span class="badge {{ $booking->payment_status === 'Paid' ? 'badge-success' : ($booking->payment_status === 'Partial' ? 'badge-warning' : 'badge-danger') }}" style="font-size: 0.85rem; font-weight: 800; padding: 0.4rem 1rem;">
                        {{ $booking->payment_status === 'Paid' ? 'Payment Completed' : ($booking->payment_status === 'Partial' ? 'Partially Paid' : 'Payment Awaiting') }}
                    </span>
                    <div style="font-size: 0.8rem; color: var(--slate-500); margin-top: 4px;">
                        Trip Status: <strong>{{ $booking->booking_status }}</strong>
                    </div>
                </div>
            </div>

            <!-- Payment Success Banner if Already Paid -->
            @if($booking->payment_status === 'Paid' || (float) $booking->balance_amount <= 0)
                <div style="background: #F0FDF4; border: 1.5px solid #BBF7D0; border-radius: var(--radius-md); padding: 1.75rem; text-align: center; margin-bottom: 2rem;">
                    <div style="width: 56px; height: 56px; background: #DCFCE7; color: #16A34A; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; margin: 0 auto 1rem;">
                        ✓
                    </div>
                    <h3 style="font-size: 1.35rem; font-weight: 900; color: #166534; margin-bottom: 0.35rem;">
                        Payment Verified & Booking Confirmed!
                    </h3>
                    <p style="color: #15803D; font-size: 0.95rem; margin-bottom: 1.25rem;">
                        Thank you! We have received full payment of <strong>₹{{ number_format($booking->paid_amount, 2) }}</strong> for your journey.
                    </p>
                    <div class="d-flex justify-center gap-3 flex-wrap">
                        <a href="{{ route('invoice.show', $booking->id) }}" target="_blank" class="btn btn-outline btn-sm">
                            <x-icon name="file-text" size="14" style="margin-right: 4px;" /> View Tax Invoice
                        </a>
                        @auth
                            <a href="{{ route('customer.bookings.show', $booking->id) }}" class="btn btn-primary btn-sm">
                                View Booking Details →
                            </a>
                        @else
                            <a href="{{ route('booking.success', $booking->id) }}" class="btn btn-primary btn-sm">
                                View Booking Confirmation →
                            </a>
                        @endauth
                    </div>
                </div>
            @endif

            <!-- Route & Booking Summary -->
            <div style="background: var(--slate-50); border: 1px solid var(--slate-200); border-radius: var(--radius-md); padding: 1.5rem; margin-bottom: 1.75rem;">
                <h4 style="font-size: 0.95rem; font-weight: 800; text-transform: uppercase; color: var(--slate-700); margin-bottom: 1rem; display: flex; align-items: center; gap: 6px;">
                    <x-icon name="route" size="16" /> Journey Overview
                </h4>
                <div class="grid grid-2 gap-3" style="font-size: 0.95rem;">
                    <div>
                        <span style="color: var(--slate-500); font-size: 0.8rem; font-weight: 600;">Pickup Location:</span>
                        <div style="font-weight: 800; color: var(--dark-950);">{{ $booking->pickup_location }}</div>
                    </div>
                    <div>
                        <span style="color: var(--slate-500); font-size: 0.8rem; font-weight: 600;">Destination:</span>
                        <div style="font-weight: 800; color: var(--dark-950);">{{ $booking->destination }}</div>
                    </div>
                    <div>
                        <span style="color: var(--slate-500); font-size: 0.8rem; font-weight: 600;">Schedule:</span>
                        <div style="font-weight: 700; color: var(--dark-900);">
                            {{ $booking->travel_date }} at {{ $booking->travel_time }}
                        </div>
                    </div>
                    <div>
                        <span style="color: var(--slate-500); font-size: 0.8rem; font-weight: 600;">Vehicle Category:</span>
                        <div style="font-weight: 700; color: var(--dark-900);">
                            {{ $booking->vehicle_category ?: ($booking->vehicle?->name ?? 'Standard AC Cab') }}
                        </div>
                    </div>
                    <div>
                        <span style="color: var(--slate-500); font-size: 0.8rem; font-weight: 600;">Passenger Name:</span>
                        <div style="font-weight: 700; color: var(--dark-900);">{{ $booking->customer?->name ?? 'Customer' }}</div>
                    </div>
                    <div>
                        <span style="color: var(--slate-500); font-size: 0.8rem; font-weight: 600;">Contact Phone:</span>
                        <div style="font-weight: 700; color: var(--dark-900);">{{ $booking->customer?->phone ?? 'On Record' }}</div>
                    </div>
                </div>
            </div>

            <!-- Billing Details Card -->
            <div style="border: 1.5px solid var(--slate-200); border-radius: var(--radius-md); padding: 1.5rem; margin-bottom: 2rem;">
                <h4 style="font-size: 0.95rem; font-weight: 800; text-transform: uppercase; color: var(--slate-700); margin-bottom: 1rem; display: flex; align-items: center; gap: 6px;">
                    <x-icon name="credit-card" size="16" /> Billing Breakdown
                </h4>
                
                <div class="d-flex justify-between align-center mb-2" style="font-size: 0.95rem;">
                    <span style="color: var(--slate-600);">Total Fare (incl. applicable taxes):</span>
                    <strong style="color: var(--dark-950);">₹{{ number_format($booking->total_amount, 2) }}</strong>
                </div>

                <div class="d-flex justify-between align-center mb-2" style="font-size: 0.95rem;">
                    <span style="color: var(--slate-600);">Amount Paid Till Now:</span>
                    <strong style="color: #059669;">₹{{ number_format($booking->paid_amount, 2) }}</strong>
                </div>

                <div class="d-flex justify-between align-center pt-3" style="border-top: 1.5px dashed var(--slate-200); font-size: 1.15rem;">
                    <span style="font-weight: 800; color: var(--dark-950);">Payable Balance:</span>
                    <strong style="font-size: 1.45rem; color: {{ (float) $booking->balance_amount > 0 ? '#DC2626' : '#16A34A' }};">
                        ₹{{ number_format($booking->balance_amount, 2) }}
                    </strong>
                </div>
            </div>

            <!-- Razorpay Checkout Action Section -->
            @if((float) $booking->balance_amount > 0 && $booking->booking_status !== 'Cancelled')
                <div id="paymentActionBox" style="text-align: center; margin-bottom: 2rem;">
                    <div style="margin-bottom: 1.25rem;">
                        <button id="payNowBtn" type="button" class="btn btn-primary btn-lg" style="padding: 1rem 2.5rem; font-size: 1.15rem; font-weight: 800; border-radius: var(--radius-md); box-shadow: 0 4px 14px rgba(245, 158, 11, 0.4); display: inline-flex; align-items: center; gap: 10px;">
                            <x-icon name="shield-check" size="20" />
                            <span>Pay ₹{{ number_format($booking->balance_amount, 2) }} via Razorpay</span>
                        </button>
                    </div>

                    <!-- Payment Trust Badges -->
                    <div style="display: flex; align-items: center; justify-content: center; gap: 1rem; flex-wrap: wrap; color: var(--slate-500); font-size: 0.85rem;">
                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                            <x-icon name="lock" size="14" style="color: #059669;" /> 256-Bit SSL Encrypted
                        </span>
                        <span>•</span>
                        <span>UPI (GPay / PhonePe / Paytm / BHIM)</span>
                        <span>•</span>
                        <span>Credit &amp; Debit Cards</span>
                        <span>•</span>
                        <span>Netbanking</span>
                    </div>

                    <!-- Status / Error message container -->
                    <div id="paymentMessage" style="display: none; margin-top: 1.25rem; padding: 1rem; border-radius: var(--radius-md); font-size: 0.925rem; font-weight: 600;"></div>
                </div>
            @endif

            <!-- Previous Payment Transactions -->
            @if($booking->payments && $booking->payments->isNotEmpty())
                <div style="border-top: 1.5px solid var(--slate-100); padding-top: 1.5rem; margin-top: 1.5rem;">
                    <h5 style="font-size: 0.85rem; font-weight: 800; text-transform: uppercase; color: var(--slate-500); margin-bottom: 0.85rem;">
                        Transaction History
                    </h5>
                    @foreach($booking->payments as $p)
                        <div class="d-flex justify-between align-center" style="padding: 0.5rem 0; border-bottom: 1px dotted var(--slate-200); font-size: 0.85rem;">
                            <div>
                                <span style="font-weight: 700;">₹{{ number_format($p->amount, 2) }}</span>
                                <span style="color: var(--slate-500); margin-left: 6px;">({{ $p->payment_method }} • {{ $p->gateway }})</span>
                                @if($p->razorpay_payment_id)
                                    <div style="font-size: 0.75rem; color: var(--slate-400); font-family: monospace;">Ref: {{ $p->razorpay_payment_id }}</div>
                                @endif
                            </div>
                            <div style="text-align: right;">
                                <span class="badge {{ $p->status === 'Success' ? 'badge-success' : ($p->status === 'Failed' ? 'badge-danger' : 'badge-warning') }}" style="font-size: 0.75rem;">
                                    {{ $p->status }}
                                </span>
                                <div style="font-size: 0.725rem; color: var(--slate-400);">{{ $p->created_at }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Support Notice -->
            <div style="margin-top: 2rem; background: #FFFBEB; border: 1px solid #FCD34D; border-radius: var(--radius-md); padding: 1rem 1.25rem; font-size: 0.85rem; color: #92400E; display: flex; align-items: center; gap: 10px;">
                <x-icon name="help-circle" size="20" style="flex-shrink: 0;" />
                <div>
                    Need help with this payment? Call our 24/7 Bilaspur dispatch desk at 
                    <a href="tel:+919244784443" style="color: #78350F; font-weight: 800; text-decoration: underline;">+91 9244784443</a> 
                    or WhatsApp us directly.
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Razorpay Checkout SDK Script -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const payBtn = document.getElementById('payNowBtn');
    const msgBox = document.getElementById('paymentMessage');

    if (!payBtn) return;

    function showMessage(type, text) {
        msgBox.style.display = 'block';
        if (type === 'error') {
            msgBox.style.background = '#FEE2E2';
            msgBox.style.border = '1px solid #FCA5A5';
            msgBox.style.color = '#991B1B';
        } else if (type === 'success') {
            msgBox.style.background = '#DCFCE7';
            msgBox.style.border = '1px solid #86EFAC';
            msgBox.style.color = '#166534';
        } else {
            msgBox.style.background = '#EFF6FF';
            msgBox.style.border = '1px solid #BFDBFE';
            msgBox.style.color = '#1E40AF';
        }
        msgBox.innerText = text;
    }

    payBtn.addEventListener('click', async function () {
        try {
            payBtn.disabled = true;
            payBtn.innerText = 'Initializing Secure Checkout...';
            showMessage('info', 'Connecting to Razorpay gateway...');

            const orderRes = await fetch('/api/payments/razorpay/create-order', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    booking_id: '{{ $booking->id }}'
                })
            });

            const orderData = await orderRes.json();
            if (!orderRes.ok || !orderData.success) {
                throw new Error(orderData.error || orderData.message || 'Failed to initialize payment order');
            }

            const options = {
                key: orderData.key_id,
                amount: orderData.amount,
                currency: orderData.currency || 'INR',
                name: 'Vaishnavi Tours',
                description: 'Payment for Booking #' + (orderData.booking?.booking_id || '{{ $booking->booking_id }}'),
                order_id: orderData.order_id,
                prefill: {
                    name: orderData.booking?.customer_name || '',
                    email: orderData.booking?.customer_email || '',
                    contact: orderData.booking?.customer_phone || ''
                },
                theme: {
                    color: '#F59E0B'
                },
                modal: {
                    ondismiss: function () {
                        payBtn.disabled = false;
                        payBtn.innerText = 'Pay ₹{{ number_format($booking->balance_amount, 2) }} via Razorpay';
                        showMessage('info', 'Payment was dismissed. You can retry whenever you are ready.');
                    }
                },
                handler: async function (response) {
                    try {
                        payBtn.innerText = 'Verifying Payment...';
                        showMessage('info', 'Authenticating payment signature and updating booking...');

                        const verifyRes = await fetch('/api/payments/razorpay/verify', {
                            method: 'POST',
                            headers: { 
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            },
                            body: JSON.stringify({
                                razorpay_order_id: response.razorpay_order_id,
                                razorpay_payment_id: response.razorpay_payment_id,
                                razorpay_signature: response.razorpay_signature,
                                booking_id: '{{ $booking->id }}'
                            })
                        });

                        const verifyData = await verifyRes.json();
                        if (!verifyRes.ok || !verifyData.success) {
                            throw new Error(verifyData.error || verifyData.message || 'Signature verification failed');
                        }

                        showMessage('success', 'Payment verified successfully! Redirecting to confirmation...');
                        setTimeout(function () {
                            window.location.href = verifyData.redirect_url || '/booking-success/{{ $booking->id }}?paid=1';
                        }, 1200);
                    } catch (verifyErr) {
                        console.error('Verification error:', verifyErr);
                        payBtn.disabled = false;
                        payBtn.innerText = 'Retry Payment';
                        showMessage('error', 'Payment verification error: ' + verifyErr.message);
                    }
                }
            };

            const rzp = new Razorpay(options);
            rzp.on('payment.failed', function (failResp) {
                console.error('Razorpay payment failed:', failResp);
                payBtn.disabled = false;
                payBtn.innerText = 'Retry Payment';
                const errDesc = failResp.error ? failResp.error.description : 'Payment transaction failed or was declined';
                showMessage('error', 'Payment Failed: ' + errDesc);
            });

            rzp.open();
        } catch (err) {
            console.error('Checkout error:', err);
            payBtn.disabled = false;
            payBtn.innerText = 'Pay ₹{{ number_format($booking->balance_amount, 2) }} via Razorpay';
            showMessage('error', err.message || 'Could not launch payment checkout');
        }
    });
});
</script>
@endsection
