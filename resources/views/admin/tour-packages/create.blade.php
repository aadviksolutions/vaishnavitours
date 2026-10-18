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

        <div class="grid grid-2 gap-2 mb-3">
            <div>
                <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">Package Name *</label>
                <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Amarkantak Narmada Darshan Tour" required>
            </div>
            <div>
                <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">Duration (Days) *</label>
                <input type="number" name="duration_days" class="form-control form-control-sm" min="1" value="2" required>
            </div>
        </div>

        <div class="grid grid-2 gap-2 mb-3">
            <div>
                <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">Route Summary</label>
                <input type="text" name="route_summary" class="form-control form-control-sm" placeholder="e.g. Bilaspur ➔ Ratanpur ➔ Amarkantak ➔ Bilaspur">
            </div>
            <div>
                <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">Base Starting Price (₹) (Optional)</label>
                <input type="number" step="0.01" name="base_price" class="form-control form-control-sm" placeholder="0.00">
            </div>
        </div>

        <div class="mb-3">
            <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">Destinations Covered (Comma separated)</label>
            <input type="text" name="destinations" class="form-control form-control-sm" placeholder="e.g. Narmada Udgam, Kapildhara, Mai ki Bagiya, Sonmuda">
        </div>

        <div class="mb-3">
            <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">Description / Itinerary *</label>
            <textarea name="description" class="form-control form-control-sm" rows="3" placeholder="Day-wise itinerary highlights and inclusions..." required></textarea>
        </div>

        <div class="mb-3">
            <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">Package Terms & Exclusions</label>
            <textarea name="terms" class="form-control form-control-sm" rows="2" placeholder="Driver night allowance, parking, entry tickets..."></textarea>
        </div>

        <div class="mb-3">
            <label style="font-size: 12px; display: flex; align-items: center; gap: 6px; cursor: pointer;">
                <input type="checkbox" name="is_active" value="1" checked>
                <span style="font-weight: 600;">Active & visible on public tour packages page</span>
            </label>
        </div>

        <div class="d-flex justify-end gap-2">
            <a href="{{ route('admin.tour-packages.index') }}" class="btn btn-secondary btn-sm">Cancel</a>
            <button type="submit" class="btn btn-primary btn-sm">Create Tour Package</button>
        </div>
    </form>
</div>
@endsection
