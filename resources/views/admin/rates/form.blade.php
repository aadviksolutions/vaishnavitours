@extends('layouts.admin')

@section('title', $rateRoute->exists ? 'Edit Rate' : 'Add Rate')
@section('page_title', $rateRoute->exists ? 'Edit Rate' : 'Add Rate')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.rates.index') }}" class="btn btn-outline btn-sm">Back to Rates</a>
    <h1 style="font-size: 1.5rem; font-weight: 800; margin: 0.75rem 0 0;">{{ $rateRoute->exists ? 'Edit rate configuration' : 'New rate configuration' }}</h1>
</div>

@if($errors->any())
    <div class="alert alert-danger mb-4"><ul style="margin: 0; padding-left: 1.25rem;">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<form method="POST" action="{{ $rateRoute->exists ? route('admin.rates.update', $rateRoute) : route('admin.rates.store') }}">
    @csrf
    @if($rateRoute->exists)@method('PUT')@endif

    <div class="card mb-4" style="padding: 1.25rem;">
        <h2 style="font-size: 1.05rem; font-weight: 800; margin-bottom: 1rem;">Route and package</h2>
        <div class="grid grid-2 gap-3">
            <div class="form-group">
                <label for="category" class="form-label">Rate category</label>
                <select name="category" id="category" class="form-select" required>
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}" {{ old('category', $rateRoute->category) === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="origin" class="form-label">Pickup / origin</label>
                <input name="origin" id="origin" class="form-control" value="{{ old('origin', $rateRoute->origin) }}" maxlength="255">
            </div>
            <div class="form-group">
                <label for="destination" class="form-label">Destination</label>
                <input name="destination" id="destination" class="form-control" value="{{ old('destination', $rateRoute->destination) }}" maxlength="255">
            </div>
            <div class="grid grid-2 gap-3">
                <div class="form-group">
                    <label for="km_limit" class="form-label">Included KM</label>
                    <input type="number" min="0" name="km_limit" id="km_limit" class="form-control" value="{{ old('km_limit', $rateRoute->km_limit) }}">
                </div>
                <div class="form-group">
                    <label for="included_hours" class="form-label">Included hours</label>
                    <input type="number" min="0" step="0.25" name="included_hours" id="included_hours" class="form-control" value="{{ old('included_hours', $rateRoute->included_hours) }}">
                </div>
            </div>
            <div class="d-flex gap-3 align-center" style="flex-wrap: wrap;">
                <label><input type="checkbox" name="return_same_rate" value="1" {{ old('return_same_rate', $rateRoute->return_same_rate) ? 'checked' : '' }}> Same rate in reverse direction</label>
                <label><input type="checkbox" name="active" value="1" {{ old('active', $rateRoute->exists ? $rateRoute->active : true) ? 'checked' : '' }}> Active</label>
            </div>
        </div>
    </div>

    @php
        $existingPrices = old('prices', $rateRoute->vehiclePrices->map(fn ($price) => $price->only([
            'vehicle_category', 'base_fare', 'gst_applicable', 'extra_km_rate', 'extra_hour_rate', 'vehicle_rent',
            'per_km_rate', 'night_charge', 'toll_type', 'parking_type', 'border_tax_type', 'driver_food_type',
        ]))->all());
        if (! $existingPrices) {
            $existingPrices = array_map(fn ($category) => ['vehicle_category' => $category], ['Sedan', 'Ertiga', 'Innova Crysta']);
        }
    @endphp

    <div class="card mb-4" style="padding: 1.25rem;">
        <div class="d-flex justify-between align-center mb-3" style="gap: 1rem; flex-wrap: wrap;">
            <h2 style="font-size: 1.05rem; font-weight: 800; margin: 0;">Vehicle rates and applicable charges</h2>
            <button type="button" class="btn btn-outline btn-sm" id="addVehicleRate">Add vehicle rate</button>
        </div>
        <div class="table-responsive">
            <table class="table" id="vehicleRatesTable">
                <thead><tr><th>Vehicle</th><th>Base fare</th><th>Rent</th><th>Per KM</th><th>Extra KM</th><th>Extra hour</th><th>GST</th><th>Toll</th><th>Parking</th><th>Border tax</th><th>Night</th><th>Driver food</th><th></th></tr></thead>
                <tbody>
                    @foreach($existingPrices as $index => $price)
                        <tr>
                            <td><input class="form-control" name="prices[{{ $index }}][vehicle_category]" value="{{ $price['vehicle_category'] ?? '' }}" required></td>
                            @foreach(['base_fare', 'vehicle_rent', 'per_km_rate', 'extra_km_rate', 'extra_hour_rate'] as $field)
                                <td><input class="form-control" type="number" min="0" step="0.01" name="prices[{{ $index }}][{{ $field }}]" value="{{ $price[$field] ?? '' }}" aria-label="{{ str_replace('_', ' ', $field) }}"></td>
                            @endforeach
                            <td><input type="checkbox" name="prices[{{ $index }}][gst_applicable]" value="1" {{ ! empty($price['gst_applicable']) ? 'checked' : '' }} aria-label="GST applicable"></td>
                            @foreach(['toll_type', 'parking_type', 'border_tax_type'] as $field)
                                <td><select class="form-select" name="prices[{{ $index }}][{{ $field }}]" aria-label="{{ str_replace('_', ' ', $field) }}"><option value="">Not set</option><option value="included" {{ ($price[$field] ?? '') === 'included' ? 'selected' : '' }}>Included</option><option value="extra" {{ ($price[$field] ?? '') === 'extra' ? 'selected' : '' }}>Extra</option></select></td>
                            @endforeach
                            <td><input class="form-control" type="number" min="0" step="0.01" name="prices[{{ $index }}][night_charge]" value="{{ $price['night_charge'] ?? '' }}" aria-label="Night charge"></td>
                            <td><select class="form-select" name="prices[{{ $index }}][driver_food_type]" aria-label="Driver food"><option value="">Not set</option><option value="included" {{ ($price['driver_food_type'] ?? '') === 'included' ? 'selected' : '' }}>Included</option><option value="extra" {{ ($price['driver_food_type'] ?? '') === 'extra' ? 'selected' : '' }}>Extra</option></select></td>
                            <td><button type="button" class="btn btn-outline btn-sm removeVehicleRate" aria-label="Remove vehicle rate">Remove</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Save rates</button>
