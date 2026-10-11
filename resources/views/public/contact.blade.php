@extends('layouts.app')

@section('title', 'Contact Vaishnavi Tours | Taxi Booking Office Bilaspur')
@section('meta_description', 'Contact Vaishnavi Tours in Mangal Chowk, Bilaspur, Chhattisgarh. Call 9244784443 / 9179484443 or WhatsApp for 24/7 cab reservations, emergency road help, and queries.')
@section('canonical', route('contact'))

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Contact Us', 'item' => route('contact')],
    ],
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endpush

@section('content')
<section style="background: var(--navy-900); color: #fff; padding: 2.25rem 0; border-bottom: 1px solid var(--navy-800);">
    <div class="container text-center">
        <span style="color: var(--accent-gold); font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.8px;">Connect With Us</span>
        <h1 style="color: #fff; font-size: 1.65rem; font-weight: 600; margin-top: 0.25rem;">Contact Vaishnavi Tours</h1>
        <p style="color: var(--slate-300); max-width: 580px; margin: 0.35rem auto 0; font-size: 0.875rem;">24/7 central dispatch desk, airport transfers, and corporate travel coordination.</p>
    </div>
</section>

<section style="padding: 2.5rem 0 3.5rem; background: var(--slate-50);">
    <div class="container">
        <div class="grid grid-2 gap-3">
            <!-- Left Info -->
            <div>
                <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem; color: var(--dark-900);">Bilaspur Central Office</h2>

                <div class="card" style="padding: 1.25rem; margin-bottom: 1rem; border: 1px solid var(--slate-200);">
                    <div style="display: flex; flex-direction: column; gap: 1rem; font-size: 0.875rem;">
                        <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
                            <div class="icon-box icon-box-sm icon-box-primary" style="margin-top: 2px;"><x-icon name="map-pin" size="16" /></div>
                            <div>
                                <strong style="display: block; margin-bottom: 0.2rem; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--slate-500);">Headquarters:</strong>
                                <span style="color: var(--dark-900);">{{ config('vaishnavi.address') }}</span>
                            </div>
                        </div>

                        <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
                            <div class="icon-box icon-box-sm icon-box-primary" style="margin-top: 2px;"><x-icon name="phone" size="16" /></div>
                            <div>
                                <strong style="display: block; margin-bottom: 0.2rem; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--slate-500);">Direct Dispatch Numbers:</strong>
                                <div class="d-flex gap-3 flex-wrap align-center">
                                    <a href="tel:{{ config('vaishnavi.phone_primary_tel') }}" style="color: var(--primary); font-weight: 600; font-size: 0.95rem; text-decoration: none;">{{ config('vaishnavi.phone_primary') }}</a>
                                    <span style="color: var(--slate-300);">•</span>
                                    <a href="tel:{{ config('vaishnavi.phone_secondary_tel') }}" style="color: var(--dark-800); font-weight: 600; font-size: 0.95rem; text-decoration: none;">{{ config('vaishnavi.phone_secondary') }}</a>
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
                            <div class="icon-box icon-box-sm icon-box-whatsapp" style="margin-top: 2px;"><x-icon.whatsapp size="16" /></div>
                            <div>
                                <strong style="display: block; margin-bottom: 0.2rem; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--slate-500);">Instant WhatsApp:</strong>
                                <a href="{{ config('vaishnavi.whatsapp_link') }}" target="_blank" class="btn btn-sm" style="background: #25D366; color: #fff; font-weight: 600; font-size: 0.775rem; padding: 0.35rem 0.75rem; display: inline-flex; align-items: center; gap: 6px;">
                                    <x-icon.whatsapp size="14" /><span>Chat on WhatsApp ({{ config('vaishnavi.whatsapp') }})</span>
                                </a>
                            </div>
                        </div>

                        <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
                            <div class="icon-box icon-box-sm icon-box-primary" style="margin-top: 2px;"><x-icon name="mail" size="16" /></div>
                            <div>
                                <strong style="display: block; margin-bottom: 0.2rem; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--slate-500);">Corporate Inquiries:</strong>
                                <a href="mailto:info@vaishnavitours.com" style="color: var(--primary); font-weight: 500;">info@vaishnavitours.com</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card" style="background: #ffffff; border: 1px solid var(--slate-200); padding: 1rem 1.15rem; margin-bottom: 1rem;">
                    <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
                        <div class="icon-box icon-box-sm icon-box-primary" style="margin-top: 2px;">
                            <x-icon name="clock-3" size="15" />
                        </div>
                        <div>
                            <h3 style="font-size: 0.95rem; font-weight: 600; margin-bottom: 0.15rem; color: var(--dark-900);">24/7 Operations Desk</h3>
                            <p style="font-size: 0.8rem; color: var(--slate-600); margin: 0; line-height: 1.45;">
                                Continuous dispatch support for airport connections, midnight pickups, and outstation trips across Chhattisgarh.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="card" style="background: #ffffff; border: 1px solid var(--slate-200); padding: 1rem 1.15rem;">
                    <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
                        <div class="icon-box icon-box-sm icon-box-primary" style="margin-top: 2px;">
                            <x-icon name="briefcase" size="15" />
                        </div>
                        <div>
                            <h3 style="font-size: 0.95rem; font-weight: 600; margin-bottom: 0.15rem; color: var(--dark-900);">Corporate &amp; Event Fleet</h3>
                            <p style="font-size: 0.8rem; color: var(--slate-600); margin: 0; line-height: 1.45;">
                                Long-term monthly contracts, employee commutes, and wedding convoy bookings. <a href="{{ route('enquiry') }}" style="font-weight: 600; color: var(--primary);">Submit Corporate Inquiry</a>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Message Form -->
            <div>
                <div class="card" style="padding: 1.5rem; border: 1px solid var(--slate-200);">
                    <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.25rem; color: var(--dark-900);">Send a Travel Inquiry</h2>
                    <p style="font-size: 0.825rem; color: var(--slate-500); margin-bottom: 1.25rem;">Our customer operations desk will respond with confirmed rates and driver details.</p>

                    <form action="{{ route('enquiry.store') }}" method="POST">
                        @csrf
                        <div class="form-group" style="margin-bottom: 0.85rem;">
                            <label class="form-label" style="font-size: 0.775rem;">Your Name</label>
                            <input type="text" name="name" class="form-control form-control-sm" placeholder="Full Name" required>
                        </div>

                        <div class="grid grid-2 gap-2" style="margin-bottom: 0.85rem;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.775rem;">Phone Number</label>
                                <input type="tel" name="phone" class="form-control form-control-sm" placeholder="10-digit mobile" required>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.775rem;">Email Address</label>
                                <input type="email" name="email" class="form-control form-control-sm" placeholder="email@example.com">
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 0.85rem;">
                            <label class="form-label" style="font-size: 0.775rem;">Service Type</label>
                            <select name="service_type" class="form-select form-select-sm">
                                <option value="General Outstation">General Outstation Cab</option>
                                <option value="Airport Transfer">Raipur Airport Pick / Drop</option>
                                <option value="Local Hourly">Local 8hr / 80km Rental</option>
                                <option value="Emergency Ambulance Cab">Emergency Medical Transfer</option>
                                <option value="Corporate / Event">Corporate or Wedding Fleet</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 0.85rem;">
                            <label class="form-label" style="font-size: 0.775rem;">Subject</label>
                            <input type="text" name="subject" class="form-control form-control-sm" placeholder="e.g. Travel Inquiry for Bastar Circuit">
                        </div>

                        <div class="form-group" style="margin-bottom: 1.15rem;">
                            <label class="form-label" style="font-size: 0.775rem;">Requirement Details</label>
                            <textarea name="message" class="form-control" rows="3" placeholder="Travel dates, passenger count, preferred car type..." required style="font-size: 0.825rem;"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; height: 38px; font-size: 0.875rem; font-weight: 600;">
                            <x-icon name="send" size="15" style="margin-right: 6px;" />
                            <span>Submit Travel Inquiry</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
