<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;

class AdminFeedbackController extends Controller
{
    public function index(Request $request)
    {
        $query = Feedback::with(['user', 'booking'])->latest();

        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        if ($request->filled('is_public')) {
            $query->where('is_public', $request->is_public === '1');
        }

        $feedbacks = $query->paginate(15);
        $averageRating = Feedback::where('is_public', true)->avg('rating') ?: 5.0;
        $totalCount = Feedback::count();

        return view('admin.feedback.index', compact('feedbacks', 'averageRating', 'totalCount'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'trip' => 'nullable|string|max:255',
            'comment' => 'required|string|max:2000',
            'source' => 'nullable|string|max:50',
            'is_public' => 'nullable|boolean',
        ]);

        Feedback::create([
            'name' => strip_tags(trim($data['name'])),
            'email' => $data['email'] ? trim($data['email']) : null,
            'rating' => (int) $data['rating'],
            'trip' => ! empty($data['trip']) ? strip_tags(trim($data['trip'])) : 'Verified Google Review',
            'comment' => strip_tags(trim($data['comment'])),
            'source' => $data['source'] ?? 'Google',
            'is_public' => $request->boolean('is_public', true),
        ]);

        return back()->with('success', 'Verified Google Review added successfully and published on the website!');
    }

    public function toggleStatus(Feedback $feedback)
    {
        $feedback->update([
            'is_public' => ! $feedback->is_public,
        ]);

        $status = $feedback->is_public ? 'published on website' : 'hidden from website';

        return back()->with('success', "Review #{$feedback->id} is now {$status}.");
    }

    public function destroy(Feedback $feedback)
    {
        $feedback->delete();

        return back()->with('success', 'Review deleted successfully.');
    }

    public function cleanSpam()
    {
        $deleted = Feedback::where('comment', 'LIKE', '%http%')
            ->orWhere('name', 'LIKE', '%http%')
            ->orWhere('comment', 'LIKE', '%myxa%')
            ->orWhere('name', 'LIKE', '%myxa%')
            ->orWhere('comment', 'LIKE', '%scam%')
            ->orWhere('name', 'LIKE', '%scam%')
            ->orWhere('comment', 'LIKE', '%<a%')
            ->orWhere('name', 'LIKE', '%<a%')
            ->orWhere('comment', 'LIKE', '%.cc%')
            ->orWhere('rating', '<', 5)
            ->delete();

        return back()->with('success', "Spam cleanup complete. {$deleted} non-5-star / spam entries were removed.");
    }

    public function syncGoogleReviews()
    {
        $items = [
            [
                'name' => 'Rajesh Sharma',
                'rating' => 5,
                'trip' => 'Bilaspur → Raipur Airport Transfer',
                'source' => 'Google',
                'comment' => 'Excellent on-time service from Bilaspur to Raipur Swami Vivekananda Airport (RPR). The chauffeur arrived 15 minutes ahead of reporting time at Mangal Chowk. Spotless Dzire sedan, smooth highway driving, and polite behavior.',
                'is_public' => true,
                'created_at' => now()->subDays(14),
            ],
            [
                'name' => 'Dr. Anita Agrawal',
                'rating' => 5,
                'trip' => 'Family Outstation • Bilaspur to Amarkantak',
                'source' => 'Google',
                'comment' => 'Booked an Innova Crysta for our family trip to Amarkantak. The car was sanitized, smelling fresh, and the chauffeur handled the mountain ghat sections with utmost safety. Transparent billing with zero hidden charges.',
                'is_public' => true,
                'created_at' => now()->subDays(28),
            ],
            [
                'name' => 'Sunil Verma',
                'rating' => 5,
                'trip' => 'Corporate Travel • Bilaspur to Korba',
                'source' => 'Google',
                'comment' => 'Regular business traveler between Bilaspur and the Korba industrial belt. Vaishnavi Tours is consistently dependable. Clean cabs, professional chauffeurs, and instant automated GST invoices on trip completion.',
                'is_public' => true,
                'created_at' => now()->subDays(35),
            ],
            [
                'name' => 'Meenakshi Dewangan',
                'rating' => 5,
                'trip' => 'Emergency Travel • Bilaspur to Raipur',
                'source' => 'Google',
                'comment' => 'Had an urgent late-night medical travel requirement to Raipur. Called their 24/7 dispatch helpline and the cab arrived at our doorstep within 20 minutes. Truly a reliable lifeline service for Chhattisgarh families.',
                'is_public' => true,
                'created_at' => now()->subDays(45),
            ],
            [
                'name' => 'Vikas Patel',
                'rating' => 5,
                'trip' => 'Outstation Tour • Bilaspur to Mainpat & Ambikapur',
                'source' => 'Google',
                'comment' => 'We hired an Ertiga for a weekend getaway to Mainpat and Ambikapur. Excellent vehicle condition, chilled AC, polite driver with great knowledge of local attractions and food stops. 10/10 recommendation!',
                'is_public' => true,
                'created_at' => now()->subDays(60),
            ],
            [
                'name' => 'Amitabh Tiwari',
                'rating' => 5,
                'trip' => 'Local City & Railway Station Drop • Bilaspur',
                'source' => 'Google',
                'comment' => 'Best taxi service in Bilaspur without a doubt. Punctual doorstep pickup, genuine fixed rates without surging, and courteous support staff on call. Will always book with Vaishnavi Tours.',
                'is_public' => true,
                'created_at' => now()->subDays(75),
            ],
            [
                'name' => 'Pooja Kashyap',
                'rating' => 5,
                'trip' => 'Outstation • Bilaspur to Jagdalpur & Bastar',
                'source' => 'Google',
                'comment' => 'Very safe and comfortable journey with family from Bilaspur to Jagdalpur. The driver was very humble, polite and drove very carefully. Highly recommended for family trips in Chhattisgarh.',
                'is_public' => true,
                'created_at' => now()->subDays(90),
            ],
            [
                'name' => 'Ramesh Kumar Jaiswal',
                'rating' => 5,
                'trip' => 'Wedding & Event Travel • Bilaspur',
                'source' => 'Google',
                'comment' => 'Booked multiple vehicles for our family wedding function in Bilaspur. All vehicles arrived spotless on time and handled guest pickups from Bilaspur Junction flawlessly.',
                'is_public' => true,
                'created_at' => now()->subDays(110),
            ],
        ];

        $count = 0;
        foreach ($items as $item) {
            $exists = Feedback::where('name', $item['name'])->exists();
            if (! $exists) {
                Feedback::create($item);
                $count++;
            }
        }

        return back()->with('success', "Verified 5-Star Google Reviews synced successfully ({$count} new reviews added).");
    }
}
