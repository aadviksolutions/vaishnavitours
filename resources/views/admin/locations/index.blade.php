@extends('layouts.admin')

@section('title', 'Pickup Locations Management - Vaishnavi Tours')
@section('page_title', 'Pickup Locations')

@section('content')
<div class="d-flex justify-between align-center flex-wrap gap-2 mb-3">
    <div>
        <h1 style="font-size: 18px; font-weight: 700; margin: 0; color: var(--dark-900);">Pickup & Drop Locations Master</h1>
        <p style="color: var(--slate-500); margin: 2px 0 0 0; font-size: 12.5px;">
            Manage standard and custom pickup hubs. New locations created via booking modal or here are immediately selectable.
        </p>
    </div>
    <div class="d-flex align-center gap-2">
        <a href="{{ route('admin.locations.create') }}" class="btn btn-primary btn-sm">
            + Add Pickup Location
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-3" style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; padding: 0.75rem 1rem; border-radius: 6px;">
        ✓ {{ session('success') }}
    </div>
@endif

<div class="card" style="background: #fff; border: 1px solid var(--slate-200); border-radius: 8px;">
    <div class="table-responsive">
        <table class="data-table" style="width: 100%; font-size: 12.5px;">
            <thead>
                <tr>
                    <th>City / Hub Name</th>
                    <th>State</th>
                    <th>Address / Landmark</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($locations as $loc)
                    <tr>
                        <td>
                            <strong style="color: var(--dark-900); font-size: 13px;">{{ $loc->name }}</strong>
                        </td>
                        <td>{{ $loc->state }}</td>
                        <td>{{ $loc->address ?: '-' }}</td>
                        <td>
                            <span class="badge badge-secondary" style="font-size: 10.5px;">
                                {{ $loc->is_airport ? 'Airport' : ($loc->is_railway_station ? 'Railway Station' : 'City Hub') }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $loc->is_active ? 'badge-success' : 'badge-danger' }}">
                                {{ $loc->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <form action="{{ route('admin.locations.destroy', $loc->id) }}" method="POST" onsubmit="return confirm('Delete this location?');" style="display: inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline btn-sm text-red-600 border-red-300" style="padding: 2px 6px; font-size: 11px;">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2.5rem; color: var(--slate-500);">
                            No locations configured yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($locations->hasPages())
        <div style="padding: 0.75rem 1rem; border-top: 1px solid var(--slate-200);">
            {{ $locations->links() }}
        </div>
    @endif
</div>
@endsection
