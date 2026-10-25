@extends('layouts.admin')

@section('title', 'Customers & Travel Partners')
@section('page_title', 'Customer & Travel Partner Directory')

@section('content')
<div class="d-flex justify-between align-center flex-wrap gap-2 mb-4">
    <div>
        <h1 style="font-size: 1.65rem; font-weight: 800; margin: 0; color: var(--dark-950);">
            <x-icon name="users" :size="24" style="color: var(--primary); vertical-align: middle; margin-right: 6px;" /> Customer & Travel Partner Directory
        </h1>
        <p style="color: var(--slate-500); margin: 2px 0 0 0; font-size: 0.875rem;">
            Manage individual passengers, corporate accounts with GST billing, and travel agency partners.
        </p>
    </div>

    <div class="d-flex align-center gap-2 flex-wrap">
        <a href="{{ route('admin.customers.sample-csv') }}" class="btn btn-outline btn-sm" style="font-weight: 600;" title="Download sample CSV template for Excel">
            <x-icon name="download" :size="13" class="me-1 inline" /> Sample CSV
        </a>
        <button type="button" class="btn btn-outline btn-sm" onclick="openBulkUploadModal()" style="font-weight: 700; border-color: #2563EB; color: #2563EB;">
            <x-icon name="upload-cloud" :size="14" class="me-1 inline" /> Bulk Upload CSV
        </button>
        <a href="{{ route('admin.customers.create') }}" class="btn btn-outline btn-sm">
            Full Registration Page →
        </a>
        <button type="button" class="btn btn-primary btn-sm" onclick="openAddCustomerModal('individual')" style="font-weight: 700;">
            + Add Customer / Partner
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-4" style="background: #F0FDF4; border: 1px solid #BBF7D0; color: #166534; padding: 0.85rem 1.25rem; border-radius: 8px;">
        <x-icon name="circle-check" :size="15" class="me-1 inline" /> {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger mb-4" style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; padding: 0.85rem 1.25rem; border-radius: 8px;">
        <x-icon name="alert-triangle" :size="15" class="me-1 inline" /> {{ session('error') }}
    </div>
@endif

<!-- Type Filter Badges -->
<div class="d-flex align-center gap-2 mb-3 flex-wrap">
    <a href="{{ route('admin.customers.index', ['type' => 'all', 'search' => $searchQuery]) }}" 
       class="btn btn-sm {{ $currentType === 'all' ? 'btn-dark' : 'btn-outline' }}" style="border-radius: 20px; font-size: 0.8rem; padding: 4px 14px;">
        All ({{ $stats['total'] ?? 0 }})
    </a>
    <a href="{{ route('admin.customers.index', ['type' => 'individual', 'search' => $searchQuery]) }}" 
       class="btn btn-sm {{ $currentType === 'individual' ? 'btn-dark' : 'btn-outline' }}" style="border-radius: 20px; font-size: 0.8rem; padding: 4px 14px;">
        <x-icon name="user" :size="13" class="me-1 inline" /> Individual Passengers ({{ $stats['individual'] ?? 0 }})
    </a>
    <a href="{{ route('admin.customers.index', ['type' => 'corporate', 'search' => $searchQuery]) }}" 
       class="btn btn-sm {{ $currentType === 'corporate' ? 'btn-dark' : 'btn-outline' }}" style="border-radius: 20px; font-size: 0.8rem; padding: 4px 14px;">
        <x-icon name="building-2" :size="13" class="me-1 inline" /> Corporate Clients ({{ $stats['corporate'] ?? 0 }})
    </a>
    <a href="{{ route('admin.customers.index', ['type' => 'travel_partner', 'search' => $searchQuery]) }}" 
       class="btn btn-sm {{ $currentType === 'travel_partner' ? 'btn-dark' : 'btn-outline' }}" style="border-radius: 20px; font-size: 0.8rem; padding: 4px 14px;">
        <x-icon name="handshake" :size="13" class="me-1 inline" /> Travel Partners ({{ $stats['travel_partner'] ?? 0 }})
    </a>
</div>

<!-- Filters & Search -->
<div class="card mb-4" style="padding: 1.15rem;">
    <form method="GET" action="{{ route('admin.customers.index') }}" class="d-flex align-center gap-2 flex-wrap">
        <input type="hidden" name="type" value="{{ $currentType }}">
        <div style="flex: 1; min-width: 260px;">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name, company, GSTIN, mobile, or city..." value="{{ $searchQuery }}">
        </div>
        <button type="submit" class="btn btn-dark btn-sm">Search Customers</button>
        @if($searchQuery)
            <a href="{{ route('admin.customers.index', ['type' => $currentType]) }}" class="btn btn-outline btn-sm">Reset</a>
        @endif
    </form>
</div>

<!-- Customers Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table" style="width: 100%;">
            <thead>
                <tr>
                    <th>Customer / Company</th>
                    <th>Category</th>
                    <th>Contact Person / Mobile</th>
                    <th>GSTIN Details</th>
                    <th>City / Address</th>
                    <th>Bookings</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $c)
                    @php
                        $custType = $c->customer->customer_type ?? 'individual';
                    @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-center gap-2">
                                <div style="width: 38px; height: 38px; border-radius: 50%; background: {{ $custType === 'corporate' ? '#EFF6FF' : ($custType === 'travel_partner' ? '#FFFBEB' : '#F1F5F9') }}; color: {{ $custType === 'corporate' ? '#1D4ED8' : ($custType === 'travel_partner' ? '#B45309' : '#334155') }}; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem; flex-shrink: 0;">
                                    @if($custType === 'corporate')
                                        <x-icon name="building-2" :size="16" />
                                    @elseif($custType === 'travel_partner')
                                        <x-icon name="handshake" :size="16" />
                                    @else
                                        <x-icon name="user" :size="16" />
                                    @endif
                                </div>
                                <div>
                                    <a href="{{ route('admin.customers.show', $c->id) }}" style="font-weight: 800; color: var(--dark-900); font-size: 0.925rem;">
                                        {{ $c->name }}
                                    </a>
                                    @if($custType === 'corporate' && !empty($c->customer->company_name) && $c->customer->company_name !== $c->name)
                                        <div style="font-size: 0.775rem; color: #2563EB; font-weight: 600;">{{ $c->customer->company_name }}</div>
                                    @elseif($custType === 'travel_partner' && !empty($c->customer->partner_name) && $c->customer->partner_name !== $c->name)
                                        <div style="font-size: 0.775rem; color: #D97706; font-weight: 600;">{{ $c->customer->partner_name }}</div>
                                    @endif
                                    <div style="font-size: 0.75rem; color: var(--slate-400);">{{ $c->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($custType === 'corporate')
                                <span class="badge" style="background: #EFF6FF; color: #1E40AF; border: 1px solid #BFDBFE; font-size: 0.725rem; font-weight: 700;">
                                    <x-icon name="building-2" :size="12" class="me-1 inline" /> Corporate
                                </span>
                            @elseif($custType === 'travel_partner')
                                <span class="badge" style="background: #FFFBEB; color: #92400E; border: 1px solid #FDE68A; font-size: 0.725rem; font-weight: 700;">
                                    <x-icon name="handshake" :size="12" class="me-1 inline" /> Travel Partner
                                </span>
                            @else
                                <span class="badge" style="background: #F8FAFC; color: #475569; border: 1px solid #E2E8F0; font-size: 0.725rem; font-weight: 600;">
                                    <x-icon name="user" :size="12" class="me-1 inline" /> Individual
                                </span>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight: 700; color: var(--dark-900);">
                                <x-icon name="phone" :size="12" class="me-1 inline" /> {{ $c->phone }}
                            </div>
                            @if(!empty($c->customer->contact_person) && $c->customer->contact_person !== $c->name)
                                <div style="font-size: 0.775rem; color: var(--slate-500);">Contact: {{ $c->customer->contact_person }}</div>
                            @endif
                            @if(!empty($c->customer->alternate_phone))
                                <div style="font-size: 0.75rem; color: var(--slate-400);">Alt: {{ $c->customer->alternate_phone }}</div>
                            @endif
                        </td>
                        <td>
                            @if(!empty($c->customer->gstin))
                                <code style="background: #F1F5F9; padding: 2px 6px; border-radius: 4px; font-weight: 700; color: #0F172A; font-size: 0.8rem;">{{ $c->customer->gstin }}</code>
                            @else
                                <span style="color: var(--slate-400); font-size: 0.8rem;">—</span>
                            @endif
                        </td>
                        <td>
                            <div style="font-size: 0.85rem; font-weight: 600; color: var(--dark-900);">{{ $c->customer->city ?? 'Bilaspur' }}</div>
                            @if(!empty($c->customer->address))
                                <div style="font-size: 0.75rem; color: var(--slate-500); max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $c->customer->address }}">
                                    {{ $c->customer->address }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-primary" style="font-size: 0.75rem; font-weight: 700;">
                                {{ $c->bookings->count() }} {{ $c->bookings->count() === 1 ? 'trip' : 'trips' }}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <a href="{{ route('admin.customers.show', $c->id) }}" class="btn btn-outline btn-sm" style="padding: 0.25rem 0.65rem; font-size: 0.775rem;">
                                View Profile →
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5" style="color: var(--slate-500);">
                            No registered customers or partners found matching your search.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($customers->hasPages())
        <div class="mt-4">
            {{ $customers->links() }}
        </div>
    @endif
</div>

<!-- Quick Add Customer Modal -->
<div id="addCustomerModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 9999; align-items: center; justify-content: center; padding: 1rem; backdrop-filter: blur(4px);">
    <div class="card" style="width: 100%; max-width: 680px; max-height: 90vh; overflow-y: auto; background: #fff; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); padding: 1.5rem; position: relative;">
        
        <div class="d-flex justify-between align-center mb-3 pb-2" style="border-bottom: 1px solid var(--slate-200);">
            <div>
                <h3 style="margin: 0; font-size: 1.25rem; font-weight: 800; color: var(--dark-950);">
                    <x-icon name="user-check" :size="18" style="color: var(--primary); vertical-align: middle; margin-right: 6px;" /> Add Customer / Corporate / Partner
                </h3>
                <p style="margin: 2px 0 0 0; font-size: 0.8rem; color: var(--slate-500);">
                    Select the account type below to open specific fields.
                </p>
            </div>
            <button type="button" onclick="closeAddCustomerModal()" style="background: none; border: none; font-size: 1.4rem; color: var(--slate-400); cursor: pointer; line-height: 1;">
                <x-icon name="x" :size="18" />
            </button>
        </div>

        <!-- Customer Type Selection Tabs -->
        <div class="d-flex gap-2 mb-4" style="background: #F8FAFC; padding: 6px; border-radius: 10px; border: 1px solid var(--slate-200);">
            <button type="button" id="tabIndividualBtn" onclick="switchCustomerType('individual')" class="btn btn-sm" style="flex: 1; border-radius: 6px; font-weight: 700; font-size: 0.825rem; background: #2563EB; color: #fff;">
                <x-icon name="user" :size="13" class="me-1 inline" /> Individual
            </button>
            <button type="button" id="tabCorporateBtn" onclick="switchCustomerType('corporate')" class="btn btn-sm btn-outline" style="flex: 1; border-radius: 6px; font-weight: 700; font-size: 0.825rem;">
                <x-icon name="building-2" :size="13" class="me-1 inline" /> Corporate
            </button>
            <button type="button" id="tabPartnerBtn" onclick="switchCustomerType('travel_partner')" class="btn btn-sm btn-outline" style="flex: 1; border-radius: 6px; font-weight: 700; font-size: 0.825rem;">
                <x-icon name="handshake" :size="13" class="me-1 inline" /> Travel Partner
            </button>
        </div>

        <form action="{{ route('admin.customers.store') }}" method="POST" id="customerForm">
            @csrf
            <input type="hidden" name="customer_type" id="modalCustomerType" value="individual">

            <!-- 1. Individual Customer Fields -->
            <div id="individualFields">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: 700; font-size: 0.825rem;">Passenger / Customer Name *</label>
                        <input type="text" name="name" id="indivName" class="form-control form-control-sm" placeholder="e.g. Rahul Sharma" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: 700; font-size: 0.825rem;">Mobile Number *</label>
                        <input type="tel" name="phone" id="indivPhone" class="form-control form-control-sm" placeholder="10-digit mobile number" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: 700; font-size: 0.825rem;">Email Address</label>
                        <input type="email" name="email" class="form-control form-control-sm" placeholder="e.g. rahul@example.com (optional)">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: 700; font-size: 0.825rem;">Alternate Phone / WhatsApp</label>
                        <input type="tel" name="alternate_phone" class="form-control form-control-sm" placeholder="Optional backup number">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" style="font-weight: 700; font-size: 0.825rem;">Pickup / Residence Address</label>
                    <input type="text" name="address" class="form-control form-control-sm" placeholder="Flat / House No., Street, Landmark, Area">
                </div>
            </div>

            <!-- 2. Corporate Customer Fields -->
            <div id="corporateFields" style="display: none; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: 700; font-size: 0.825rem;">Company / Organization Name *</label>
                        <input type="text" name="company_name" id="corpCompanyName" class="form-control form-control-sm" placeholder="e.g. Jindal Steel, NTPC Ltd">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: 700; font-size: 0.825rem;">Company GSTIN Number *</label>
                        <input type="text" name="corporate_gstin" class="form-control form-control-sm" placeholder="15-digit GSTIN (e.g. 22AAAAA0000A1Z5)" maxlength="15" style="text-transform: uppercase;">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: 700; font-size: 0.825rem;">Contact Person / Officer *</label>
                        <input type="text" name="corporate_contact_person" class="form-control form-control-sm" placeholder="e.g. Mr. Anil Verma (Admin Manager)">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: 700; font-size: 0.825rem;">Official Contact Number *</label>
                        <input type="tel" name="corporate_phone" class="form-control form-control-sm" placeholder="Contact number for billing & dispatch">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: 700; font-size: 0.825rem;">Billing Email Address</label>
                        <input type="email" name="corporate_email" class="form-control form-control-sm" placeholder="finance@company.com">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: 700; font-size: 0.825rem;">Payment Terms</label>
                        <select name="corporate_payment_terms" class="form-select form-select-sm">
                            <option value="Net 30 Days">Net 30 Days Invoice</option>
                            <option value="Net 15 Days">Net 15 Days Invoice</option>
                            <option value="Payment on Billing">Immediate Payment on Billing</option>
                            <option value="50% Advance">50% Advance + 50% on Completion</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" style="font-weight: 700; font-size: 0.825rem;">Registered Corporate Billing Address *</label>
                    <textarea name="corporate_billing_address" class="form-control form-control-sm" rows="2" placeholder="Registered office address for GST tax invoicing"></textarea>
                </div>
            </div>

            <!-- 3. Travel Partner Fields -->
            <div id="partnerFields" style="display: none; background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: 700; font-size: 0.825rem;">Agency / Travel Partner Name *</label>
                        <input type="text" name="partner_name" id="partnerAgencyName" class="form-control form-control-sm" placeholder="e.g. Chhattisgarh Travels, Sharma Tours">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: 700; font-size: 0.825rem;">GSTIN (If Agency is Registered)</label>
                        <input type="text" name="partner_gstin" class="form-control form-control-sm" placeholder="GST Number (optional)" maxlength="15" style="text-transform: uppercase;">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: 700; font-size: 0.825rem;">Partner Contact Person *</label>
                        <input type="text" name="partner_contact_person" class="form-control form-control-sm" placeholder="e.g. Agent Name / Manager">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: 700; font-size: 0.825rem;">Partner Mobile / WhatsApp *</label>
                        <input type="tel" name="partner_phone" class="form-control form-control-sm" placeholder="Mobile number for dispatch alerts">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: 700; font-size: 0.825rem;">Agency Email Address</label>
                        <input type="email" name="partner_email" class="form-control form-control-sm" placeholder="partner@travelagency.com">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: 700; font-size: 0.825rem;">Commission Rate / Agreement</label>
                        <input type="text" name="partner_commission" class="form-control form-control-sm" placeholder="e.g. 10% per booking or ₹300 per trip">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" style="font-weight: 700; font-size: 0.825rem;">Agency Office Address</label>
                    <input type="text" name="partner_address" class="form-control form-control-sm" placeholder="Agency branch / office address">
                </div>
            </div>

            <!-- Common Location Fields -->
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label" style="font-weight: 700; font-size: 0.825rem;">City *</label>
                    <input type="text" name="city" class="form-control form-control-sm" value="Bilaspur" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label" style="font-weight: 700; font-size: 0.825rem;">State *</label>
                    <input type="text" name="state" class="form-control form-control-sm" value="Chhattisgarh" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label" style="font-weight: 700; font-size: 0.825rem;">Pincode</label>
                    <input type="text" name="pincode" class="form-control form-control-sm" value="495001">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label" style="font-weight: 700; font-size: 0.825rem;">Internal Admin Notes</label>
                <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Special preferences, vehicle preference, VIP status, billing notes..."></textarea>
            </div>

            <div class="d-flex justify-end gap-2 pt-3" style="border-top: 1px solid var(--slate-200);">
                <button type="button" onclick="closeAddCustomerModal()" class="btn btn-outline btn-sm">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm" style="font-weight: 700; padding: 6px 18px;">
                    Save Customer Account
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Upload Customers Modal -->
<div id="bulkUploadModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 9999; align-items: center; justify-content: center; padding: 1rem; backdrop-filter: blur(4px);">
    <div class="card" style="width: 100%; max-width: 620px; max-height: 90vh; overflow-y: auto; background: #fff; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); padding: 1.5rem; position: relative;">
        
        <div class="d-flex justify-between align-center mb-3 pb-2" style="border-bottom: 1px solid var(--slate-200);">
            <div>
                <h3 style="margin: 0; font-size: 1.25rem; font-weight: 800; color: var(--dark-950);">
                    <x-icon name="upload-cloud" :size="18" style="color: #2563EB; vertical-align: middle; margin-right: 6px;" /> Bulk Upload Customers & Partners
                </h3>
                <p style="margin: 2px 0 0 0; font-size: 0.8rem; color: var(--slate-500);">
                    Import multiple passengers, corporate clients, or travel partners using a CSV file.
                </p>
            </div>
            <button type="button" onclick="closeBulkUploadModal()" style="background: none; border: none; font-size: 1.4rem; color: var(--slate-400); cursor: pointer; line-height: 1;">
                <x-icon name="x" :size="18" />
            </button>
        </div>

        <form method="POST" action="{{ route('admin.customers.bulk-upload') }}" enctype="multipart/form-data" id="bulkUploadForm">
            @csrf

            <!-- File Upload Zone -->
            <div class="mb-3">
                <label class="form-label" style="font-weight: 700; font-size: 0.825rem;">
                    Select CSV File <span style="color: red;">*</span>
                </label>
                <div style="border: 2px dashed #CBD5E1; border-radius: 8px; padding: 1.5rem; text-align: center; background: #F8FAFC; transition: border-color 0.2s;" id="dropZone">
                    <div style="margin-bottom: 0.5rem; color: #2563EB;">
                        <x-icon name="file-text" :size="36" />
                    </div>
                    <p style="margin: 0 0 0.5rem 0; font-weight: 600; font-size: 0.875rem; color: var(--dark-900);">
                        Choose a .CSV file or drag & drop it here
                    </p>
                    <input type="file" name="csv_file" id="csvFileInput" accept=".csv,text/csv" required style="display: block; margin: 0 auto; font-size: 0.825rem;">
                    <div id="fileSelectedInfo" style="margin-top: 0.5rem; font-size: 0.8rem; color: #166534; font-weight: 700; display: none;"></div>
                </div>
            </div>

            <!-- Import Options -->
            <div class="grid grid-2 gap-3 mb-3">
                <div>
                    <label class="form-label" style="font-weight: 700; font-size: 0.825rem;">If Duplicate Phone Exists</label>
                    <select name="duplicate_action" class="form-select form-select-sm">
                        <option value="update" selected>Update existing details with CSV</option>
                        <option value="skip">Skip (keep current customer data)</option>
                    </select>
                </div>

                <div>
                    <label class="form-label" style="font-weight: 700; font-size: 0.825rem;">Default Customer Type</label>
                    <select name="default_type" class="form-select form-select-sm">
                        <option value="auto" selected>Auto-detect from columns/data</option>
                        <option value="individual">Individual Passenger</option>
                        <option value="corporate">Corporate Client</option>
                        <option value="travel_partner">Travel Partner / Agency</option>
                    </select>
                </div>
            </div>

            <!-- Format Info & Download Sample -->
            <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1.25rem;">
                <div class="d-flex justify-between align-center mb-1 flex-wrap gap-1">
                    <span style="font-weight: 700; font-size: 0.825rem; color: #1E40AF;">
                        <x-icon name="file-text" :size="14" class="me-1 inline" /> Supported CSV Columns:
                    </span>
                    <a href="{{ route('admin.customers.sample-csv') }}" class="btn btn-sm" style="background: #2563EB; color: #fff; font-size: 0.75rem; padding: 3px 10px; font-weight: 700;">
                        <x-icon name="download" :size="12" class="me-1 inline" /> Download Sample Template (.CSV)
                    </a>
                </div>
                <p style="margin: 0; font-size: 0.775rem; color: #1E3A8A; line-height: 1.4;">
                    <strong>Required:</strong> <code>Name</code>, <code>Mobile</code> (10 digits)<br>
                    <strong>Optional:</strong> <code>Email</code>, <code>Customer_Type</code> (individual / corporate / travel_partner), <code>Company_Name</code>, <code>Contact_Person</code>, <code>GSTIN</code>, <code>City</code>, <code>Address</code>, <code>State</code>, <code>Pincode</code>, <code>Partner_Name</code>, <code>Partner_Commission</code>, <code>Notes</code>
                </p>
            </div>

            <!-- Footer Buttons -->
            <div class="d-flex justify-end gap-2 pt-2" style="border-top: 1px solid var(--slate-200);">
                <button type="button" class="btn btn-outline btn-sm" onclick="closeBulkUploadModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm" id="btnSubmitBulkUpload" style="font-weight: 700; padding: 0.5rem 1.25rem;">
                    <x-icon name="upload-cloud" :size="15" class="me-1 inline" /> Upload & Import Customers
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openAddCustomerModal(type = 'individual') {
        const modal = document.getElementById('addCustomerModal');
        if (modal) {
            modal.style.display = 'flex';
            switchCustomerType(type);
        }
    }

    function closeAddCustomerModal() {
        const modal = document.getElementById('addCustomerModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    function switchCustomerType(type) {
        document.getElementById('modalCustomerType').value = type;
        
        const indivDiv = document.getElementById('individualFields');
        const corpDiv = document.getElementById('corporateFields');
        const partnerDiv = document.getElementById('partnerFields');

        const tabIndiv = document.getElementById('tabIndividualBtn');
        const tabCorp = document.getElementById('tabCorporateBtn');
        const tabPartner = document.getElementById('tabPartnerBtn');

        // Reset tab styles
        [tabIndiv, tabCorp, tabPartner].forEach(btn => {
            btn.className = 'btn btn-sm btn-outline';
            btn.style.background = '';
            btn.style.color = '';
        });

        const indivName = document.getElementById('indivName');
        const indivPhone = document.getElementById('indivPhone');
        const corpComp = document.getElementById('corpCompanyName');
        const partnerAgency = document.getElementById('partnerAgencyName');

        if (type === 'corporate') {
            indivDiv.style.display = 'none';
            corpDiv.style.display = 'block';
            partnerDiv.style.display = 'none';
            tabCorp.className = 'btn btn-sm';
            tabCorp.style.background = '#2563EB';
            tabCorp.style.color = '#fff';

            indivName.removeAttribute('required');
            indivPhone.removeAttribute('required');
            if (corpComp) corpComp.setAttribute('required', 'required');
            if (partnerAgency) partnerAgency.removeAttribute('required');
        } else if (type === 'travel_partner') {
            indivDiv.style.display = 'none';
            corpDiv.style.display = 'none';
            partnerDiv.style.display = 'block';
            tabPartner.className = 'btn btn-sm';
            tabPartner.style.background = '#D97706';
            tabPartner.style.color = '#fff';

            indivName.removeAttribute('required');
            indivPhone.removeAttribute('required');
            if (corpComp) corpComp.removeAttribute('required');
            if (partnerAgency) partnerAgency.setAttribute('required', 'required');
        } else {
            indivDiv.style.display = 'block';
            corpDiv.style.display = 'none';
            partnerDiv.style.display = 'none';
            tabIndiv.className = 'btn btn-sm';
            tabIndiv.style.background = '#2563EB';
            tabIndiv.style.color = '#fff';

            indivName.setAttribute('required', 'required');
            indivPhone.setAttribute('required', 'required');
            if (corpComp) corpComp.removeAttribute('required');
            if (partnerAgency) partnerAgency.removeAttribute('required');
        }
    }

    function openBulkUploadModal() {
        const modal = document.getElementById('bulkUploadModal');
        if (modal) {
            modal.style.display = 'flex';
        }
    }

    function closeBulkUploadModal() {
        const modal = document.getElementById('bulkUploadModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    // Close modals on click outside
    window.addEventListener('click', function(e) {
        const addModal = document.getElementById('addCustomerModal');
        if (e.target === addModal) {
            closeAddCustomerModal();
        }
        const bulkModal = document.getElementById('bulkUploadModal');
        if (e.target === bulkModal) {
            closeBulkUploadModal();
        }
    });

    // File input change indicator
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('csvFileInput');
        const fileInfo = document.getElementById('fileSelectedInfo');
        if (fileInput && fileInfo) {
            fileInput.addEventListener('change', function() {
                if (fileInput.files && fileInput.files[0]) {
                    fileInfo.style.display = 'block';
                    fileInfo.textContent = 'Selected: ' + fileInput.files[0].name + ' (' + (fileInput.files[0].size / 1024).toFixed(1) + ' KB)';
                } else {
                    fileInfo.style.display = 'none';
                }
            });
        }
    });
</script>
@endpush
@endsection
