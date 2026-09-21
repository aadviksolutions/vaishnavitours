@extends('layouts.admin')

@section('title', 'Edit Chauffeur: ' . $driver->name)
@section('page_title', 'Edit Chauffeur')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.drivers.show', $driver->id) }}" style="font-size: 0.875rem; color: var(--slate-600); font-weight: 600;">
        ← Back to Chauffeur Details
    </a>
    <h1 style="font-size: 1.65rem; font-weight: 800; margin: 4px 0 0 0;">Edit {{ $driver->name }}</h1>
    <p style="color: var(--slate-500); font-size: 0.875rem; margin: 2px 0 0 0;">Update contact information, commercial license details, duty status, and assigned vehicle.</p>
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

<form action="{{ route('admin.drivers.update', $driver->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="grid grid-3 gap-4">
        <!-- Left 2 Cols: Details -->
        <div style="grid-column: span 2;">
            <div class="card mb-4">
                <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1.25rem;">👨‍✈️ Personal & Commercial License Details</h3>

                <div class="grid grid-2 gap-3 mb-3">
                    <div class="form-group">
                        <label for="name" class="form-label">Full Name of Driver *</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $driver->name) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="mobile" class="form-label">Primary Mobile Number *</label>
                        <input type="text" name="mobile" id="mobile" class="form-control" value="{{ old('mobile', $driver->mobile) }}" required>
                    </div>
                </div>

                <div class="grid grid-2 gap-3 mb-3">
                    <div class="form-group">
                        <label for="alternate_mobile" class="form-label">Alternate / Emergency Contact</label>
                        <input type="text" name="alternate_mobile" id="alternate_mobile" class="form-control" value="{{ old('alternate_mobile', $driver->alternate_mobile) }}">
                    </div>

                    <div class="form-group">
                        <label for="license_number" class="form-label">Commercial Driving License (DL) *</label>
                        <input type="text" name="license_number" id="license_number" class="form-control" value="{{ old('license_number', $driver->license_number) }}" required>
                    </div>
                </div>

                <div class="grid grid-2 gap-3 mb-3">
                    <div class="form-group">
                        <label for="license_expiry" class="form-label">License Expiry Date</label>
                        <input type="date" name="license_expiry" id="license_expiry" class="form-control" value="{{ old('license_expiry', $driver->license_expiry ? \Carbon\Carbon::parse($driver->license_expiry)->format('Y-m-d') : '') }}">
                    </div>

                    <div class="form-group">
                        <label for="rating" class="form-label">Performance Rating (1.0 - 5.0)</label>
                        <input type="number" step="0.1" min="1" max="5" name="rating" id="rating" class="form-control" value="{{ old('rating', $driver->rating) }}">
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label for="address" class="form-label">Permanent Address / Bilaspur Residence</label>
                    <textarea name="address" id="address" class="form-control" rows="2">{{ old('address', $driver->address) }}</textarea>
                </div>

                <div class="form-group">
                    <label for="notes" class="form-label">Police Verification & Background Notes</label>
                    <textarea name="notes" id="notes" class="form-control" rows="2">{{ old('notes', $driver->notes) }}</textarea>
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
                            <option value="{{ $v->id }}" {{ old('assigned_vehicle_id', $driver->assigned_vehicle_id) == $v->id ? 'selected' : '' }}>
                                {{ $v->name }} ({{ $v->registration_number }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-4">
                    <label for="status" class="form-label">Duty Status *</label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="Available" {{ old('status', $driver->status) == 'Available' ? 'selected' : '' }}>Available for Duty</option>
                        <option value="Assigned" {{ old('status', $driver->status) == 'Assigned' ? 'selected' : '' }}>Assigned to Trip</option>
                        <option value="On Trip" {{ old('status', $driver->status) == 'On Trip' ? 'selected' : '' }}>On Active Trip</option>
                        <option value="Inactive" {{ old('status', $driver->status) == 'Inactive' ? 'selected' : '' }}>Inactive / On Leave</option>
                    </select>
                </div>

                <div class="d-flex justify-between gap-2">
                    <a href="{{ route('admin.drivers.show', $driver->id) }}" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary flex-1">
                        💾 Update Driver
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
