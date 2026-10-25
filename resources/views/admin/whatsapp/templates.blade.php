@extends('layouts.admin')

@section('title', 'WhatsApp Notification Templates')
@section('page_title', 'WhatsApp Notifications')

@section('content')
<div class="mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
        <h1 style="font-size:1.65rem;font-weight:800;margin:0;">📱 WhatsApp Notification Templates</h1>
        <p style="color:var(--slate-500);font-size:0.875rem;margin:2px 0 0 0;">Customise every automatic WhatsApp message sent to customers and admins.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.whatsapp.scan') }}" class="btn btn-primary btn-sm">📲 Link WhatsApp (Scan QR)</a>
        <a href="{{ route('admin.whatsapp.logs') }}" class="btn btn-secondary btn-sm">📋 Message Logs</a>
        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#testModal">🚀 Send Test</button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-4">✅ {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger mb-4">❌ {{ session('error') }}</div>
@endif

{{-- Variable Reference Card --}}
<div class="card mb-4" style="background: linear-gradient(135deg,#f0fdf4,#dcfce7); border:1px solid #86efac;">
    <div class="d-flex align-items-start gap-3">
        <span style="font-size:2rem;">💡</span>
        <div>
            <strong>Using Template Variables</strong>
            <p style="font-size:0.85rem;margin:4px 0 0 0;color:#166534;">
                Wrap variable names in double curly braces: <code>&#123;&#123; customer_name &#125;&#125;</code>, <code>&#123;&#123; booking_id &#125;&#125;</code>, <code>&#123;&#123; pickup &#125;&#125;</code>, <code>&#123;&#123; destination &#125;&#125;</code>, <code>&#123;&#123; travel_date &#125;&#125;</code>, <code>&#123;&#123; travel_time &#125;&#125;</code>, <code>&#123;&#123; total_amount &#125;&#125;</code>, <code>&#123;&#123; driver_name &#125;&#125;</code>, <code>&#123;&#123; driver_phone &#125;&#125;</code>, <code>&#123;&#123; vehicle_name &#125;&#125;</code>, <code>&#123;&#123; vehicle_number &#125;&#125;</code>, <code>&#123;&#123; company_phone &#125;&#125;</code> etc.
                Each template card shows which variables are available for that event.
            </p>
        </div>
    </div>
</div>

{{-- Templates Grid --}}
<div class="grid grid-2 gap-4">
@foreach($templates as $template)
<div class="card" id="template-{{ $template->event_key }}">
    <form method="POST" action="{{ route('admin.whatsapp.templates.update', $template) }}">
        @csrf
        @method('PUT')

        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <span style="font-size:1.15rem;font-weight:800;">{{ $template->event_label }}</span>
                <div style="font-size:0.75rem;color:var(--slate-500);margin-top:2px;font-family:monospace;">
                    event: <strong>{{ $template->event_key }}</strong>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                {{-- Active toggle --}}
                <div class="form-check form-switch mb-0" title="Enable/Disable this notification">
                    <input class="form-check-input" type="checkbox" name="is_active" id="active_{{ $template->id }}"
                        value="1" {{ $template->is_active ? 'checked' : '' }}>
                    <label class="form-check-label small" for="active_{{ $template->id }}">Active</label>
                </div>
            </div>
        </div>

        {{-- Send toggles --}}
        <div class="d-flex gap-4 mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="send_to_customer"
                    id="cust_{{ $template->id }}" value="1"
                    {{ $template->send_to_customer ? 'checked' : '' }}>
                <label class="form-check-label small fw-semibold" for="cust_{{ $template->id }}">
                    👤 Send to Customer
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="send_to_admin"
                    id="adm_{{ $template->id }}" value="1"
                    {{ $template->send_to_admin ? 'checked' : '' }}>
                <label class="form-check-label small fw-semibold" for="adm_{{ $template->id }}">
                    🛡️ Send to Admin (copy)
                </label>
            </div>
        </div>

        {{-- Available variables hint --}}
        @if($template->available_variables)
        <div class="mb-2" style="font-size:0.78rem;">
            <span style="color:var(--slate-500);">Available vars: </span>
            @foreach($template->available_variables as $v)
                <code style="background:#f1f5f9;padding:1px 4px;border-radius:3px;margin:1px;">&#123;&#123; {{ $v }} &#125;&#125;</code>
            @endforeach
        </div>
        @endif

        {{-- Template body --}}
        <div class="form-group mb-3">
            <textarea name="template_body" id="body_{{ $template->id }}"
                class="form-control" rows="8"
                style="font-family:monospace;font-size:0.82rem;resize:vertical;"
                placeholder="Enter WhatsApp message template...">{{ old('template_body', $template->template_body) }}</textarea>
        </div>

        <div class="d-flex gap-2 justify-content-end">
            <button type="submit" class="btn btn-primary btn-sm">💾 Save Template</button>
            <button type="button" class="btn btn-outline-secondary btn-sm"
                onclick="resetTemplate({{ $template->id }}, '{{ route('admin.whatsapp.templates.reset', $template) }}')">
                🔄 Reset to Default
            </button>
        </div>
    </form>
</div>
@endforeach
</div>

{{-- Test Message Modal --}}
<div class="modal fade" id="testModal" tabindex="-1" aria-labelledby="testModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.whatsapp.send-test') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="testModalLabel">🚀 Send Test WhatsApp Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label fw-semibold">Phone Number (10 digits, India)</label>
                        <input type="text" name="phone" class="form-control"
                            placeholder="9876543210" maxlength="10" pattern="[0-9]{10}" required>
                        <div class="form-text">Enter 10-digit mobile number without country code.</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label fw-semibold">Message</label>
                        <textarea name="message" class="form-control" rows="5"
                            placeholder="Type your test message here..." required>Hello from Vaishnavi Tours! 🚖 This is a test message from the WhatsApp notification system.</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">📤 Send Test</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Reset confirmation forms (hidden) --}}
<div id="reset-forms" style="display:none;">
    @foreach($templates as $template)
    <form id="reset-form-{{ $template->id }}" method="POST"
        action="{{ route('admin.whatsapp.templates.reset', $template) }}">
        @csrf
    </form>
    @endforeach
</div>

@endsection

@push('scripts')
<script>
function resetTemplate(id, url) {
    if (confirm('Reset this template to its factory default? Your custom edits will be lost.')) {
        document.getElementById('reset-form-' + id).submit();
    }
}
</script>
@endpush
