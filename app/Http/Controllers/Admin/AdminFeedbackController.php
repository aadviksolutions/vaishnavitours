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
        $averageRating = Feedback::avg('rating') ?: 5.0;
        $totalCount = Feedback::count();

        return view('admin.feedback.index', compact('feedbacks', 'averageRating', 'totalCount'));
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
}
