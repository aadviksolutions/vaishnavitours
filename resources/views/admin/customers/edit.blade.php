@extends('layouts.admin')

@section('title', 'Edit Customer / Corporate / Partner')
@section('page_title', 'Edit Customer Details')

@section('content')
@php
    $currentType = old('customer_type', $user->customer->customer_type ?? 'individual');
    $cleanEmail = !str_ends_with($user->email, '@vaishnavitours.in') ? $user->email : '';
@endphp

<div class="d-flex justify-between align-center flex-wrap gap-2 mb-4">
    <div>
        <a href="{{ route('admin.customers.show', $user->id) }}" style="font-size: 0.875rem; color: var(--slate-600); font-weight: 600;">
            ← Back to Customer Profile
        </a>
        <h1 style="font-size: 1.65rem; font-weight: 800; margin: 4px 0 0 0; color: var(--dark-950);">
            <x-icon name="user-check" :size="22" style="color: var(--primary); vertical-align: middle; margin-right: 6px;" /> Edit: {{ $user->name }}
        </h1>
        <p style="color: var(--slate-500); margin: 2px 0 0 0; font-size: 0.875rem;">
            Update customer contact information, GSTIN details, agency commission rate, or base location.
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.customers.show', $user->id) }}" class="btn btn-outline">
            View Profile
        </a>
    </div>
</div>

@if(session('error'))
    <div class="alert alert-danger mb-4" style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; padding: 0.85rem 1.25rem; border-radius: 8px;">
        <x-icon name="alert-triangle" :size="15" class="me-1 inline" /> {{ session('error') }}
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success mb-4" style="background: #F0FDF4; border: 1px solid #BBF7D0; color: #166534; padding: 0.85rem 1.25rem; border-radius: 8px;">
        <x-icon name="circle-check" :size="15" class="me-1 inline" /> {{ session('success') }}
    </div>
@endif

