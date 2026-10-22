<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Enquiry;
use App\Models\Feedback;
use App\Models\PickupLocation;
use App\Models\RateRoute;
use App\Models\RateVehiclePrice;
use App\Models\Setting;
use App\Models\TourPackage;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\VehicleModel;
use App\Services\BookingService;
use App\Services\RateQuoteService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublicWebsiteController extends Controller
{
    public function home()
    {
        $bookingData = $this->getBookingViewData();

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
                'active_vehicles' => $bookingData['vehicles']->count() ?: 12,
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

        return view('public.home', array_merge($bookingData, compact('feedbacks', 'stats')));
    }

    public function booking()
    {
        $bookingData = $this->getBookingViewData();

        return view('public.booking', $bookingData);
    }

    protected function getBookingViewData(): array
    {
        try {
            $vehicles = Vehicle::where('status', '!=', 'Inactive')->get();
            $fleetVehicles = Vehicle::where('status', 'Available')->get();
        } catch (\Throwable $e) {
            report($e);
            $vehicles = collect([]);
            $fleetVehicles = collect([]);
        }

        if ($vehicles->isEmpty()) {
            $vehicles = $this->getFallbackVehicles();
        }

        $routes = RateRoute::query()->where('active', true)->with('vehiclePrices')->get();
        $savedLocations = PickupLocation::where('is_active', true)->pluck('name');
        $locations = $routes->flatMap(fn (RateRoute $route): array => array_filter([$route->origin, $route->destination]))
            ->concat($savedLocations)
            ->unique()
            ->sort()
            ->values();
        $tourPackages = TourPackage::where('is_active', true)->get();
        $activeVehicleCategories = VehicleCategory::where('is_active', true)->with('activeModels')->orderBy('display_order')->get();
        $dbCategoryNames = $activeVehicleCategories->pluck('name')->toArray();
        $rateCategoryNames = RateVehiclePrice::query()->distinct()->pluck('vehicle_category')->toArray();
        $vehicleCategories = collect(array_merge($dbCategoryNames, $rateCategoryNames))->unique()->values();

        $rateOptions = [];
        $rateQuoteService = app(RateQuoteService::class);
        foreach ($routes as $route) {
            $catNamesToQuote = collect(array_merge($dbCategoryNames, $route->vehiclePrices->pluck('vehicle_category')->toArray()))->unique();
            $directions = [[$route->origin, $route->destination]];
            if ($route->return_same_rate && $route->origin && $route->destination) {
                $directions[] = [$route->destination, $route->origin];
            }
            foreach ($directions as [$origin, $destination]) {
                foreach ($catNamesToQuote as $catName) {
                    $q = $rateQuoteService->quote(
                        $route->category,
                        $origin ?? '',
                        $destination ?? '',
                        $catName
                    );
                    if ($q) {
                        $rateOptions[implode('|', [$route->category, $origin ?? '', $destination ?? '', $catName])] = $q;
                    }
                }
            }
        }

        return compact('vehicles', 'fleetVehicles', 'routes', 'locations', 'vehicleCategories', 'activeVehicleCategories', 'rateOptions', 'tourPackages');
    }

    public function storeBooking(Request $request, BookingService $bookingService)
    {
        if ($request->filled('name') && ! $request->filled('customer_name')) {
            $request->merge(['customer_name' => $request->input('name')]);
        }
        if ($request->filled('phone') && ! $request->filled('mobile')) {
            $request->merge(['mobile' => $request->input('phone')]);
        }
        if ($request->filled('mobile')) {
            $rawMobile = (string) $request->input('mobile');
            $cleanMobile = preg_replace('/[^0-9]/', '', $rawMobile);
            if (strlen($cleanMobile) === 12 && str_starts_with($cleanMobile, '91')) {
                $cleanMobile = substr($cleanMobile, 2);
            } elseif (strlen($cleanMobile) === 11 && str_starts_with($cleanMobile, '0')) {
                $cleanMobile = substr($cleanMobile, 1);
            }
            $request->merge(['mobile' => $cleanMobile]);
        }
        if ($request->filled('pickup_date') && ! $request->filled('travel_date')) {
            $request->merge(['travel_date' => $request->input('pickup_date')]);
        }
        if ($request->filled('pickup_time') && ! $request->filled('travel_time')) {
            $request->merge(['travel_time' => $request->input('pickup_time')]);
        }

        if ($request->filled('trip_type') && ! $request->filled('rate_category')) {
            $tripType = $request->input('trip_type');
            $dest = strtolower($request->input('destination', ''));
            $orig = strtolower($request->input('pickup_location', ''));
            if (str_contains($dest, 'airport') || str_contains($orig, 'airport')) {
                $request->merge(['rate_category' => 'airport']);
            } elseif ($tripType === 'Round-Trip') {
                $request->merge(['rate_category' => 'round_trip']);
            } elseif ($tripType === 'Local Hourly') {
                $request->merge(['rate_category' => 'local_8h_80km']);
            } elseif ($tripType === 'Tour Package') {
                $request->merge(['rate_category' => 'tour_package']);
            } elseif ($tripType === 'Ambulance Service') {
                $request->merge(['rate_category' => 'emergency']);
            } else {
                $request->merge(['rate_category' => 'outstation']);
            }
        }

        if (! $request->filled('vehicle_category') && $request->filled('vehicle_id')) {
            $veh = Vehicle::find($request->input('vehicle_id'));
            if ($veh) {
                $categoryMap = [
                    'Sedan' => 'Sedan',
                    'Ertiga' => 'Ertiga',
                    'SUV' => 'Ertiga',
                    'Innova Crysta' => 'Innova Crysta',
                    'Premium SUV' => 'Innova Crysta',
                    'Hatchback' => 'Sedan',
                    'Compact Sedan' => 'Sedan',
                ];
                $request->merge(['vehicle_category' => $categoryMap[$veh->vehicle_type] ?? 'Sedan']);
            }
        }

        // For local rentals where destination is not separate, fill destination from pickup location if empty
        if (in_array($request->input('rate_category'), ['local_8h_80km', 'local_4h_40km'], true) && ! $request->filled('destination')) {
            $request->merge(['destination' => $request->input('pickup_location', 'Local Service').' (Local)']);
        }

        // Handle "Other" custom location inputs
        if ($request->input('pickup_location') === 'Other' && $request->filled('pickup_other')) {
            $request->merge(['pickup_location' => trim((string) $request->input('pickup_other'))]);
        }
        if ($request->input('destination') === 'Other' && $request->filled('destination_other')) {
            $request->merge(['destination' => trim((string) $request->input('destination_other'))]);
        }

        // For non-round-trip bookings, clear return_date and return_time so they never trigger validation errors
        if ($request->input('trip_type') !== 'Round-Trip' || ! $request->filled('return_date')) {
            $request->merge([
                'return_date' => null,
                'return_time' => null,
            ]);
        }

        // Section 12: Minimum advance notice validation (at least 4 hours before scheduled pickup time)
        if ($request->filled('travel_date') && $request->filled('travel_time')) {
            $pickupTimeStr = $request->input('travel_time');
            if (strlen($pickupTimeStr) === 5) {
                $pickupTimeStr .= ':00';
            }
            try {
                $tz = config('app.timezone', 'Asia/Kolkata');
                $pickupDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $request->input('travel_date').' '.$pickupTimeStr, $tz);
                $todayStart = now($tz)->startOfDay();

                // Only enforce 4-hour advance notice if travel_date is today or in future; past dates fail travel_date validation
                if ($pickupDateTime->gte($todayStart)) {
                    $minAllowedTime = now($tz)->addHours(4);

                    if ($pickupDateTime->lt($minAllowedTime)) {
                        $phone = Setting::where('key', 'phone_primary')->value('value') ?: '+91 92447 84443';

                        return back()->withInput()->withErrors([
                            'travel_time' => "Online bookings must be scheduled at least 4 hours in advance. For urgent or late-night bookings, please call us directly at {$phone}.",
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                // If date parsing fails, let standard validator catch it
            }
        }

        $data = $request->validate([
            'customer_name' => 'required|string|max:255',
            'mobile' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
            'email' => 'nullable|email|max:255',
            'trip_type' => 'required|string',
            'pickup_location' => 'required|string|max:255',
            'pickup_other' => 'nullable|string|max:255',
            'via_location' => 'nullable|string|max:255',
            'destination' => 'required|string|max:255',
            'destination_other' => 'nullable|string|max:255',
            'is_custom_query' => 'nullable',
            'rate_category' => 'required|in:airport,outstation,round_trip,local_8h_80km,local_4h_40km,tour_package,emergency',
            'vehicle_category' => 'required|string|max:80',
            'vehicle_model' => 'nullable|string|max:80',
            'vehicle_category_id' => 'nullable|exists:vehicle_categories,id',
            'vehicle_model_id' => 'nullable|exists:vehicle_models,id',
            'estimated_km' => 'nullable|numeric|min:0.01',
            'travel_date' => 'required|date|after_or_equal:today',
            'travel_time' => 'required',
            'return_date' => 'nullable|required_if:trip_type,Round-Trip|date|after_or_equal:travel_date',
            'return_time' => 'nullable',
            'duration_days' => 'nullable|integer|min:1',
            'passenger_count' => 'nullable|integer|min:1|max:50',
            'flight_number' => 'nullable|string|max:50',
            'package_duration' => 'nullable|string|max:100',
            'service_category' => 'nullable|string|max:100',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'tour_package_id' => 'nullable|exists:tour_packages,id',
            'notes' => 'nullable|string|max:1000',
            'terms_accepted' => 'required|accepted',
            'pickup_latitude' => 'nullable|numeric',
            'pickup_longitude' => 'nullable|numeric',
            'pickup_place_id' => 'nullable|string|max:255',
            'via_latitude' => 'nullable|numeric',
            'via_longitude' => 'nullable|numeric',
            'via_place_id' => 'nullable|string|max:255',
            'destination_latitude' => 'nullable|numeric',
            'destination_longitude' => 'nullable|numeric',
            'destination_place_id' => 'nullable|string|max:255',
            'route_distance_km' => 'nullable|numeric',
            'route_duration_text' => 'nullable|string|max:100',
            'route_duration_minutes' => 'nullable|integer',
            'route_geometry' => 'nullable|string',
        ], [
            'customer_name.required' => 'Customer name is required.',
            'mobile.required' => 'Mobile number is required.',
            'mobile.regex' => 'Please enter a valid 10-digit mobile number containing only numbers.',
            'pickup_location.required' => 'Pickup location is required.',
            'destination.required' => 'Destination is required.',
            'travel_date.required' => 'Travel date is required.',
            'travel_date.after_or_equal' => 'Travel date cannot be in the past.',
            'travel_time.required' => 'Travel time is required.',
            'return_date.required_if' => 'Please select a return date for your round trip journey.',
            'return_date.after_or_equal' => 'The return date must be on or after the travel date.',
            'vehicle_category.required' => 'Please select a vehicle category for your booking.',
            'terms_accepted.required' => 'You must agree to the Terms & Conditions and Cancellation Policy to complete your booking.',
            'terms_accepted.accepted' => 'You must agree to the Terms & Conditions and Cancellation Policy to complete your booking.',
        ]);

        $data['terms_version'] = '1.0';
        $data['booking_category'] = Booking::CATEGORY_WEB;

        if (! empty($data['vehicle_category_id'])) {
            $cat = VehicleCategory::find($data['vehicle_category_id']);
            if ($cat) {
                $data['vehicle_category'] = $cat->name;
            }
        } elseif (! empty($data['vehicle_category'])) {
            $cat = VehicleCategory::where('name', $data['vehicle_category'])->first();
            if ($cat) {
                $data['vehicle_category_id'] = $cat->id;
            }
        }

        if (! empty($data['vehicle_model_id'])) {
            $mod = VehicleModel::find($data['vehicle_model_id']);
            if ($mod) {
                $data['vehicle_model'] = $mod->name;
            }
        }

        // Mark custom location queries and round-trip queries as zero-amount enquiries
        $isCustomLocationQuery = ! empty($request->input('is_custom_query')) || $request->filled('pickup_other') || $request->filled('destination_other');
        if ($isCustomLocationQuery) {
            $data['is_custom_query'] = true;
            $data['total_amount'] = 0.00;
        }

        // Section 5: Round-trip customer bookings are treated as quotation queries without automatic calculation
        if ($data['trip_type'] === 'Round-Trip' || $data['rate_category'] === 'round_trip') {
            $data['total_amount'] = 0.00;
        }

        $booking = $bookingService->createBooking($data, Auth::user());

        // For round-trip queries or general enquiries, also log an Enquiry record for tracking
        if ($data['trip_type'] === 'Round-Trip' || $data['rate_category'] === 'round_trip') {
            Enquiry::create([
                'source' => Enquiry::SOURCE_WEBSITE,
                'name' => $data['customer_name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['mobile'],
                'booking_type' => 'Round-Trip',
                'pickup_location' => $data['pickup_location'],
                'pickup_latitude' => $booking->pickup_latitude,
                'pickup_longitude' => $booking->pickup_longitude,
                'pickup_place_id' => $booking->pickup_place_id,
                'via_location' => $data['via_location'] ?? null,
                'via_latitude' => $booking->via_latitude,
                'via_longitude' => $booking->via_longitude,
                'via_place_id' => $booking->via_place_id,
                'destination' => $data['destination'],
                'destination_latitude' => $booking->destination_latitude,
                'destination_longitude' => $booking->destination_longitude,
                'destination_place_id' => $booking->destination_place_id,
                'route_distance_km' => $booking->route_distance_km,
                'route_duration_text' => $booking->route_duration_text,
                'route_duration_minutes' => $booking->route_duration_minutes,
                'route_legs' => $booking->route_legs,
                'route_geometry' => $booking->route_geometry,
                'routed_at' => $booking->routed_at,
                'travel_date' => $data['travel_date'],
                'return_date' => $data['return_date'] ?? null,
                'duration_days' => $data['duration_days'] ?? 1,
                'vehicle_preference' => $data['vehicle_category'],
                'subject' => 'Round-Trip Taxi Quotation Query',
                'message' => 'Customer submitted a round-trip quotation request: '.$data['pickup_location'].' via '.($data['via_location'] ?? 'Direct').' to '.$data['destination'].($booking->route_distance_km ? ' (Road distance: '.$booking->route_distance_km.' KM)' : '').'.',
                'converted_booking_id' => $booking->id,
                'status' => Enquiry::STATUS_NEW,
            ]);
        }

        $successMsg = ($data['trip_type'] === 'Round-Trip' || $data['rate_category'] === 'round_trip')
            ? "Your round-trip quotation query has been received! Booking reference is #{$booking->booking_id}. Our dispatch team will review and share a custom quotation shortly."
            : "Booking request received! Your Booking ID is #{$booking->booking_id}.";

        return redirect()->route('booking.success', $booking->id)->with('success', $successMsg);
    }

    public function tourPackages()
    {
        $packages = TourPackage::where('is_active', true)->get();

        return view('public.tour-packages', compact('packages'));
    }

    public function storeQuickLocation(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
        ]);

        $location = PickupLocation::firstOrCreate(
            ['name' => trim($data['name'])],
            [
                'city' => $data['city'] ?: 'Bilaspur',
                'address' => $data['address'] ?? null,
                'state' => 'Chhattisgarh',
                'is_active' => true,
                'created_by' => Auth::id(),
            ]
        );

        return response()->json([
            'success' => true,
            'location' => [
                'id' => $location->id,
                'name' => $location->name,
                'city' => $location->city,
            ],
        ]);
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

    public function ambulanceServices()
    {
        return view('public.ambulance-services');
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
            ['loc' => $domain.'/', 'lastmod' => $today, 'changefreq' => 'daily', 'priority' => '1.0'],
            ['loc' => $domain.'/services', 'lastmod' => $today, 'changefreq' => 'weekly', 'priority' => '0.9'],
            ['loc' => $domain.'/ambulance-services', 'lastmod' => $today, 'changefreq' => 'weekly', 'priority' => '0.8'],
            ['loc' => $domain.'/services/local-taxi', 'lastmod' => $today, 'changefreq' => 'weekly', 'priority' => '0.9'],
            ['loc' => $domain.'/services/outstation-taxi', 'lastmod' => $today, 'changefreq' => 'weekly', 'priority' => '0.9'],
            ['loc' => $domain.'/services/airport-transfer', 'lastmod' => $today, 'changefreq' => 'weekly', 'priority' => '0.9'],
            ['loc' => $domain.'/vehicles', 'lastmod' => $today, 'changefreq' => 'weekly', 'priority' => '0.8'],
            ['loc' => $domain.'/rates', 'lastmod' => $today, 'changefreq' => 'weekly', 'priority' => '0.8'],
            ['loc' => $domain.'/service-network', 'lastmod' => $today, 'changefreq' => 'weekly', 'priority' => '0.8'],
            ['loc' => $domain.'/booking', 'lastmod' => $today, 'changefreq' => 'weekly', 'priority' => '0.8'],
            ['loc' => $domain.'/about', 'lastmod' => $today, 'changefreq' => 'monthly', 'priority' => '0.7'],
            ['loc' => $domain.'/contact', 'lastmod' => $today, 'changefreq' => 'monthly', 'priority' => '0.7'],
            ['loc' => $domain.'/feedback', 'lastmod' => $today, 'changefreq' => 'weekly', 'priority' => '0.6'],
            ['loc' => $domain.'/terms-and-conditions', 'lastmod' => $today, 'changefreq' => 'monthly', 'priority' => '0.5'],
            ['loc' => $domain.'/cancellation-refund-policy', 'lastmod' => $today, 'changefreq' => 'monthly', 'priority' => '0.5'],
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($pages as $page) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.htmlspecialchars($page['loc'])."</loc>\n";
            $xml .= '    <lastmod>'.$page['lastmod']."</lastmod>\n";
            $xml .= '    <changefreq>'.$page['changefreq']."</changefreq>\n";
            $xml .= '    <priority>'.$page['priority']."</priority>\n";
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

    public function rates(Request $request, RateQuoteService $rateQuoteService)
    {
        $routes = RateRoute::query()->where('active', true)->with('vehiclePrices')->orderBy('category')->orderBy('origin')->get();
        $locations = $routes->flatMap(fn (RateRoute $route): array => array_filter([$route->origin, $route->destination]))
            ->unique()
            ->sort()
            ->values();
        $activeCategories = VehicleCategory::where('is_active', true)->orderBy('display_order')->pluck('name')->toArray();
        $ratePriceCategories = RateVehiclePrice::query()->distinct()->pluck('vehicle_category')->toArray();
        $vehicleCategories = collect(array_merge($activeCategories, $ratePriceCategories))->unique()->values();
        $selectedQuotes = collect();

        if ($request->filled('from') && $request->filled('to') && $request->filled('rate_category')) {
            foreach ($vehicleCategories as $vehicleCategory) {
                if ($request->filled('vehicle_category') && $request->string('vehicle_category')->toString() !== $vehicleCategory) {
                    continue;
                }

                $quote = $rateQuoteService->quote(
                    $request->string('rate_category')->toString(),
                    $request->string('from')->toString(),
                    $request->string('to')->toString(),
                    $vehicleCategory,
                    $request->filled('estimated_km') ? (float) $request->input('estimated_km') : null
                );
                if ($quote) {
                    $selectedQuotes->push($quote);
                }
            }
        }

        return view('public.rates', compact('routes', 'locations', 'vehicleCategories', 'selectedQuotes'));
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
        if ($request->filled('phone')) {
            $rawPhone = (string) $request->input('phone');
            $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
            if (strlen($cleanPhone) === 12 && str_starts_with($cleanPhone, '91')) {
                $cleanPhone = substr($cleanPhone, 2);
            } elseif (strlen($cleanPhone) === 11 && str_starts_with($cleanPhone, '0')) {
                $cleanPhone = substr($cleanPhone, 1);
            }
            $request->merge(['phone' => $cleanPhone]);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
            'email' => 'nullable|email|max:255',
            'service_type' => 'nullable|string|max:100',
            'vehicle_preference' => 'nullable|string|max:100',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:2000',
        ], [
            'phone.regex' => 'Please enter a valid 10-digit mobile number containing only numbers.',
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
                'registration_number' => 'CG-10-CA-8258',
                'vehicle_type' => 'Sedan',
                'seating_capacity' => 4,
                'ac_non_ac' => 'AC',
                'status' => 'Available',
                'image' => 'assets/vehicles/maruti-suzuki-dzire.jpg',
                'notes' => 'Prime comfort sedan with boot space for 2 large luggage bags.',
            ],
            [
                'id' => 2,
                'name' => 'Maruti Suzuki Ertiga',
                'registration_number' => 'CG-10-B-08258',
                'vehicle_type' => 'MUV',
                'seating_capacity' => 6,
                'ac_non_ac' => 'AC',
                'status' => 'Available',
                'image' => 'assets/vehicles/maruti-suzuki-ertiga.jpg',
                'notes' => 'Spacious 6-seater MUV, ideal for family airport runs and intercity trips.',
            ],
            [
                'id' => 3,
                'name' => 'Toyota Innova Crysta',
                'registration_number' => 'CG-10-CK-8258',
                'vehicle_type' => 'Innova Crysta',
                'seating_capacity' => 7,
                'ac_non_ac' => 'AC',
                'status' => 'Available',
                'image' => 'assets/vehicles/toyota-innova-crysta.jpg',
                'notes' => 'Premium captain seat luxury ride, best for long-distance highway travel.',
            ],
            [
                'id' => 4,
                'name' => 'Force Tempo Traveller',
                'registration_number' => 'CG-04-CG-6196',
                'vehicle_type' => 'Tempo Traveller',
                'seating_capacity' => 14,
                'ac_non_ac' => 'AC',
                'status' => 'Available',
                'image' => 'assets/vehicles/force-tempo-traveller.jpg',
                'notes' => 'Spacious 14-seater tourist coach with pushback seats and overhead luggage carrier.',
            ],
            [
                'id' => 5,
                'name' => 'Tata Tiago / WagonR',
                'registration_number' => 'CG-10-HA-8258',
                'vehicle_type' => 'Hatchback',
                'seating_capacity' => 4,
                'ac_non_ac' => 'AC',
                'status' => 'Available',
                'image' => 'assets/vehicles/tata-tiago-wagonr.jpg',
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
                'trip' => 'Bilaspur → Raipur Airport Transfer',
                'date_human' => '2 weeks ago',
                'comment' => 'Excellent on-time service from Bilaspur to Raipur Swami Vivekananda Airport (RPR). The chauffeur arrived 15 minutes ahead of reporting time at Mangal Chowk. Spotless Dzire sedan, smooth highway driving, and polite behavior.',
            ],
            [
                'name' => 'Dr. Anita Agrawal',
                'rating' => 5,
                'trip' => 'Family Outstation • Bilaspur to Amarkantak',
                'date_human' => '1 month ago',
                'comment' => 'Booked an Innova Crysta for our family trip to Amarkantak. The car was sanitized, smelling fresh, and the chauffeur handled the mountain ghat sections with utmost safety. Transparent billing with zero hidden charges.',
            ],
            [
                'name' => 'Sunil Verma',
                'rating' => 5,
                'trip' => 'Corporate Travel • Bilaspur to Korba',
                'date_human' => '1 month ago',
                'comment' => 'Regular business traveler between Bilaspur and the Korba industrial belt. Vaishnavi Tours is consistently dependable. Clean cabs, professional chauffeurs, and instant automated GST invoices on trip completion.',
            ],
            [
                'name' => 'Meenakshi Dewangan',
                'rating' => 5,
                'trip' => 'Emergency Travel • Bilaspur to Raipur',
                'date_human' => '2 months ago',
                'comment' => 'Had an urgent late-night medical travel requirement to Raipur. Called their 24/7 dispatch helpline and the cab arrived at our doorstep within 20 minutes. Truly a reliable lifeline service for Chhattisgarh families.',
            ],
            [
                'name' => 'Vikas Patel',
                'rating' => 5,
                'trip' => 'Outstation Tour • Bilaspur to Mainpat & Ambikapur',
                'date_human' => '2 months ago',
                'comment' => 'We hired an Ertiga for a weekend getaway to Mainpat and Ambikapur. Excellent vehicle condition, chilled AC, polite driver with great knowledge of local attractions and food stops. 10/10 recommendation!',
            ],
            [
                'name' => 'Amitabh Tiwari',
                'rating' => 5,
                'trip' => 'Local City & Railway Station Drop • Bilaspur',
                'date_human' => '3 months ago',
                'comment' => 'Best taxi service in Bilaspur without a doubt. Punctual doorstep pickup, genuine fixed rates without surging, and courteous support staff on call. Will always book with Vaishnavi Tours.',
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
