@extends('layouts.app')

@section('title', 'Contact Us & Head Office - Vaishnavi Tours Bilaspur')

@section('content')
<section style="background: var(--dark-900); color: #fff; padding: 3rem 0;">
    <div class="container text-center">
        <span style="color: var(--primary); font-weight: 800; text-transform: uppercase; font-size: 0.85rem;">Get in Touch</span>
        <h1 style="color: #fff; font-size: 2.5rem; margin-top: 0.25rem;">Contact Vaishnavi Tours</h1>
        <p style="color: var(--slate-300); max-width: 600px; margin: 0.5rem auto 0;">24/7 central booking desk, airport dispatch office, and road travel assistance.</p>
    </div>
</section>

<section style="padding: 4rem 0 5rem;">
    <div class="container">
        <div class="grid grid-2 gap-4">
            <!-- Left Info -->
            <div>
                <h2 style="font-size: 1.75rem; margin-bottom: 1.25rem;">Bilaspur Head Office</h2>

                <div class="card" style="padding: 2rem; margin-bottom: 2rem;">
                    <div style="display: flex; flex-direction: column; gap: 1.25rem; font-size: 1rem;">
                        <div style="display: flex; gap: 1rem; align-items: flex-start;">
                            <div style="font-size: 1.5rem; color: var(--primary-dark);">📍</div>
                            <div>
                                <strong style="display: block; margin-bottom: 0.25rem;">Address:</strong>
                                {{ config('vaishnavi.address') }}
                            </div>
                        </div>

                        <div style="display: flex; gap: 1rem; align-items: flex-start;">
                            <div style="font-size: 1.5rem; color: var(--primary-dark);">📞</div>
                            <div>
                                <strong style="display: block; margin-bottom: 0.25rem;">Calling Numbers:</strong>
                                <div>
                                    <a href="tel:{{ config('vaishnavi.phone_primary_tel') }}" style="color: var(--primary-dark); font-weight: 700; font-size: 1.1rem; text-decoration: none;">{{ config('vaishnavi.phone_primary') }}</a>
                                </div>
                                <div style="margin-top: 0.25rem;">
                                    <a href="tel:{{ config('vaishnavi.phone_secondary_tel') }}" style="color: var(--dark-900); font-weight: 700; font-size: 1.05rem; text-decoration: none;">{{ config('vaishnavi.phone_secondary') }}</a>
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; gap: 1rem; align-items: flex-start;">
                            <div style="font-size: 1.5rem; color: #25D366;">💬</div>
                            <div>
                                <strong style="display: block; margin-bottom: 0.25rem; color: #166534;">WhatsApp:</strong>
                                <a href="{{ config('vaishnavi.whatsapp_link') }}" target="_blank" class="btn btn-sm" style="background: #25D366; color: #fff; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; margin-top: 2px;">
                                    <span>Chat on WhatsApp ({{ config('vaishnavi.whatsapp') }})</span>
                                </a>
                            </div>
                        </div>

                        <div style="display: flex; gap: 1rem; align-items: flex-start;" id="emergency">
                            <div style="font-size: 1.5rem; color: var(--primary-dark);">🚗</div>
                            <div>
                                <strong style="display: block; margin-bottom: 0.25rem;">24/7 Road Assistance & Booking:</strong>
                                <span style="color: var(--dark-900); font-weight: 600;">Direct Dispatch / Calling: </span>
                                <a href="tel:{{ config('vaishnavi.phone_primary_tel') }}" style="color: var(--primary-dark); font-weight: 700;">{{ config('vaishnavi.phone_primary') }}</a>
                            </div>
                        </div>

                        <div style="display: flex; gap: 1rem; align-items: flex-start;">
                            <div style="font-size: 1.5rem; color: #25D366;">🚖</div>
                            <div>
                                <strong style="display: block; margin-bottom: 0.25rem;">Online Cab Reservation:</strong>
                                <a href="{{ route('booking') }}" style="color: #059669; font-weight: 700;">Book a Cab Online</a>
                            </div>
                        </div>

                        <div style="display: flex; gap: 1rem; align-items: flex-start;">
                            <div style="font-size: 1.5rem; color: var(--info);">✉️</div>
                            <div>
                                <strong style="display: block; margin-bottom: 0.25rem;">Email Support:</strong>
                                <a href="mailto:info@vaishnavitours.com">info@vaishnavitours.com</a> / <a href="mailto:support@vaishnavitours.com">support@vaishnavitours.com</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card" style="background: var(--slate-100);">
                    <h3 style="font-size: 1.15rem; margin-bottom: 0.5rem;">Corporate & Long-term Contracts</h3>
                    <p style="font-size: 0.9rem; color: var(--slate-600);">
                        For regular business transfers, monthly billing accounts, or multi-car event logistics for weddings in Bilaspur and Raipur, submit our <a href="{{ route('enquiry') }}" style="font-weight: 700;">corporate enquiry form</a>.
                    </p>
                </div>
            </div>

            <!-- Right Message Form -->
            <div>
                <div class="card" style="padding: 2.25rem;">
                    <h2 style="font-size: 1.75rem; margin-bottom: 0.5rem;">Send Us a Message</h2>
                    <p style="font-size: 0.9rem; color: var(--slate-500); margin-bottom: 1.5rem;">Fill out this form and our dispatch manager will call you back within 15 minutes.</p>

                    <form action="{{ route('enquiry.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Your Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Full Name" required>
                        </div>

                        <div class="grid grid-2 gap-2">
                            <div class="form-group">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="phone" class="form-control" placeholder="10-digit mobile" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" placeholder="email@example.com">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Service Type</label>
                            <select name="service_type" class="form-select">
                                <option value="General Outstation">General Outstation Cab</option>
                                <option value="Airport Transfer">Raipur Airport Pick / Drop</option>
                                <option value="Local Hourly">Local 8hr / 80km Rental</option>
                                <option value="Emergency Ambulance Cab">Emergency Medical Transfer</option>
                                <option value="Corporate / Event">Corporate or Wedding Car Fleet</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Subject</label>
                            <input type="text" name="subject" class="form-control" placeholder="Brief subject (e.g. Travel Inquiry for Bastar)">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Your Message / Travel Requirement</label>
                            <textarea name="message" class="form-control" rows="4" placeholder="Mention travel dates, passenger count, preferred car..." required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                            Submit Inquiry
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
