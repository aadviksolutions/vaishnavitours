@extends('layouts.admin')

@section('title', 'WhatsApp QR Scanner & Device Linking')
@section('page_title', 'Link WhatsApp Number')

@section('content')
{{-- Navigation Tabs --}}
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h1 style="font-size: 1.65rem; font-weight: 800; margin: 0;">📱 WhatsApp Device Linking</h1>
        <p style="color: var(--slate-500); font-size: 0.875rem; margin: 2px 0 0 0;">
            Scan the QR code with WhatsApp on your phone to link your sender number.
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.whatsapp.scan') }}" class="btn btn-primary btn-sm">📲 Scan & Connect</a>
        <a href="{{ route('admin.whatsapp.templates') }}" class="btn btn-outline-secondary btn-sm">⚙️ Templates</a>
        <a href="{{ route('admin.whatsapp.logs') }}" class="btn btn-outline-secondary btn-sm">📋 Logs</a>
        <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary btn-sm">🔧 Settings</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-4">✅ {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger mb-4">❌ {{ session('error') }}</div>
@endif

@php
    $session = is_array($session ?? null) ? $session : ['status' => 'disconnected', 'online' => false, 'phone' => null, 'pushName' => null, 'sessionId' => 'default', 'qr' => null];
    $status = $session['status'] ?? 'disconnected';
    $adminPhone = $adminPhone ?? '';
@endphp

<div class="row g-4">
    {{-- Left Column: QR Code & Live Status --}}
    <div class="col-lg-7">
        <div class="card p-4 text-center" style="box-shadow: 0 4px 12px rgba(0,0,0,0.06); border-radius: 12px;">
            <div id="status-badge" class="mb-3">
                @if($status === 'ready')
                    <span class="badge" style="background:#dcfce7;color:#15803d;padding:8px 16px;font-size:0.95rem;font-weight:700;border-radius:20px;">
                        ● Connected & Online
                    </span>
                @elseif($status === 'qr_ready')
                    <span class="badge" style="background:#fef9c3;color:#854d0e;padding:8px 16px;font-size:0.95rem;font-weight:700;border-radius:20px;">
                        ● Ready to Scan QR Code
                    </span>
                @else
                    <span class="badge" style="background:#fee2e2;color:#991b1b;padding:8px 16px;font-size:0.95rem;font-weight:700;border-radius:20px;">
                        ● {{ ucfirst($status) }}
                    </span>
                @endif
            </div>

            {{-- Connected State View --}}
            <div id="connected-box" style="display: {{ $status === 'ready' ? 'block' : 'none' }};">
                <div style="width: 80px; height: 80px; background: #25D366; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 1.25rem;">
                    ✓
                </div>
                <h3 style="font-weight: 800; color: #166534; margin-bottom: 0.25rem;">WhatsApp Account Linked!</h3>
                <p style="color: var(--slate-500); font-size: 0.9rem; margin-bottom: 1.5rem;">
                    All automated customer notifications and admin confirmations will be sent via this number.
                </p>

                <div class="card p-3 mb-4 text-start" style="background: #f8fafc; border: 1px solid #e2e8f0; max-width: 420px; margin: 0 auto;">
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span style="color: var(--slate-500); font-size: 0.85rem;">Connected Phone:</span>
                        <strong id="connected-phone" style="font-family: monospace; font-size: 1rem; color: #0f172a;">
                            {{ !empty($session['phone']) ? '+' . $session['phone'] : 'Not detected' }}
                        </strong>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span style="color: var(--slate-500); font-size: 0.85rem;">Profile / Push Name:</span>
                        <strong id="connected-name" style="color: #0f172a;">
                            {{ !empty($session['pushName']) ? $session['pushName'] : 'Vaishnavi Tours' }}
                        </strong>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span style="color: var(--slate-500); font-size: 0.85rem;">Gateway Session:</span>
                        <strong style="color: #0f172a;">{{ $session['sessionId'] ?? 'default' }}</strong>
                    </div>
                </div>

                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    @if(!empty($session['phone']))
                        <form method="POST" action="{{ route('admin.whatsapp.save-phone') }}">
                            @csrf
                            <input type="hidden" name="phone" value="{{ $session['phone'] }}">
                            <button type="submit" class="btn btn-primary">
                                💾 Save as Admin Number
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('admin.whatsapp.templates') }}" class="btn btn-outline-success">
                        📤 Send Test Message
                    </a>
                    <form method="POST" action="{{ route('admin.whatsapp.logout') }}" onsubmit="return confirm('Disconnect this WhatsApp session? You will need to scan again.');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger">
                            🔴 Disconnect Device
                        </button>
                    </form>
                </div>
            </div>

            {{-- Scanning State View (QR Code) --}}
            <div id="qr-box" style="display: {{ $session['status'] !== 'ready' ? 'block' : 'none' }};">
                <div id="qr-container" style="display: flex; justify-content: center; align-items: center; min-height: 270px; margin-bottom: 1.5rem;">
                    @if(!empty($session['qr']))
                        <img id="qr-image" src="{{ $session['qr'] }}" alt="WhatsApp QR Code"
                             style="max-width: 260px; max-height: 260px; border: 4px solid #fff; border-radius: 8px; box-shadow: 0 4px 14px rgba(0,0,0,0.12);">
                    @else
                        <div id="qr-placeholder" class="p-4" style="background:#f8fafc; border:2px dashed #cbd5e1; border-radius:12px; width:260px; height:260px; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                            <div class="spinner-border text-success mb-2" role="status"></div>
                            <span style="font-size:0.85rem;color:var(--slate-500);" id="qr-status-text">
                                Generating QR Code...
                            </span>
                        </div>
                    @endif
                </div>

                <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                    <span id="polling-indicator" style="width: 8px; height: 8px; background: #25D366; border-radius: 50%; display: inline-block; animation: pulse 1.5s infinite;"></span>
                    <small style="color: var(--slate-500);" id="polling-text">Checking connection status every 3 seconds...</small>
                </div>

                <button type="button" class="btn btn-outline-secondary btn-sm mt-2" onclick="fetchQrStatus(true)">
                    🔄 Refresh QR Code Now
                </button>
            </div>
        </div>
    </div>

    {{-- Right Column: Instructions & Setup Details --}}
    <div class="col-lg-5">
        <div class="card p-4 mb-4" style="border-radius: 12px;">
            <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1.25rem;">
                📲 How to Link WhatsApp
            </h3>

            <ol style="padding-left: 1.25rem; font-size: 0.9rem; line-height: 1.8; color: #334155; margin: 0;">
                <li>Open <strong>WhatsApp</strong> on your mobile phone.</li>
                <li>Tap <strong>Menu (⋮)</strong> on Android or <strong>Settings (⚙️)</strong> on iPhone.</li>
                <li>Tap <strong>Linked Devices</strong>.</li>
                <li>Tap <strong>Link a Device</strong> and point your camera at the QR code on the left.</li>
                <li>Once scanned, this page will <strong>automatically detect</strong> your phone number!</li>
            </ol>
        </div>

        <div class="card p-4" style="border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0;">
            <h4 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 0.75rem;">
                ⚙️ Current Gateway Status
            </h4>
            <div style="font-size: 0.85rem; line-height: 1.6; color: #475569;">
                <div><strong>Gateway URL:</strong> <code>{{ \App\Models\Setting::get('openwa_base_url') ?: config('services.openwa.base_url', 'http://localhost:2785') }}</code></div>
                <div><strong>Admin Phone:</strong> {{ $adminPhone ? '+91 ' . $adminPhone : 'Not configured yet' }}</div>
                <div class="mt-3">
                    <a href="{{ route('admin.settings.index') }}" class="btn btn-link p-0 text-primary" style="font-size: 0.85rem;">
                        Edit Gateway URL in Settings →
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes pulse {
    0% { transform: scale(0.95); opacity: 0.6; }
    50% { transform: scale(1.2); opacity: 1; }
    100% { transform: scale(0.95); opacity: 0.6; }
}
</style>
@endsection

