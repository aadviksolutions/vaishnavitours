<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Enquiry;
use App\Models\Feedback;
use App\Models\Rate;
use App\Models\Setting;
use App\Models\Vehicle;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublicWebsiteController extends Controller
{
    public function home()
    {
        try {
            $vehicles = Vehicle::where('status', '!=', 'Inactive')->get();
            $feedbacks = Feedback::where('is_public', true)->latest()->take(6)->get();
            $stats = [
                'trips_completed' => Booking::where('booking_status', 'Completed')->count() + 15400,
                'active_vehicles' => $vehicles->count() ?: 12,
                'satisfaction_rate' => '99.4%',
                'support_hours' => '24/7',
            ];
        } catch (\Throwable $e) {
            report($e);
            $vehicles = collect([]);
            $feedbacks = collect([]);
            $stats = [
                'trips_completed' => 15400,
                'active_vehicles' => 12,
                'satisfaction_rate' => '99.4%',
                'support_hours' => '24/7',
            ];
        }

        return view('public.home', compact('vehicles', 'feedbacks', 'stats'));
    }

    public function booking()
    {
        try {
            $vehicles = Vehicle::where('status', '!=', 'Inactive')->get();
        } catch (\Throwable $e) {
            report($e);
            $vehicles = collect([]);
        }
        return view('public.booking', compact('vehicles'));
    }

    public function storeBooking(Request $request, BookingService $bookingService)
    {
        if (!$request->filled('trip_type')) {
            $request->merge(['trip_type' => 'One-Way']);
        }

        $data = $request->validate([
            'customer_name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'trip_type' => 'required|string',
            'pickup_location' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'travel_date' => 'required|date|after_or_equal:today',
            'travel_time' => 'required',
            'vehicle_id' => 'required|exists:vehicles,id',
            'notes' => 'nullable|string|max:1000',
        ], [
            'customer_name.required' => 'Customer name is required.',
            'mobile.required' => 'Mobile number is required.',
            'pickup_location.required' => 'Pickup location is required.',
            'destination.required' => 'Destination is required.',
            'travel_date.required' => 'Travel date is required.',
            'travel_date.after_or_equal' => 'Travel date cannot be in the past.',
            'travel_time.required' => 'Travel time is required.',
            'vehicle_id.required' => 'Please select a vehicle category for your booking.',
        ]);

        $booking = $bookingService->createBooking($data, Auth::user());

        return redirect()->route('booking.success', $booking->id)
            ->with('success', "Booking request received! Your Booking ID is #{$booking->booking_id}.");
    }

    public function bookingSuccess(Booking $booking)
    {
        $booking->load(['customer.customer', 'vehicle', 'driver']);
        return view('public.booking-success', compact('booking'));
    }

    public function vehicles()
    {
        try {
            $vehicles = Vehicle::where('status', '!=', 'Inactive')->get();
        } catch (\Throwable $e) {
            report($e);
            $vehicles = collect([]);
        }
        return view('public.vehicles', compact('vehicles'));
    }

    public function rates()
    {
        try {
            $rates = Rate::all();
            $vehicles = Vehicle::where('status', '!=', 'Inactive')->get();
        } catch (\Throwable $e) {
            report($e);
            $rates = collect([]);
            $vehicles = collect([]);
        }
        return view('public.rates', compact('rates', 'vehicles'));
    }

    public function network()
    {
        return view('public.network');
    }

    public function about()
    {
        return view('public.about');
    }

    public function feedback()
    {
        $feedbacks = Feedback::where('is_public', true)->latest()->paginate(10);
        return view('public.feedback', compact('feedbacks'));
    }

    public function storeFeedback(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        Feedback::create([
            'user_id' => Auth::id(),
            'name' => $data['name'],
            'email' => $data['email'],
            'rating' => $data['rating'],
            'comment' => $data['comment'],
            'is_public' => true,
        ]);

        return back()->with('success', 'Thank you for your valuable feedback! It has been posted.');
    }

    public function contact()
    {
        return view('public.contact');
    }

    public function enquiry()
    {
        return view('public.enquiry');
    }

    public function storeEnquiry(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'service_type' => 'nullable|string|max:100',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        Enquiry::create($data);

        return back()->with('success', 'Thank you! Your enquiry has been received. Our team will call you back shortly.');
    }
}
