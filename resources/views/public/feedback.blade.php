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
        <!-- Google Reviews Banner -->
        <div class="card mb-4" style="background: linear-gradient(135deg, #FFFFFF 0%, #F8FAFC 100%); border: 1.5px solid #E2E8F0; padding: 1.5rem 1.75rem; border-radius: 14px; box-shadow: 0 4px 15px -3px rgba(15, 23, 42, 0.05);">
            <div class="d-flex justify-between align-center flex-wrap gap-3">
                <div class="d-flex align-center gap-3">
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: #FFFFFF; border: 1px solid #E2E8F0; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.04); flex-shrink: 0;">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" fill="#EA4335"/>
                        </svg>
                    </div>
                    <div>
                        <div class="d-flex align-center gap-2 flex-wrap">
                            <span style="font-size: 1.35rem; font-weight: 800; color: #0F172A;">{{ \App\Models\Setting::get('google_rating', config('vaishnavi.google_rating', '4.9')) }}</span>
                            <div style="display: flex; gap: 2px; color: #FBBC05;">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg width="15" height="15" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                @endfor
                            </div>
                            <span class="badge" style="background: #DEF7EC; color: #03543F; font-size: 0.725rem; font-weight: 700; padding: 2px 8px; border-radius: 4px;">
                                Verified Google Profile
                            </span>
                        </div>
                        <div style="font-size: 0.825rem; color: #64748B; margin-top: 2px;">
                            Over <strong>{{ \App\Models\Setting::get('google_review_count', config('vaishnavi.google_review_count', '160+')) }} verified Google reviews</strong> for {{ config('vaishnavi.google_business_name', 'Vaishnavi Tours Taxi Cab Service Car Rental') }}.
                        </div>
                    </div>
                </div>

                <div class="d-flex align-center gap-2 flex-wrap">
                    <a href="{{ \App\Models\Setting::get('google_write_review_url', config('vaishnavi.google_write_review_url')) }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="btn btn-primary"
                       style="background: #1A73E8; border-color: #1A73E8; font-size: 0.85rem; padding: 0.5rem 1rem;">
                        ✍ Write a Review on Google
                    </a>
                    <a href="{{ \App\Models\Setting::get('google_all_reviews_url', config('vaishnavi.google_all_reviews_url')) }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="btn btn-outline"
                       style="font-size: 0.85rem; padding: 0.5rem 1rem;">
                        Read on Google Maps &rarr;
                    </a>
                </div>
            </div>
        </div>

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
                                        @for($i = 1; $i <= 5; $i++) <x-icon name="star" size="16" style="fill: {{ $i <= $fb->rating ? '#F59E0B' : 'none' }}; color: {{ $i <= $fb->rating ? '#F59E0B' : '#CBD5E1' }};" /> @endfor
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
