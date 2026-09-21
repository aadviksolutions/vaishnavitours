@extends('layouts.app')

@section('title', 'Rates & Tariffs - Vaishnavi Tours Bilaspur')
@section('meta_description', 'Database-driven transparent rate card for Vaishnavi Tours taxi services. Clear tariffs, extra km rates, waiting charges, and night allowances.')

@section('content')
<section style="background: var(--dark-900); color: #fff; padding: 3.5rem 0;">
    <div class="container text-center">
        <span style="color: var(--primary); font-weight: 800; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px;">Transparent Pricing</span>
        <h1 style="color: #fff; font-size: 2.5rem; margin-top: 0.25rem;">Service Rates & Tariffs</h1>
        <p style="color: var(--slate-300); max-width: 620px; margin: 0.5rem auto 0;">No hidden costs or dynamic peak surges. Transparent distance and hourly pricing for Bilaspur and all Chhattisgarh routes.</p>
    </div>
</section>

<section style="padding: 4rem 0 5rem; background: var(--slate-50);">
    <div class="container">
        @if(isset($rates) && $rates->isNotEmpty())
            <div class="card" style="padding: 1.75rem; margin-bottom: 2.5rem;">
                <div class="card-header" style="margin-bottom: 1.5rem;">
                    <div>
                        <h2 class="card-title" style="font-size: 1.35rem; font-weight: 800;">Current Tariff Schedule</h2>
                        <p style="color: var(--slate-500); font-size: 0.85rem; margin: 2px 0 0 0;">Official database-driven tariffs managed by our operations center.</p>
                    </div>
                    <span class="badge badge-available">Live Database Rates</span>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Trip Type</th>
                                <th>Rate</th>
                                <th>Extra KM</th>
                                <th>Waiting Charge</th>
                                <th>Night Charge</th>
                                <th>Notes</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rates as $r)
                                <tr>
                                    <td>
                                        <div style="font-weight: 800; color: var(--dark-900); font-size: 0.95rem;">
                                            {{ $r->vehicle_name }}
                                        </div>
                                        @if($r->vehicle_type)
                                            <span class="badge badge-outline" style="font-size: 0.725rem;">{{ $r->vehicle_type }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge" style="background: var(--primary-light); color: var(--primary-dark); font-weight: 700;">
                                            {{ $r->trip_type }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="rate-highlight">
                                            ₹{{ number_format($r->rate, 2) }}
                                        </div>
                                    </td>
                                    <td>
                                        <span style="font-weight: 600; color: var(--dark-800);">
                                            {{ $r->extra_km_rate ? '₹' . number_format($r->extra_km_rate, 2) . '/km' : '—' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span style="color: var(--slate-600);">
                                            {{ $r->waiting_charge ? '₹' . number_format($r->waiting_charge, 2) . '/hr' : '—' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span style="color: var(--slate-600);">
                                            {{ $r->night_charge ? '₹' . number_format($r->night_charge, 2) : '—' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.825rem; color: var(--slate-500); max-width: 260px; line-height: 1.4;">
                                            {{ $r->notes ?? 'Standard commercial terms apply.' }}
                                        </div>
                                    </td>
                                    <td style="text-align: right;">
                                        <a href="{{ route('booking') }}?trip_type={{ urlencode($r->trip_type) }}" class="btn btn-primary btn-sm">
                                            Book Taxi
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <!-- Empty state fallback required by section 11 -->
            <div class="rates-empty-state">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📞</div>
                <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--dark-900); margin-bottom: 0.5rem;">
                    Contact us for current service rates.
                </h2>
                <p style="color: var(--slate-500); max-width: 500px; margin: 0 auto 1.5rem; font-size: 0.95rem;">
                    Our dispatch desk is available 24/7 to provide instant quotes tailored to your destination and schedule.
                </p>
                <div class="d-flex justify-center gap-2">
                    <a href="{{ route('contact') }}" class="btn btn-primary">
                        Contact Us
                    </a>
                    <a href="tel:+919893012345" class="btn btn-dark">
                        Call +91 98930 12345
                    </a>
                </div>
            </div>
        @endif

        <!-- General Terms Card -->
        <div class="card" style="padding: 1.75rem; background: var(--white); border-left: 4px solid var(--primary);">
            <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 0.75rem; color: var(--dark-900);">Important Billing Information</h3>
            <ul style="color: var(--slate-600); font-size: 0.885rem; line-height: 1.7; display: flex; flex-direction: column; gap: 0.35rem;">
                <li>• Toll taxes, state permit taxes, and parking fees are charged on actuals against official receipts.</li>
                <li>• Night driver allowances apply between 10:00 PM and 06:00 AM.</li>
                <li>• Outstation day packages are calculated on a minimum daily average of 250 kilometers.</li>
                <li>• Doorstep pickup within Bilaspur municipal limits has zero extra pickup charge.</li>
            </ul>
        </div>
    </div>
</section>
@endsection
