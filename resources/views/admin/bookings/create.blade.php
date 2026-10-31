@extends('layouts.admin')

@section('title', 'Create New Booking')
@section('page_title', 'New Booking Creation')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.bookings.index') }}" style="font-size: 0.875rem; color: var(--slate-600); font-weight: 600;">
        ← Back to All Bookings
    </a>
    <h1 style="font-size: 1.65rem; font-weight: 800; margin: 4px 0 0 0;">Create Manual Cab Booking</h1>
    <p style="color: var(--slate-500); font-size: 0.875rem; margin: 2px 0 0 0;">Create a dispatch reservation for phone, walk-in, or corporate customer bookings.</p>
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

@if(isset($prefill) && $prefill)
    <div class="alert alert-info mb-4" style="background: #EFF6FF; border: 1px solid #BFDBFE; color: #1E40AF; padding: 0.85rem 1.25rem; border-radius: 8px;">
        <strong style="display: flex; align-items: center; gap: 6px;">
            <x-icon name="mail" size="16" /> 
            Converting Enquiry #{{ $prefill->enquiry_id }} ({{ $prefill->name }} · {{ $prefill->phone }})
        </strong>
        <p style="margin: 0.25rem 0 0 0; font-size: 0.825rem;">
            Customer and trip route details have been automatically filled from the original enquiry record.
        </p>
        <input type="hidden" name="from_enquiry_id" value="{{ $prefill->id }}">
    </div>
@endif

