@extends('layouts.admin')

@section('title', 'Complaints & Quality Reviews - Vaishnavi Tours')
@section('page_title', 'Complaints & Reviews')

@section('content')
<div class="d-flex justify-between align-center flex-wrap gap-2 mb-3">
    <div>
        <h1 style="font-size: 18px; font-weight: 700; margin: 0; color: var(--dark-900);">Driver & Vehicle Quality Audit System</h1>
        <p style="color: var(--slate-500); margin: 2px 0 0 0; font-size: 12.5px;">
            Separate quality review streams for Drivers vs Fleet Vehicles. Driver ratings and vehicle ratings are maintained independently.
        </p>
    </div>
    <div class="d-flex align-center gap-2">
        <a href="{{ route('admin.complaints.create') }}" class="btn btn-primary btn-sm">
            + File Complaint / Review
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-3" style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; padding: 0.75rem 1rem; border-radius: 6px;">
        ✓ {{ session('success') }}
    </div>
@endif

<!-- Quality Metrics -->
<div class="grid grid-4 gap-2 mb-3">
    <div class="kpi-card" style="background: #fff; padding: 0.75rem 1rem; border: 1px solid var(--slate-200); border-radius: 6px;">
        <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--slate-500);">Total Issues Logged</span>
        <div style="font-size: 20px; font-weight: 700; color: var(--dark-900);">{{ number_format($stats['total']) }}</div>
    </div>
    <div class="kpi-card" style="background: #fff; padding: 0.75rem 1rem; border: 1px solid var(--slate-200); border-radius: 6px;">
        <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: #2563EB;">Driver Reviews & Issues</span>
        <div style="font-size: 20px; font-weight: 700; color: #2563EB;">{{ number_format($stats['driver']) }}</div>
    </div>
    <div class="kpi-card" style="background: #fff; padding: 0.75rem 1rem; border: 1px solid var(--slate-200); border-radius: 6px;">
        <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: #0D9488;">Vehicle Condition Issues</span>
        <div style="font-size: 20px; font-weight: 700; color: #0D9488;">{{ number_format($stats['vehicle']) }}</div>
    </div>
    <div class="kpi-card" style="background: #fff; padding: 0.75rem 1rem; border: 1px solid var(--slate-200); border-radius: 6px;">
        <span style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: #DC2626;">Pending Resolution</span>
        <div style="font-size: 20px; font-weight: 700; color: #DC2626;">{{ number_format($stats['pending']) }}</div>
    </div>
</div>

<!-- Tabs for Category: Driver vs Vehicle -->
<div class="d-flex align-center gap-1 mb-3" style="border-bottom: 2px solid var(--slate-200);">
    <a href="{{ route('admin.complaints.index') }}" 
       style="padding: 0.45rem 0.85rem; font-size: 12.5px; font-weight: 600; text-decoration: none; border-bottom: 2px solid {{ !request()->has('type') ? '#2563EB' : 'transparent' }}; color: {{ !request()->has('type') ? '#2563EB' : 'var(--slate-600)' }}; margin-bottom: -2px;">
       All Quality Records
    </a>
    <a href="{{ route('admin.complaints.index', ['type' => 'driver']) }}" 
       style="padding: 0.45rem 0.85rem; font-size: 12.5px; font-weight: 600; text-decoration: none; border-bottom: 2px solid {{ request('type') === 'driver' ? '#2563EB' : 'transparent' }}; color: {{ request('type') === 'driver' ? '#2563EB' : 'var(--slate-600)' }}; margin-bottom: -2px;">
       Driver Complaints & Reviews
    </a>
    <a href="{{ route('admin.complaints.index', ['type' => 'vehicle']) }}" 
       style="padding: 0.45rem 0.85rem; font-size: 12.5px; font-weight: 600; text-decoration: none; border-bottom: 2px solid {{ request('type') === 'vehicle' ? '#0D9488' : 'transparent' }}; color: {{ request('type') === 'vehicle' ? '#0D9488' : 'var(--slate-600)' }}; margin-bottom: -2px;">
       Vehicle Condition & Cleanliness Reviews
    </a>
