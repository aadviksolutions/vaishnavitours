@extends('layouts.app')

@section('title', 'Taxi Service Cancellation Policy &amp; Terms &amp; Conditions - Vaishnavi Tours')
@section('meta_description', 'Official Taxi Service Cancellation Policy and Terms &amp; Conditions for Vaishnavi Tours Taxi Service. Read booking confirmation, cancellation, fare rules, and customer responsibilities.')

@section('content')
<!-- Hero Section -->
<section style="background: var(--dark-900); color: #fff; padding: 3.5rem 0;">
    <div class="container text-center">
        <span style="color: var(--primary); font-weight: 800; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px;">Customer Agreement &amp; Policies</span>
        <h1 style="color: #fff; font-size: 2.35rem; margin-top: 0.35rem; font-weight: 800;">Taxi Service Cancellation Policy &amp; Terms &amp; Conditions</h1>
        <p style="color: var(--slate-300); max-width: 720px; margin: 0.65rem auto 0; font-size: 1rem; line-height: 1.6;">
            Please read these terms carefully before booking. They govern all taxi and car rental reservations made with Vaishnavi Tour's Taxi Service.
        </p>
        <div style="margin-top: 1.25rem; display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap;">
            <span class="badge" style="background: rgba(245, 158, 11, 0.15); color: var(--primary); border: 1px solid rgba(245, 158, 11, 0.3); font-size: 0.85rem; padding: 0.4rem 0.9rem;">Effective Version: 1.0</span>
            <span class="badge" style="background: rgba(255, 255, 255, 0.1); color: #E2E8F0; font-size: 0.85rem; padding: 0.4rem 0.9rem;">Jurisdiction: Bilaspur (C.G.)</span>
        </div>
    </div>
</section>

