@extends('layouts.admin')

@section('title', 'Create Tour Package - Vaishnavi Tours')
@section('page_title', 'Add Tour Package')

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.tour-packages.index') }}" style="font-size: 13px; color: var(--slate-600); font-weight: 600; text-decoration: none;">
        ← Back to Tour Packages
    </a>
    <h1 style="font-size: 18px; font-weight: 700; margin: 4px 0 0 0; color: var(--dark-900);">Create Curated Tour Package</h1>
</div>

<div class="card" style="max-width: 650px; background: #fff; border: 1px solid var(--slate-200); border-radius: 8px; padding: 1.5rem;">
    @if($errors->any())
        <div class="alert alert-danger mb-3" style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; padding: 0.75rem 1rem; border-radius: 6px;">
            <ul style="margin: 0; padding-left: 1.25rem; font-size: 13px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.tour-packages.store') }}" method="POST">
        @csrf

        <div class="grid grid-3 gap-2 mb-3">
            <div style="grid-column: span 2;">
                <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">Package Name *</label>
                <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Amarkantak Narmada Darshan Tour" value="{{ old('name') }}" required>
            </div>
            <div>
                <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">Duration (Days) *</label>
                <input type="number" name="duration_days" id="durationDaysInput" class="form-control form-control-sm" min="1" value="{{ old('duration_days', 2) }}" required>
            </div>
        </div>

        <div class="grid grid-2 gap-2 mb-3">
            <div>
                <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">Duration Label (e.g. 2 Days / 1 Night)</label>
                <input type="text" name="duration" id="durationTextInput" class="form-control form-control-sm" placeholder="e.g. 2 Days / 1 Night" value="{{ old('duration', '2 Days / 1 Night') }}">
            </div>
            <div>
                <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">Route Summary</label>
                <input type="text" name="route" class="form-control form-control-sm" placeholder="e.g. Bilaspur ➔ Ratanpur ➔ Amarkantak ➔ Bilaspur" value="{{ old('route', old('route_summary')) }}">
            </div>
        </div>

        <div class="grid grid-3 gap-2 mb-3">
            <div>
                <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">Sedan / Base Price (₹)</label>
                <input type="number" step="0.01" name="sedan_price" class="form-control form-control-sm" placeholder="e.g. 4500.00" value="{{ old('sedan_price', old('base_price')) }}">
            </div>
            <div>
                <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">Ertiga (6+1) Price (₹)</label>
                <input type="number" step="0.01" name="ertiga_price" class="form-control form-control-sm" placeholder="e.g. 6000.00" value="{{ old('ertiga_price') }}">
            </div>
            <div>
                <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">Innova Crysta Price (₹)</label>
                <input type="number" step="0.01" name="innova_price" class="form-control form-control-sm" placeholder="e.g. 7500.00" value="{{ old('innova_price') }}">
            </div>
        </div>

        <div class="mb-3">
            <div class="d-flex justify-between align-center mb-1">
                <label style="font-size: 12px; font-weight: 600; margin: 0;">Destinations Covered (Comma separated)</label>
                <button type="button" id="btnTourDestMap" class="btn btn-xs btn-outline" style="font-size: 11px; padding: 2px 7px; color: #2563EB; border-color: #BFDBFE; background: #EFF6FF; display: inline-flex; align-items: center; gap: 4px;">
                    <x-icon name="map-pin" :size="11" /> Pick on Map
                </button>
            </div>
            <input type="text" name="destinations" id="tourDestInput" class="form-control form-control-sm" placeholder="e.g. Narmada Udgam, Kapildhara, Mai ki Bagiya, Sonmuda" value="{{ old('destinations') }}">
        </div>

        <div class="mb-3">
            <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">Description / Itinerary *</label>
            <textarea name="description" class="form-control form-control-sm" rows="3" placeholder="Day-wise itinerary highlights and inclusions..." required>{{ old('description') }}</textarea>
        </div>

        <div class="mb-3">
            <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">Package Terms & Exclusions</label>
            <textarea name="terms" class="form-control form-control-sm" rows="2" placeholder="Driver night allowance, parking, entry tickets...">{{ old('terms') }}</textarea>
        </div>

        <div class="mb-3">
            <label style="font-size: 12px; display: flex; align-items: center; gap: 6px; cursor: pointer;">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                <span style="font-weight: 600;">Active & visible on public tour packages page</span>
            </label>
        </div>

        <div class="d-flex justify-end gap-2">
            <a href="{{ route('admin.tour-packages.index') }}" class="btn btn-secondary btn-sm">Cancel</a>
            <button type="submit" class="btn btn-primary btn-sm">Create Tour Package</button>
        </div>
    </form>
</div>

<x-location-picker-modal />

<script>
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('btnTourDestMap');
    const destInput = document.getElementById('tourDestInput');

    if (btn && destInput) {
        btn.addEventListener('click', function () {
            if (window.VaishnaviLocationPicker) {
                window.VaishnaviLocationPicker.open({
                    targetLabel: 'Tour Package Destination',
                    inputEl: destInput,
                    onConfirm: function (selected) {
                        if (selected.name) {
                            const cur = destInput.value.trim();
                            if (cur.length > 0) {
                                destInput.value = cur + ', ' + selected.name;
                            } else {
                                destInput.value = selected.name;
                            }
                        }
                    }
                });
            }
        });
    }
});
</script>
@endsection
