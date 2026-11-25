@extends('layouts.admin')

@section('title', 'Customer Feedback & Google Reviews')
@section('page_title', 'Customer Reviews')

@section('content')
<div class="mb-4 d-flex justify-between align-center flex-wrap gap-2">
    <div>
        <h1 style="font-size: 1.65rem; font-weight: 800; margin: 0; color: var(--dark-900);">Customer Feedback &amp; Google Reviews</h1>
        <p style="color: var(--slate-500); margin: 2px 0 0 0; font-size: 0.875rem;">Moderate verified Google reviews, 5-star customer testimonials, and eliminate spam bot entries.</p>
    </div>
    <div class="d-flex align-center gap-2 flex-wrap">
        <span class="badge badge-success" style="font-size: 0.9rem; padding: 0.45rem 0.85rem;">
            ⭐ {{ number_format($averageRating, 1) }} / 5.0 ({{ $totalCount }} Total Reviews)
        </span>

        <button type="button" class="btn btn-primary btn-sm" onclick="toggleAddReviewForm()" style="font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
            <x-icon name="plus" :size="14" /> Add Google Review
        </button>

        <form method="POST" action="{{ route('admin.feedback.sync-google') }}" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn-outline btn-sm" style="font-weight: 600; color: #1E40AF; border-color: #93C5FD; background: #EFF6FF; display: inline-flex; align-items: center; gap: 4px;">
                <x-icon name="zap" :size="14" /> Sync Official 5-Star Reviews
            </button>
        </form>

        <form method="POST" action="{{ route('admin.feedback.clean-spam') }}" style="display: inline;" onsubmit="return confirm('Clean all spam, scam links, and non-5-star bot submissions?');">
            @csrf
            <button type="submit" class="btn btn-outline btn-sm" style="font-weight: 600; color: #DC2626; border-color: #FCA5A5; background: #FEF2F2; display: inline-flex; align-items: center; gap: 4px;">
                <x-icon name="trash-2" :size="14" /> Clean Spam Reviews
            </button>
        </form>
    </div>
</div>

<!-- Add Verified Google Review Form (Toggleable) -->
<div id="addReviewCard" class="card mb-4" style="display: none; background: #F8FAFC; border: 1.5px solid #CBD5E1; border-radius: 12px; padding: 1.5rem;">
    <div class="d-flex justify-between align-center mb-3">
        <div>
            <h3 style="font-size: 1.15rem; font-weight: 800; margin: 0; color: var(--dark-900);">Add Verified Google Review</h3>
            <p style="font-size: 0.8rem; color: var(--slate-500); margin: 2px 0 0 0;">Add genuine customer feedback directly from your official Google Business Profile.</p>
        </div>
        <button type="button" class="btn btn-outline btn-sm" onclick="toggleAddReviewForm()">&times; Close</button>
    </div>

    <form method="POST" action="{{ route('admin.feedback.store') }}">
        @csrf
        <div class="grid grid-3 gap-3 mb-3">
            <div class="form-group">
                <label class="form-label" style="font-weight: 700;">Customer Name *</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Rajesh Sharma" required>
            </div>

            <div class="form-group">
                <label class="form-label" style="font-weight: 700;">Route / Trip Taken (Optional)</label>
                <input type="text" name="trip" class="form-control" placeholder="e.g. Bilaspur → Raipur Airport Transfer">
            </div>

            <div class="form-group">
                <label class="form-label" style="font-weight: 700;">Rating *</label>
                <select name="rating" class="form-select" required>
                    <option value="5" selected>5 Stars (Recommended)</option>
                    <option value="4">4 Stars</option>
                </select>
            </div>
        </div>

        <div class="grid grid-2 gap-3 mb-3">
            <div class="form-group">
                <label class="form-label" style="font-weight: 700;">Review Source</label>
                <select name="source" class="form-select">
                    <option value="Google" selected>Google Business Profile</option>
                    <option value="Justdial">Justdial</option>
                    <option value="Direct">Direct Customer</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" style="font-weight: 700;">Customer Email (Optional)</label>
                <input type="email" name="email" class="form-control" placeholder="Optional">
            </div>
        </div>

        <div class="form-group mb-3">
            <label class="form-label" style="font-weight: 700;">Customer Review Text *</label>
            <textarea name="comment" class="form-control" rows="3" placeholder="Enter genuine review comment text..." required></textarea>
        </div>

        <div class="d-flex justify-between align-center">
            <label style="display: flex; align-items: center; gap: 8px; font-size: 0.875rem; cursor: pointer;">
                <input type="checkbox" name="is_public" value="1" checked style="width: 16px; height: 16px;">
                <span style="font-weight: 600; color: var(--dark-800);">Publish immediately on website homepage &amp; feedback page</span>
            </label>

            <button type="submit" class="btn btn-primary" style="padding: 0.6rem 1.5rem; font-weight: 700; display: inline-flex; align-items: center; gap: 5px;">
                <x-icon name="check" :size="15" /> Save &amp; Publish Review
            </button>
        </div>
    </form>
