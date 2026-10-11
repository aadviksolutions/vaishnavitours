@extends('layouts.admin')

@section('title', 'Customer Feedback & Reviews')
@section('page_title', 'Customer Reviews')

@section('content')
<div class="mb-4 d-flex justify-between align-center flex-wrap gap-2">
    <div>
        <h1 style="font-size: 1.65rem; font-weight: 800; margin: 0; color: var(--dark-900);">Customer Feedback & Reviews</h1>
        <p style="color: var(--slate-500); margin: 2px 0 0 0; font-size: 0.875rem;">Moderate customer ratings, testimonials, and Google review satisfaction.</p>
    </div>
    <div class="d-flex align-center gap-2">
        <span class="badge badge-success" style="font-size: 0.9rem; padding: 0.4rem 0.8rem;">
            ⭐ {{ number_format($averageRating, 1) }} / 5.0 ({{ $totalCount }} Reviews)
        </span>
    </div>
</div>

<!-- Feedback Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Customer Name</th>
                    <th>Rating</th>
                    <th>Review Comment</th>
                    <th>Booking Ref</th>
                    <th>Website Display</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($feedbacks as $item)
                    <tr>
                        <td style="white-space: nowrap; font-size: 0.8rem; color: var(--slate-500);">
                            {{ $item->created_at->format('d M Y') }}
                        </td>
                        <td>
                            <div style="font-weight: 800; color: var(--dark-900);">{{ $item->name }}</div>
                            @if($item->email)
                                <small style="color: var(--slate-500);">{{ $item->email }}</small>
                            @endif
                        </td>
                        <td>
                            <div style="color: #F59E0B; font-weight: 800; font-size: 0.9rem;">
                                {{ str_repeat('★', $item->rating) }}{{ str_repeat('☆', 5 - $item->rating) }}
                                <span style="color: var(--dark-900); font-size: 0.8rem; margin-left: 4px;">({{ $item->rating }}/5)</span>
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 0.85rem; color: var(--dark-800); max-width: 320px;">
                                "{{ $item->comment }}"
                            </div>
                        </td>
                        <td>
                            @if($item->booking)
                                <a href="{{ route('admin.bookings.show', $item->booking_id) }}" style="font-weight: 700; color: var(--primary-dark);">
                                    #{{ $item->booking->booking_id }}
                                </a>
                            @else
                                <span style="color: var(--slate-400);">General</span>
                            @endif
                        </td>
                        <td>
                            @if($item->is_public)
                                <span class="badge badge-success">✓ Published</span>
                            @else
                                <span class="badge badge-secondary">Hidden</span>
                            @endif
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <form method="POST" action="{{ route('admin.feedback.toggle-status', $item->id) }}" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-outline btn-sm">
                                    {{ $item->is_public ? 'Hide' : 'Approve & Publish' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.feedback.destroy', $item->id) }}" style="display: inline;" onsubmit="return confirm('Delete this review?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline btn-sm" style="color: #DC2626; border-color: #FCA5A5;">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 3rem; color: var(--slate-400);">
                            No customer feedback recorded yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($feedbacks->hasPages())
        <div style="padding: 1rem; border-top: 1px solid var(--slate-200);">
            {{ $feedbacks->links() }}
        </div>
    @endif
</div>
@endsection