<div class="card" style="max-width: 860px; margin: 0 auto; padding: 2rem;">

    <!-- Customer Type Selector -->
    <div class="mb-4">
        <label class="form-label" style="font-weight: 800; font-size: 0.95rem; margin-bottom: 0.5rem; display: block;">
            Account Category:
        </label>
        <div class="d-flex gap-3 flex-wrap">
            <label style="flex: 1; min-width: 200px; border: 2px solid {{ $currentType === 'individual' ? '#2563EB' : 'var(--slate-200)' }}; background: {{ $currentType === 'individual' ? '#EFF6FF' : '#fff' }}; padding: 1rem; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 10px;" id="lblTypeIndiv">
                <input type="radio" name="cust_type_select" value="individual" {{ $currentType === 'individual' ? 'checked' : '' }} onchange="setType('individual')">
                <div>
                    <div style="font-weight: 800; color: #1E40AF; font-size: 0.95rem;">
                        <x-icon name="user" :size="15" class="me-1 inline" /> Individual Passenger
                    </div>
                    <div style="font-size: 0.775rem; color: #3B82F6;">Regular personal client</div>
                </div>
            </label>

            <label style="flex: 1; min-width: 200px; border: 2px solid {{ $currentType === 'corporate' ? '#2563EB' : 'var(--slate-200)' }}; background: {{ $currentType === 'corporate' ? '#EFF6FF' : '#fff' }}; padding: 1rem; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 10px;" id="lblTypeCorp">
                <input type="radio" name="cust_type_select" value="corporate" {{ $currentType === 'corporate' ? 'checked' : '' }} onchange="setType('corporate')">
                <div>
                    <div style="font-weight: 800; color: var(--dark-900); font-size: 0.95rem;">
                        <x-icon name="building-2" :size="15" class="me-1 inline" /> Corporate Client
                    </div>
                    <div style="font-size: 0.775rem; color: var(--slate-500);">GST billing company</div>
                </div>
            </label>

            <label style="flex: 1; min-width: 200px; border: 2px solid {{ $currentType === 'travel_partner' ? '#D97706' : 'var(--slate-200)' }}; background: {{ $currentType === 'travel_partner' ? '#FFFBEB' : '#fff' }}; padding: 1rem; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 10px;" id="lblTypePartner">
                <input type="radio" name="cust_type_select" value="travel_partner" {{ $currentType === 'travel_partner' ? 'checked' : '' }} onchange="setType('travel_partner')">
                <div>
                    <div style="font-weight: 800; color: var(--dark-900); font-size: 0.95rem;">
                        <x-icon name="handshake" :size="15" class="me-1 inline" /> Travel Partner / Agent
                    </div>
                    <div style="font-size: 0.775rem; color: var(--slate-500);">Agency & referral commission</div>
                </div>
            </label>
        </div>
    </div>

    <form action="{{ route('admin.customers.update', $user->id) }}" method="POST" id="mainCustomerForm">
        @csrf
        @method('PUT')
        <input type="hidden" name="customer_type" id="pageCustomerType" value="{{ $currentType }}">

        <!-- 1. Individual Section -->
        <div id="sectionIndividual" style="{{ $currentType === 'individual' ? '' : 'display: none;' }}">
            <h3 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 1rem; color: var(--dark-900); border-bottom: 1px solid var(--slate-100); padding-bottom: 0.5rem;">
                <x-icon name="user" :size="16" class="me-1 inline" /> Passenger Personal Details
            </h3>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" style="font-weight: 700;">Full Name *</label>
                    <input type="text" name="name" id="fldIndivName" class="form-control" placeholder="e.g. Ramesh Agrawal" value="{{ old('name', $user->name) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" style="font-weight: 700;">Mobile Number *</label>
                    <input type="tel" name="phone" id="fldIndivPhone" class="form-control" placeholder="10-digit mobile number" value="{{ old('phone', $user->phone) }}">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" style="font-weight: 700;">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="e.g. ramesh@example.com (optional)" value="{{ old('email', $cleanEmail) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" style="font-weight: 700;">Alternate Phone / WhatsApp</label>
                    <input type="tel" name="alternate_phone" class="form-control" placeholder="Optional backup contact" value="{{ old('alternate_phone', $user->customer->alternate_phone ?? '') }}">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label" style="font-weight: 700;">Residential / Pickup Address</label>
                <input type="text" name="address" class="form-control" placeholder="House/Flat No, Landmark, Area..." value="{{ old('address', $user->customer->address ?? '') }}">
            </div>
        </div>

        <!-- 2. Corporate Section -->
        <div id="sectionCorporate" style="{{ $currentType === 'corporate' ? '' : 'display: none;' }}">
            <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 8px; padding: 1.25rem; margin-bottom: 1.5rem;">
                <h3 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 1rem; color: #1E40AF;">
                    <x-icon name="building-2" :size="16" class="me-1 inline" /> Corporate Account & GST Billing Info
                </h3>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: 700; color: #1E3A8A;">Company / Organization Name *</label>
                        <input type="text" name="company_name" id="fldCorpComp" class="form-control" placeholder="e.g. Jindal Steel & Power, NTPC Ltd." value="{{ old('company_name', $user->customer->company_name ?? $user->name) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: 700; color: #1E3A8A;">Company GSTIN Number</label>
                        <input type="text" name="corporate_gstin" class="form-control" placeholder="15-digit GSTIN (e.g. 22AAAAA0000A1Z5)" maxlength="15" style="text-transform: uppercase;" value="{{ old('corporate_gstin', $user->customer->gstin ?? '') }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: 700; color: #1E3A8A;">Authorized Contact Person / Officer *</label>
                        <input type="text" name="corporate_contact_person" class="form-control" placeholder="e.g. Mr. Anil Verma (Admin Manager)" value="{{ old('corporate_contact_person', $user->customer->contact_person ?? '') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: 700; color: #1E3A8A;">Official Phone / WhatsApp *</label>
                        <input type="tel" name="corporate_phone" class="form-control" placeholder="Mobile number for invoice delivery" value="{{ old('corporate_phone', $user->phone) }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: 700; color: #1E3A8A;">Billing Email Address</label>
                        <input type="email" name="corporate_email" class="form-control" placeholder="accounts@company.com" value="{{ old('corporate_email', $cleanEmail) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: 700; color: #1E3A8A;">Payment Terms</label>
                        <select name="corporate_payment_terms" class="form-select">
                            <option value="Net 30 Days">Net 30 Days Invoice</option>
                            <option value="Net 15 Days">Net 15 Days Invoice</option>
                            <option value="Payment on Billing">Immediate Payment on Billing</option>
                            <option value="50% Advance">50% Advance + 50% on Completion</option>
                        </select>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label" style="font-weight: 700; color: #1E3A8A;">Registered Corporate Billing Address</label>
                    <textarea name="corporate_billing_address" class="form-control" rows="2" placeholder="Registered office address for GST tax invoice...">{{ old('corporate_billing_address', $user->customer->billing_address ?? $user->customer->address ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <!-- 3. Travel Partner Section -->
        <div id="sectionPartner" style="{{ $currentType === 'travel_partner' ? '' : 'display: none;' }}">
            <div style="background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 8px; padding: 1.25rem; margin-bottom: 1.5rem;">
                <h3 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 1rem; color: #92400E;">
                    <x-icon name="handshake" :size="16" class="me-1 inline" /> Travel Partner & Agency Collaboration
                </h3>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: 700; color: #78350F;">Travel Agency / Partner Name *</label>
                        <input type="text" name="partner_name" id="fldPartnerName" class="form-control" placeholder="e.g. Chhattisgarh Travels, Sharma Tours" value="{{ old('partner_name', $user->customer->partner_name ?? $user->name) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: 700; color: #78350F;">Agency GSTIN (If Registered)</label>
                        <input type="text" name="partner_gstin" class="form-control" placeholder="15-digit GSTIN (optional)" maxlength="15" style="text-transform: uppercase;" value="{{ old('partner_gstin', $user->customer->gstin ?? '') }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: 700; color: #78350F;">Managing Agent / Contact Person *</label>
                        <input type="text" name="partner_contact_person" class="form-control" placeholder="e.g. Agent Name / Manager" value="{{ old('partner_contact_person', $user->customer->contact_person ?? '') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: 700; color: #78350F;">Partner Mobile / WhatsApp *</label>
                        <input type="tel" name="partner_phone" class="form-control" placeholder="Mobile number for dispatch updates" value="{{ old('partner_phone', $user->phone) }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: 700; color: #78350F;">Agency Email Address</label>
                        <input type="email" name="partner_email" class="form-control" placeholder="partner@travelagency.com" value="{{ old('partner_email', $cleanEmail) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: 700; color: #78350F;">Commission Rate / Agreement</label>
                        <input type="text" name="partner_commission" class="form-control" placeholder="e.g. 10% per booking or ₹300 per trip" value="{{ old('partner_commission', $user->customer->partner_commission ?? '') }}">
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label" style="font-weight: 700; color: #78350F;">Agency Branch Office Address</label>
                    <input type="text" name="partner_address" class="form-control" placeholder="Agency branch / office address" value="{{ old('partner_address', $user->customer->address ?? '') }}">
                </div>
            </div>
        </div>

        <!-- Common Location Fields -->
        <h3 style="font-size: 1.05rem; font-weight: 800; margin: 1.5rem 0 1rem 0; color: var(--dark-900); border-bottom: 1px solid var(--slate-100); padding-bottom: 0.5rem;">
            Location & Base Details
        </h3>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label" style="font-weight: 700;">City *</label>
                <input type="text" name="city" class="form-control" value="{{ old('city', $user->customer->city ?? 'Bilaspur') }}" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label" style="font-weight: 700;">State *</label>
                <input type="text" name="state" class="form-control" value="{{ old('state', $user->customer->state ?? 'Chhattisgarh') }}" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label" style="font-weight: 700;">Pincode</label>
                <input type="text" name="pincode" class="form-control" value="{{ old('pincode', $user->customer->pincode ?? '495001') }}">
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 mb-3">
                <label class="form-label" style="font-weight: 700;">Internal Admin Notes</label>
                <textarea name="notes" class="form-control" rows="3" placeholder="Special preferences, vehicle preference, VIP status, billing notes...">{{ old('notes', $user->customer->notes ?? '') }}</textarea>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label" style="font-weight: 700;">Account Status</label>
                <div style="background: var(--slate-50); border: 1px solid var(--slate-200); border-radius: 8px; padding: 1rem;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 700;">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} style="width: 18px; height: 18px;">
                        <span>Account is Active</span>
                    </label>
                    <p style="margin: 6px 0 0 26px; font-size: 0.775rem; color: var(--slate-500);">Uncheck to suspend login or bookings for this profile.</p>
                </div>
            </div>
        </div>

        <div class="d-flex justify-between align-center pt-3" style="border-top: 1px solid var(--slate-200);">
            <a href="{{ route('admin.customers.show', $user->id) }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary" style="font-weight: 800; padding: 0.65rem 1.75rem;">
                Save Changes & Update Profile
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function setType(type) {
        document.getElementById('pageCustomerType').value = type;

        const secIndiv = document.getElementById('sectionIndividual');
        const secCorp = document.getElementById('sectionCorporate');
        const secPartner = document.getElementById('sectionPartner');

        const lblIndiv = document.getElementById('lblTypeIndiv');
        const lblCorp = document.getElementById('lblTypeCorp');
        const lblPartner = document.getElementById('lblTypePartner');

        const fldIndivName = document.getElementById('fldIndivName');
        const fldIndivPhone = document.getElementById('fldIndivPhone');
        const fldCorpComp = document.getElementById('fldCorpComp');
        const fldPartnerName = document.getElementById('fldPartnerName');

        // Reset radio borders
        [lblIndiv, lblCorp, lblPartner].forEach(lbl => {
            lbl.style.borderColor = 'var(--slate-200)';
            lbl.style.background = '#fff';
        });

        if (type === 'corporate') {
            secIndiv.style.display = 'none';
            secCorp.style.display = 'block';
            secPartner.style.display = 'none';

            lblCorp.style.borderColor = '#2563EB';
            lblCorp.style.background = '#EFF6FF';

            if (fldIndivName) fldIndivName.removeAttribute('required');
            if (fldIndivPhone) fldIndivPhone.removeAttribute('required');
            if (fldCorpComp) fldCorpComp.setAttribute('required', 'required');
            if (fldPartnerName) fldPartnerName.removeAttribute('required');
        } else if (type === 'travel_partner') {
            secIndiv.style.display = 'none';
            secCorp.style.display = 'none';
            secPartner.style.display = 'block';

            lblPartner.style.borderColor = '#D97706';
            lblPartner.style.background = '#FFFBEB';

            if (fldIndivName) fldIndivName.removeAttribute('required');
            if (fldIndivPhone) fldIndivPhone.removeAttribute('required');
            if (fldCorpComp) fldCorpComp.removeAttribute('required');
            if (fldPartnerName) fldPartnerName.setAttribute('required', 'required');
        } else {
            secIndiv.style.display = 'block';
            secCorp.style.display = 'none';
            secPartner.style.display = 'none';

            lblIndiv.style.borderColor = '#2563EB';
            lblIndiv.style.background = '#EFF6FF';

            if (fldIndivName) fldIndivName.setAttribute('required', 'required');
            if (fldIndivPhone) fldIndivPhone.setAttribute('required', 'required');
            if (fldCorpComp) fldCorpComp.removeAttribute('required');
            if (fldPartnerName) fldPartnerName.removeAttribute('required');
        }
    }
</script>
@endpush
@endsection
