@extends('layouts.admin')

@section('title', 'Company Settings')
@section('page_title', 'Configuration & Settings')

@section('content')
<div class="mb-4">
    <h1 style="font-size: 1.65rem; font-weight: 800; margin: 0;">Vaishnavi Tours Company Settings</h1>
    <p style="color: var(--slate-500); font-size: 0.875rem; margin: 2px 0 0 0;">Update Bilaspur head office contacts, emergency numbers, GST registration, and policy terms.</p>
</div>

@if(session('success'))
    <div class="alert alert-success mb-4">
        ✅ {{ session('success') }}
    </div>
@endif

<form action="{{ route('admin.settings.update') }}" method="POST">
    @csrf

    <div class="grid grid-3 gap-4">
        <div style="grid-column: span 2;">
            <!-- Brand & Contact -->
            <div class="card mb-4">
                <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1.25rem;">🏢 Brand & Company Profile</h3>

                <div class="grid grid-2 gap-3 mb-3">
                    <div class="form-group">
                        <label for="company_name" class="form-label">Brand Name *</label>
                        <input type="text" name="company_name" id="company_name" class="form-control" value="{{ old('company_name', $settings['company_name']->value ?? 'Vaishnavi Tours') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="company_tagline" class="form-label">Tagline / Slogan</label>
                        <input type="text" name="company_tagline" id="company_tagline" class="form-control" value="{{ old('company_tagline', $settings['company_tagline']->value ?? '24/7 Car Rentals, Cabs & Travel Service') }}">
                    </div>
                </div>

                <div class="grid grid-2 gap-3 mb-3">
                    <div class="form-group">
                        <label for="contact_email" class="form-label">General Contact Email *</label>
                        <input type="email" name="contact_email" id="contact_email" class="form-control" value="{{ old('contact_email', $settings['contact_email']->value ?? 'info@vaishnavitours.com') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="support_email" class="form-label">Customer Support Email</label>
                        <input type="email" name="support_email" id="support_email" class="form-control" value="{{ old('support_email', $settings['support_email']->value ?? 'support@vaishnavitours.com') }}">
                    </div>
                </div>
            </div>

            <!-- Helplines -->
            <div class="card mb-4">
                <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1.25rem;">📞 Contact & Emergency Helplines</h3>

                <div class="grid grid-2 gap-3 mb-3">
                    <div class="form-group">
                        <label for="phone_primary" class="form-label">Primary Booking Helpline</label>
                        <input type="text" name="phone_primary" id="phone_primary" class="form-control" value="{{ old('phone_primary', $settings['phone_primary']->value ?? '') }}" placeholder="Enter primary contact number">
                    </div>

                    <div class="form-group">
                        <label for="phone_secondary" class="form-label">Secondary Helpline / Landline</label>
                        <input type="text" name="phone_secondary" id="phone_secondary" class="form-control" value="{{ old('phone_secondary', $settings['phone_secondary']->value ?? '') }}" placeholder="Optional alternate number">
                    </div>
                </div>

                <div class="grid grid-2 gap-3">
                    <div class="form-group">
                        <label for="emergency_phone" class="form-label">24/7 Emergency Ambulance Helpline</label>
                        <input type="text" name="emergency_phone" id="emergency_phone" class="form-control" value="{{ old('emergency_phone', $settings['emergency_phone']->value ?? '') }}" placeholder="Optional emergency number">
                    </div>

                    <div class="form-group">
                        <label for="whatsapp_number" class="form-label">WhatsApp Number (with Country Code)</label>
                        <input type="text" name="whatsapp_number" id="whatsapp_number" class="form-control" value="{{ old('whatsapp_number', $settings['whatsapp_number']->value ?? '') }}" placeholder="e.g. 9198XXXXXXXX">
                    </div>
                </div>
            </div>

            <!-- Head Office Address & GST -->
            <div class="card mb-4">
                <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1.25rem;">📍 Bilaspur Head Office Address</h3>

                <div class="grid grid-2 gap-3 mb-3">
                    <div class="form-group">
                        <label for="address_line_1" class="form-label">Address Line 1 *</label>
                        <input type="text" name="address_line_1" id="address_line_1" class="form-control" value="{{ old('address_line_1', $settings['address_line_1']->value ?? 'B.N City Colony, Jonki Road') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="address_line_2" class="form-label">Address Line 2 / Landmark *</label>
                        <input type="text" name="address_line_2" id="address_line_2" class="form-control" value="{{ old('address_line_2', $settings['address_line_2']->value ?? 'Mangla Chowk') }}" required>
                    </div>
                </div>

                <div class="grid grid-3 gap-3 mb-3">
                    <div class="form-group">
                        <label for="city" class="form-label">City *</label>
                        <input type="text" name="city" id="city" class="form-control" value="{{ old('city', $settings['city']->value ?? 'Bilaspur') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="state" class="form-label">State *</label>
                        <input type="text" name="state" id="state" class="form-control" value="{{ old('state', $settings['state']->value ?? 'Chhattisgarh') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="pincode" class="form-label">PIN Code *</label>
                        <input type="text" name="pincode" id="pincode" class="form-control" value="{{ old('pincode', $settings['pincode']->value ?? '495001') }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="gstin" class="form-label">Goods & Services Tax (GSTIN) Number</label>
                    <input type="text" name="gstin" id="gstin" class="form-control" value="{{ old('gstin', $settings['gstin']->value ?? '22AAAAA0000A1Z5') }}">
                </div>
            </div>

            <!-- Booking Policy Terms -->
            <div class="card mb-4">
                <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1.25rem;">📄 Booking Policy Terms & Conditions</h3>

                <div class="form-group">
                    <label for="booking_terms" class="form-label">Standard Customer Policy (Printed on Invoices)</label>
                    <textarea name="booking_terms" id="booking_terms" class="form-control" rows="3">{{ old('booking_terms', $settings['booking_terms']->value ?? 'Toll plaza taxes, state border permits & parking fees are extra if applicable. Night driving allowance applies between 10:00 PM and 06:00 AM.') }}</textarea>
                </div>
            </div>

            <div class="d-flex justify-end gap-2 mb-5">
                <button type="submit" class="btn btn-primary" style="min-width: 200px;">
                    💾 Save All Settings
                </button>
            </div>
        </div>

        <!-- Right 1 Col: Quick Info -->
        <div>
            <div class="card mb-4" style="background: var(--slate-50);">
                <h4 style="font-size: 0.95rem; font-weight: 800; margin-bottom: 0.5rem;">🏢 Vaishnavi Tours HQ</h4>
                <p style="font-size: 0.825rem; color: var(--slate-600); line-height: 1.5; margin-bottom: 0.75rem;">
                    The contact numbers and addresses configured here are automatically synchronized across:
                </p>
                <ul style="font-size: 0.8rem; color: var(--slate-600); padding-left: 1.2rem; line-height: 1.6; margin: 0;">
                    <li>Public website header, footer & contact cards</li>
                    <li>24/7 Emergency Ambulance banner</li>
                    <li>Official GST Tax Invoices and customer receipts</li>
                    <li>Customer booking SMS/WhatsApp dispatch templates</li>
                </ul>
            </div>
        </div>
    </div>
</form>
@endsection
