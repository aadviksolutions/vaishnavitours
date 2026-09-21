@extends('layouts.admin')

@section('title', 'Registered Customers')
@section('page_title', 'Customer Directory')

@section('content')
<div class="d-flex justify-between align-center flex-wrap gap-2 mb-4">
    <div>
        <h1 style="font-size: 1.65rem; font-weight: 800; margin: 0;">Registered Customers & Passengers</h1>
        <p style="color: var(--slate-500); margin: 2px 0 0 0; font-size: 0.875rem;">Directory of registered accounts, frequent travelers, and booking histories.</p>
    </div>
</div>

<!-- Filters & Search -->
<div class="card mb-4" style="padding: 1.25rem;">
    <form method="GET" action="{{ route('admin.customers.index') }}" class="d-flex align-center gap-3 flex-wrap">
        <div style="flex: 1; min-width: 260px;">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name, mobile number, or email..." value="{{ request('search') }}">
        </div>
        <button type="submit" class="btn btn-dark btn-sm">Search Customers</button>
        @if(request()->filled('search'))
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline btn-sm">Reset</a>
        @endif
    </form>
</div>

<!-- Customers Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Passenger Name</th>
                    <th>Mobile</th>
                    <th>Email</th>
                    <th>City / Location</th>
                    <th>Total Bookings</th>
                    <th>Registered Date</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $c)
                    <tr>
                        <td>
                            <div class="d-flex align-center gap-2">
                                <div style="width: 34px; height: 34px; border-radius: 50%; background: var(--dark-900); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem;">
                                    {{ strtoupper(substr($c->name, 0, 2)) }}
                                </div>
                                <div>
                                    <a href="{{ route('admin.customers.show', $c->id) }}" style="font-weight: 800; color: var(--dark-900);">
                                        {{ $c->name }}
                                    </a>
                                </div>
                            </div>
                        </td>
                        <td style="font-weight: 600;">{{ $c->phone }}</td>
                        <td style="color: var(--slate-600);">{{ $c->email }}</td>
                        <td>{{ $c->customer->city ?? 'Bilaspur' }}</td>
                        <td>
                            <span class="badge badge-primary">{{ $c->bookings->count() }} bookings</span>
                        </td>
                        <td style="font-size: 0.85rem; color: var(--slate-500);">{{ $c->created_at->format('d M Y') }}</td>
                        <td style="text-align: right;">
                            <a href="{{ route('admin.customers.show', $c->id) }}" class="btn btn-outline btn-sm" style="padding: 0.2rem 0.6rem; font-size: 0.75rem;">
                                View Profile →
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5" style="color: var(--slate-500);">
                            No customers found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($customers->hasPages())
        <div class="mt-4">
            {{ $customers->links() }}
        </div>
    @endif
</div>
@endsection
