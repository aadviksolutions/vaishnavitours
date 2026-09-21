@extends('layouts.admin')

@section('title', 'Admin Notifications Desk - Vaishnavi Tours')
@section('page_title', 'Notifications & Alerts')

@section('content')
<div class="d-flex justify-between align-center flex-wrap gap-2 mb-4">
    <div>
        <div class="d-flex align-center gap-2">
            <h1 style="font-size: 1.65rem; font-weight: 800; margin: 0; color: var(--dark-950);">
                🔔 Operations Alerts & Notifications
            </h1>
            @if(isset($unreadCount) && $unreadCount > 0)
                <span class="badge badge-danger" style="font-size: 0.85rem; font-weight: 800;">
                    {{ $unreadCount }} Unread
                </span>
            @endif
        </div>
        <p style="color: var(--slate-500); margin: 2px 0 0 0; font-size: 0.875rem;">
            System alerts, customer booking requests, cancellations, and payment updates.
        </p>
    </div>

    @if($notifications->where('is_read', false)->isNotEmpty())
        <form action="{{ route('admin.notifications.mark-read') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline btn-sm">
                ✓ Mark All as Read
            </button>
        </form>
    @endif
</div>

@if(session('success'))
    <div class="alert alert-success mb-3">
        ✓ {{ session('success') }}
    </div>
@endif

<div class="card" style="max-width: 900px;">
    @forelse($notifications as $notif)
        <div style="display: flex; gap: 1rem; padding: 1.25rem 0; border-bottom: 1px solid var(--slate-100); {{ !$notif->is_read ? 'background: #FFFDF5; margin: 0 -1.5rem; padding-left: 1.5rem; padding-right: 1.5rem;' : '' }}">
            <div style="width: 44px; height: 44px; border-radius: 50%; background: {{ $notif->type === 'booking' ? '#EFF6FF' : ($notif->type === 'payment' ? '#F0FDF4' : '#FFFBEB') }}; color: {{ $notif->type === 'booking' ? '#2563EB' : ($notif->type === 'payment' ? '#16A34A' : '#D97706') }}; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; flex-shrink: 0;">
                @if($notif->type === 'booking') 🚖
                @elseif($notif->type === 'payment') 💳
                @else 🔔
                @endif
            </div>

            <div style="flex: 1;">
                <div class="d-flex align-center justify-between gap-2">
                    <div class="d-flex align-center gap-2">
                        <h4 style="margin: 0; font-size: 1rem; font-weight: 800; color: var(--dark-900);">{{ $notif->title }}</h4>
                        @if(!$notif->is_read)
                            <span class="badge badge-sm badge-danger" style="padding: 0.15rem 0.4rem; font-size: 0.65rem;">NEW</span>
                        @endif
                    </div>
                    <span style="font-size: 0.75rem; color: var(--slate-400); white-space: nowrap;">{{ $notif->created_at->diffForHumans() }}</span>
                </div>

                <p style="margin: 4px 0 8px 0; font-size: 0.875rem; color: var(--slate-600); line-height: 1.5;">
                    {{ $notif->message }}
                </p>

                <div class="d-flex align-center gap-2">
                    @if($notif->action_url)
                        <a href="{{ $notif->action_url }}" class="btn btn-primary btn-sm" style="display: inline-block; font-size: 0.775rem;">
                            View Action →
                        </a>
                    @endif

                    @if(!$notif->is_read)
                        <form action="{{ route('admin.notifications.read', $notif->id) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-outline btn-sm" style="font-size: 0.775rem; padding: 0.2rem 0.6rem;">
                                Mark as read
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-5">
            <div style="font-size: 3rem; margin-bottom: 0.5rem;">🔔</div>
            <h3 style="font-weight: 700; color: var(--slate-700);">No notifications yet</h3>
            <p style="color: var(--slate-500); font-size: 0.875rem;">All operation alerts and dispatch events will be recorded here.</p>
        </div>
    @endforelse

    @if($notifications->hasPages())
        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
