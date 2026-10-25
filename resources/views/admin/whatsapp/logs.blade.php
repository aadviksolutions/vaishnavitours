@extends('layouts.admin')

@section('title', 'WhatsApp Message Logs')
@section('page_title', 'WhatsApp Logs')

@section('content')
<div class="mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
        <h1 style="font-size:1.65rem;font-weight:800;margin:0;">📋 WhatsApp Message Logs</h1>
        <p style="color:var(--slate-500);font-size:0.875rem;margin:2px 0 0 0;">
            All WhatsApp messages sent — to customers and admin copies.
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.whatsapp.scan') }}" class="btn btn-outline-primary btn-sm">📲 Link WhatsApp</a>
        <a href="{{ route('admin.whatsapp.templates') }}" class="btn btn-primary btn-sm">⚙️ Manage Templates</a>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <form method="GET" class="d-flex gap-3 align-items-end flex-wrap">
        <div class="form-group mb-0">
            <label class="form-label small fw-semibold">Status</label>
            <select name="status" class="form-control form-control-sm">
                <option value="">All Statuses</option>
                <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>✅ Sent</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>❌ Failed</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>⏳ Pending</option>
            </select>
        </div>
        <div class="form-group mb-0">
            <label class="form-label small fw-semibold">Event</label>
            <select name="event_key" class="form-control form-control-sm">
                <option value="">All Events</option>
                @foreach($eventKeys as $key)
                    <option value="{{ $key }}" {{ request('event_key') === $key ? 'selected' : '' }}>
                        {{ $key }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group mb-0">
            <label class="form-label small fw-semibold">Recipient</label>
            <select name="recipient_type" class="form-control form-control-sm">
                <option value="">All</option>
                <option value="customer" {{ request('recipient_type') === 'customer' ? 'selected' : '' }}>👤 Customer</option>
                <option value="admin" {{ request('recipient_type') === 'admin' ? 'selected' : '' }}>🛡️ Admin</option>
                <option value="manual" {{ request('recipient_type') === 'manual' ? 'selected' : '' }}>✏️ Manual</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">🔍 Filter</button>
        <a href="{{ route('admin.whatsapp.logs') }}" class="btn btn-secondary btn-sm">Clear</a>
    </form>
</div>

{{-- Stats Row --}}
<div class="grid grid-3 gap-3 mb-4">
    <div class="card text-center" style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1px solid #86efac;">
        <div style="font-size:2rem;font-weight:800;color:#16a34a;">
            {{ $logs->getCollection()->where('status','sent')->count() }} / {{ $logs->total() }}
        </div>
        <div style="color:#15803d;font-size:0.85rem;font-weight:600;">✅ Sent (this page / total)</div>
    </div>
    <div class="card text-center" style="background:linear-gradient(135deg,#fef2f2,#fee2e2);border:1px solid #fca5a5;">
        <div style="font-size:2rem;font-weight:800;color:#dc2626;">
            {{ $logs->getCollection()->where('status','failed')->count() }}
        </div>
        <div style="color:#b91c1c;font-size:0.85rem;font-weight:600;">❌ Failed (this page)</div>
    </div>
    <div class="card text-center" style="background:linear-gradient(135deg,#eff6ff,#dbeafe);border:1px solid #93c5fd;">
        <div style="font-size:2rem;font-weight:800;color:#2563eb;">{{ $logs->total() }}</div>
        <div style="color:#1d4ed8;font-size:0.85rem;font-weight:600;">📊 Total Messages</div>
    </div>
</div>

{{-- Logs Table --}}
<div class="card">
    @if($logs->isEmpty())
        <div class="text-center py-5">
            <div style="font-size:3rem;">📭</div>
            <h3 class="mt-3">No messages logged yet</h3>
            <p style="color:var(--slate-500);">WhatsApp messages will appear here once notifications are sent.</p>
        </div>
    @else
        <div style="overflow-x:auto;">
            <table class="table table-hover" style="font-size:0.85rem;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Event</th>
                        <th>Recipient</th>
                        <th>To Number</th>
                        <th>Status</th>
                        <th>Message Preview</th>
                        <th>Sent At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr>
                        <td style="color:var(--slate-400);font-size:0.75rem;">{{ $log->id }}</td>
                        <td>
                            <code style="font-size:0.75rem;background:#f1f5f9;padding:2px 5px;border-radius:3px;">
                                {{ $log->event_key }}
                            </code>
                        </td>
                        <td>
                            @if($log->recipient_type === 'customer')
                                <span class="badge" style="background:#dbeafe;color:#1e40af;">👤 Customer</span>
                            @elseif($log->recipient_type === 'admin')
                                <span class="badge" style="background:#fef9c3;color:#713f12;">🛡️ Admin</span>
                            @else
                                <span class="badge" style="background:#f3f4f6;color:#374151;">✏️ Manual</span>
                            @endif
                        </td>
                        <td style="font-family:monospace;font-size:0.78rem;">
                            {{ str_replace('@c.us', '', $log->to_number) }}
                        </td>
                        <td>
                            @if($log->status === 'sent')
                                <span class="badge" style="background:#dcfce7;color:#166534;">✅ Sent</span>
                            @elseif($log->status === 'failed')
                                <span class="badge" style="background:#fee2e2;color:#991b1b;" title="{{ $log->response }}">❌ Failed</span>
                            @else
                                <span class="badge" style="background:#fef3c7;color:#92400e;">⏳ Pending</span>
                            @endif
                        </td>
                        <td style="max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                            title="{{ $log->message }}">
                            {{ Str::limit($log->message, 70) }}
                        </td>
                        <td style="white-space:nowrap;color:var(--slate-500);">
                            {{ $log->created_at->format('d M y, h:i A') }}
                        </td>
                        <td>
                            <button class="btn btn-xs btn-outline-primary"
                                onclick="showMsg({{ $log->id }})"
                                style="font-size:0.7rem;padding:2px 8px;">View</button>
                        </td>
                    </tr>
                    {{-- Hidden message row --}}
                    <tr id="msg-{{ $log->id }}" style="display:none;background:#fafafa;">
                        <td colspan="8">
                            <div style="white-space:pre-wrap;font-family:monospace;font-size:0.82rem;padding:8px 12px;background:#f8fafc;border-radius:6px;border:1px solid #e2e8f0;">{{ $log->message }}</div>
                            @if($log->response)
                            <div style="margin-top:6px;font-size:0.75rem;color:var(--slate-500);">
                                <strong>API Response:</strong> {{ $log->response }}
                            </div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $logs->links() }}
        </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
function showMsg(id) {
    const row = document.getElementById('msg-' + id);
    row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
}
</script>
@endpush
