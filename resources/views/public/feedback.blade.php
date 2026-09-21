@extends('layouts.app')

@section('title', 'Customer Feedback & Reviews - Vaishnavi Tours')

@section('content')
<section style="background: var(--dark-900); color: #fff; padding: 3rem 0;">
    <div class="container text-center">
        <span style="color: var(--primary); font-weight: 800; text-transform: uppercase; font-size: 0.85rem;">Traveler Experiences</span>
        <h1 style="color: #fff; font-size: 2.5rem; margin-top: 0.25rem;">Feedback & Reviews</h1>
        <p style="color: var(--slate-300); max-width: 600px; margin: 0.5rem auto 0;">See genuine feedback from families, professionals, and corporate clients across Chhattisgarh.</p>
    </div>
</section>

<section style="padding: 4rem 0 5rem;">
    <div class="container">
        <div class="grid grid-3 gap-4" style="align-items: flex-start;">
            <!-- Reviews Column (2 spans) -->
            <div style="grid-column: span 2;">
                <h2 style="font-size: 1.5rem; margin-bottom: 1.5rem;">Recent Traveler Feedback</h2>

                @if($feedbacks->isEmpty())
                    <div class="card" style="text-align: center; padding: 3rem;">
                        <p style="color: var(--slate-500);">No reviews posted yet. Be the first to share your experience!</p>
                    </div>
                @else
                    <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                        @foreach($feedbacks as $fb)
                            <div class="card" style="padding: 1.5rem;">
                                <div class="d-flex justify-between align-center" style="margin-bottom: 0.5rem;">
                                    <div>
                                        <h3 style="font-size: 1.1rem; font-weight: 800;">{{ $fb->name }}</h3>
                                        <div style="font-size: 0.775rem; color: var(--slate-400);">Verified Journey • {{ $fb->created_at->diffForHumans() }}</div>
                                    </div>
                                    <div style="color: var(--primary); font-size: 1.15rem;">
                                        @for($i = 1; $i <= $fb->rating; $i++) ★ @endfor
                                    </div>
                                </div>
                                <p style="font-size: 0.925rem; color: var(--slate-700); line-height: 1.6;">
                                    "{{ $fb->comment }}"
                                </p>
                            </div>
                        @endforeach
                    </div>

                    <div style="margin-top: 1.5rem;">
                        {{ $feedbacks->links() }}
                    </div>
                @endif
            </div>

            <!-- Submit Feedback Form -->
            <div>
                <div class="card" style="padding: 1.75rem; border: 2px solid var(--primary);">
                    <h3 style="font-size: 1.35rem; font-weight: 800; margin-bottom: 0.5rem;">Leave Your Review</h3>
                    <p style="font-size: 0.85rem; color: var(--slate-500); margin-bottom: 1.5rem;">Tell us about your recent journey with Vaishnavi Tours.</p>

                    <form action="{{ route('feedback.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Your Name</label>
                            <input type="text" name="name" class="form-control" value="{{ Auth::check() ? Auth::user()->name : '' }}" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email Address (Optional)</label>
                            <input type="email" name="email" class="form-control" value="{{ Auth::check() ? Auth::user()->email : '' }}">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Rating</label>
                            <select name="rating" class="form-select" required>
                                <option value="5">★★★★★ - Excellent (5 Stars)</option>
                                <option value="4">★★★★☆ - Very Good (4 Stars)</option>
                                <option value="3">★★★☆☆ - Average (3 Stars)</option>
                                <option value="2">★★☆☆☆ - Poor (2 Stars)</option>
                                <option value="1">★☆☆☆☆ - Terrible (1 Star)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Your Experience / Comments</label>
                            <textarea name="comment" class="form-control" rows="4" placeholder="How was the driver, vehicle cleanliness, and overall trip?" required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            Submit Review
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
