@extends('layouts.admin')

@section('title', 'Customer Enquiries')
@section('page_title', 'Customer Enquiries')

@section('content')
<div class="mb-4 d-flex justify-between align-center flex-wrap gap-2">
    <div>
        <h1 style="font-size: 1.65rem; font-weight: 800; margin: 0; color: var(--dark-900);">Customer Enquiries</h1>
        <p style="color: var(--slate-500); margin: 2px 0 0 0; font-size: 0.875rem;">Manage lead queries and web bookings inquiries submitted via website form.</p>
    </div>
    <div class="d-flex align-center gap-2">
        <span class="badge badge-warning" style="font-size: 0.85rem; padding: 0.4rem 0.8rem;">
            📬 {{ $newCount }} Pending Leads
        </span>
    </div>
</div>

<!-- Filter Bar -->
<div class="card mb-4" style="padding: 1rem 1.25rem;">
    <form method="GET" action="{{ route('admin.enquiries.index') }}" class="d-flex align-center gap-3 flex-wrap">
        <div style="flex: 1; min-width: 220px;">
            <input type="text" name="search" class="form-control" placeholder="Search by name, phone, email, subject..." value="{{ request('search') }}">
        </div>

        <div>
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                @foreach(['New', 'Contacted', 'In Progress', 'Resolved', 'Closed'] as $st)
                    <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        @if(request()->hasAny(['search', 'status']))
            <a href="{{ route('admin.enquiries.index') }}" class="btn btn-outline btn-sm">Reset</a>
        @endif
    </form>
</div>

<!-- Enquiries Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Customer Name</th>
                    <th>Contact Info</th>
                    <th>Service Type</th>
                    <th>Subject & Message</th>
                    <th>Status</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($enquiries as $item)
                    <tr>
                        <td style="white-space: nowrap; font-size: 0.8rem; color: var(--slate-500);">
                            {{ $item->created_at->format('d M Y, h:i A') }}
                        </td>
                        <td>
                            <div style="font-weight: 800; color: var(--dark-900);">{{ $item->name }}</div>
                        </td>
                        <td>
                            <div style="font-size: 0.85rem; font-weight: 700;">
                                <a href="tel:{{ $item->phone }}" style="color: var(--primary-dark);">📞 {{ $item->phone }}</a>
                            </div>
                            @if($item->email)
                                <div style="font-size: 0.75rem; color: var(--slate-500);">✉️ {{ $item->email }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-secondary" style="font-size: 0.75rem;">
                                {{ $item->service_type ?: 'General' }}
                            </span>
                        </td>
                        <td>
                            @if($item->subject)
                                <div style="font-weight: 700; font-size: 0.85rem; color: var(--dark-900);">{{ $item->subject }}</div>
                            @endif
                            <div style="font-size: 0.8rem; color: var(--slate-600); max-width: 320px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ $item->message }}
                            </div>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.enquiries.update', $item->id) }}" style="display: inline-block;">
                                @csrf
                                @method('PUT')
                                <select name="status" onchange="this.form.submit()" class="form-select" style="padding: 0.2rem 0.5rem; font-size: 0.75rem; font-weight: 700; border-radius: 4px; border: 1px solid var(--slate-300);">
                                    @foreach(['New', 'Contacted', 'In Progress', 'Resolved', 'Closed'] as $st)
                                        <option value="{{ $st }}" {{ ($item->status ?? 'New') === $st ? 'selected' : '' }}>{{ $st }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $item->phone) }}?text=Hello%20{{ urlencode($item->name) }},%20thank%20you%20for%20contacting%20Vaishnavi%20Tours!" target="_blank" class="btn btn-outline btn-sm" style="color: #25D366; border-color: #25D366; padding: 0.2rem 0.5rem; font-size: 0.75rem;" title="Chat on WhatsApp">
                                💬 WhatsApp
                            </a>
                            <form method="POST" action="{{ route('admin.enquiries.destroy', $item->id) }}" style="display: inline;" onsubmit="return confirm('Delete this enquiry?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline btn-sm" style="color: #DC2626; border-color: #FCA5A5; padding: 0.2rem 0.4rem; font-size: 0.75rem;">
                                    🗑️
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 3rem; color: var(--slate-400);">
                            No website enquiries found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($enquiries->hasPages())
        <div style="padding: 1rem; border-top: 1px solid var(--slate-200);">
            {{ $enquiries->links() }}
        </div>
    @endif
</div>
@endsection