<form action="{{ route('admin.bookings.store') }}" method="POST">
    @csrf

    <!-- SECTION 4: Mandatory Booking Category Selection (CORP, MAN, TP) -->
    <div class="card mb-4" style="background: #fff; border: 1px solid var(--slate-200); border-radius: 8px; padding: 1.25rem;">
        <div style="font-weight: 800; font-size: 1rem; color: var(--dark-900); margin-bottom: 0.75rem;">
            <x-icon name="tag" :size="16" style="color: var(--primary); vertical-align: middle; margin-right: 6px;" /> 1. Booking Category / Source Classification *
        </div>
        <p style="font-size: 0.8rem; color: var(--slate-500); margin: 0 0 0.85rem 0;">
            Select the booking source. Unique collision-safe booking ID sequence will be assigned with appropriate prefix (CORP, MAN, TP).
        </p>
        <div class="grid grid-3 gap-3">
            <label style="border: 2px solid {{ old('booking_category', isset($prefill) && $prefill->source === 'corporate' ? 'CORP' : (isset($prefill) && $prefill->source === 'third_party' ? 'TP' : 'MAN')) === 'MAN' ? '#2563EB' : 'var(--slate-200)' }}; background: {{ old('booking_category', 'MAN') === 'MAN' ? '#EFF6FF' : '#fff' }}; padding: 0.75rem; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 8px;" id="lblCatMan">
                <input type="radio" name="booking_category" value="MAN" {{ old('booking_category', isset($prefill) && $prefill->source === 'corporate' ? 'CORP' : (isset($prefill) && $prefill->source === 'third_party' ? 'TP' : 'MAN')) === 'MAN' ? 'checked' : '' }} onchange="toggleCategoryFields()">
                <div>
                    <strong style="color: #1E40AF; display: block; font-size: 13px;">MAN — Manual Booking</strong>
                    <span style="font-size: 11px; color: var(--slate-500);">Direct phone / walk-in customer</span>
                </div>
            </label>

            <label style="border: 2px solid {{ old('booking_category') === 'CORP' ? '#7C3AED' : 'var(--slate-200)' }}; background: {{ old('booking_category') === 'CORP' ? '#F5F3FF' : '#fff' }}; padding: 0.75rem; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 8px;" id="lblCatCorp">
                <input type="radio" name="booking_category" value="CORP" {{ old('booking_category', isset($prefill) && $prefill->source === 'corporate' ? 'CORP' : '') === 'CORP' ? 'checked' : '' }} onchange="toggleCategoryFields()">
                <div>
                    <strong style="color: #6D28D9; display: block; font-size: 13px;">CORP — Corporate Booking</strong>
                    <span style="font-size: 11px; color: var(--slate-500);">B2B Company account & GST billing</span>
                </div>
            </label>

            <label style="border: 2px solid {{ old('booking_category') === 'TP' ? '#D97706' : 'var(--slate-200)' }}; background: {{ old('booking_category') === 'TP' ? '#FFFBEB' : '#fff' }}; padding: 0.75rem; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 8px;" id="lblCatTp">
                <input type="radio" name="booking_category" value="TP" {{ old('booking_category', isset($prefill) && $prefill->source === 'third_party' ? 'TP' : '') === 'TP' ? 'checked' : '' }} onchange="toggleCategoryFields()">
                <div>
                    <strong style="color: #B45309; display: block; font-size: 13px;">TP — Travel Partner / Agent</strong>
                    <span style="font-size: 11px; color: var(--slate-500);">Third-party partner reservation</span>
                </div>
            </label>
        </div>

        <!-- Corporate Dedicated Workflow Fields (Section 4 requirement) -->
        <div id="corporateFieldsCard" style="display: {{ old('booking_category', isset($prefill) && $prefill->source === 'corporate' ? 'CORP' : 'MAN') === 'CORP' ? 'block' : 'none' }}; margin-top: 1rem; padding: 1.15rem; background: #F5F3FF; border: 1.5px solid #DDD6FE; border-radius: 8px;">
            <div class="d-flex justify-between align-center mb-2 flex-wrap gap-2">
                <div style="font-weight: 800; font-size: 14px; color: #5B21B6; display: flex; align-items: center; gap: 7px;">
                    <x-icon name="building-2" :size="16" /> Corporate Account & GST Billing Profile
                </div>
                <span class="badge" style="background: #EDE9FE; color: #6D28D9; font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 9999px;">
                    {{ count($corporateClients) }} Saved Corporate Accounts
                </span>
            </div>

            <!-- Corporate Client Search & Dropdown Picker -->
            <div style="background: #ffffff; border: 1.5px solid #C4B5FD; border-radius: 8px; padding: 10px 12px; margin-bottom: 12px;">
                <div class="d-flex justify-between align-center mb-1">
                    <label style="font-size: 11.5px; font-weight: 700; color: #5B21B6; margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">
                        Select Saved Corporate Client or Add New
                    </label>
                    <button type="button" onclick="setNewCorporateClientMode()" style="background: none; border: none; font-size: 11.5px; color: #7C3AED; font-weight: 700; cursor: pointer; text-decoration: underline; padding: 0;">
                        + Clear & Add New Corporate Client
                    </button>
                </div>

                <!-- Live Search Box with Icon -->
                <div style="position: relative;" class="mb-2">
                    <div style="position: relative; display: flex; align-items: center;">
                        <span style="position: absolute; left: 10px; color: #8B5CF6; pointer-events: none; display: flex; align-items: center;">
                            <x-icon name="search" :size="14" />
                        </span>
                        <input type="text" 
                               id="corporate_search_input" 
                               class="form-control form-control-sm" 
                               placeholder="🔍 Search saved corporate by Company Name, Contact Person, Mobile, GSTIN..." 
                               autocomplete="off"
                               style="padding-left: 32px; padding-right: 30px; border: 1.5px solid #DDD6FE; border-radius: 6px; font-size: 12.5px;"
                               onkeyup="handleCorporateSearch(this.value)">
                        <button type="button" 
                                id="btn_clear_corp_search" 
                                onclick="clearCorporateSearch()" 
                                style="display: none; position: absolute; right: 8px; background: transparent; border: none; color: #8B5CF6; cursor: pointer; font-size: 14px; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;"
                                title="Clear search">✕</button>
                    </div>

                    <!-- Live floating search results dropdown -->
                    <div id="corporate_search_dropdown" 
                         style="display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0; background: #ffffff; border: 1.5px solid #8B5CF6; border-radius: 8px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.2); max-height: 220px; overflow-y: auto; z-index: 1060;">
                    </div>
                </div>

                <!-- Dropdown Select directly -->
                <div class="d-flex align-center gap-2">
                    <select id="corporate_client_select" class="form-select form-select-sm" onchange="onCorporateDropdownChange(this)" style="font-size: 12px; border: 1.5px solid #DDD6FE; border-radius: 6px;">
                        <option value="">-- Choose Existing Saved Corporate Client --</option>
                        <option value="new" style="font-weight: 800; color: #6D28D9; background: #F5F3FF;">➕ + Add New Corporate Client</option>
                        @foreach($corporateClients as $index => $cc)
                            <option value="{{ $index }}"
                                    data-company="{{ $cc['company_name'] }}"
                                    data-contact="{{ $cc['contact_person'] }}"
                                    data-phone="{{ $cc['phone'] }}"
                                    data-email="{{ $cc['email'] }}"
                                    data-gstin="{{ $cc['gstin'] }}"
                                    data-address="{{ $cc['billing_address'] }}"
                                    data-user-id="{{ $cc['user_id'] }}">
                                {{ $cc['company_name'] }} — {{ $cc['contact_person'] ?: 'No contact' }} ({{ $cc['phone'] ? '📞 ' . $cc['phone'] : ($cc['gstin'] ? 'GST: ' . $cc['gstin'] : 'Saved') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Selected Corporate Client Badge -->
                <div id="corporate_selected_card" style="display: none; margin-top: 8px; background: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 6px; padding: 6px 10px; font-size: 12px; color: #065F46;">
                    <div class="d-flex justify-between align-center">
                        <span id="corporate_selected_text" style="font-weight: 700;">✓ Saved Corporate Details Loaded</span>
                        <button type="button" onclick="setNewCorporateClientMode()" style="background: none; border: none; color: #047857; text-decoration: underline; font-size: 11px; cursor: pointer; padding: 0;">Change / Clear</button>
                    </div>
                </div>
            </div>

            <div class="grid grid-3 gap-2 mb-2">
                <div>
                    <label style="font-size: 11px; font-weight: 700; color: #4C1D95; display: block; margin-bottom: 2px;">Company / Client Name *</label>
                    <input type="text" name="company_name" id="corp_company_name" class="form-control form-control-sm" placeholder="e.g. Jindal Steel / NTPC Limited" value="{{ old('company_name') }}">
                </div>
                <div>
                    <label style="font-size: 11px; font-weight: 700; color: #4C1D95; display: block; margin-bottom: 2px;">Contact Person</label>
                    <input type="text" name="corporate_contact_person" id="corp_contact_person" class="form-control form-control-sm" placeholder="e.g. Mr. Anil Verma (Admin Manager)" value="{{ old('corporate_contact_person') }}">
                </div>
                <div>
                    <label style="font-size: 11px; font-weight: 700; color: #4C1D95; display: block; margin-bottom: 2px;">Corporate GSTIN</label>
                    <input type="text" name="corporate_gstin" id="corp_gstin" class="form-control form-control-sm" placeholder="e.g. 22AAAAA0000A1Z5" value="{{ old('corporate_gstin') }}">
                </div>
            </div>
            <div>
                <label style="font-size: 11px; font-weight: 700; color: #4C1D95; display: block; margin-bottom: 2px;">Corporate Billing Address</label>
                <input type="text" name="billing_address" id="corp_billing_address" class="form-control form-control-sm" placeholder="Registered office address for tax invoice..." value="{{ old('billing_address') }}">
            </div>
        </div>

        <!-- Travel Partner Dedicated Workflow Fields -->
        <div id="travelPartnerFieldsCard" style="display: {{ old('booking_category', isset($prefill) && $prefill->source === 'third_party' ? 'TP' : 'MAN') === 'TP' ? 'block' : 'none' }}; margin-top: 1rem; padding: 1.15rem; background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 8px;">
            <div style="font-weight: 800; font-size: 13.5px; color: #92400E; margin-bottom: 0.65rem; display: flex; align-items: center; gap: 6px;">
                <x-icon name="users" :size="15" /> Travel Partner / Agent Referral Details
            </div>
            <div class="grid grid-3 gap-3 mb-2">
                <div>
                    <label style="font-size: 11px; font-weight: 700; color: #78350F; display: block; margin-bottom: 3px;">Travel Partner / Agency Name *</label>
                    <input type="text" name="travel_partner_name" class="form-control form-control-sm" placeholder="e.g. MakeMyTrip, Sharma Tour & Travels" value="{{ old('travel_partner_name', isset($prefill) && $prefill->source === 'third_party' ? $prefill->name : '') }}">
                </div>
                <div>
                    <label style="font-size: 11px; font-weight: 700; color: #78350F; display: block; margin-bottom: 3px;">Partner Mobile / Phone *</label>
                    <input type="text" name="travel_partner_phone" class="form-control form-control-sm" placeholder="e.g. 9876543210" value="{{ old('travel_partner_phone', isset($prefill) && $prefill->source === 'third_party' ? $prefill->phone : '') }}">
                </div>
                <div>
                    <label style="font-size: 11px; font-weight: 700; color: #78350F; display: block; margin-bottom: 3px;">Partner Email</label>
                    <input type="email" name="travel_partner_email" class="form-control form-control-sm" placeholder="e.g. partner@travelagency.com" value="{{ old('travel_partner_email', isset($prefill) && $prefill->source === 'third_party' ? $prefill->email : '') }}">
                </div>
            </div>
            <div class="grid grid-2 gap-3 mb-2">
                <div>
                    <label style="font-size: 11px; font-weight: 700; color: #78350F; display: block; margin-bottom: 3px;">Partner Office Address</label>
                    <input type="text" name="travel_partner_address" class="form-control form-control-sm" placeholder="Shop / Office address, City, Pin..." value="{{ old('travel_partner_address') }}">
                </div>
                <div>
                    <label style="font-size: 11px; font-weight: 700; color: #78350F; display: block; margin-bottom: 3px;">Partner Commission / Payout (₹)</label>
                    <input type="number" step="0.01" name="travel_partner_commission" class="form-control form-control-sm" placeholder="e.g. 500.00" value="{{ old('travel_partner_commission') }}">
                </div>
            </div>
            <div>
                <label style="font-size: 11px; font-weight: 700; color: #78350F; display: block; margin-bottom: 3px;">Partner Reference Notes / Special Agreement</label>
                <input type="text" name="travel_partner_notes" class="form-control form-control-sm" placeholder="e.g. Reference Voucher #MMT-8921, Payout after trip completion" value="{{ old('travel_partner_notes') }}">
            </div>
        </div>
    </div>

    <div class="grid grid-3 gap-4">
        <!-- Left 2 Cols: Customer & Trip Details -->
        <div style="grid-column: span 2;">
            <!-- Customer Selection Card -->
            <div class="card mb-4">
                <div class="d-flex justify-between align-center flex-wrap gap-2 mb-3">
                    <h3 style="font-size: 1.15rem; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
                        <x-icon name="user" :size="16" /> 2. Passenger & Customer Details
                    </h3>
                    <!-- Mode Switch Buttons -->
                    <div class="d-flex gap-1" style="background: var(--slate-100); padding: 4px; border-radius: 8px;">
                        <button type="button" id="modeExistingBtn" class="btn btn-sm" onclick="setCustomerMode('existing')" style="border-radius: 6px; font-weight: 700; font-size: 0.8rem; padding: 5px 14px; transition: all 0.2s;">
                            Existing Customer
                        </button>
                        <button type="button" id="modeNewBtn" class="btn btn-sm" onclick="setCustomerMode('new')" style="border-radius: 6px; font-weight: 700; font-size: 0.8rem; padding: 5px 14px; transition: all 0.2s;">
                            + New Customer
                        </button>
                    </div>
                </div>

                <input type="hidden" name="customer_mode" id="customer_mode" value="{{ old('customer_mode', (isset($prefill) || old('new_customer_name') || old('new_customer_phone')) ? 'new' : 'existing') }}">

                <!-- Existing Customer Mode -->
                <div id="existingCustomerSection">
                    <div class="form-group mb-3">
                        <div class="d-flex justify-between align-center mb-1">
                            <label for="customer_search_input" class="form-label mb-0" style="font-weight: 700;">
                                Select Registered Customer *
                            </label>
                            <span id="customerCountBadge" class="badge" style="background: #E2E8F0; color: #475569; font-size: 0.75rem; font-weight: 600; padding: 3px 8px; border-radius: 9999px;">
                                {{ count($customers) }} Registered
                            </span>
                        </div>

                        <!-- Search Input with Icon and Clear Button -->
                        <div style="position: relative;" class="customer-search-container mb-2">
                            <div style="position: relative; display: flex; align-items: center;">
                                <span style="position: absolute; left: 12px; color: var(--slate-400); pointer-events: none; display: flex; align-items: center;">
                                    <x-icon name="search" :size="16" />
                                </span>
                                <input type="text" 
                                       id="customer_search_input" 
                                       class="form-control" 
                                       placeholder="🔍 Type Name or Mobile number to search existing customer..." 
                                       autocomplete="off"
                                       style="padding-left: 38px; padding-right: 36px; height: 42px; border-radius: 8px; font-size: 0.875rem; border: 1.5px solid var(--slate-300);">
                                <button type="button" 
                                        id="clear_customer_search" 
                                        style="display: none; position: absolute; right: 10px; background: transparent; border: none; color: var(--slate-400); cursor: pointer; font-size: 16px; width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center;"
                                        title="Clear search">✕</button>
                            </div>

                            <!-- Live Floating Autocomplete Suggestions -->
                            <div id="customer_search_dropdown" 
                                 style="display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0; background: #ffffff; border: 1px solid var(--slate-200); border-radius: 8px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15), 0 8px 10px -6px rgba(0,0,0,0.1); max-height: 260px; overflow-y: auto; z-index: 1050;">
                            </div>
                        </div>

                        <!-- Selected Customer Confirmation Card -->
                        <div id="selected_customer_card" style="display: none; background: #F0FDF4; border: 1.5px solid #86EFAC; border-radius: 8px; padding: 10px 14px; margin-bottom: 10px;">
                            <div class="d-flex justify-between align-center">
                                <div class="d-flex align-center gap-2">
                                    <span style="background: #16A34A; color: white; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; flex-shrink: 0;">✓</span>
                                    <div>
                                        <div style="font-weight: 700; color: #166534; font-size: 0.875rem;" id="selected_customer_name_display"></div>
                                        <div style="font-size: 0.775rem; color: #15803D;" id="selected_customer_detail_display"></div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm" id="btn_change_customer" style="font-size: 0.75rem; padding: 3px 10px; background: #DCFCE7; border: 1px solid #86EFAC; color: #166534; border-radius: 6px; font-weight: 600;">
                                    Change
                                </button>
                            </div>
                        </div>

                        <!-- Standard Select Dropdown (filtered in real-time or selectable directly) -->
                        <div class="customer-select-wrapper">
                            <label for="customer_id" class="form-label" style="font-size: 0.75rem; color: var(--slate-500); margin-bottom: 3px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                Or pick directly from customer list:
                            </label>
                            <select name="customer_id" id="customer_id" class="form-select" style="border-radius: 8px; font-size: 0.875rem; height: 40px;">
                                <option value="">-- Choose Existing Customer --</option>
                                <option value="new" style="font-weight: 700; color: var(--primary);">+ Add New Customer Instead</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" 
                                            data-name="{{ $c->name }}" 
                                            data-phone="{{ $c->phone }}" 
                                            data-email="{{ $c->email ?? '' }}"
                                            {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }} ({{ $c->phone }}{{ $c->email ? ' | ' . $c->email : '' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <p style="font-size: 0.8rem; color: var(--slate-500); margin: 0;">
                        Can't find the customer? Click <strong>+ New Customer</strong> above to enter customer name & phone directly.
                    </p>
                </div>

                <!-- New Customer Mode -->
                <div id="newCustomerSection" style="display: none; background: #FFFBEB; border: 1px solid #FCD34D; border-radius: 8px; padding: 1.25rem;">
                    <div class="d-flex align-center gap-2 mb-3">
                        <x-icon name="user-plus" :size="18" style="color: #92400E;" />
                        <div>
                            <strong style="color: #92400E; font-size: 0.95rem;">Enter New Passenger / Customer Information</strong>
                            <p style="font-size: 0.8rem; color: #B45309; margin: 0;">A customer account will be created automatically and linked to this booking.</p>
                        </div>
                    </div>

                    <div class="grid grid-2 gap-3 mb-3">
                        <div class="form-group">
                            <label for="new_customer_name" class="form-label">Full Name *</label>
                            <input type="text" name="new_customer_name" id="new_customer_name" class="form-control" placeholder="e.g. Rajesh Sharma" value="{{ old('new_customer_name', $prefill->name ?? '') }}">
                        </div>

                        <div class="form-group">
                            <label for="new_customer_phone" class="form-label">Mobile / WhatsApp Number *</label>
                            <input type="tel" name="new_customer_phone" id="new_customer_phone" class="form-control" placeholder="e.g. 9876543210" value="{{ old('new_customer_phone', $prefill->phone ?? '') }}">
                        </div>
                    </div>

                    <div class="grid grid-3 gap-3">
                        <div class="form-group">
                            <label for="new_customer_email" class="form-label">Email Address (Optional)</label>
                            <input type="email" name="new_customer_email" id="new_customer_email" class="form-control" placeholder="e.g. customer@gmail.com" value="{{ old('new_customer_email', $prefill->email ?? '') }}">
                        </div>

                        <div class="form-group">
                            <label for="new_customer_city" class="form-label">City / Area</label>
                            <input type="text" name="new_customer_city" id="new_customer_city" class="form-control" placeholder="e.g. Bilaspur / Raipur" value="{{ old('new_customer_city', 'Bilaspur') }}">
                        </div>

                        <div class="form-group">
                            <label for="new_customer_address" class="form-label">Pickup Address / Landmark</label>
                            <input type="text" name="new_customer_address" id="new_customer_address" class="form-control" placeholder="e.g. Near Railway Station" value="{{ old('new_customer_address') }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Route & Schedule Card -->
            <div class="card mb-4">
                <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 8px;">
                    <x-icon name="map-pin" :size="16" /> 3. Route & Schedule Information
                </h3>

                <div class="grid grid-2 gap-3 mb-3">
                    <div class="form-group">
                        <label for="rate_category" class="form-label" style="font-weight: 700;">Trip Type / Service *</label>
                        <select name="rate_category" id="rate_category" class="form-select" onchange="onTripTypeChange()" required>
                            <option value="outstation" {{ old('rate_category', isset($prefill) && $prefill->booking_type === 'Round-Trip' ? 'round_trip' : 'outstation') === 'outstation' ? 'selected' : '' }}>
                                🚗 Oneway trip
                            </option>
                            <option value="round_trip" {{ old('rate_category') === 'round_trip' ? 'selected' : '' }}>
                                🔄 Round trip
                            </option>
                            <option value="tour_package" {{ old('rate_category') === 'tour_package' ? 'selected' : '' }}>
                                🌴 Tour Package
                            </option>
                            <option value="ambulance" {{ old('rate_category') === 'ambulance' ? 'selected' : '' }}>
                                🚑 Ambulance
                            </option>
                            <option value="airport" {{ old('rate_category') === 'airport' ? 'selected' : '' }}>
                                ✈️ Quick Airport Booking
                            </option>
                            <option value="local_8h_80km" {{ old('rate_category') === 'local_8h_80km' ? 'selected' : '' }}>
                                ⏱️ Local 8 Hours / 80 KM
                            </option>
                            <option value="local_4h_40km" {{ old('rate_category') === 'local_4h_40km' ? 'selected' : '' }}>
                                ⏱️ Local 4 Hours / 40 KM
                            </option>
                        </select>
                    </div>

                    <div class="form-group" id="tourPackageGroup" style="display: {{ old('rate_category') === 'tour_package' ? 'block' : 'none' }};">
                        <label for="tour_package_id" class="form-label" style="font-weight: 700; color: #166534;">Linked Tour Package</label>
                        <select name="tour_package_id" id="tour_package_id" class="form-select" onchange="onTourPackageChange(this)">
                            <option value="">-- Choose Tour Package --</option>
                            @if(isset($tourPackages))
                                @foreach($tourPackages as $tp)
                                    <option value="{{ $tp->id }}" 
                                            data-name="{{ $tp->name }}"
                                            data-route="{{ $tp->route }}" 
                                            data-duration="{{ $tp->duration }}" 
                                            data-days="{{ $tp->duration_days }}" 
                                            data-sedan="{{ $tp->sedan_price }}" 
                                            data-ertiga="{{ $tp->ertiga_price }}" 
                                            data-innova="{{ $tp->innova_price }}"
                                            {{ old('tour_package_id') == $tp->id ? 'selected' : '' }}>
                                        {{ $tp->name }} ({{ $tp->duration_days }} Days{{ $tp->sedan_price ? ' · from ₹'.number_format($tp->sedan_price, 0) : '' }})
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="form-group" id="ambulanceNoticeGroup" style="display: {{ old('rate_category') === 'ambulance' ? 'block' : 'none' }};">
                        <label class="form-label" style="font-weight: 700; color: #991B1B;">Emergency Ambulance Service</label>
                        <div style="background: #FEF2F2; border: 1px solid #FCA5A5; color: #991B1B; padding: 7px 12px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                            🚑 24/7 Patient Emergency Cab (BLS / Oxygen Support available).
                        </div>
                    </div>
                </div>

                <!-- Google Maps & Routing Coordinate Hidden Fields -->
                <input type="hidden" name="pickup_latitude" id="adminPickupLat" value="{{ old('pickup_latitude') }}">
                <input type="hidden" name="pickup_longitude" id="adminPickupLng" value="{{ old('pickup_longitude') }}">
                <input type="hidden" name="pickup_place_id" id="adminPickupPlaceId" value="{{ old('pickup_place_id') }}">

                <input type="hidden" name="via_latitude" id="adminViaLat" value="{{ old('via_latitude') }}">
                <input type="hidden" name="via_longitude" id="adminViaLng" value="{{ old('via_longitude') }}">
                <input type="hidden" name="via_place_id" id="adminViaPlaceId" value="{{ old('via_place_id') }}">

                <input type="hidden" name="destination_latitude" id="adminDestinationLat" value="{{ old('destination_latitude') }}">
                <input type="hidden" name="destination_longitude" id="adminDestinationLng" value="{{ old('destination_longitude') }}">
                <input type="hidden" name="destination_place_id" id="adminDestinationPlaceId" value="{{ old('destination_place_id') }}">

                <input type="hidden" name="route_distance_km" id="adminRouteDistanceKm" value="{{ old('route_distance_km') }}">
                <input type="hidden" name="route_duration_text" id="adminRouteDurationText" value="{{ old('route_duration_text') }}">
                <input type="hidden" name="route_duration_minutes" id="adminRouteDurationMinutes" value="{{ old('route_duration_minutes') }}">
                <input type="hidden" name="route_geometry" id="adminRouteGeometry" value="{{ old('route_geometry') }}">

                <div class="grid grid-3 gap-3 mb-3">
                    <div class="form-group">
                        <div class="d-flex justify-between align-center mb-1">
                            <label for="pickup_location" class="form-label" style="margin: 0;">Pickup Location *</label>
                            <button type="button" class="btn btn-xs btn-outline btn-admin-select-map" data-target="pickup" style="font-size: 11px; padding: 2px 7px; border-radius: 4px; color: #2563EB; border-color: #BFDBFE; background: #EFF6FF; display: inline-flex; align-items: center; gap: 4px;">
                                <x-icon name="map-pin" :size="11" /> Pick Map
                            </button>
                        </div>
                        <input type="text" name="pickup_location" id="pickup_location" class="form-control" list="pickupLocationsList" value="{{ old('pickup_location', $prefill->pickup_location ?? 'Bilaspur') }}" required>
                        <datalist id="pickupLocationsList">
                            @foreach($locations as $location)<option value="{{ $location }}">@endforeach
                        </datalist>
                    </div>

                    <div class="form-group">
                        <div class="d-flex justify-between align-center mb-1">
                            <label for="via_location" class="form-label" style="margin: 0;">Via Location (Round-Trip)</label>
                            <button type="button" class="btn btn-xs btn-outline btn-admin-select-map" data-target="via" style="font-size: 11px; padding: 2px 7px; border-radius: 4px; color: #2563EB; border-color: #BFDBFE; background: #EFF6FF; display: inline-flex; align-items: center; gap: 4px;">
                                <x-icon name="map-pin" :size="11" /> Pick Map
                            </button>
                        </div>
                        <input type="text" name="via_location" id="via_location" class="form-control" placeholder="e.g. Amarkantak" value="{{ old('via_location', $prefill->via_location ?? '') }}">
                    </div>

                    <div class="form-group">
                        <div class="d-flex justify-between align-center mb-1">
                            <label for="destination" class="form-label" style="margin: 0;">Drop Destination *</label>
                            <button type="button" class="btn btn-xs btn-outline btn-admin-select-map" data-target="destination" style="font-size: 11px; padding: 2px 7px; border-radius: 4px; color: #2563EB; border-color: #BFDBFE; background: #EFF6FF; display: inline-flex; align-items: center; gap: 4px;">
                                <x-icon name="map-pin" :size="11" /> Pick Map
                            </button>
                        </div>
                        <input type="text" name="destination" id="destination" class="form-control" list="destinationLocationsList" value="{{ old('destination', $prefill->destination ?? 'Raipur') }}" required>
                        <datalist id="destinationLocationsList">
                            @foreach($locations as $location)<option value="{{ $location }}">@endforeach
                        </datalist>
                    </div>
                </div>

                <!-- Admin Route & Distance Preview Container -->
                <div id="adminRoutePreviewBox" style="display: none; margin-bottom: 1rem; border: 1px solid #BFDBFE; border-radius: 8px; overflow: hidden; background: #EFF6FF;">
                    <div style="padding: 0.6rem 0.85rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem; border-bottom: 1px solid #BFDBFE; background: #DBEAFE;">
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <x-icon name="navigation" :size="14" style="color: #1E40AF;" />
                            <strong style="color: #1E40AF; font-size: 12.5px;">Google Road Route & Driving Distance</strong>
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span id="adminRouteDistDisplay" style="font-weight: 800; font-size: 13px; color: #1D4ED8; background: #fff; padding: 2px 7px; border-radius: 4px; border: 1px solid #BFDBFE;">-- KM</span>
                            <span id="adminRouteDurDisplay" style="font-size: 12px; color: #1E40AF; background: #fff; padding: 2px 7px; border-radius: 4px; border: 1px solid #BFDBFE;">--</span>
                        </div>
                    </div>
                    <div style="height: 200px; width: 100%;">
                        <div id="adminRouteMapCanvas" style="width: 100%; height: 100%;"></div>
                    </div>
                    <div id="adminRouteLegsList" style="padding: 0.4rem 0.85rem; font-size: 11px; color: #1E40AF; display: flex; gap: 6px; flex-wrap: wrap;"></div>
                </div>

                <div class="grid grid-3 gap-3 mb-3">
                    <div class="form-group" id="vehicleCategoryGroup">
                        <div class="d-flex justify-between align-center mb-1">
                            <label for="vehicle_category" class="form-label mb-0" id="vehicleCategoryLabel">Vehicle Category *</label>
                            <button type="button" onclick="openQuickCategoryModal()" style="background: none; border: none; color: #2563EB; font-weight: 700; font-size: 11px; cursor: pointer; text-decoration: underline; padding: 0;">
                                + Add Category
                            </button>
                        </div>
                        <select name="vehicle_category" id="vehicle_category" class="form-select" onchange="onAdminBookingCategoryChange()" required>
                            <option value="">Select vehicle category</option>
                            <option value="new_category" style="font-weight: 800; color: #2563EB; background: #EFF6FF;">➕ + Add New Category</option>
                            @php
                                $ambNames = ['Ambulance with Body Freezer', 'Ambulance with Ventilator', 'ICU Ambulance', 'Dead Body Transfer'];
                            @endphp
                            @if(isset($activeVehicleCategories))
                                @foreach($activeVehicleCategories as $cat)
                                    @if(!in_array($cat->name, $ambNames))
                                        <option value="{{ $cat->name }}" data-id="{{ $cat->id }}" {{ old('vehicle_category') === $cat->name ? 'selected' : '' }}>
                                            {{ $cat->name }} ({{ $cat->is_capacity_configurable ? 'Configurable '.$cat->min_seating_capacity.'-'.$cat->max_seating_capacity.' Seats' : $cat->default_seating_capacity.' Seats' }})
                                        </option>
                                    @endif
                                @endforeach
                            @endif
                            @foreach($vehicleCategories as $category)
                                @if(!in_array($category, $ambNames) && (!isset($activeVehicleCategories) || !$activeVehicleCategories->contains('name', $category)))
                                    <option value="{{ $category }}" {{ old('vehicle_category') === $category ? 'selected' : '' }}>{{ $category }}</option>
                                @endif
                            @endforeach
                        </select>
                        <input type="hidden" name="vehicle_category_id" id="admin_vehicle_category_id" value="{{ old('vehicle_category_id') }}">
                    </div>

                    <div class="form-group" id="vehicleModelGroup">
                        <label for="vehicle_model_id" class="form-label">Vehicle Model Preference (Optional)</label>
                        <select name="vehicle_model_id" id="vehicle_model_id" class="form-select" onchange="onAdminBookingModelChange()">
                            <option value="">-- Any / All Models in Category --</option>
                        </select>
                        <input type="hidden" name="vehicle_model" id="admin_vehicle_model" value="{{ old('vehicle_model') }}">
                    </div>

                    <div class="form-group" id="estimatedKmGroup" style="display: none;">
                        <label for="estimated_km" class="form-label">Estimated round-trip distance (KM) *</label>
                        <input type="number" min="0.01" step="0.1" name="estimated_km" id="estimated_km" class="form-control" value="{{ old('estimated_km') }}">
                    </div>
                </div>

                <div class="grid grid-3 gap-3 mb-3">
                    <div class="form-group">
                        <label for="travel_date" class="form-label">Travel Date *</label>
                        <input type="date" name="travel_date" id="travel_date" class="form-control" value="{{ old('travel_date', date('Y-m-d')) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="travel_time" class="form-label">Pickup Time *</label>
                        <input type="time" name="travel_time" id="travel_time" class="form-control" value="{{ old('travel_time', '09:00') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="return_date" class="form-label">Return Date (If Round Trip)</label>
                        <input type="date" name="return_date" id="return_date" class="form-control" value="{{ old('return_date') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="notes" class="form-label">Dispatcher Notes / Passenger Requests</label>
                    <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Luggage details, flight timings, special route requests...">{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- Fleet & Driver Assignment Card -->
            <div class="card mb-4" id="fleetAssignmentCard">
                <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;">
                    <x-icon name="car" :size="16" /> Immediate Fleet & Chauffeur Assignment (Optional)
                </h3>

                <!-- Vehicle Source Option (Own Vehicle vs Third Party Vehicle) -->
                <div class="mb-3" style="background: #F8FAFC; border: 1px solid var(--slate-200); border-radius: 8px; padding: 0.85rem 1rem;">
                    <label class="form-label" style="font-size: 0.85rem; font-weight: 800; color: var(--dark-900); display: block; margin-bottom: 0.5rem;">
                        Select Vehicle Source / Type *
                    </label>
                    <div class="d-flex gap-3 flex-wrap">
                        <label style="border: 2px solid {{ old('vehicle_assignment_source', 'own') === 'own' ? '#2563EB' : 'var(--slate-200)' }}; background: {{ old('vehicle_assignment_source', 'own') === 'own' ? '#EFF6FF' : '#fff' }}; padding: 0.75rem 1rem; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 10px; flex: 1; min-width: 220px;" id="lblVehicleSourceOwn">
                            <input type="radio" name="vehicle_assignment_source" value="own" {{ old('vehicle_assignment_source', 'own') === 'own' ? 'checked' : '' }} onchange="toggleVehicleSource('own')">
                            <div>
                                <strong style="color: #1E40AF; display: block; font-size: 13.5px;">🏢 Own Vehicle (Company Fleet)</strong>
                                <span style="font-size: 11px; color: var(--slate-500);">Assign from Vaishnavi Tours owned fleet & regular drivers</span>
                            </div>
                        </label>

                        <label style="border: 2px solid {{ old('vehicle_assignment_source') === 'third_party' ? '#D97706' : 'var(--slate-200)' }}; background: {{ old('vehicle_assignment_source') === 'third_party' ? '#FFFBEB' : '#fff' }}; padding: 0.75rem 1rem; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 10px; flex: 1; min-width: 220px;" id="lblVehicleSourceThirdParty">
                            <input type="radio" name="vehicle_assignment_source" value="third_party" {{ old('vehicle_assignment_source') === 'third_party' ? 'checked' : '' }} onchange="toggleVehicleSource('third_party')">
                            <div>
                                <strong style="color: #B45309; display: block; font-size: 13.5px;">🤝 Third Party Vehicle (Travel Partner)</strong>
                                <span style="font-size: 11px; color: var(--slate-500);">Travel partner vehicle — Only vehicle number required, no documents needed</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- SECTION A: Own Vehicle Assignment -->
                <div id="ownVehicleSection" style="display: {{ old('vehicle_assignment_source', 'own') === 'own' ? 'block' : 'none' }};">
                    <div class="grid grid-2 gap-3">
                        <div class="form-group">
                            <div class="d-flex justify-between align-center mb-1">
                                <label for="vehicle_id" class="form-label mb-0">Assign Fleet Vehicle</label>
                                <button type="button" onclick="openQuickVehicleModal()" style="background: none; border: none; color: #2563EB; font-weight: 700; font-size: 11px; cursor: pointer; text-decoration: underline; padding: 0;">
                                    + Add New Vehicle
                                </button>
                            </div>
                            <select name="vehicle_id" id="vehicle_id" class="form-select" onchange="onAdminVehicleSelectChange(this)">
                                <option value="">-- Assign Later --</option>
                                <option value="new_vehicle" style="font-weight: 800; color: #2563EB; background: #EFF6FF;">➕ + Add New Vehicle</option>
                                @foreach($vehicles->where('is_third_party', false) as $veh)
                                    <option value="{{ $veh->id }}" {{ old('vehicle_id') == $veh->id ? 'selected' : '' }}>
                                        {{ $veh->name }} - {{ $veh->registration_number }} ({{ $veh->vehicle_type }} • {{ $veh->status }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="driver_id" class="form-label">Assign Chauffeur / Driver</label>
                            <select name="driver_id" id="driver_id" class="form-select">
                                <option value="">-- Assign Later --</option>
                                @foreach($drivers as $drv)
                                    <option value="{{ $drv->id }}" {{ old('driver_id') == $drv->id ? 'selected' : '' }}>
                                        {{ $drv->name }} ({{ $drv->mobile }} • {{ $drv->status }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- SECTION B: Third Party Vehicle Assignment (Travel Partner) -->
                <div id="thirdPartyVehicleSection" style="display: {{ old('vehicle_assignment_source') === 'third_party' ? 'block' : 'none' }}; background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 8px; padding: 1.15rem;">
                    <div class="d-flex align-center justify-between mb-3 flex-wrap gap-2">
                        <div class="d-flex align-center gap-2">
                            <x-icon name="truck" :size="18" style="color: #92400E;" />
                            <div>
                                <strong style="color: #92400E; font-size: 0.95rem;">Travel Partner Vehicle Assignment</strong>
                                <p style="font-size: 0.8rem; color: #B45309; margin: 0;">Vehicle dispatched from travel partner. Documents are not required.</p>
                            </div>
                        </div>
                        <span class="badge" style="background: #FEF3C7; color: #92400E; border: 1px solid #FCD34D; font-size: 11px; font-weight: 700;">
                            ✓ Only Vehicle Number Mandatory
                        </span>
                    </div>

                    @if($vehicles->where('is_third_party', true)->count() > 0)
                        <div class="form-group mb-3">
                            <label for="existing_tp_vehicle_id" class="form-label" style="font-size: 0.8rem; font-weight: 700; color: #78350F;">
                                Select Existing Partner Vehicle (Or enter new details below)
                            </label>
                            <select name="existing_tp_vehicle_id" id="existing_tp_vehicle_id" class="form-select form-select-sm" onchange="onExistingTpVehicleSelect(this)">
                                <option value="">-- Enter New Third-Party Vehicle Details Below --</option>
                                @foreach($vehicles->where('is_third_party', true) as $tpVeh)
                                    <option value="{{ $tpVeh->id }}" 
                                            data-number="{{ $tpVeh->registration_number }}" 
                                            data-name="{{ $tpVeh->name }}" 
                                            data-vendor="{{ $tpVeh->vendor_name }}" 
                                            data-phone="{{ $tpVeh->vendor_phone }}"
                                            {{ old('existing_tp_vehicle_id') == $tpVeh->id ? 'selected' : '' }}>
                                        {{ $tpVeh->registration_number }} — {{ $tpVeh->name }} (Partner: {{ $tpVeh->vendor_name ?: 'Partner' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="grid grid-2 gap-3 mb-3">
                        <div class="form-group">
                            <label for="third_party_vehicle_number" class="form-label" style="font-size: 0.825rem; font-weight: 700; color: #78350F;">
                                Third Party Vehicle Number * <span class="text-danger">(Mandatory)</span>
                            </label>
                            <input type="text" name="third_party_vehicle_number" id="third_party_vehicle_number" class="form-control form-control-sm" placeholder="e.g. CG 04 HA 4521" value="{{ old('third_party_vehicle_number') }}" style="font-weight: 700; text-transform: uppercase;">
                            <small style="color: #92400E; font-size: 11px;">Vehicle number of partner's car. No documents required.</small>
                        </div>

                        <div class="form-group">
                            <label for="third_party_vehicle_name" class="form-label" style="font-size: 0.825rem; font-weight: 700; color: #78350F;">
                                Vehicle Model / Name (Optional)
                            </label>
                            <input type="text" name="third_party_vehicle_name" id="third_party_vehicle_name" class="form-control form-control-sm" placeholder="e.g. Maruti Dzire White / Ertiga" value="{{ old('third_party_vehicle_name') }}">
                        </div>
                    </div>

                    <div class="grid grid-3 gap-3 mb-2">
                        <div class="form-group">
                            <label for="third_party_vendor_name" class="form-label" style="font-size: 0.825rem; font-weight: 700; color: #78350F;">
                                Travel Partner / Agency Name
                            </label>
                            <input type="text" name="third_party_vendor_name" id="third_party_vendor_name" class="form-control form-control-sm" placeholder="e.g. Maa Sharda Travels / MMT Partner" value="{{ old('third_party_vendor_name') }}">
                        </div>

                        <div class="form-group">
                            <label for="third_party_driver_name" class="form-label" style="font-size: 0.825rem; font-weight: 700; color: #78350F;">
                                Partner Driver Name (Optional)
                            </label>
                            <input type="text" name="third_party_driver_name" id="third_party_driver_name" class="form-control form-control-sm" placeholder="e.g. Suresh Kumar" value="{{ old('third_party_driver_name') }}">
                        </div>

                        <div class="form-group">
                            <label for="third_party_driver_phone" class="form-label" style="font-size: 0.825rem; font-weight: 700; color: #78350F;">
                                Partner Driver Phone (Optional)
                            </label>
                            <input type="tel" name="third_party_driver_phone" id="third_party_driver_phone" class="form-control form-control-sm" placeholder="e.g. 9827100000" value="{{ old('third_party_driver_phone') }}">
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label for="third_party_agreed_amount" class="form-label" style="font-size: 0.825rem; font-weight: 700; color: #78350F;">
                            Partner Agreed Hire Cost (₹) (Optional)
                        </label>
                        <input type="number" step="0.01" name="third_party_agreed_amount" id="third_party_agreed_amount" class="form-control form-control-sm" placeholder="e.g. 2200.00" value="{{ old('third_party_agreed_amount') }}" style="max-width: 300px;">
                    </div>
                </div>
            </div>
        </div>

        <!-- Right 1 Col: Financials & Status -->
        <div>
            <!-- Status & Billing Card -->
            <div class="card mb-4">
                <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 8px;">
                    <x-icon name="credit-card" :size="16" /> Fare & Booking Status
                </h3>

                <div class="form-group mb-3">
                    <label for="booking_status" class="form-label">Initial Booking Status *</label>
                    <select name="booking_status" id="booking_status" class="form-select" required>
                        @foreach(['Pending', 'Confirmed', 'Vehicle Assigned', 'Driver Assigned'] as $st)
                            <option value="{{ $st }}" {{ old('booking_status', 'Confirmed') == $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-3">
                    <div class="d-flex justify-between align-center mb-1">
                        <label for="total_amount" class="form-label" style="margin: 0;">Configured Trip Fare (₹) *</label>
                        <label style="font-size: 0.75rem; color: var(--slate-600); cursor: pointer; user-select: none;">
                            <input type="checkbox" id="customFareToggle" onchange="toggleCustomFare(this)"> Custom Fare
                        </label>
                    </div>
                    <input type="number" step="0.01" name="total_amount" id="total_amount" class="form-control" value="{{ old('total_amount', '0.00') }}" readonly required>
                    <input type="hidden" name="is_custom_fare" id="is_custom_fare" value="{{ old('is_custom_fare', '0') }}">
                    <div id="fareDetails" style="font-size: 0.8rem; color: var(--slate-600); margin-top: 0.35rem;" aria-live="polite">Select route and vehicle category.</div>
                </div>

                <div class="form-group mb-3">
                    <label for="paid_amount" class="form-label">Advance / Paid Amount (₹)</label>
                    <input type="number" step="0.01" name="paid_amount" id="paid_amount" class="form-control" value="{{ old('paid_amount', '500.00') }}">
                </div>

                <div class="form-group mb-4">
                    <label for="payment_status" class="form-label">Payment Status *</label>
                    <select name="payment_status" id="payment_status" class="form-select" required>
                        @foreach(['Pending', 'Partial', 'Paid'] as $pst)
                            <option value="{{ $pst }}" {{ old('payment_status', 'Partial') == $pst ? 'selected' : '' }}>{{ $pst }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-block" style="text-align: center; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 6px;">
                    <x-icon name="check-circle" :size="16" /> Confirm & Create Booking
                </button>
            </div>
        </div>
    </div>
</form>

<!-- Quick Add Fleet Vehicle Modal -->
<div id="quickVehicleModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.55); z-index: 10000; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #fff; border-radius: 8px; width: 100%; max-width: 500px; padding: 1.5rem; box-shadow: 0 10px 25px rgba(0,0,0,0.25); max-height: 90vh; overflow-y: auto;">
        <div class="d-flex justify-between align-center mb-3">
            <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: var(--dark-900);">➕ Quick Add Fleet Vehicle</h3>
            <button type="button" onclick="closeQuickVehicleModal()" style="background: none; border: none; font-size: 18px; cursor: pointer; color: var(--slate-400);">✕</button>
        </div>
        <form id="quickVehicleForm" onsubmit="submitQuickVehicle(event)">
            <div class="mb-3">
                <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Vehicle Name & Make *</label>
                <input type="text" id="qv_name" class="form-control form-control-sm" placeholder="e.g. Maruti Suzuki Dzire VXI" required>
            </div>
            <div class="grid grid-2 gap-2 mb-3">
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">RTO Registration Number *</label>
                    <input type="text" id="qv_reg" class="form-control form-control-sm" placeholder="e.g. CG 10 AB 4501" required>
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Vehicle Category *</label>
                    <select id="qv_category" class="form-select form-select-sm" required>
                        @foreach($activeVehicleCategories ?? [] as $cat)
                            <option value="{{ $cat->id }}" data-name="{{ $cat->name }}" data-capacity="{{ $cat->default_seating_capacity }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-2 gap-2 mb-3">
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Seating Capacity *</label>
                    <input type="number" id="qv_capacity" class="form-control form-control-sm" value="4" min="1" max="70" required>
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Air Conditioning</label>
                    <select id="qv_ac" class="form-select form-select-sm">
                        <option value="AC" selected>AC</option>
                        <option value="Non-AC">Non-AC</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Ownership</label>
                <select id="qv_ownership" class="form-select form-select-sm">
                    <option value="owned" selected>Own Fleet Vehicle</option>
                    <option value="third_party">Third-Party Travel Partner Cab</option>
                </select>
            </div>
            <div id="qv_error" style="display: none; color: #dc2626; font-size: 12px; margin-bottom: 10px;"></div>
            <div class="d-flex justify-end gap-2">
                <button type="button" onclick="closeQuickVehicleModal()" class="btn btn-outline btn-sm">Cancel</button>
                <button type="submit" id="qv_submit_btn" class="btn btn-primary btn-sm">Register & Assign Vehicle</button>
            </div>
        </form>
    </div>
</div>

<!-- Quick Add Category Modal -->
<div id="quickCategoryModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.55); z-index: 10000; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #fff; border-radius: 8px; width: 100%; max-width: 480px; padding: 1.5rem; box-shadow: 0 10px 25px rgba(0,0,0,0.25);">
        <div class="d-flex justify-between align-center mb-3">
            <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: var(--dark-900);">➕ Add New Vehicle Category</h3>
            <button type="button" onclick="closeQuickCategoryModal()" style="background: none; border: none; font-size: 18px; cursor: pointer; color: var(--slate-400);">✕</button>
        </div>
        <form id="quickCategoryForm" onsubmit="submitQuickCategory(event)">
            <div class="mb-3">
                <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Category Name *</label>
                <input type="text" id="qc_name" class="form-control form-control-sm" placeholder="e.g. Luxury SUV, Mini Bus" required>
            </div>
            <div class="grid grid-2 gap-2 mb-3">
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Default Seats *</label>
                    <input type="number" id="qc_default_capacity" class="form-control form-control-sm" value="4" min="1" max="70" required>
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Max Seats (if range)</label>
                    <input type="number" id="qc_max_capacity" class="form-control form-control-sm" value="4" min="1" max="70">
                </div>
            </div>
            <div class="mb-3">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 12px; font-weight: 600;">
                    <input type="checkbox" id="qc_configurable" value="1">
                    Allow Configurable Seating Capacity
                </label>
            </div>
            <div id="qc_error" style="display: none; color: #dc2626; font-size: 12px; margin-bottom: 10px;"></div>
            <div class="d-flex justify-end gap-2">
                <button type="button" onclick="closeQuickCategoryModal()" class="btn btn-outline btn-sm">Cancel</button>
                <button type="submit" id="qc_submit_btn" class="btn btn-primary btn-sm">Save & Select Category</button>
            </div>
        </form>
    </div>
</div>

<script>
    const rateOptions = @json($rateOptions);
    const activeVehicleCategoriesData = @json($activeVehicleCategories ?? []);
    const corporateClientsData = @json($corporateClients ?? []);
    let lastValidVehCategory = '';
    let lastValidVehicle = '';

    function handleCorporateSearch(q) {
        q = (q || '').trim().toLowerCase();
        const dropdown = document.getElementById('corporate_search_dropdown');
        const clearBtn = document.getElementById('btn_clear_corp_search');
        if (!dropdown) return;

        if (!q) {
            dropdown.style.display = 'none';
            if (clearBtn) clearBtn.style.display = 'none';
            return;
        }

        if (clearBtn) clearBtn.style.display = 'flex';

        const filtered = corporateClientsData.filter(c => {
            return (c.company_name && c.company_name.toLowerCase().includes(q)) ||
                   (c.contact_person && c.contact_person.toLowerCase().includes(q)) ||
                   (c.phone && c.phone.includes(q)) ||
                   (c.gstin && c.gstin.toLowerCase().includes(q));
        });

        let html = '';
        html += `<div onclick="setNewCorporateClientMode('${q.replace(/'/g, "\\'")}')" style="padding: 9px 12px; border-bottom: 1.5px solid #DDD6FE; background: #F5F3FF; cursor: pointer; font-weight: 700; color: #6D28D9; font-size: 12px; display: flex; align-items: center; gap: 6px;">
            <span>➕</span> <span>+ Add New Corporate Client: <strong>"${q}"</strong></span>
        </div>`;

        if (filtered.length === 0) {
            html += `<div style="padding: 10px 12px; font-size: 12px; color: var(--slate-500);">No saved corporate accounts matching "${q}". Click above to add as new.</div>`;
        } else {
            filtered.forEach(c => {
                const origIdx = corporateClientsData.indexOf(c);
                html += `<div onclick="selectCorporateClient(${origIdx})" style="padding: 8px 12px; border-bottom: 1px solid var(--slate-100); cursor: pointer; font-size: 12px; transition: background 0.15s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                    <div style="font-weight: 700; color: #1E293B;">${c.company_name}</div>
                    <div style="font-size: 11px; color: var(--slate-500); display: flex; gap: 8px; flex-wrap: wrap;">
                        <span>👤 ${c.contact_person || 'No contact'}</span>
                        ${c.phone ? `<span>📞 ${c.phone}</span>` : ''}
                        ${c.gstin ? `<span>🏛️ GST: ${c.gstin}</span>` : ''}
                    </div>
                </div>`;
            });
        }

        dropdown.innerHTML = html;
        dropdown.style.display = 'block';
    }

    function clearCorporateSearch() {
        const inp = document.getElementById('corporate_search_input');
        if (inp) inp.value = '';
        const dropdown = document.getElementById('corporate_search_dropdown');
        if (dropdown) dropdown.style.display = 'none';
        const clearBtn = document.getElementById('btn_clear_corp_search');
        if (clearBtn) clearBtn.style.display = 'none';
    }

    function onCorporateDropdownChange(sel) {
        if (sel.value === 'new') {
            setNewCorporateClientMode();
        } else if (sel.value !== '') {
            selectCorporateClient(parseInt(sel.value));
        }
    }

    function selectCorporateClient(idx) {
        const c = corporateClientsData[idx];
        if (!c) return;

        document.getElementById('corp_company_name').value = c.company_name || '';
        document.getElementById('corp_contact_person').value = c.contact_person || '';
        document.getElementById('corp_gstin').value = c.gstin || '';
        document.getElementById('corp_billing_address').value = c.billing_address || '';

        const sel = document.getElementById('corporate_client_select');
        if (sel) sel.value = idx;

        const card = document.getElementById('corporate_selected_card');
        const text = document.getElementById('corporate_selected_text');
        if (card && text) {
            text.textContent = `✓ Selected: ${c.company_name} (Contact: ${c.contact_person || 'N/A'})`;
            card.style.display = 'block';
            card.style.background = '#ECFDF5';
            card.style.borderColor = '#A7F3D0';
            card.style.color = '#065F46';
        }

        const dropdown = document.getElementById('corporate_search_dropdown');
        if (dropdown) dropdown.style.display = 'none';

        // Prepopulate passenger details if blank
        const newName = document.getElementById('new_customer_name');
        const newPhone = document.getElementById('new_customer_phone');
        if (newName && !newName.value && c.contact_person) {
            newName.value = c.contact_person;
        }
        if (newPhone && !newPhone.value && c.phone) {
            newPhone.value = c.phone;
        }
    }

    function setNewCorporateClientMode(prefillName = '') {
        document.getElementById('corp_company_name').value = prefillName;
        document.getElementById('corp_contact_person').value = '';
        document.getElementById('corp_gstin').value = '';
        document.getElementById('corp_billing_address').value = '';

        const sel = document.getElementById('corporate_client_select');
        if (sel) sel.value = 'new';

        const card = document.getElementById('corporate_selected_card');
        const text = document.getElementById('corporate_selected_text');
        if (card && text) {
            text.textContent = `➕ New Corporate Client (Details will be saved upon booking confirmation)`;
            card.style.display = 'block';
            card.style.background = '#EFF6FF';
            card.style.borderColor = '#BFDBFE';
            card.style.color = '#1E40AF';
        }

        clearCorporateSearch();
        document.getElementById('corp_company_name').focus();
    }

    // Quick Add Vehicle functions
    function openQuickVehicleModal() {
        const m = document.getElementById('quickVehicleModal');
        if (m) {
            m.style.display = 'flex';
            document.getElementById('qv_name').focus();
        }
    }

    function closeQuickVehicleModal() {
        const m = document.getElementById('quickVehicleModal');
        if (m) m.style.display = 'none';
        const vSel = document.getElementById('vehicle_id');
        if (vSel && vSel.value === 'new_vehicle') {
            vSel.value = lastValidVehicle;
        }
    }

    function onAdminVehicleSelectChange(sel) {
        if (sel.value === 'new_vehicle') {
            openQuickVehicleModal();
        } else {
            lastValidVehicle = sel.value;
        }
    }

    async function submitQuickVehicle(e) {
        e.preventDefault();
        const btn = document.getElementById('qv_submit_btn');
        const err = document.getElementById('qv_error');
        btn.disabled = true;
        btn.textContent = 'Saving...';
        err.style.display = 'none';

        const name = document.getElementById('qv_name').value.trim();
        const reg = document.getElementById('qv_reg').value.trim();
        const catSelect = document.getElementById('qv_category');
        const catId = catSelect ? catSelect.value : null;
        const catName = catSelect ? catSelect.options[catSelect.selectedIndex]?.dataset?.name : 'Sedan';
        const cap = parseInt(document.getElementById('qv_capacity').value) || 4;
        const ac = document.getElementById('qv_ac').value;
        const own = document.getElementById('qv_ownership').value;

        try {
            const resp = await fetch('{{ route('admin.vehicles.quick') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    name: name,
                    registration_number: reg,
                    vehicle_category_id: catId,
                    vehicle_type: catName,
                    seating_capacity: cap,
                    ac_non_ac: ac,
                    ownership_type: own
                })
            });
            const data = await resp.json();
            if (data.success && data.vehicle) {
                const veh = data.vehicle;
                const vSel = document.getElementById('vehicle_id');
                const opt = document.createElement('option');
                opt.value = veh.id;
                opt.textContent = `${veh.name} - ${veh.registration_number} (${veh.vehicle_type} • ${veh.status})`;
                vSel.appendChild(opt);
                vSel.value = veh.id;
                lastValidVehicle = veh.id;
                closeQuickVehicleModal();
                alert(`✓ Vehicle '${veh.name} (${veh.registration_number})' registered and allocated!`);
            } else {
                err.textContent = data.message || 'Error saving vehicle.';
                err.style.display = 'block';
            }
        } catch (ex) {
            err.textContent = 'Server error: ' + ex.message;
            err.style.display = 'block';
        } finally {
            btn.disabled = false;
            btn.textContent = 'Register & Assign Vehicle';
        }
    }

    // Quick Add Category functions
    function openQuickCategoryModal() {
        const m = document.getElementById('quickCategoryModal');
        if (m) {
            m.style.display = 'flex';
            document.getElementById('qc_name').focus();
        }
    }

    function closeQuickCategoryModal() {
        const m = document.getElementById('quickCategoryModal');
        if (m) m.style.display = 'none';
        const cSel = document.getElementById('vehicle_category');
        if (cSel && cSel.value === 'new_category') {
            cSel.value = lastValidVehCategory;
        }
    }

    async function submitQuickCategory(e) {
        e.preventDefault();
        const btn = document.getElementById('qc_submit_btn');
        const err = document.getElementById('qc_error');
        btn.disabled = true;
        btn.textContent = 'Saving...';
        err.style.display = 'none';

        const name = document.getElementById('qc_name').value.trim();
        const cap = parseInt(document.getElementById('qc_default_capacity').value) || 4;
        const maxCap = parseInt(document.getElementById('qc_max_capacity').value) || cap;
        const conf = document.getElementById('qc_configurable').checked ? 1 : 0;

        try {
            const resp = await fetch('{{ route('admin.vehicle-categories.quick') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    name: name,
                    default_seating_capacity: cap,
                    min_seating_capacity: cap,
                    max_seating_capacity: maxCap,
                    is_capacity_configurable: conf
                })
            });
            const data = await resp.json();
            if (data.success && data.category) {
                const cat = data.category;
                activeVehicleCategoriesData.push(cat);
                standardCategoriesList.push(cat);
                const cSel = document.getElementById('vehicle_category');
                const opt = document.createElement('option');
                opt.value = cat.name;
                opt.dataset.id = cat.id;
                opt.textContent = `${cat.name} (${cat.is_capacity_configurable ? 'Configurable ' + cat.min_seating_capacity + '-' + cat.max_seating_capacity + ' Seats' : cat.default_seating_capacity + ' Seats'})`;
                cSel.appendChild(opt);
                cSel.value = cat.name;
                lastValidVehCategory = cat.name;
                closeQuickCategoryModal();
                onAdminBookingCategoryChange();
                alert(`✓ Category '${cat.name}' created and selected!`);
            } else {
                err.textContent = data.message || 'Error saving category.';
                err.style.display = 'block';
            }
        } catch (ex) {
            err.textContent = 'Server error: ' + ex.message;
            err.style.display = 'block';
        } finally {
            btn.disabled = false;
            btn.textContent = 'Save & Select Category';
        }
    }
    const rateCategory = document.querySelector('#rate_category');
    const pickup = document.querySelector('#pickup_location');
    const destination = document.querySelector('#destination');
    const vehicleCategory = document.querySelector('#vehicle_category');
    const estimatedKm = document.querySelector('#estimated_km');
    const estimatedKmGroup = document.querySelector('#estimatedKmGroup');
    const totalAmount = document.querySelector('#total_amount');
    const fareDetails = document.querySelector('#fareDetails');

    const ambulanceCategoryOptions = [
        { name: 'Ambulance with Body Freezer', label: '❄️ Ambulance with Body Freezer' },
        { name: 'Ambulance with Ventilator', label: '🫁 Ambulance with Ventilator' },
        { name: 'ICU Ambulance', label: '🏥 ICU Ambulance' },
        { name: 'Dead Body Transfer', label: '🕊️ Dead Body Transfer' }
    ];
    const ambulanceCategoryNames = ambulanceCategoryOptions.map(a => a.name);
    const standardCategoriesList = activeVehicleCategoriesData.filter(c => !ambulanceCategoryNames.includes(c.name));

    function syncVehicleCategoriesForTripType(preferredCategory) {
        const cat = rateCategory ? rateCategory.value : '';
        const isAmbulance = (cat === 'ambulance');
        const vehCatSelect = document.getElementById('vehicle_category');
        const vehCatLabel = document.getElementById('vehicleCategoryLabel');
        const modelGroup = document.getElementById('vehicleModelGroup');

        if (!vehCatSelect) return;

        const currentVal = (preferredCategory !== undefined && preferredCategory !== '') ? preferredCategory : vehCatSelect.value;
        vehCatSelect.innerHTML = '';

        if (isAmbulance) {
            if (vehCatLabel) vehCatLabel.textContent = 'Ambulance Category *';
            if (modelGroup) modelGroup.style.display = 'none';

            const defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.textContent = '-- Choose Ambulance Category --';
            vehCatSelect.appendChild(defaultOpt);

            ambulanceCategoryOptions.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.name;
                opt.textContent = item.label;
                const dbCat = activeVehicleCategoriesData.find(c => c.name === item.name);
                if (dbCat) {
                    opt.dataset.id = dbCat.id;
                }
                if (currentVal === item.name) {
                    opt.selected = true;
                }
                vehCatSelect.appendChild(opt);
            });
        } else {
            if (vehCatLabel) vehCatLabel.textContent = 'Vehicle Category *';
            if (modelGroup) modelGroup.style.display = 'block';

            const defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.textContent = 'Select vehicle category';
            vehCatSelect.appendChild(defaultOpt);

            const newCatOpt = document.createElement('option');
            newCatOpt.value = 'new_category';
            newCatOpt.textContent = '➕ + Add New Category';
            newCatOpt.style.fontWeight = '800';
            newCatOpt.style.color = '#2563EB';
            newCatOpt.style.background = '#EFF6FF';
            vehCatSelect.appendChild(newCatOpt);

            standardCategoriesList.forEach(catObj => {
                const opt = document.createElement('option');
                opt.value = catObj.name;
                opt.dataset.id = catObj.id;
                const cap = catObj.is_capacity_configurable ? `Configurable ${catObj.min_seating_capacity}-${catObj.max_seating_capacity} Seats` : `${catObj.default_seating_capacity} Seats`;
                opt.textContent = `${catObj.name} (${cap})`;
                if (currentVal === catObj.name) {
                    opt.selected = true;
                }
                vehCatSelect.appendChild(opt);
            });
        }

        onAdminBookingCategoryChange();
    }

    function onAdminBookingCategoryChange() {
        const catSelect = document.getElementById('vehicle_category');
        if (catSelect.value === 'new_category') {
            openQuickCategoryModal();
            return;
        }
        lastValidVehCategory = catSelect.value;
        const catIdInput = document.getElementById('admin_vehicle_category_id');
        const modelSelect = document.getElementById('vehicle_model_id');
        const modelNameInput = document.getElementById('admin_vehicle_model');

        const selectedOpt = catSelect.options[catSelect.selectedIndex];
        const catId = selectedOpt ? selectedOpt.dataset.id : null;
        if (catIdInput) catIdInput.value = catId || '';

        const previousVal = modelSelect.value;
        modelSelect.innerHTML = '<option value="">-- Any / All Models in Category --</option>';
        if (catId) {
            const catObj = activeVehicleCategoriesData.find(c => c.id == catId);
            if (catObj && catObj.active_models && catObj.active_models.length > 0) {
                catObj.active_models.forEach(m => {
                    const opt = document.createElement('option');
                    opt.value = m.id;
                    opt.textContent = m.name + (m.brand ? ` (${m.brand})` : '');
                    opt.dataset.name = m.name;
                    if (previousVal == m.id) opt.selected = true;
                    modelSelect.appendChild(opt);
                });
            }
        }
        if (modelNameInput) {
            const curOpt = modelSelect.options[modelSelect.selectedIndex];
            modelNameInput.value = curOpt && curOpt.value ? curOpt.dataset.name : '';
        }
        updateFare();
    }

    function onAdminBookingModelChange() {
        const modelSelect = document.getElementById('vehicle_model_id');
        const modelNameInput = document.getElementById('admin_vehicle_model');
        const selectedOpt = modelSelect.options[modelSelect.selectedIndex];
        if (modelNameInput) {
            modelNameInput.value = selectedOpt && selectedOpt.value ? (selectedOpt.dataset.name || selectedOpt.textContent) : '';
        }
    }

    function onTripTypeChange() {
        const cat = rateCategory.value;
        const tpGroup = document.getElementById('tourPackageGroup');
        const ambGroup = document.getElementById('ambulanceNoticeGroup');

        if (tpGroup) tpGroup.style.display = (cat === 'tour_package') ? 'block' : 'none';
        if (ambGroup) ambGroup.style.display = (cat === 'ambulance') ? 'block' : 'none';

        if (cat === 'round_trip') {
            if (estimatedKmGroup) estimatedKmGroup.style.display = 'block';
            if (estimatedKm) estimatedKm.required = true;
        } else {
            if (estimatedKmGroup) estimatedKmGroup.style.display = 'none';
            if (estimatedKm) estimatedKm.required = false;
        }

        syncVehicleCategoriesForTripType();
        updateFare();
    }

    function onTourPackageChange(select) {
        if (!select || !select.value) return;
        const opt = select.options[select.selectedIndex];
        if (!opt) return;

        const route = opt.dataset.route;
        const sedanPrice = opt.dataset.sedan;
        const ertigaPrice = opt.dataset.ertiga;
        const innovaPrice = opt.dataset.innova;
        const catVal = (vehicleCategory.value || '').toLowerCase();

        let price = sedanPrice;
        if (catVal.includes('ertiga') || catVal.includes('suv') || catVal.includes('muv')) {
            price = ertigaPrice || sedanPrice;
        } else if (catVal.includes('innova') || catVal.includes('crysta')) {
            price = innovaPrice || ertigaPrice || sedanPrice;
        }

        if (price && Number(price) > 0) {
            totalAmount.value = Number(price).toFixed(2);
            fareDetails.innerHTML = '🌴 <strong style="color:#166534;">' + opt.dataset.name + ':</strong> Fixed tour package rate ₹' + price + ' applied (' + (opt.dataset.days || 1) + ' Days).';
        }
    }

    function toggleVehicleSource(source) {
        const ownSection = document.getElementById('ownVehicleSection');
        const tpSection = document.getElementById('thirdPartyVehicleSection');
        const lblOwn = document.getElementById('lblVehicleSourceOwn');
        const lblTp = document.getElementById('lblVehicleSourceThirdParty');

        if (source === 'third_party') {
            if (ownSection) ownSection.style.display = 'none';
            if (tpSection) tpSection.style.display = 'block';
            if (lblOwn) {
                lblOwn.style.borderColor = 'var(--slate-200)';
                lblOwn.style.background = '#fff';
            }
            if (lblTp) {
                lblTp.style.borderColor = '#D97706';
                lblTp.style.background = '#FFFBEB';
            }

            // Sync partner name & phone from Category Section if available
            const tpNameInput = document.querySelector('input[name="travel_partner_name"]');
            const tpVendorInput = document.getElementById('third_party_vendor_name');
            if (tpNameInput && tpVendorInput && !tpVendorInput.value && tpNameInput.value) {
                tpVendorInput.value = tpNameInput.value;
            }
            const tpPhoneInput = document.querySelector('input[name="travel_partner_phone"]');
            const tpDriverPhone = document.getElementById('third_party_driver_phone');
            if (tpPhoneInput && tpDriverPhone && !tpDriverPhone.value && tpPhoneInput.value) {
                tpDriverPhone.value = tpPhoneInput.value;
            }
        } else {
            if (ownSection) ownSection.style.display = 'block';
            if (tpSection) tpSection.style.display = 'none';
            if (lblOwn) {
                lblOwn.style.borderColor = '#2563EB';
                lblOwn.style.background = '#EFF6FF';
            }
            if (lblTp) {
                lblTp.style.borderColor = 'var(--slate-200)';
                lblTp.style.background = '#fff';
            }
        }
    }

    function onExistingTpVehicleSelect(select) {
        if (!select || !select.value) return;
        const opt = select.options[select.selectedIndex];
        if (!opt) return;

        const regInput = document.getElementById('third_party_vehicle_number');
        const nameInput = document.getElementById('third_party_vehicle_name');
        const vendorInput = document.getElementById('third_party_vendor_name');
        const phoneInput = document.getElementById('third_party_driver_phone');

        if (regInput && opt.dataset.number) regInput.value = opt.dataset.number;
        if (nameInput && opt.dataset.name) nameInput.value = opt.dataset.name;
        if (vendorInput && opt.dataset.vendor) vendorInput.value = opt.dataset.vendor;
        if (phoneInput && opt.dataset.phone) phoneInput.value = opt.dataset.phone;
    }

    function updateFare() {
        if (document.getElementById('customFareToggle')?.checked) {
            return;
        }
        const category = rateCategory.value;

        if (category === 'tour_package') {
            const tpSelect = document.getElementById('tour_package_id');
            if (tpSelect && tpSelect.value) {
                onTourPackageChange(tpSelect);
            } else {
                fareDetails.innerHTML = '🌴 <strong style="color:#166534;">Tour Package:</strong> Please select a tour package from the dropdown, or tick <label for="customFareToggle" style="text-decoration:underline; cursor:pointer; font-weight:700; color:#2563EB;">Custom Fare</label>.';
            }
            return;
        }

        if (category === 'ambulance') {
            fareDetails.innerHTML = '🚑 <strong style="color:#991B1B;">Emergency Ambulance:</strong> 24/7 Patient Dispatch. Tick <label for="customFareToggle" style="text-decoration:underline; cursor:pointer; font-weight:700; color:#2563EB;">Custom Fare</label> to enter agreed trip rate.';
            return;
        }

        const globalRate = category.startsWith('local_') || category === 'round_trip';
        estimatedKmGroup.style.display = category === 'round_trip' ? 'block' : 'none';
        estimatedKm.required = category === 'round_trip';
        if (category.startsWith('local_') && pickup.value) destination.value = pickup.value;
        const key = [category, globalRate ? '' : pickup.value, globalRate ? '' : destination.value, vehicleCategory.value].join('|');
        const quote = rateOptions[key];
        if (!quote) {
            const isCustom = document.getElementById('customFareToggle')?.checked;
            if (!isCustom) {
                totalAmount.value = (totalAmount.value && Number(totalAmount.value) > 0) ? totalAmount.value : '0.00';
                fareDetails.innerHTML = '<span style="color:#D97706; font-weight:700;">⚠️ Rate not pre-configured for this route.</span> Please tick <label for="customFareToggle" style="text-decoration:underline; cursor:pointer; font-weight:700; color:#2563EB;">Custom Fare</label> above to enter custom trip fare.';
            }
            return;
        }
        let amount = Number(quote.total_amount);
        if (quote.details.vehicle_rent !== null) {
            amount = Number(quote.details.vehicle_rent) + Number(estimatedKm.value || 0) * Number(quote.details.per_km_rate);
        }
        totalAmount.value = amount.toFixed(2);
        fareDetails.textContent = [
            quote.details.included_km ? `${quote.details.included_km} KM included` : '',
            quote.details.included_hours ? `${quote.details.included_hours} hours included` : '',
            quote.details.extra_km_rate !== null ? `Extra KM ₹${quote.details.extra_km_rate}` : '',
            quote.details.extra_hour_rate !== null ? `Extra hour ₹${quote.details.extra_hour_rate}` : '',
            quote.details.gst_applicable ? 'GST applicable' : '',
        ].filter(Boolean).join(' · ');
    }

    function toggleCustomFare(checkbox) {
        const customInput = document.getElementById('is_custom_fare');
        if (checkbox.checked) {
            totalAmount.removeAttribute('readonly');
            totalAmount.style.background = '#fff';
            totalAmount.style.borderColor = '#2563EB';
            totalAmount.style.boxShadow = '0 0 0 3px rgba(37,99,235,0.15)';
            if (customInput) customInput.value = '1';
            fareDetails.innerHTML = '✏️ <strong style="color:#2563EB;">Custom Fare Active:</strong> Enter your custom trip fare. Booking will be confirmed with this rate.';
            totalAmount.focus();
            if (totalAmount.value === '0.00') {
                totalAmount.select();
            }
        } else {
            totalAmount.setAttribute('readonly', 'readonly');
            totalAmount.style.background = '';
            totalAmount.style.borderColor = '';
            totalAmount.style.boxShadow = '';
            if (customInput) customInput.value = '0';
            updateFare();
        }
    }

    function setCustomerMode(mode) {
        const modeInput = document.getElementById('customer_mode');
        const existingSection = document.getElementById('existingCustomerSection');
        const newSection = document.getElementById('newCustomerSection');
        const existingBtn = document.getElementById('modeExistingBtn');
        const newBtn = document.getElementById('modeNewBtn');
        const customerSelect = document.getElementById('customer_id');
        const newName = document.getElementById('new_customer_name');
        const newPhone = document.getElementById('new_customer_phone');

        if (!modeInput) return;
        modeInput.value = mode;

        if (mode === 'new') {
            existingSection.style.display = 'none';
            newSection.style.display = 'block';
            if (customerSelect) customerSelect.required = false;
            if (newName) newName.required = true;
            if (newPhone) newPhone.required = true;
            newBtn.style.background = 'var(--primary)';
            newBtn.style.color = '#fff';
            existingBtn.style.background = 'transparent';
            existingBtn.style.color = 'var(--slate-700)';
            setTimeout(() => { if (newName) newName.focus(); }, 50);
        } else {
            newSection.style.display = 'none';
            existingSection.style.display = 'block';
            if (customerSelect) customerSelect.required = true;
            if (newName) newName.required = false;
            if (newPhone) newPhone.required = false;
            existingBtn.style.background = 'var(--primary)';
            existingBtn.style.color = '#fff';
            newBtn.style.background = 'transparent';
            newBtn.style.color = 'var(--slate-700)';
        }
    }

    const customerSelectElem = document.getElementById('customer_id');
    if (customerSelectElem) {
        customerSelectElem.addEventListener('change', function() {
            if (this.value === 'new') {
                this.value = '';
                setCustomerMode('new');
            }
        });
    }

    const initialCustomerMode = "{{ old('customer_mode', (old('new_customer_name') || old('new_customer_phone')) ? 'new' : 'existing') }}";
    setCustomerMode(initialCustomerMode);

    // Searchable Customer Dropdown & Autocomplete Handler
    (function initCustomerSearchableSelect() {
        const searchInput = document.getElementById('customer_search_input');
        const clearBtn = document.getElementById('clear_customer_search');
        const dropdown = document.getElementById('customer_search_dropdown');
        const select = document.getElementById('customer_id');
        const countBadge = document.getElementById('customerCountBadge');
        const selectedCard = document.getElementById('selected_customer_card');
        const selectedNameDisplay = document.getElementById('selected_customer_name_display');
        const selectedDetailDisplay = document.getElementById('selected_customer_detail_display');
        const btnChange = document.getElementById('btn_change_customer');

        if (!searchInput || !select || !dropdown) return;

        // Parse customer database from select options
        const customers = [];
        Array.from(select.options).forEach(opt => {
            if (!opt.value || opt.value === 'new') return;
            const name = opt.getAttribute('data-name') || opt.text.split('(')[0].trim();
            const phone = opt.getAttribute('data-phone') || '';
            const email = opt.getAttribute('data-email') || '';
            const cleanPhone = (phone || '').replace(/\D/g, '');

            customers.push({
                id: opt.value,
                name: name,
                phone: phone,
                cleanPhone: cleanPhone,
                email: email,
                label: `${name} (${phone}${email ? ' | ' + email : ''})`
            });
        });

        let activeIndex = -1;
        let currentFiltered = [];

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function highlightMatch(text, query) {
            if (!query || !query.trim()) return escapeHtml(text);
            const escapedQuery = query.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            const regex = new RegExp(`(${escapedQuery})`, 'gi');
            return escapeHtml(text).replace(regex, '<mark style="background:#FEF08A;color:#854D0E;padding:0 2px;border-radius:2px;font-weight:700;">$1</mark>');
        }

        function renderDropdown(items, query) {
            currentFiltered = items;
            activeIndex = -1;
            dropdown.innerHTML = '';

            if (items.length === 0) {
                const noMatch = document.createElement('div');
                noMatch.style.padding = '14px';
                noMatch.style.textAlign = 'center';
                noMatch.style.color = 'var(--slate-500)';
                noMatch.style.fontSize = '0.85rem';

                const qSafe = escapeHtml(query || '');
                noMatch.innerHTML = `
                    <div>No registered customer matches <strong>"${qSafe}"</strong></div>
                    <div style="margin-top: 8px;">
                        <button type="button" class="btn btn-sm" id="dropdownAddCustomerBtn" style="background: var(--primary); color: #fff; font-size: 0.775rem; border-radius: 6px; padding: 4px 12px; font-weight: 600;">
                            + Add "${qSafe}" as New Customer
                        </button>
                    </div>
                `;
                dropdown.appendChild(noMatch);

                const addBtn = document.getElementById('dropdownAddCustomerBtn');
                if (addBtn) {
                    addBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        dropdown.style.display = 'none';
                        setCustomerMode('new');
                        const cleanQ = (query || '').trim();
                        const isPhone = /^[0-9+\-\s]+$/.test(cleanQ);
                        if (isPhone) {
                            const newPhoneInput = document.getElementById('new_customer_phone');
                            if (newPhoneInput) newPhoneInput.value = cleanQ.replace(/\D/g, '');
                        } else {
                            const newNameInput = document.getElementById('new_customer_name');
                            if (newNameInput) newNameInput.value = cleanQ;
                        }
                    });
                }

                dropdown.style.display = 'block';
                return;
            }

            const maxDisplay = 15;
            const displayItems = items.slice(0, maxDisplay);

            displayItems.forEach((c, idx) => {
                const itemEl = document.createElement('div');
                itemEl.className = 'customer-search-item';
                itemEl.dataset.id = c.id;
                itemEl.dataset.index = idx;
                itemEl.style.cssText = 'padding: 9px 12px; border-bottom: 1px solid #f1f5f9; cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: background 0.15s;';

                const nameHtml = highlightMatch(c.name, query);
                const phoneHtml = c.phone ? highlightMatch(c.phone, query) : '';
                const emailHtml = c.email ? `<span style="font-size: 0.75rem; color: #64748b; margin-left: 6px;">${escapeHtml(c.email)}</span>` : '';

                itemEl.innerHTML = `
                    <div style="flex: 1; min-width: 0; padding-right: 8px;">
                        <div style="font-weight: 700; font-size: 0.875rem; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            👤 ${nameHtml}
                        </div>
                        ${emailHtml ? `<div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${emailHtml}</div>` : ''}
                    </div>
                    ${phoneHtml ? `
                    <div style="flex-shrink: 0;">
                        <span style="background: #EFF6FF; color: #1E40AF; border: 1px solid #BFDBFE; padding: 2px 7px; border-radius: 6px; font-size: 0.775rem; font-weight: 700; font-family: monospace;">
                            📞 ${phoneHtml}
                        </span>
                    </div>` : ''}
                `;

                itemEl.addEventListener('mouseenter', () => {
                    setActiveIndex(idx);
                });

                itemEl.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    selectCustomer(c.id);
                });

                dropdown.appendChild(itemEl);
            });

            if (items.length > maxDisplay) {
                const footerEl = document.createElement('div');
                footerEl.style.cssText = 'padding: 6px 12px; font-size: 0.75rem; color: #64748b; background: #f8fafc; text-align: center; font-style: italic;';
                footerEl.textContent = `Showing 15 of ${items.length} matching customers. Type more to narrow down.`;
                dropdown.appendChild(footerEl);
            }

            dropdown.style.display = 'block';
        }

        function setActiveIndex(index) {
            activeIndex = index;
            const allItems = dropdown.querySelectorAll('.customer-search-item');
            allItems.forEach((el, idx) => {
                if (idx === index) {
                    el.style.background = '#EFF6FF';
                } else {
                    el.style.background = '#ffffff';
                }
            });
        }

        function filterCustomers(query) {
            const trimmed = (query || '').trim().toLowerCase();
            const cleanQueryDigits = trimmed.replace(/\D/g, '');

            if (!trimmed) {
                return customers;
            }

            return customers.filter(c => {
                const nameMatch = c.name.toLowerCase().includes(trimmed);
                const emailMatch = c.email.toLowerCase().includes(trimmed);
                const rawPhoneMatch = c.phone.toLowerCase().includes(trimmed);
                const digitsMatch = cleanQueryDigits.length > 0 && c.cleanPhone.includes(cleanQueryDigits);
                return nameMatch || emailMatch || rawPhoneMatch || digitsMatch;
            });
        }

        function syncSelectOptions(matchingItems) {
            const matchingIds = new Set(matchingItems.map(m => String(m.id)));
            const currentVal = select.value;

            select.innerHTML = '';

            const defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.textContent = matchingItems.length === customers.length
                ? '-- Choose Existing Customer --'
                : `-- Matching Customers (${matchingItems.length}) --`;
            select.appendChild(defaultOpt);

            const newOpt = document.createElement('option');
            newOpt.value = 'new';
            newOpt.style.fontWeight = '700';
            newOpt.style.color = 'var(--primary)';
            newOpt.textContent = '+ Add New Customer Instead';
            select.appendChild(newOpt);

            customers.forEach(c => {
                if (matchingIds.has(String(c.id))) {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.setAttribute('data-name', c.name);
                    opt.setAttribute('data-phone', c.phone);
                    opt.setAttribute('data-email', c.email);
                    opt.textContent = c.label;
                    if (String(c.id) === String(currentVal)) {
                        opt.selected = true;
                    }
                    select.appendChild(opt);
                }
            });

            if (countBadge) {
                countBadge.textContent = `${matchingItems.length} Available`;
            }
        }

        function selectCustomer(id, updateSelect = true) {
            const c = customers.find(item => String(item.id) === String(id));
            if (!c) return;

            if (updateSelect) {
                let opt = select.querySelector(`option[value="${c.id}"]`);
                if (!opt) {
                    opt = document.createElement('option');
                    opt.value = c.id;
                    opt.setAttribute('data-name', c.name);
                    opt.setAttribute('data-phone', c.phone);
                    opt.setAttribute('data-email', c.email);
                    opt.textContent = c.label;
                    select.appendChild(opt);
                }
                select.value = c.id;
                select.dispatchEvent(new Event('change'));
            }

            searchInput.value = `${c.name} — ${c.phone}`;
            if (clearBtn) clearBtn.style.display = 'flex';

            if (selectedCard) {
                if (selectedNameDisplay) selectedNameDisplay.textContent = c.name;
                if (selectedDetailDisplay) {
                    const details = [
                        c.phone ? `📞 ${c.phone}` : '',
                        c.email ? `✉️ ${c.email}` : ''
                    ].filter(Boolean).join('  ·  ');
                    selectedDetailDisplay.textContent = details;
                }
                selectedCard.style.display = 'block';
            }

            dropdown.style.display = 'none';
        }

        function clearSelection() {
            searchInput.value = '';
            if (clearBtn) clearBtn.style.display = 'none';
            if (selectedCard) selectedCard.style.display = 'none';

            syncSelectOptions(customers);
            select.value = '';
            select.dispatchEvent(new Event('change'));

            renderDropdown(customers.slice(0, 15), '');
            searchInput.focus();
        }

        searchInput.addEventListener('input', function() {
            const q = this.value;
            if (clearBtn) {
                clearBtn.style.display = q.length > 0 ? 'flex' : 'none';
            }
            const filtered = filterCustomers(q);
            renderDropdown(filtered, q);
            syncSelectOptions(filtered);
        });

        searchInput.addEventListener('focus', function() {
            const q = this.value;
            const filtered = filterCustomers(q);
            renderDropdown(filtered, q);
        });

        searchInput.addEventListener('keydown', function(e) {
            if (dropdown.style.display === 'none') {
                if (e.key === 'ArrowDown') {
                    const filtered = filterCustomers(this.value);
                    renderDropdown(filtered, this.value);
                    e.preventDefault();
                }
                return;
            }

            const visibleCount = currentFiltered.slice(0, 15).length;
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (visibleCount > 0) {
                    setActiveIndex((activeIndex + 1) % visibleCount);
                    const activeEl = dropdown.querySelector(`[data-index="${activeIndex}"]`);
                    if (activeEl) activeEl.scrollIntoView({ block: 'nearest' });
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (visibleCount > 0) {
                    setActiveIndex((activeIndex - 1 + visibleCount) % visibleCount);
                    const activeEl = dropdown.querySelector(`[data-index="${activeIndex}"]`);
                    if (activeEl) activeEl.scrollIntoView({ block: 'nearest' });
                }
            } else if (e.key === 'Enter') {
                if (activeIndex >= 0 && activeIndex < currentFiltered.length) {
                    e.preventDefault();
                    selectCustomer(currentFiltered[activeIndex].id);
                }
            } else if (e.key === 'Escape') {
                dropdown.style.display = 'none';
            }
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function(e) {
                e.preventDefault();
                clearSelection();
            });
        }

        if (btnChange) {
            btnChange.addEventListener('click', function(e) {
                e.preventDefault();
                clearSelection();
            });
        }

        select.addEventListener('change', function() {
            if (this.value && this.value !== 'new') {
                selectCustomer(this.value, false);
            } else if (!this.value) {
                searchInput.value = '';
                if (clearBtn) clearBtn.style.display = 'none';
                if (selectedCard) selectedCard.style.display = 'none';
            }
        });

        document.addEventListener('click', function(e) {
            const container = searchInput.closest('.customer-search-container');
            if (container && !container.contains(e.target) && e.target !== btnChange) {
                dropdown.style.display = 'none';
            }
        });

        // Initialize if an existing customer was already selected
        if (select.value && select.value !== 'new') {
            selectCustomer(select.value, false);
        }
    })();

    function toggleCategoryFields() {
        const selected = document.querySelector('input[name="booking_category"]:checked')?.value || 'MAN';
        const corpCard = document.getElementById('corporateFieldsCard');
        const tpCard = document.getElementById('travelPartnerFieldsCard');
        if (corpCard) {
            corpCard.style.display = (selected === 'CORP') ? 'block' : 'none';
        }
        if (tpCard) {
            tpCard.style.display = (selected === 'TP') ? 'block' : 'none';
        }
        const lblMan = document.getElementById('lblCatMan');
        const lblCorp = document.getElementById('lblCatCorp');
        const lblTp = document.getElementById('lblCatTp');
        if (lblMan) lblMan.style.borderColor = (selected === 'MAN') ? '#2563EB' : 'var(--slate-200)';
        if (lblCorp) lblCorp.style.borderColor = (selected === 'CORP') ? '#7C3AED' : 'var(--slate-200)';
        if (lblTp) lblTp.style.borderColor = (selected === 'TP') ? '#D97706' : 'var(--slate-200)';
    }
    toggleCategoryFields();

    [rateCategory, pickup, destination, vehicleCategory, estimatedKm].forEach((input) => input.addEventListener('change', updateFare));
    syncVehicleCategoriesForTripType(@json(old('vehicle_category', '')));
    updateFare();

    // Google Maps Location Picker & Route Preview Integration for Admin
    let adminRouteMap = null;
    let adminRoutePolyline = null;
    let adminRouteMarkers = [];

    document.querySelectorAll('.btn-admin-select-map').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const target = this.dataset.target;

            if (target === 'pickup') {
                window.VaishnaviLocationPicker.open({
                    label: 'Admin Booking: Pickup Location',
                    inputEl: pickup,
                    latEl: document.getElementById('adminPickupLat'),
                    lngEl: document.getElementById('adminPickupLng'),
                    placeIdEl: document.getElementById('adminPickupPlaceId'),
                    onConfirm: () => {
                        updateFare();
                        calculateAdminRoute();
                    }
                });
            } else if (target === 'via') {
                window.VaishnaviLocationPicker.open({
                    label: 'Admin Booking: Via Location',
                    inputEl: document.getElementById('via_location'),
                    latEl: document.getElementById('adminViaLat'),
                    lngEl: document.getElementById('adminViaLng'),
                    placeIdEl: document.getElementById('adminViaPlaceId'),
                    onConfirm: () => {
                        calculateAdminRoute();
                    }
                });
            } else if (target === 'destination') {
                window.VaishnaviLocationPicker.open({
                    label: 'Admin Booking: Final Destination Drop',
                    inputEl: destination,
                    latEl: document.getElementById('adminDestinationLat'),
                    lngEl: document.getElementById('adminDestinationLng'),
                    placeIdEl: document.getElementById('adminDestinationPlaceId'),
                    onConfirm: () => {
                        updateFare();
                        calculateAdminRoute();
                    }
                });
            }
        });
    });

    pickup.addEventListener('change', calculateAdminRoute);
    destination.addEventListener('change', calculateAdminRoute);
    const viaLocationInput = document.getElementById('via_location');
    if (viaLocationInput) viaLocationInput.addEventListener('change', calculateAdminRoute);
    rateCategory.addEventListener('change', calculateAdminRoute);

    async function calculateAdminRoute() {
        const pVal = pickup.value.trim();
        const dVal = destination.value.trim();
        const vVal = viaLocationInput ? viaLocationInput.value.trim() : '';

        if (!pVal || !dVal) {
            document.getElementById('adminRoutePreviewBox').style.display = 'none';
            return;
        }

        const isRoundTrip = (rateCategory.value === 'round_trip');
        const pLat = document.getElementById('adminPickupLat')?.value;
        const pLng = document.getElementById('adminPickupLng')?.value;
        const dLat = document.getElementById('adminDestinationLat')?.value;
        const dLng = document.getElementById('adminDestinationLng')?.value;
        const vLat = document.getElementById('adminViaLat')?.value;
        const vLng = document.getElementById('adminViaLng')?.value;

        const originParam = (pLat && pLng) ? { lat: parseFloat(pLat), lng: parseFloat(pLng), address: pVal } : pVal;
        const destParam = (dLat && dLng) ? { lat: parseFloat(dLat), lng: parseFloat(dLng), address: dVal } : dVal;
        const viaParam = vVal ? ((vLat && vLng) ? { lat: parseFloat(vLat), lng: parseFloat(vLng), address: vVal } : vVal) : null;

        try {
            const resp = await fetch('{{ route("api.maps.calculate-route") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    origin: originParam,
                    destination: destParam,
                    via: viaParam,
                    is_round_trip: isRoundTrip
                })
            });
            const data = await resp.json();

            if (data && data.success) {
                document.getElementById('adminRoutePreviewBox').style.display = 'block';
                document.getElementById('adminRouteDistDisplay').textContent = `${data.total_distance_km} KM`;
                document.getElementById('adminRouteDurDisplay').textContent = `⏱️ ${data.total_duration_text}`;

                document.getElementById('adminRouteDistanceKm').value = data.total_distance_km;
                document.getElementById('adminRouteDurationText').value = data.total_duration_text;
                document.getElementById('adminRouteDurationMinutes').value = data.total_duration_minutes;
                document.getElementById('adminRouteGeometry').value = data.route_geometry || '';

                if (isRoundTrip && estimatedKm) {
                    estimatedKm.value = data.total_distance_km;
                    updateFare();
                }

                // Render legs
                const legsEl = document.getElementById('adminRouteLegsList');
                if (legsEl && data.legs) {
                    legsEl.innerHTML = data.legs.map((leg, i) => `
                        <div style="background: #fff; padding: 2px 6px; border-radius: 4px; border: 1px solid #BFDBFE;">
                            <strong>Leg ${i + 1}:</strong> ${leg.from_name} &rarr; ${leg.to_name} (<b>${leg.distance_km} KM</b>)
                        </div>
                    `).join('');
                }

                renderAdminRouteMap(data);
            }
        } catch (e) {
            console.error('Admin route calc error:', e);
        }
    }

    function renderAdminRouteMap(data) {
        const canvas = document.getElementById('adminRouteMapCanvas');
        if (!canvas || typeof L === 'undefined') return;

        if (!adminRouteMap) {
            adminRouteMap = L.map(canvas).setView([22.0797, 82.1409], 8);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '© OpenStreetMap'
            }).addTo(adminRouteMap);
        }

        adminRouteMarkers.forEach(m => adminRouteMap.removeLayer(m));
        adminRouteMarkers = [];
        if (adminRoutePolyline) adminRouteMap.removeLayer(adminRoutePolyline);

        const points = [];
        if (data.legs) {
            data.legs.forEach((leg, idx) => {
                if (leg.start_location && leg.start_location.lat) {
                    const startPos = [leg.start_location.lat, leg.start_location.lng];
                    if (idx === 0) {
                        const m = L.marker(startPos).addTo(adminRouteMap).bindPopup(`🟢 ${leg.from_name}`);
                        adminRouteMarkers.push(m);
                        points.push(startPos);
                    }
                }
                if (leg.end_location && leg.end_location.lat) {
                    const endPos = [leg.end_location.lat, leg.end_location.lng];
                    const label = (idx === data.legs.length - 1) ? (data.is_round_trip ? '🏁 Return Drop' : '🔴 Destination') : `🟡 Via ${leg.to_name}`;
                    const m = L.marker(endPos).addTo(adminRouteMap).bindPopup(`${label}: ${leg.to_name}`);
                    adminRouteMarkers.push(m);
                    points.push(endPos);
                }
            });
        }

        if (points.length >= 2) {
            adminRoutePolyline = L.polyline(points, {
                color: '#2563EB',
                weight: 4,
                opacity: 0.9,
                dashArray: data.is_round_trip ? '6, 5' : '0'
            }).addTo(adminRouteMap);
            adminRouteMap.fitBounds(adminRoutePolyline.getBounds(), { padding: [20, 20] });
        }
        setTimeout(() => adminRouteMap.invalidateSize(), 200);
    }
</script>

<x-location-picker-modal />
@endsection
