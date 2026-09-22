@extends('layouts.app')

@section('title', 'Taxi Booking Cancellation &amp; Refund Policy - Vaishnavi Tours')
@section('meta_description', 'Official Taxi Booking Cancellation &amp; Refund Policy for Vaishnavi Tours. Transparent timeline, refund process, no-show rules, and advance retention details.')

@section('content')
<!-- Hero Section -->
<section style="background: var(--dark-900); color: #fff; padding: 3.5rem 0;">
    <div class="container text-center">
        <span style="color: var(--primary); font-weight: 800; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px;">Customer Transparency</span>
        <h1 style="color: #fff; font-size: 2.35rem; margin-top: 0.35rem; font-weight: 800;">Taxi Booking Cancellation &amp; Refund Policy</h1>
        <p style="color: var(--slate-300); max-width: 680px; margin: 0.65rem auto 0; font-size: 1rem; line-height: 1.6;">
            Clear, fair, and transparent guidelines governing taxi cancellations, reschedule requests, and payment refunds.
        </p>
    </div>
</section>

<section style="padding: 3.5rem 0 5rem; background: var(--slate-50);">
    <div class="container" style="max-width: 960px;">
        
        <!-- Integration Statement Banner -->
        <div style="background: #ffffff; border-left: 4px solid var(--primary); border-radius: var(--radius-lg); padding: 1.5rem 1.75rem; margin-bottom: 2rem; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 280px;">
                    <div style="font-weight: 800; font-size: 1.05rem; color: var(--dark-950); margin-bottom: 0.25rem;">
                        Integral Part of our Unified Terms &amp; Conditions
                    </div>
                    <p style="margin: 0; font-size: 0.925rem; color: var(--slate-600); line-height: 1.6;">
                        This Cancellation &amp; Refund Policy constitutes <strong>Section 2</strong> of the comprehensive Vaishnavi Tour's Taxi Service Terms &amp; Conditions. All taxi bookings made through our website, phone dispatch, or WhatsApp are governed by these standardized terms.
                    </p>
                </div>
                <div>
                    <a href="{{ route('terms-and-conditions') }}" class="btn btn-outline btn-sm" style="font-weight: 700; white-space: nowrap;">
                        View Full Terms &amp; Conditions →
                    </a>
                </div>
            </div>
        </div>

        <!-- Section 2: Cancellation Policy Detail Card -->
        <div class="card" style="padding: 2.5rem 2rem; border-radius: var(--radius-lg); margin-bottom: 2rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem;">
                <span style="background: var(--primary); color: var(--dark-950); font-weight: 800; font-size: 1rem; padding: 0.3rem 0.75rem; border-radius: 6px;">Section 2</span>
                <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--dark-950); margin: 0;">Cancellation &amp; Refund Schedule</h2>
            </div>

            <!-- Visual Schedule Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                
                <div style="background: #F0FDF4; border: 1.5px solid #BBF7D0; border-radius: var(--radius-md); padding: 1.25rem; text-align: center;">
                    <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">🟢</div>
                    <div style="font-weight: 800; color: #166534; font-size: 1rem;">≥ 24 Hours</div>
                    <div style="font-size: 0.75rem; color: var(--slate-500); margin-bottom: 0.5rem; text-transform: uppercase; font-weight: 700;">Before Pickup</div>
                    <div style="font-size: 0.875rem; font-weight: 700; color: #15803D;">No cancellation charge</div>
                    <div style="font-size: 0.75rem; color: #166534; margin-top: 0.25rem;">(subject to applicable transaction or processing charges, if any)</div>
                </div>

                <div style="background: #FFFBEB; border: 1.5px solid #FDE68A; border-radius: var(--radius-md); padding: 1.25rem; text-align: center;">
                    <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">🟡</div>
                    <div style="font-weight: 800; color: #92400E; font-size: 1rem;">12 – 24 Hours</div>
                    <div style="font-size: 0.75rem; color: var(--slate-500); margin-bottom: 0.5rem; text-transform: uppercase; font-weight: 700;">Before Pickup</div>
                    <div style="font-size: 0.875rem; font-weight: 700; color: #B45309;">Up to 25%</div>
                    <div style="font-size: 0.75rem; color: #92400E; margin-top: 0.25rem;">of advance amount retained</div>
                </div>

                <div style="background: #FFF7ED; border: 1.5px solid #FFEDD5; border-radius: var(--radius-md); padding: 1.25rem; text-align: center;">
                    <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">🟠</div>
                    <div style="font-weight: 800; color: #9A3412; font-size: 1rem;">6 – 12 Hours</div>
                    <div style="font-size: 0.75rem; color: var(--slate-500); margin-bottom: 0.5rem; text-transform: uppercase; font-weight: 700;">Before Pickup</div>
                    <div style="font-size: 0.875rem; font-weight: 700; color: #C2410C;">Up to 50%</div>
                    <div style="font-size: 0.75rem; color: #9A3412; margin-top: 0.25rem;">of advance amount retained</div>
                </div>

                <div style="background: #FEF2F2; border: 1.5px solid #FECACA; border-radius: var(--radius-md); padding: 1.25rem; text-align: center;">
                    <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">🔴</div>
                    <div style="font-weight: 800; color: #991B1B; font-size: 1rem;">&lt; 6 Hours</div>
                    <div style="font-size: 0.75rem; color: var(--slate-500); margin-bottom: 0.5rem; text-transform: uppercase; font-weight: 700;">Before Pickup</div>
                    <div style="font-size: 0.875rem; font-weight: 700; color: #DC2626;">Non-Refundable</div>
                    <div style="font-size: 0.75rem; color: #991B1B; margin-top: 0.25rem;">advance amount retained</div>
                </div>

            </div>

            <!-- Complete Text Policy Items -->
            <div style="border-top: 1px solid var(--slate-200); padding-top: 1.5rem;">
                <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--dark-950); margin-bottom: 1rem;">Official Policy Rules</h3>
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 1rem;">
                    <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.95rem; color: var(--slate-700); line-height: 1.6;">
                        <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                        <span><strong>Cancellation 24 hours or more before the scheduled pickup time:</strong> No cancellation charge, subject to applicable transaction or processing charges, if any.</span>
                    </li>
                    <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.95rem; color: var(--slate-700); line-height: 1.6;">
                        <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                        <span><strong>Cancellation 12–24 hours before pickup:</strong> Up to 25% of the advance amount may be retained.</span>
                    </li>
                    <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.95rem; color: var(--slate-700); line-height: 1.6;">
                        <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                        <span><strong>Cancellation 6–12 hours before pickup:</strong> Up to 50% of the advance amount may be retained.</span>
                    </li>
                    <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.95rem; color: var(--slate-700); line-height: 1.6;">
                        <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                        <span><strong>Cancellation less than 6 hours before pickup:</strong> The advance amount may be non-refundable.</span>
                    </li>
                    <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.95rem; color: var(--slate-700); line-height: 1.6;">
                        <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                        <span><strong>No-show:</strong> If the customer does not arrive at the pickup location or cannot be contacted, the booking may be treated as a no-show and the advance amount may be non-refundable.</span>
                    </li>
                    <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.95rem; color: var(--slate-700); line-height: 1.6;">
                        <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                        <span>If the vehicle has already reached the pickup location, applicable waiting or cancellation charges may be payable.</span>
                    </li>
                    <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.95rem; color: var(--slate-700); line-height: 1.6;">
                        <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                        <span><strong>Refunds:</strong> Where applicable, refunds will be processed through the original payment method.</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- How to Request Cancellation -->
        <div class="card" style="padding: 2rem; border-radius: var(--radius-lg); margin-bottom: 2rem; background: #ffffff;">
            <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--dark-950); margin-bottom: 1rem;">How to Cancel or Reschedule Your Booking</h3>
            <div class="grid grid-2 gap-3">
                <div style="background: var(--slate-50); padding: 1.25rem; border-radius: var(--radius-md);">
                    <div style="font-weight: 700; color: var(--dark-900); margin-bottom: 0.25rem;">📱 Option 1: Customer Portal</div>
                    <p style="font-size: 0.875rem; color: var(--slate-600); margin: 0 0 0.75rem 0;">
                        Log into your customer dashboard, select your active booking, and click "Request Cancellation" with your reason.
                    </p>
                    <a href="{{ route('customer.bookings.index') }}" class="btn btn-outline btn-sm">Go to My Bookings</a>
                </div>

                <div style="background: var(--slate-50); padding: 1.25rem; border-radius: var(--radius-md);">
                    <div style="font-weight: 700; color: var(--dark-900); margin-bottom: 0.25rem;">📞 Option 2: 24/7 Dispatch Desk</div>
                    <p style="font-size: 0.875rem; color: var(--slate-600); margin: 0 0 0.75rem 0;">
                        Contact our central support desk in Bilaspur directly with your Booking ID (e.g., VT-1006) for immediate assistance.
                    </p>
                    <a href="{{ route('contact') }}" class="btn btn-primary btn-sm">Contact Support</a>
                </div>
            </div>
        </div>

    </div>
</section>
@endsection
