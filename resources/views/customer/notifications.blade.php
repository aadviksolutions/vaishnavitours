@extends('layouts.customer')

@section('title', 'My Notifications')

@section('content')
<div class="d-flex align-center justify-between flex-wrap gap-2 mb-4">
    <div>
        <h1 style="font-size: 1.75rem; font-weight: 800; margin: 0;">🔔 Notifications & Trip Alerts</h1>
        <p style="color: var(--slate-500); margin: 4px 0 0 0;">Real-time journey updates, chauffeur assignments, and payment confirmations.</p>
    </div>
</div>

<div class="card" style="max-width: 800px;">
    @forelse($notifications as $notif)
        <div style="display: flex; gap: 1rem; padding: 1.25rem 0; border-bottom: 1px solid var(--slate-100); {{ !$notif->is_read ? 'background: #f8fafc; margin: 0 -1.5rem; padding-left: 1.5rem; padding-right: 1.5rem;' : '' }}">
            <div style="width: 42px; height: 42px; border-radius: 50%; background: {{ $notif->type === 'booking' ? '#eff6ff' : ($notif->type === 'payment' ? '#f0fdf4' : '#fffbeb') }}; color: {{ $notif->type === 'booking' ? '#2563eb' : ($notif->type === 'payment' ? '#16a34a' : '#d97706') }}; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                @if($notif->type === 'booking') 🚖
                @elseif($notif->type === 'payment') 💳
                @else 🔔
                @endif
            </div>

            <div style="flex: 1;">
                <div class="d-flex align-center justify-between gap-2">
                    <h4 style="margin: 0; font-size: 1rem; font-weight: 700; color: var(--dark-900);">{{ $notif->title }}</h4>
                    <span style="font-size: 0.75rem; color: var(--slate-400); white-space: nowrap;">{{ $notif->created_at->diffForHumans() }}</span>
                </div>
                <p style="margin: 4px 0 8px 0; font-size: 0.875rem; color: var(--slate-600); line-height: 1.5;">
                    {{ $notif->message }}
                </p>

                @if($notif->action_url)
                    <a href="{{ $notif->action_url }}" class="btn btn-primary btn-sm" style="display: inline-block;">
                        View Details →
                    </a>
                @endif
            </div>
        </div>
    @empty
        <div class="text-center py-5">
            <div style="font-size: 3rem; margin-bottom: 0.5rem;">📭</div>
            <h3 style="font-weight: 700; color: var(--slate-700);">No notifications yet</h3>
            <p style="color: var(--slate-500); font-size: 0.875rem;">When you book a cab or when status changes, alerts will show up here.</p>
        </div>
    @endforelse

    @if($notifications->hasPages())
        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
