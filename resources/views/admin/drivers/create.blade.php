@extends('layouts.admin')

@section('title', 'Onboard New Chauffeur')
@section('page_title', 'Onboard Chauffeur')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.drivers.index') }}" style="font-size: 0.875rem; color: var(--slate-600); font-weight: 600;">
        ← Back to Chauffeurs Roster
    </a>
    <h1 style="font-size: 1.65rem; font-weight: 800; margin: 4px 0 0 0;">Onboard New Chauffeur</h1>
    <p style="color: var(--slate-500); font-size: 0.875rem; margin: 2px 0 0 0;">Register verified commercial driver with license and background verification records.</p>
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

<form action="{{ route('admin.drivers.store') }}" method="POST">
    @csrf

    <div class="grid grid-3 gap-4">
        <!-- Left 2 Cols: Details -->
        <div style="grid-column: span 2;">
            <div class="card mb-4">
                <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1.25rem;">👨‍✈️ Personal & Commercial License Details</h3>

                <div class="grid grid-2 gap-3 mb-3">
                    <div class="form-group">
                        <label for="name" class="form-label">Full Name of Driver *</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Ramesh Kumar Sahu" required>
                    </div>

                    <div class="form-group">
                        <label for="mobile" class="form-label">Primary Mobile Number *</label>
                        <input type="text" name="mobile" id="mobile" class="form-control" value="{{ old('mobile') }}" placeholder="10-digit mobile number" required>
                    </div>
                </div>

                <div class="grid grid-2 gap-3 mb-3">
                    <div class="form-group">
                        <label for="alternate_mobile" class="form-label">Alternate / Emergency Contact</label>
                        <input type="text" name="alternate_mobile" id="alternate_mobile" class="form-control" value="{{ old('alternate_mobile') }}" placeholder="10-digit mobile number">
                    </div>

                    <div class="form-group">
                        <label for="license_number" class="form-label">Commercial Driving License (DL) *</label>
                        <input type="text" name="license_number" id="license_number" class="form-control" value="{{ old('license_number') }}" placeholder="e.g. CG-10-2018-004523" required>
                    </div>
                </div>

                <div class="grid grid-2 gap-3 mb-3">
                    <div class="form-group">
                        <label for="license_expiry" class="form-label">License Expiry Date</label>
                        <input type="date" name="license_expiry" id="license_expiry" class="form-control" value="{{ old('license_expiry') }}">
                    </div>

                    <div class="form-group">
                        <label for="rating" class="form-label">Performance Rating (1.0 - 5.0)</label>
                        <input type="number" step="0.1" min="1" max="5" name="rating" id="rating" class="form-control" value="{{ old('rating', '5.0') }}">
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label for="address" class="form-label">Permanent Address / Bilaspur Residence</label>
                    <textarea name="address" id="address" class="form-control" rows="2" placeholder="Street, Colony, Bilaspur...">{{ old('address') }}</textarea>
                </div>

                <div class="form-group">
                    <label for="notes" class="form-label">Police Verification & Background Notes</label>
                    <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Police verification clear, 10+ yrs highway driving experience...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Right 1 Col: Vehicle Attachment & Duty Status -->
        <div>
            <div class="card mb-4">
                <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1.25rem;">🚗 Allocation & Status</h3>

                <div class="form-group mb-3">
                    <label for="assigned_vehicle_id" class="form-label">Default Vehicle Assignment</label>
                    <select name="assigned_vehicle_id" id="assigned_vehicle_id" class="form-select">
                        <option value="">-- Pool Driver (No Fixed Vehicle) --</option>
                        @foreach($vehicles as $v)
                            <option value="{{ $v->id }}" {{ old('assigned_vehicle_id') == $v->id ? 'selected' : '' }}>
                                {{ $v->name }} ({{ $v->registration_number }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-4">
                    <label for="status" class="form-label">Duty Status *</label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="Available" {{ old('status', 'Available') == 'Available' ? 'selected' : '' }}>Available for Duty</option>
                        <option value="Assigned" {{ old('status') == 'Assigned' ? 'selected' : '' }}>Assigned to Trip</option>
                        <option value="On Trip" {{ old('status') == 'On Trip' ? 'selected' : '' }}>On Active Trip</option>
                        <option value="Inactive" {{ old('status') == 'Inactive' ? 'selected' : '' }}>Inactive / On Leave</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    💾 Onboard Driver
                </button>
            </div>
        </div>
    </div>
</form>
@endsection