</form>

<template id="vehicleRateRow">
    <tr>
        <td><input class="form-control" data-field="vehicle_category" required></td>
        @foreach(['base_fare', 'vehicle_rent', 'per_km_rate', 'extra_km_rate', 'extra_hour_rate'] as $field)
            <td><input class="form-control" type="number" min="0" step="0.01" data-field="{{ $field }}" aria-label="{{ str_replace('_', ' ', $field) }}"></td>
        @endforeach
        <td><input type="checkbox" value="1" data-field="gst_applicable" aria-label="GST applicable"></td>
        @foreach(['toll_type', 'parking_type', 'border_tax_type'] as $field)
            <td><select class="form-select" data-field="{{ $field }}" aria-label="{{ str_replace('_', ' ', $field) }}"><option value="">Not set</option><option value="included">Included</option><option value="extra">Extra</option></select></td>
        @endforeach
        <td><input class="form-control" type="number" min="0" step="0.01" data-field="night_charge" aria-label="Night charge"></td>
        <td><select class="form-select" data-field="driver_food_type" aria-label="Driver food"><option value="">Not set</option><option value="included">Included</option><option value="extra">Extra</option></select></td>
        <td><button type="button" class="btn btn-outline btn-sm removeVehicleRate" aria-label="Remove vehicle rate">Remove</button></td>
    </tr>
</template>

<script>
    const rateBody = document.querySelector('#vehicleRatesTable tbody');
    document.querySelector('#addVehicleRate').addEventListener('click', () => {
        const index = rateBody.querySelectorAll('tr').length;
        const row = document.querySelector('#vehicleRateRow').content.cloneNode(true);
        row.querySelectorAll('[data-field]').forEach((field) => {
            field.name = `prices[${index}][${field.dataset.field}]`;
        });
        rateBody.append(row);
    });
    rateBody.addEventListener('click', (event) => {
        if (event.target.matches('.removeVehicleRate') && rateBody.querySelectorAll('tr').length > 1) {
            event.target.closest('tr').remove();
            rateBody.querySelectorAll('tr').forEach((row, index) => {
                row.querySelectorAll('[name]').forEach((field) => {
                    field.name = field.name.replace(/prices\[\d+\]/, `prices[${index}]`);
                });
            });
        }
    });
</script>
@endsection