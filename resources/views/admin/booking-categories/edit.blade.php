@extends('layouts.admin')

@section('title', 'Edit Booking Category - Vaishnavi Tours')
@section('page_title', 'Edit Booking Category')

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.booking-categories.index') }}" style="font-size: 13px; color: var(--slate-600); font-weight: 600; text-decoration: none;">
        ← Back to Booking Categories
    </a>
    <h1 style="font-size: 18px; font-weight: 700; margin: 4px 0 0 0; color: var(--dark-900);">Edit Booking Category: {{ $bookingCategory->name }}</h1>
</div>

<div class="card" style="max-width: 560px; background: #fff; border: 1px solid var(--slate-200); border-radius: 8px; padding: 1.5rem;">
    @if($errors->any())
        <div class="alert alert-danger mb-3" style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; padding: 0.75rem 1rem; border-radius: 6px;">
            <ul style="margin: 0; padding-left: 1.25rem; font-size: 13px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.booking-categories.update', $bookingCategory) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">Category Name *</label>
            <input type="text" name="name" class="form-control form-control-sm" value="{{ old('name', $bookingCategory->name) }}" required>
        </div>

        <div class="mb-3">
            <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">Category Code / Identifier</label>
            <input type="text" name="code" class="form-control form-control-sm" value="{{ old('code', $bookingCategory->code) }}" {{ $bookingCategory->is_system ? 'readonly style=background-color:var(--slate-100);' : '' }}>
            @if($bookingCategory->is_system)
                <small style="color: var(--slate-500); font-size: 11px;">Core system codes cannot be renamed.</small>
            @endif
        </div>

        <div class="mb-3">
            <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">Description & Notes</label>
            <textarea name="description" class="form-control form-control-sm" rows="3">{{ old('description', $bookingCategory->description) }}</textarea>
        </div>

        <div class="mb-3">
            <label style="font-size: 12px; display: flex; align-items: center; gap: 6px; cursor: pointer;">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $bookingCategory->is_active) ? 'checked' : '' }}>
                <span style="font-weight: 600;">Active & ready for admin dispatch</span>
            </label>
        </div>

        <div class="d-flex justify-end gap-2">
            <a href="{{ route('admin.booking-categories.index') }}" class="btn btn-secondary btn-sm">Cancel</a>
            <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
        </div>
    </form>
</div>
@endsection
