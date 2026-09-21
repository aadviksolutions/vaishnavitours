@extends('layouts.app')

@section('title', 'Booking Enquiry & Custom Quotes - Vaishnavi Tours')

@section('content')
<section style="background: var(--dark-900); color: #fff; padding: 3rem 0;">
    <div class="container text-center">
        <span style="color: var(--primary); font-weight: 800; text-transform: uppercase; font-size: 0.85rem;">Custom Quotes</span>
        <h1 style="color: #fff; font-size: 2.5rem; margin-top: 0.25rem;">Submit a Travel Enquiry</h1>
        <p style="color: var(--slate-300); max-width: 600px; margin: 0.5rem auto 0;">Have customized travel requirements or need multi-vehicle fleets for corporate events or weddings? Reach out for customized pricing.</p>
    </div>
</section>

<section style="padding: 4rem 0 5rem;">
    <div class="container" style="max-width: 760px;">
        <div class="card" style="padding: 2.5rem; border: 2px solid var(--primary);">
            <form action="{{ route('enquiry.store') }}" method="POST">
                @csrf
                <div class="grid grid-2 gap-2">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Your Name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mobile Number</label>
                        <input type="tel" name="phone" class="form-control" placeholder="10-digit mobile" required>
                    </div>
                </div>

                <div class="grid grid-2 gap-2">
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="email@example.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Service Category</label>
                        <select name="service_type" class="form-select">
                            <option value="Outstation Cabs">Outstation Cabs & Trips</option>
                            <option value="Airport Transfer">Raipur Airport Pick / Drop</option>
                            <option value="Local Hourly">Local 8hr / 80km Rental</option>
                            <option value="Wedding / Event Convoy">Wedding / Event Multi-Car Fleet</option>
                            <option value="Corporate Monthly Rental">Corporate Monthly Contract</option>
                            <option value="Medical Emergency Transfer">Emergency Medical Transfer</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Subject</label>
                    <input type="text" name="subject" class="form-control" placeholder="e.g. Need 2 Innovas for 4 days trip to Jagdalpur" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Requirement Details</label>
                    <textarea name="message" class="form-control" rows="5" placeholder="Mention travel dates, pickup city, destinations, passenger count, luggage details, and any special preferences..." required></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                    Submit Travel Enquiry
                </button>
            </form>
        </div>
    </div>
</section>
@endsection