</div>

<!-- Feedback Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Customer Name &amp; Trip</th>
                    <th>Source</th>
                    <th>Rating</th>
                    <th>Review Comment</th>
                    <th>Website Display</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($feedbacks as $item)
                    <tr>
                        <td style="white-space: nowrap; font-size: 0.8rem; color: var(--slate-500);">
                            {{ $item->created_at ? $item->created_at->format('d M Y') : 'Recent' }}
                        </td>
                        <td>
                            <div style="font-weight: 800; color: var(--dark-900);">{{ $item->name }}</div>
                            @if($item->trip)
                                <div style="font-size: 0.75rem; color: #0284C7; font-weight: 600;">{{ $item->trip }}</div>
                            @endif
                            @if($item->email)
                                <small style="color: var(--slate-500);">{{ $item->email }}</small>
                            @endif
                        </td>
                        <td>
                            @if($item->source === 'Google')
                                <span class="badge" style="background: #E8F0FE; color: #1A73E8; font-weight: 700; font-size: 0.75rem;">
                                    Google
                                </span>
                            @else
                                <span class="badge" style="background: #F1F5F9; color: #475569; font-size: 0.75rem;">
                                    {{ $item->source ?? 'Website' }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <div style="color: #F59E0B; font-weight: 800; font-size: 0.9rem; white-space: nowrap; display: inline-flex; align-items: center; gap: 2px;">
                                @for($i = 0; $i < $item->rating; $i++)
                                    <x-icon name="star" :size="13" class="text-amber-500 fill-amber-500" />
                                @endfor
                                <span style="color: var(--dark-900); font-size: 0.8rem; margin-left: 4px;">({{ $item->rating }}/5)</span>
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 0.85rem; color: var(--dark-800); max-width: 340px; line-height: 1.5;">
                                "{{ $item->comment }}"
                            </div>
                        </td>
                        <td>
                            @if($item->is_public)
                                <span class="badge badge-success" style="display: inline-flex; align-items: center; gap: 3px;"><x-icon name="check" :size="11" /> Published</span>
                            @else
                                <span class="badge badge-secondary">Pending Approval</span>
                            @endif
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <form method="POST" action="{{ route('admin.feedback.toggle-status', $item->id) }}" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-outline btn-sm">
                                    {{ $item->is_public ? 'Hide' : 'Approve & Publish' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.feedback.destroy', $item->id) }}" style="display: inline;" onsubmit="return confirm('Delete this review permanently?');">
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
                            No customer feedback recorded yet. Click "Sync Official 5-Star Reviews" to load verified Google reviews.
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

<script>
function toggleAddReviewForm() {
    const card = document.getElementById('addReviewCard');
    if (card) {
        card.style.display = card.style.display === 'none' ? 'block' : 'none';
        if (card.style.display === 'block') {
            card.scrollIntoView({ behavior: 'smooth' });
        }
    }
}
</script>
@endsection