</div>

<!-- Table -->
<div class="card" style="background: #fff; border: 1px solid var(--slate-200); border-radius: 8px;">
    <div class="table-responsive">
        <table class="data-table" style="width: 100%; font-size: 12.5px;">
            <thead>
                <tr>
                    <th>Ref # & Date</th>
                    <th>Audit Stream</th>
                    <th>Target Entity</th>
                    <th>Booking Ref</th>
                    <th>Customer Details</th>
                    <th>Rating</th>
                    <th>Issue Description</th>
                    <th>Status</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($complaints as $c)
                    <tr>
                        <td>
                            <a href="{{ route('admin.complaints.show', $c->id) }}" style="font-weight: 700; color: #2563EB;">
                                {{ $c->complaint_number }}
                            </a>
                            <div style="font-size: 10.5px; color: var(--slate-500);">
                                {{ \Carbon\Carbon::parse($c->complaint_date)->format('d M Y') }}
                            </div>
                        </td>
                        <td>
                            @if($c->complaint_type === 'driver')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800">
                                    Driver Review
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-teal-100 text-teal-800">
                                    Vehicle Review
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($c->complaint_type === 'driver' && $c->driver)
                                <div style="font-weight: 600;">{{ $c->driver->name }}</div>
                                <div style="font-size: 10.5px; color: var(--slate-500);">📞 {{ $c->driver->mobile }}</div>
                            @elseif($c->complaint_type === 'vehicle' && $c->vehicle)
                                <div style="font-weight: 600;">{{ $c->vehicle->name }}</div>
                                <div style="font-size: 10.5px; font-family: monospace; color: #2563EB;">{{ $c->vehicle->registration_number }}</div>
                            @else
                                <span style="color: var(--slate-400);">-</span>
                            @endif
                        </td>
                        <td>
                            @if($c->booking)
                                <a href="{{ route('admin.bookings.show', $c->booking_id) }}" style="font-weight: 600; color: #4F46E5;">
                                    {{ $c->booking->booking_id }}
                                </a>
                            @else
                                <span style="color: var(--slate-400);">-</span>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight: 600;">{{ $c->customer_name }}</div>
                            <div style="font-size: 10.5px; color: var(--slate-500);">{{ $c->customer_phone }}</div>
                        </td>
                        <td>
                            <span style="font-weight: 700; color: {{ $c->rating <= 2 ? '#DC2626' : ($c->rating >= 4 ? '#059669' : '#D97706') }};">
                                ⭐ {{ $c->rating }}/5
                            </span>
                        </td>
                        <td style="max-width: 250px;">
                            <div style="font-weight: 600; color: var(--dark-900);">{{ $c->title }}</div>
                            <div style="font-size: 11px; color: var(--slate-500); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ $c->description }}
                            </div>
                        </td>
                        <td>
                            @php
                                $sBadge = match($c->status) {
                                    'Resolved' => 'bg-emerald-100 text-emerald-800',
                                    'Under Investigation' => 'bg-amber-100 text-amber-800',
                                    'Rejected' => 'bg-slate-100 text-slate-800',
                                    default => 'bg-rose-100 text-rose-800',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $sBadge }}">
                                {{ $c->status }}
                            </span>
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <a href="{{ route('admin.complaints.show', $c->id) }}" class="btn btn-outline btn-sm" style="padding: 2px 7px; font-size: 11px;">
                                Resolve / Audit
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 2.5rem; color: var(--slate-500);">
                            No quality issues or complaints logged yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($complaints->hasPages())
        <div style="padding: 0.75rem 1rem; border-top: 1px solid var(--slate-200);">
            {{ $complaints->links() }}
        </div>
    @endif
</div>
@endsection
