@extends('layouts.admin')

@section('title', 'Add Pickup Location - Vaishnavi Tours')
@section('page_title', 'Add Location')

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.locations.index') }}" style="font-size: 13px; color: var(--slate-600); font-weight: 600; text-decoration: none;">
        ← Back to Locations
    </a>
    <h1 style="font-size: 18px; font-weight: 700; margin: 4px 0 0 0; color: var(--dark-900);">Add New Pickup Location Hub</h1>
</div>

<div class="card" style="max-width: 500px; background: #fff; border: 1px solid var(--slate-200); border-radius: 8px; padding: 1.5rem;">
    @if($errors->any())
        <div class="alert alert-danger mb-3" style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; padding: 0.75rem 1rem; border-radius: 6px;">
            <ul style="margin: 0; padding-left: 1.25rem; font-size: 13px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.locations.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">City / Area Name *</label>
            <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Korba, Champa, Janjgir, Raigarh" required>
        </div>

        <div class="mb-3">
            <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">State</label>
            <input type="text" name="state" class="form-control form-control-sm" value="Chhattisgarh">
        </div>

        <div class="mb-3">
            <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">Address / Landmark</label>
            <input type="text" name="address" class="form-control form-control-sm" placeholder="e.g. Railway Station Main Exit, Airport Gate 1">
        </div>

        <div class="grid grid-2 gap-2 mb-3">
            <label style="font-size: 12px; display: flex; align-items: center; gap: 4px; cursor: pointer;">
                <input type="checkbox" name="is_airport" value="1">
                <span>Airport Hub</span>
            </label>
            <label style="font-size: 12px; display: flex; align-items: center; gap: 4px; cursor: pointer;">
                <input type="checkbox" name="is_railway_station" value="1">
                <span>Railway Station Hub</span>
            </label>
        </div>

        <div class="d-flex justify-end gap-2">
            <a href="{{ route('admin.locations.index') }}" class="btn btn-secondary btn-sm">Cancel</a>
            <button type="submit" class="btn btn-primary btn-sm">Save Location</button>
        </div>
    </form>
</div>
@endsection
