@extends('layouts.admin')

@section('title', 'Rates')
@section('page_title', 'Rates')

@section('content')
<div class="d-flex justify-between align-center mb-4" style="gap: 1rem; flex-wrap: wrap;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 800; margin: 0;">Configured Rates</h1>
        <p style="color: var(--slate-500); margin: 0.35rem 0 0;">Routes, packages, vehicle fares, limits, and applicable charges.</p>
    </div>
    <a href="{{ route('admin.rates.create') }}" class="btn btn-primary">Add Route</a>
</div>

@if(session('success'))
    <div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif

<div class="table-responsive card" style="padding: 1rem;">
    <table class="table">
        <thead>
            <tr>
                <th>Category</th>
                <th>Route / Package</th>
                <th>Limit</th>
                <th>Vehicle Rates</th>
                <th>Return Direction</th>
                <th>Status</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($routes as $route)
                <tr>
                    <td>{{ $categories[$route->category] ?? $route->category }}</td>
                    <td>{{ $route->origin && $route->destination ? $route->origin . ' ↔ ' . $route->destination : 'Any location' }}</td>
                    <td>
                        @if($route->km_limit !== null){{ $route->km_limit }} KM@endif
                        @if($route->included_hours !== null){{ $route->km_limit !== null ? ' / ' : '' }}{{ rtrim(rtrim(number_format((float) $route->included_hours, 2), '0'), '.') }} hr@endif
                        @if($route->km_limit === null && $route->included_hours === null)–@endif
                    </td>
                    <td>
                        @foreach($route->vehiclePrices as $price)
                            <div><strong>{{ $price->vehicle_category }}:</strong>
                                @if($price->base_fare !== null)₹{{ number_format((float) $price->base_fare, 2) }}@endif
                                @if($price->vehicle_rent !== null)₹{{ number_format((float) $price->vehicle_rent, 2) }} rent + ₹{{ number_format((float) $price->per_km_rate, 2) }}/KM@endif
                                @if($price->gst_applicable) + GST@endif
                            </div>
                        @endforeach
                    </td>
                    <td>{{ $route->return_same_rate ? 'Same rate' : 'Separate' }}</td>
                    <td>{{ $route->active ? 'Active' : 'Inactive' }}</td>
                    <td style="text-align: right; white-space: nowrap;">
                        <a href="{{ route('admin.rates.edit', $route) }}" class="btn btn-outline btn-sm" aria-label="Edit {{ $route->origin ?? $categories[$route->category] }}">Edit</a>
                        <form method="POST" action="{{ route('admin.rates.destroy', $route) }}" style="display: inline;" onsubmit="return confirm('Delete this rate route?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-outline btn-sm" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align: center; color: var(--slate-500);">No rate routes configured.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $routes->links() }}
</div>
@endsection