@push('scripts')
<script>
let checkInterval = null;

function fetchQrStatus(forceRefresh = false) {
    const url = '{{ route('admin.whatsapp.qr-status') }}';

    fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
        .then(res => res.json())
        .then(data => {
            const badge = document.getElementById('status-badge');
            const connectedBox = document.getElementById('connected-box');
            const qrBox = document.getElementById('qr-box');
            const qrContainer = document.getElementById('qr-container');

            if (data.status === 'ready') {
                // Device is connected!
                badge.innerHTML = '<span class="badge" style="background:#dcfce7;color:#15803d;padding:8px 16px;font-size:0.95rem;font-weight:700;border-radius:20px;">● Connected & Online</span>';
                qrBox.style.display = 'none';
                connectedBox.style.display = 'block';

                if (data.phone) {
                    document.getElementById('connected-phone').innerText = '+' + data.phone;
                }
                if (data.pushName) {
                    document.getElementById('connected-name').innerText = data.pushName;
                }
            } else if (data.status === 'qr_ready' || data.status === 'created' || data.status === 'initializing') {
                badge.innerHTML = '<span class="badge" style="background:#fef9c3;color:#854d0e;padding:8px 16px;font-size:0.95rem;font-weight:700;border-radius:20px;">● Ready to Scan QR Code</span>';
                connectedBox.style.display = 'none';
                qrBox.style.display = 'block';

                if (data.qr) {
                    qrContainer.innerHTML = `<img id="qr-image" src="${data.qr}" alt="WhatsApp QR Code" style="max-width:260px;max-height:260px;border:4px solid #fff;border-radius:8px;box-shadow:0 4px 14px rgba(0,0,0,0.12);">`;
                }
            } else {
                badge.innerHTML = `<span class="badge" style="background:#fee2e2;color:#991b1b;padding:8px 16px;font-size:0.95rem;font-weight:700;border-radius:20px;">● Offline</span>`;
                connectedBox.style.display = 'none';
                qrBox.style.display = 'block';
                qrContainer.innerHTML = `
                    <div class="p-4 text-center" style="background:#fef2f2; border:2px dashed #f87171; border-radius:12px; width:280px; min-height:220px; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                        <span style="font-size:2rem; margin-bottom:8px;">⚠️</span>
                        <strong style="color:#991b1b; font-size:0.95rem; margin-bottom:4px;">Gateway Service Offline</strong>
                        <p style="font-size:0.8rem; color:#7f1d1d; margin-bottom:8px; line-height:1.4;">
                            ${data.message || 'OpenWA gateway is not running or unreachable at the configured URL.'}
                        </p>
                        <small style="color:#b91c1c; font-size:0.75rem;">Please check your Settings or start the gateway service.</small>
                    </div>`;
            }
        })
        .catch(err => {
            console.error('QR status check error:', err);
        });
}

// Start polling every 3 seconds
document.addEventListener('DOMContentLoaded', () => {
    fetchQrStatus();
    checkInterval = setInterval(fetchQrStatus, 3000);
});
</script>
@endpush
