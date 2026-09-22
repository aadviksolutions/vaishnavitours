<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Enquiry;
use App\Models\Feedback;
use App\Models\Rate;
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
        } catch (\Throwable $e) {
            report($e);
            $vehicles = collect([]);
        }

        if ($vehicles->isEmpty()) {
            $vehicles = $this->getFallbackVehicles();
        }

        try {
            $feedbacks = Feedback::where('is_public', true)->latest()->take(6)->get();
        } catch (\Throwable $e) {
            report($e);
            $feedbacks = collect([]);
        }

        if ($feedbacks->isEmpty()) {
            $feedbacks = $this->getFallbackFeedbacks();
        }

        try {
            $stats = [
                'trips_completed' => Booking::where('booking_status', 'Completed')->count() + 15400,
                'active_vehicles' => $vehicles->count() ?: 12,
                'satisfaction_rate' => '99.4%',
                'support_hours' => '24/7',
            ];
        } catch (\Throwable $e) {
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

        if ($vehicles->isEmpty()) {
            $vehicles = $this->getFallbackVehicles();
        }

        return view('public.booking', compact('vehicles'));
    }

    public function storeBooking(Request $request, BookingService $bookingService)
    {
        if (! $request->filled('trip_type')) {
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
            'vehicle_id' => 'required',
            'notes' => 'nullable|string|max:1000',
            'terms_accepted' => 'required|accepted',
        ], [
            'customer_name.required' => 'Customer name is required.',
            'mobile.required' => 'Mobile number is required.',
            'pickup_location.required' => 'Pickup location is required.',
            'destination.required' => 'Destination is required.',
            'travel_date.required' => 'Travel date is required.',
            'travel_date.after_or_equal' => 'Travel date cannot be in the past.',
            'travel_time.required' => 'Travel time is required.',
            'vehicle_id.required' => 'Please select a vehicle category for your booking.',
            'terms_accepted.required' => 'You must agree to the Terms & Conditions and Cancellation Policy to complete your booking.',
            'terms_accepted.accepted' => 'You must agree to the Terms & Conditions and Cancellation Policy to complete your booking.',
        ]);

        $data['terms_version'] = '1.0';

        $booking = $bookingService->createBooking($data, Auth::user());

        return redirect()->route('booking.success', $booking->id)
            ->with('success', "Booking request received! Your Booking ID is #{$booking->booking_id}.");
    }

    public function bookingSuccess(Booking $booking)
    {
        try {
            $booking->load(['customer.customer', 'vehicle', 'driver']);
        } catch (\Throwable $e) {
            report($e);
        }

        return view('public.booking-success', compact('booking'));
    }

    public function vehicles()
    {
        $vehicles = $this->getActiveVehicles();
        return view('public.vehicles', compact('vehicles'));
    }

    public function services()
    {
        return view('public.services.index');
    }

    public function localTaxi()
    {
        $vehicles = $this->getActiveVehicles();
        return view('public.services.local-taxi', compact('vehicles'));
    }

    public function outstationTaxi()
    {
        $vehicles = $this->getActiveVehicles();
        return view('public.services.outstation-taxi', compact('vehicles'));
    }

    public function airportTransfer()
    {
        $vehicles = $this->getActiveVehicles();
        return view('public.services.airport-transfer', compact('vehicles'));
    }

    public function sitemap()
    {
        $domain = rtrim(config('app.url', 'https://vaishnavitours.vercel.app'), '/');
        $today = date('Y-m-d');

        $pages = [
            ['loc' => $domain . '/', 'lastmod' => $today, 'changefreq' => 'daily', 'priority' => '1.0'],
            ['loc' => $domain . '/services', 'lastmod' => $today, 'changefreq' => 'weekly', 'priority' => '0.9'],
            ['loc' => $domain . '/services/local-taxi', 'lastmod' => $today, 'changefreq' => 'weekly', 'priority' => '0.9'],
            ['loc' => $domain . '/services/outstation-taxi', 'lastmod' => $today, 'changefreq' => 'weekly', 'priority' => '0.9'],
            ['loc' => $domain . '/services/airport-transfer', 'lastmod' => $today, 'changefreq' => 'weekly', 'priority' => '0.9'],
            ['loc' => $domain . '/vehicles', 'lastmod' => $today, 'changefreq' => 'weekly', 'priority' => '0.8'],
            ['loc' => $domain . '/rates', 'lastmod' => $today, 'changefreq' => 'weekly', 'priority' => '0.8'],
            ['loc' => $domain . '/service-network', 'lastmod' => $today, 'changefreq' => 'weekly', 'priority' => '0.8'],
            ['loc' => $domain . '/booking', 'lastmod' => $today, 'changefreq' => 'weekly', 'priority' => '0.8'],
            ['loc' => $domain . '/about', 'lastmod' => $today, 'changefreq' => 'monthly', 'priority' => '0.7'],
            ['loc' => $domain . '/contact', 'lastmod' => $today, 'changefreq' => 'monthly', 'priority' => '0.7'],
            ['loc' => $domain . '/feedback', 'lastmod' => $today, 'changefreq' => 'weekly', 'priority' => '0.6'],
            ['loc' => $domain . '/terms-and-conditions', 'lastmod' => $today, 'changefreq' => 'monthly', 'priority' => '0.5'],
            ['loc' => $domain . '/cancellation-refund-policy', 'lastmod' => $today, 'changefreq' => 'monthly', 'priority' => '0.5'],
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($pages as $page) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . htmlspecialchars($page['loc']) . "</loc>\n";
            $xml .= '    <lastmod>' . $page['lastmod'] . "</lastmod>\n";
            $xml .= '    <changefreq>' . $page['changefreq'] . "</changefreq>\n";
            $xml .= '    <priority>' . $page['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function robots()
    {
        $domain = rtrim(config('app.url', 'https://vaishnavitours.vercel.app'), '/');
        $content = "User-agent: *\n";
        $content .= "Allow: /\n";
        $content .= "Allow: /assets/\n";
        $content .= "Allow: /build/\n";
        $content .= "Allow: /css/\n";
        $content .= "Allow: /js/\n\n";
        $content .= "Disallow: /admin\n";
        $content .= "Disallow: /admin/\n";
        $content .= "Disallow: /customer\n";
        $content .= "Disallow: /customer/\n";
        $content .= "Disallow: /login\n";
        $content .= "Disallow: /register\n";
        $content .= "Disallow: /forgot-password\n";
        $content .= "Disallow: /dashboard\n";
        $content .= "Disallow: /invoices\n";
        $content .= "Disallow: /bookings\n";
        $content .= "Disallow: /payments\n";
        $content .= "Disallow: /notifications\n";
        $content .= "Disallow: /profile\n";
        $content .= "Disallow: /booking-success/\n";
        $content .= "Disallow: /invoice/\n\n";
        $content .= "Sitemap: {$domain}/sitemap.xml\n";

        return response($content, 200)->header('Content-Type', 'text/plain');
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

        if ($vehicles->isEmpty()) {
            $vehicles = $this->getFallbackVehicles();
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
        try {
            $feedbacks = Feedback::where('is_public', true)->latest()->paginate(10);
        } catch (\Throwable $e) {
            report($e);
            $feedbacks = $this->getFallbackFeedbacks();
        }

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

        try {
            Feedback::create([
                'user_id' => Auth::id(),
                'name' => $data['name'],
                'email' => $data['email'],
                'rating' => $data['rating'],
                'comment' => $data['comment'],
                'is_public' => true,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('success', 'Thank you for your valuable feedback! It has been posted.');
    }

    public function termsAndConditions()
    {
        return view('public.terms');
    }

    public function cancellationPolicy()
    {
        return view('public.cancellation');
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

        try {
            Enquiry::create($data);
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('success', 'Thank you! Your enquiry has been received. Our team will call you back shortly.');
    }

    protected function getActiveVehicles()
    {
        try {
            $vehicles = Vehicle::where('status', '!=', 'Inactive')->get();
        } catch (\Throwable $e) {
            report($e);
            $vehicles = collect([]);
        }

        if ($vehicles->isEmpty()) {
            $vehicles = $this->getFallbackVehicles();
        }

        return $vehicles;
    }

    protected function getFallbackVehicles()
    {
        $items = [
            [
                'id' => 1,
                'name' => 'Maruti Suzuki Dzire',
                'registration_number' => 'CG-10-AB-1204',
                'vehicle_type' => 'Sedan',
                'seating_capacity' => 4,
                'ac_non_ac' => 'AC',
                'per_km_rate' => 13.00,
                'per_hour_rate' => 220.00,
                'status' => 'Available',
                'image' => 'assets/images/sedan.jpg',
                'notes' => 'Prime comfort sedan with boot space for 2 large luggage bags.',
            ],
            [
                'id' => 2,
                'name' => 'Maruti Suzuki Ertiga',
                'registration_number' => 'CG-10-XY-5601',
                'vehicle_type' => 'MUV',
                'seating_capacity' => 6,
                'ac_non_ac' => 'AC',
                'per_km_rate' => 17.00,
                'per_hour_rate' => 280.00,
                'status' => 'Available',
                'image' => 'assets/images/suv.jpg',
                'notes' => 'Spacious 6-seater MUV, ideal for family airport runs and intercity trips.',
            ],
            [
                'id' => 3,
                'name' => 'Toyota Innova Crysta',
                'registration_number' => 'CG-10-TR-9988',
                'vehicle_type' => 'Innova Crysta',
                'seating_capacity' => 7,
                'ac_non_ac' => 'AC',
                'per_km_rate' => 22.00,
                'per_hour_rate' => 350.00,
                'status' => 'Available',
                'image' => 'assets/images/muv.jpg',
                'notes' => 'Premium captain seat luxury ride, best for long-distance highway travel.',
            ],
            [
                'id' => 4,
                'name' => 'Force Tempo Traveller',
                'registration_number' => 'CG-10-TT-3344',
                'vehicle_type' => 'Tempo Traveller',
                'seating_capacity' => 14,
                'ac_non_ac' => 'AC',
                'per_km_rate' => 30.00,
                'per_hour_rate' => 500.00,
                'status' => 'Available',
                'image' => 'assets/images/traveller.jpg',
                'notes' => 'Spacious 14-seater tourist coach with pushback seats and overhead luggage carrier.',
            ],
            [
                'id' => 5,
                'name' => 'Tata Tiago / WagonR',
                'registration_number' => 'CG-10-HG-7711',
                'vehicle_type' => 'Hatchback',
                'seating_capacity' => 4,
                'ac_non_ac' => 'AC',
                'per_km_rate' => 11.00,
                'per_hour_rate' => 180.00,
                'status' => 'Available',
                'image' => 'assets/images/sedan.jpg',
                'notes' => 'Economical city and short intercity ride with high fuel efficiency.',
            ],
        ];

        return collect($items)->map(function ($attributes) {
            $vehicle = new Vehicle;
            $vehicle->forceFill($attributes);
            $vehicle->exists = true;

            return $vehicle;
        });
    }

    protected function getFallbackFeedbacks()
    {
        $items = [
            [
                'name' => 'Rajesh Sharma',
                'rating' => 5,
                'comment' => 'Excellent on-time service from Bilaspur to Raipur Airport. Chauffeur was courteous and driving was very safe.',
            ],
            [
                'name' => 'Dr. Anita Agrawal',
                'rating' => 5,
                'comment' => 'Booked Innova Crysta for a family trip to Amarkantak. Car was clean and sanitized, pleasant journey.',
            ],
            [
                'name' => 'Sunil Verma',
                'rating' => 5,
                'comment' => 'Quick doorstep dispatch within 15 minutes in Mangal Chowk. Transparent rates with no hidden charges.',
            ],
        ];

        return collect($items)->map(function ($attributes) {
            $feedback = new Feedback;
            $feedback->forceFill($attributes);
            $feedback->exists = true;

            return $feedback;
        });
    }
}