<!-- Content Section with Quick Navigation -->
<section style="padding: 3.5rem 0 5rem; background: var(--slate-50);">
    <div class="container" style="max-width: 1120px;">
        <div class="grid grid-3 gap-4 align-start">
            
            <!-- Sticky Quick Navigation / Table of Contents -->
            <div style="position: sticky; top: 90px;">
                <div class="card" style="padding: 1.5rem; border-radius: var(--radius-lg); border-top: 4px solid var(--primary); box-shadow: 0 4px 15px rgba(0,0,0,0.04);">
                    <h3 style="font-size: 1.05rem; font-weight: 800; color: var(--dark-950); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <span>📋</span> Table of Contents
                    </h3>
                    <nav class="toc-links" style="display: flex; flex-direction: column; gap: 0.4rem; font-size: 0.875rem;">
                        <a href="#booking-confirmation" style="color: var(--slate-700); padding: 0.4rem 0.6rem; border-radius: 6px; text-decoration: none; display: block; transition: all 0.2s;">1. Booking Confirmation</a>
                        <a href="#cancellation-policy" style="color: var(--primary-dark); font-weight: 700; background: rgba(245, 158, 11, 0.08); padding: 0.4rem 0.6rem; border-radius: 6px; text-decoration: none; display: block;">2. Cancellation Policy</a>
                        <a href="#rescheduling" style="color: var(--slate-700); padding: 0.4rem 0.6rem; border-radius: 6px; text-decoration: none; display: block;">3. Rescheduling</a>
                        <a href="#waiting-pickup" style="color: var(--slate-700); padding: 0.4rem 0.6rem; border-radius: 6px; text-decoration: none; display: block;">4. Waiting &amp; Pickup</a>
                        <a href="#fare-additional-charges" style="color: var(--slate-700); padding: 0.4rem 0.6rem; border-radius: 6px; text-decoration: none; display: block;">5. Fare &amp; Additional Charges</a>
                        <a href="#vehicle-driver" style="color: var(--slate-700); padding: 0.4rem 0.6rem; border-radius: 6px; text-decoration: none; display: block;">6. Vehicle &amp; Driver</a>
                        <a href="#customer-responsibilities" style="color: var(--slate-700); padding: 0.4rem 0.6rem; border-radius: 6px; text-decoration: none; display: block;">7. Customer Responsibilities</a>
                        <a href="#driver-payment-communication" style="color: var(--slate-700); padding: 0.4rem 0.6rem; border-radius: 6px; text-decoration: none; display: block;">8. Driver &amp; Payment Communication</a>
                        <a href="#unavoidable-circumstances" style="color: var(--slate-700); padding: 0.4rem 0.6rem; border-radius: 6px; text-decoration: none; display: block;">9. Unavoidable Circumstances</a>
                        <a href="#general-terms" style="color: var(--slate-700); padding: 0.4rem 0.6rem; border-radius: 6px; text-decoration: none; display: block;">10. General Terms</a>
                        <a href="#privacy-policy" style="color: var(--slate-700); padding: 0.4rem 0.6rem; border-radius: 6px; text-decoration: none; display: block; border-top: 1px dashed var(--slate-200); margin-top: 0.25rem; padding-top: 0.6rem;">🔒 Privacy Policy</a>
                    </nav>

                    <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--slate-200); text-align: center;">
                        <a href="{{ route('booking') }}" class="btn btn-primary btn-block btn-sm" style="font-weight: 700;">
                            Book a Cab Now →
                        </a>
                        <a href="{{ route('cancellation-refund-policy') }}" style="display: inline-block; margin-top: 0.6rem; font-size: 0.8rem; color: var(--slate-500); text-decoration: underline;">
                            View Standalone Cancellation Policy
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Legal Policy Content (spanning 2 columns) -->
            <div style="grid-column: span 2;">
                
                <!-- Notice Banner -->
                <div style="background: #ffffff; border: 1.5px solid var(--slate-200); border-radius: var(--radius-lg); padding: 1.5rem 1.75rem; margin-bottom: 2rem;">
                    <p style="margin: 0; font-size: 0.95rem; color: var(--slate-700); line-height: 1.7;">
                        These terms and conditions apply to all passenger transport and taxi booking services offered by <strong>Vaishnavi Tour's Taxi Service</strong> (headquartered at Mangla Chowk, Bilaspur, Chhattisgarh). By requesting, reserving, or utilizing any of our taxi vehicles, you acknowledge and agree to abide by the policies detailed below.
                    </p>
                </div>

                <!-- Section 1 -->
                <div id="booking-confirmation" class="card" style="padding: 2rem; margin-bottom: 1.75rem; border-radius: var(--radius-lg);">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem;">
                        <span style="background: var(--dark-950); color: var(--primary); font-weight: 800; font-size: 0.9rem; padding: 0.25rem 0.65rem; border-radius: 6px;">1</span>
                        <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--dark-950); margin: 0;">Booking Confirmation</h2>
                    </div>
                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.85rem;">
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span>A booking will be considered confirmed only after confirmation from Vaishnavi Tour's Taxi Service.</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span>An advance payment may be required to confirm the booking.</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span>Vehicle and driver details will be shared as per the booking and availability.</span>
                        </li>
                    </ul>
                </div>

                <!-- Section 2 -->
                <div id="cancellation-policy" class="card" style="padding: 2rem; margin-bottom: 1.75rem; border-radius: var(--radius-lg); border-left: 4px solid var(--primary); background: #FFFDF9;">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1.25rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <span style="background: var(--primary); color: var(--dark-950); font-weight: 800; font-size: 0.9rem; padding: 0.25rem 0.65rem; border-radius: 6px;">2</span>
                            <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--dark-950); margin: 0;">Cancellation Policy</h2>
                        </div>
                        <span class="badge" style="background: rgba(245, 158, 11, 0.15); color: var(--primary-dark); font-weight: 700; font-size: 0.8rem;">Standard Schedule</span>
                    </div>

                    <!-- Highlighted Timeline Grid -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                        <div style="background: #ffffff; border: 1px solid var(--slate-200); border-radius: 8px; padding: 1rem;">
                            <div style="color: #059669; font-weight: 800; font-size: 0.95rem; margin-bottom: 0.25rem;">≥ 24 Hours Before</div>
                            <div style="font-size: 0.85rem; color: var(--slate-600); line-height: 1.4;">No cancellation charge, subject to applicable transaction or processing charges, if any.</div>
                        </div>
                        <div style="background: #ffffff; border: 1px solid var(--slate-200); border-radius: 8px; padding: 1rem;">
                            <div style="color: #D97706; font-weight: 800; font-size: 0.95rem; margin-bottom: 0.25rem;">12 – 24 Hours Before</div>
                            <div style="font-size: 0.85rem; color: var(--slate-600); line-height: 1.4;">Up to 25% of the advance amount may be retained.</div>
                        </div>
                        <div style="background: #ffffff; border: 1px solid var(--slate-200); border-radius: 8px; padding: 1rem;">
                            <div style="color: #EA580C; font-weight: 800; font-size: 0.95rem; margin-bottom: 0.25rem;">6 – 12 Hours Before</div>
                            <div style="font-size: 0.85rem; color: var(--slate-600); line-height: 1.4;">Up to 50% of the advance amount may be retained.</div>
                        </div>
                        <div style="background: #ffffff; border: 1px solid var(--slate-200); border-radius: 8px; padding: 1rem;">
                            <div style="color: #DC2626; font-weight: 800; font-size: 0.95rem; margin-bottom: 0.25rem;">&lt; 6 Hours Before</div>
                            <div style="font-size: 0.85rem; color: var(--slate-600); line-height: 1.4;">The advance amount may be non-refundable.</div>
                        </div>
                    </div>

                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.85rem;">
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span><strong>Cancellation 24 hours or more before the scheduled pickup time:</strong> No cancellation charge, subject to applicable transaction or processing charges, if any.</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span><strong>Cancellation 12–24 hours before pickup:</strong> Up to 25% of the advance amount may be retained.</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span><strong>Cancellation 6–12 hours before pickup:</strong> Up to 50% of the advance amount may be retained.</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span><strong>Cancellation less than 6 hours before pickup:</strong> The advance amount may be non-refundable.</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span><strong>No-show:</strong> If the customer does not arrive at the pickup location or cannot be contacted, the booking may be treated as a no-show and the advance amount may be non-refundable.</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span>If the vehicle has already reached the pickup location, applicable waiting or cancellation charges may be payable.</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span><strong>Refunds:</strong> Where applicable, refunds will be processed through the original payment method.</span>
                        </li>
                    </ul>
                </div>

                <!-- Section 3 -->
                <div id="rescheduling" class="card" style="padding: 2rem; margin-bottom: 1.75rem; border-radius: var(--radius-lg);">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem;">
                        <span style="background: var(--dark-950); color: var(--primary); font-weight: 800; font-size: 0.9rem; padding: 0.25rem 0.65rem; border-radius: 6px;">3</span>
                        <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--dark-950); margin: 0;">Rescheduling</h2>
                    </div>
                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.85rem;">
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span>Customers should inform us as early as possible if they need to change the pickup date or time.</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span>Rescheduling is subject to vehicle availability.</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span>Last-minute changes may attract additional charges.</span>
                        </li>
                    </ul>
                </div>

                <!-- Section 4 -->
                <div id="waiting-pickup" class="card" style="padding: 2rem; margin-bottom: 1.75rem; border-radius: var(--radius-lg);">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem;">
                        <span style="background: var(--dark-950); color: var(--primary); font-weight: 800; font-size: 0.9rem; padding: 0.25rem 0.65rem; border-radius: 6px;">4</span>
                        <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--dark-950); margin: 0;">Waiting &amp; Pickup</h2>
                    </div>
                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.85rem;">
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span>Customers are requested to provide accurate pickup details and a reachable mobile number.</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span>Reasonable waiting time may be provided at the pickup location.</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span>Additional waiting charges may apply after the complimentary waiting period.</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span>Delays caused by traffic, weather, road conditions, flight/train delays or other circumstances may affect the pickup time.</span>
                        </li>
                    </ul>
                </div>

                <!-- Section 5 -->
                <div id="fare-additional-charges" class="card" style="padding: 2rem; margin-bottom: 1.75rem; border-radius: var(--radius-lg);">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem;">
                        <span style="background: var(--dark-950); color: var(--primary); font-weight: 800; font-size: 0.9rem; padding: 0.25rem 0.65rem; border-radius: 6px;">5</span>
                        <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--dark-950); margin: 0;">Fare &amp; Additional Charges</h2>
                    </div>
                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.85rem;">
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span>The fare will be based on the quotation/booking confirmation provided to the customer.</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span>Toll tax, parking charges, state/border taxes, permits and other applicable charges may be extra unless specifically included in the quotation.</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span>For one-way trips, the fare will be as agreed for the selected route.</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span>For round trips and tour packages, additional kilometres, extra hours, night halt and other applicable charges will be as per the agreed booking terms.</span>
                        </li>
                    </ul>
                </div>

                <!-- Section 6 -->
                <div id="vehicle-driver" class="card" style="padding: 2rem; margin-bottom: 1.75rem; border-radius: var(--radius-lg);">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem;">
                        <span style="background: var(--dark-950); color: var(--primary); font-weight: 800; font-size: 0.9rem; padding: 0.25rem 0.65rem; border-radius: 6px;">6</span>
                        <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--dark-950); margin: 0;">Vehicle &amp; Driver</h2>
                    </div>
                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.85rem;">
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span>Vehicle allocation is subject to availability.</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span>In case of unavoidable circumstances, Vaishnavi Tour's Taxi Service may provide a similar-category vehicle.</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span>Driver and vehicle details may be shared before the journey as per the booking.</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span>Customers are requested to follow reasonable safety instructions given by the driver.</span>
                        </li>
                    </ul>
                </div>

                <!-- Section 7 -->
                <div id="customer-responsibilities" class="card" style="padding: 2rem; margin-bottom: 1.75rem; border-radius: var(--radius-lg);">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem;">
                        <span style="background: var(--dark-950); color: var(--primary); font-weight: 800; font-size: 0.9rem; padding: 0.25rem 0.65rem; border-radius: 6px;">7</span>
                        <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--dark-950); margin: 0;">Customer Responsibilities</h2>
                    </div>
                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.85rem;">
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span>Customers must provide correct booking information, including pickup location, destination, date and contact number.</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span>Customers are responsible for their personal belongings. Vaishnavi Tour's Taxi Service is not responsible for belongings left inside the vehicle.</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span>Smoking and consumption of alcohol inside the vehicle may be prohibited.</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span>Any damage caused to the vehicle due to customer negligence or misuse may be chargeable.</span>
                        </li>
                    </ul>
                </div>

                <!-- Section 8 -->
                <div id="driver-payment-communication" class="card" style="padding: 2rem; margin-bottom: 1.75rem; border-radius: var(--radius-lg);">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem;">
                        <span style="background: var(--dark-950); color: var(--primary); font-weight: 800; font-size: 0.9rem; padding: 0.25rem 0.65rem; border-radius: 6px;">8</span>
                        <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--dark-950); margin: 0;">Driver &amp; Payment Communication</h2>
                    </div>
                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.85rem;">
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span>Customers should make payments only through the agreed payment method or to authorised representatives of Vaishnavi Tour's Taxi Service.</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span>Drivers are not authorised to offer unauthorised discounts, change agreed fares or make independent booking arrangements.</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span>For any fare, payment or booking-related concern, customers should contact Vaishnavi Tour's Taxi Service directly.</span>
                        </li>
                    </ul>
                </div>

                <!-- Section 9 -->
                <div id="unavoidable-circumstances" class="card" style="padding: 2rem; margin-bottom: 1.75rem; border-radius: var(--radius-lg);">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem;">
                        <span style="background: var(--dark-950); color: var(--primary); font-weight: 800; font-size: 0.9rem; padding: 0.25rem 0.65rem; border-radius: 6px;">9</span>
                        <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--dark-950); margin: 0;">Unavoidable Circumstances</h2>
                    </div>
                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.85rem;">
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span>Vaishnavi Tour's Taxi Service will not be responsible for delays or changes caused by circumstances beyond reasonable control, including severe weather, road closures, traffic restrictions, government restrictions, natural events, accidents or other unforeseen circumstances.</span>
                        </li>
                    </ul>
                </div>

                <!-- Section 10 -->
                <div id="general-terms" class="card" style="padding: 2rem; margin-bottom: 1.75rem; border-radius: var(--radius-lg);">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem;">
                        <span style="background: var(--dark-950); color: var(--primary); font-weight: 800; font-size: 0.9rem; padding: 0.25rem 0.65rem; border-radius: 6px;">10</span>
                        <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--dark-950); margin: 0;">General Terms</h2>
                    </div>
                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.85rem;">
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span>All bookings are subject to vehicle availability and confirmation.</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span>By confirming a booking, the customer agrees to these Cancellation Policy and Terms &amp; Conditions.</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.975rem; color: var(--slate-700); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span>Any special terms agreed at the time of booking will take precedence over the general terms stated above.</span>
                        </li>
                    </ul>
                </div>

                <!-- Privacy Policy Section -->
                <div id="privacy-policy" class="card" style="padding: 2rem; margin-bottom: 1.75rem; border-radius: var(--radius-lg); background: #F8FAFC; border: 1.5px solid var(--slate-200);">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem;">
                        <span style="background: var(--dark-900); color: #fff; font-weight: 800; font-size: 0.9rem; padding: 0.25rem 0.65rem; border-radius: 6px;">🔒</span>
                        <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--dark-950); margin: 0;">Privacy Policy &amp; Passenger Data Protection</h2>
                    </div>
                    <p style="font-size: 0.95rem; color: var(--slate-700); line-height: 1.7; margin-bottom: 1rem;">
                        Vaishnavi Tour's Taxi Service respects and values the privacy of every traveler. We collect personal details (such as your full name, telephone number, pickup address, travel itinerary, and email address) exclusively to coordinate your taxi booking, send chauffeur dispatch updates, ensure journey safety, and generate legal GST invoices.
                    </p>
                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.75rem;">
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.925rem; color: var(--slate-600); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span>Your contact details are shared only with the assigned vehicle driver for seamless pickup coordination.</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.925rem; color: var(--slate-600); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span>We never sell, rent, lease, or distribute passenger contact information to unauthorized third parties or marketing telemarketers.</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.75rem; font-size: 0.925rem; color: var(--slate-600); line-height: 1.6;">
                            <span style="color: var(--primary); font-size: 1.1rem; line-height: 1.2;">•</span>
                            <span>Payment and transaction records are kept strictly confidential in accordance with Indian financial accounting standards.</span>
                        </li>
                    </ul>
                </div>

                <!-- Contact &amp; Queries Callout -->
                <div style="background: linear-gradient(135deg, var(--dark-950) 0%, #1e293b 100%); border-radius: var(--radius-lg); padding: 2rem; color: #fff; text-align: center;">
                    <h3 style="font-size: 1.25rem; font-weight: 800; color: #fff; margin-bottom: 0.5rem;">Have Questions About Our Booking Terms?</h3>
                    <p style="font-size: 0.925rem; color: var(--slate-300); max-width: 580px; margin: 0 auto 1.25rem; line-height: 1.6;">
                        Our dispatch control team in Bilaspur is available 24/7 to answer questions regarding rates, routes, cancellations, or custom tour itineraries.
                    </p>
                    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                        <a href="{{ route('contact') }}" class="btn btn-primary" style="font-weight: 700;">
                            Contact Our Team
                        </a>
                        <a href="{{ route('booking') }}" class="btn btn-outline" style="border-color: #fff; color: #fff; font-weight: 700;">
                            Proceed to Taxi Booking →
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
@endsection
