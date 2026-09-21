@extends('layouts.admin')

@section('title', 'Edit Vehicle: ' . $vehicle->name)
@section('page_title', 'Edit Fleet Vehicle')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.vehicles.show', $vehicle->id) }}" style="font-size: 0.875rem; color: var(--slate-600); font-weight: 600;">
        ← Back to Vehicle Details
    </a>
    <h1 style="font-size: 1.65rem; font-weight: 800; margin: 4px 0 0 0;">Edit {{ $vehicle->name }}</h1>
    <p style="color: var(--slate-500); font-size: 0.875rem; margin: 2px 0 0 0;">Update vehicle registration, capacity, rates, and operational status.</p>
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

<form action="{{ route('admin.vehicles.update', $vehicle->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="grid grid-3 gap-4">
        <!-- Left 2 Cols: Specs -->
        <div style="grid-column: span 2;">
            <div class="card mb-4">
                <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1.25rem;">🚗 Vehicle Specification</h3>

                <div class="grid grid-2 gap-3 mb-3">
                    <div class="form-group">
                        <label for="name" class="form-label">Vehicle Make & Model Name *</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $vehicle->name) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="registration_number" class="form-label">Commercial RTO Number *</label>
                        <input type="text" name="registration_number" id="registration_number" class="form-control" value="{{ old('registration_number', $vehicle->registration_number) }}" required>
                    </div>
                </div>

                <div class="grid grid-3 gap-3 mb-3">
                    <div class="form-group">
                        <label for="vehicle_type" class="form-label">Category *</label>
                        <select name="vehicle_type" id="vehicle_type" class="form-select" required>
                            @foreach(['Hatchback', 'Sedan', 'SUV', 'Innova Crysta', 'Tempo Traveller', 'Luxury'] as $t)
                                <option value="{{ $t }}" {{ old('vehicle_type', $vehicle->vehicle_type) == $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="seating_capacity" class="form-label">Passenger Capacity (Seats) *</label>
                        <input type="number" name="seating_capacity" id="seating_capacity" class="form-control" value="{{ old('seating_capacity', $vehicle->seating_capacity) }}" min="1" max="50" required>
                    </div>

                    <div class="form-group">
                        <label for="ac_non_ac" class="form-label">Air Conditioning *</label>
                        <select name="ac_non_ac" id="ac_non_ac" class="form-select" required>
                            <option value="AC" {{ old('ac_non_ac', $vehicle->ac_non_ac) == 'AC' ? 'selected' : '' }}>AC (Air Conditioned)</option>
                            <option value="Non-AC" {{ old('ac_non_ac', $vehicle->ac_non_ac) == 'Non-AC' ? 'selected' : '' }}>Non-AC</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="notes" class="form-label">Maintenance Notes / Special Equipment</label>
                    <textarea name="notes" id="notes" class="form-control" rows="2">{{ old('notes', $vehicle->notes) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Right 1 Col: Pricing & Status -->
        <div>
            <div class="card mb-4">
                <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1.25rem;">💰 Tariff & Status</h3>

                <div class="form-group mb-3">
                    <label for="per_km_rate" class="form-label">Standard Per KM Rate (₹) *</label>
                    <input type="number" step="0.50" name="per_km_rate" id="per_km_rate" class="form-control" value="{{ old('per_km_rate', $vehicle->per_km_rate) }}" required>
                </div>

                <div class="form-group mb-3">
                    <label for="per_hour_rate" class="form-label">Local Per Hour Rate (₹) *</label>
                    <input type="number" step="10" name="per_hour_rate" id="per_hour_rate" class="form-control" value="{{ old('per_hour_rate', $vehicle->per_hour_rate) }}" required>
                </div>

                <div class="form-group mb-4">
                    <label for="status" class="form-label">Operational Status *</label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="Available" {{ old('status', $vehicle->status) == 'Available' ? 'selected' : '' }}>Available for Booking</option>
                        <option value="On Trip" {{ old('status', $vehicle->status) == 'On Trip' ? 'selected' : '' }}>On Active Trip</option>
                        <option value="Maintenance" {{ old('status', $vehicle->status) == 'Maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                        <option value="Inactive" {{ old('status', $vehicle->status) == 'Inactive' ? 'selected' : '' }}>Inactive / Decommissioned</option>
                    </select>
                </div>

                <div class="d-flex justify-between gap-2">
                    <a href="{{ route('admin.vehicles.show', $vehicle->id) }}" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary flex-1">
                        💾 Update Vehicle
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
