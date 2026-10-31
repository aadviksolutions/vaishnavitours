<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingCategory;
use App\Models\BookingStatusHistory;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Enquiry;
use App\Models\Payment;
use App\Models\PickupLocation;
use App\Models\RateRoute;
use App\Models\RateVehiclePrice;
use App\Models\TourPackage;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\VehicleModel;
use App\Services\BookingService;
use App\Services\GoogleMapsRoutingService;
use App\Services\PaymentService;
use App\Services\RateQuoteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AdminBookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['customer.customer', 'vehicle', 'driver', 'payments', 'trip']);

        if ($request->filled('status')) {
            $query->where('booking_status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('booking_category', $request->category);
        }

        if ($request->filled('filter')) {
            $f = $request->filter;
            if ($f === 'unassigned') {
                $query->where(function ($q) {
                    $q->whereNull('driver_id')->orWhereNull('vehicle_id');
                })->whereNotIn('booking_status', ['Completed', 'Cancelled']);
            } elseif ($f === 'unassigned_driver') {
                $query->whereNull('driver_id')->whereNotIn('booking_status', ['Completed', 'Cancelled']);
            } elseif ($f === 'unassigned_vehicle') {
                $query->whereNull('vehicle_id')->whereNotIn('booking_status', ['Completed', 'Cancelled']);
            } elseif ($f === 'upcoming') {
                $query->where('travel_date', '>', now()->toDateString())->whereNotIn('booking_status', ['Completed', 'Cancelled']);
            }
        }

        if ($request->filled('date')) {
            $query->whereDate('travel_date', $request->date);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('trip_type')) {
            $query->where('trip_type', $request->trip_type);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('booking_id', 'like', "%{$s}%")
                    ->orWhere('pickup_location', 'like', "%{$s}%")
                    ->orWhere('destination', 'like', "%{$s}%")
                    ->orWhereHas('customer', function ($cq) use ($s) {
                        $cq->where('name', 'like', "%{$s}%")
                            ->orWhere('phone', 'like', "%{$s}%");
                    });
            });
        }

        $bookings = $query->latest()->paginate(15)->withQueryString();

        // Pass only Available vehicles and drivers for assignments
        $availableVehicles = Vehicle::where('status', 'Available')->get();
        $availableDrivers = Driver::where('status', 'Available')->get();
        $allVehicles = Vehicle::where('status', '!=', 'Inactive')->get();
        $allDrivers = Driver::where('status', '!=', 'Inactive')->get();

        return view('admin.bookings.index', compact('bookings', 'availableVehicles', 'availableDrivers', 'allVehicles', 'allDrivers'));
    }

    public function create(Request $request)
    {
        $customers = User::where('role', 'customer')->orderBy('name')->get();
        $vehicles = Vehicle::where('status', 'Available')->get();
        $drivers = Driver::where('status', 'Available')->get();
        $routes = RateRoute::query()->where('active', true)->with('vehiclePrices')->get();
        $locations = $routes->flatMap(fn (RateRoute $route): array => array_filter([$route->origin, $route->destination]))
            ->unique()->sort()->values();
        $activeVehicleCategories = VehicleCategory::where('is_active', true)->with('activeModels')->orderBy('sort_order')->get();
        $dbCategories = $activeVehicleCategories->pluck('name')->toArray();
        $ratePriceCategories = RateVehiclePrice::query()->distinct()->pluck('vehicle_category')->toArray();
        $vehicleCategories = collect(array_merge($dbCategories, $ratePriceCategories))->unique()->values();

        $rateOptions = [];
        $rateQuoteService = app(RateQuoteService::class);
        foreach ($routes as $route) {
            $catNamesToQuote = collect(array_merge($dbCategories, $route->vehiclePrices->pluck('vehicle_category')->toArray()))->unique();
            $directions = [[$route->origin, $route->destination]];
            if ($route->return_same_rate && $route->origin && $route->destination) {
                $directions[] = [$route->destination, $route->origin];
            }
            foreach ($directions as [$origin, $destination]) {
                foreach ($catNamesToQuote as $catName) {
                    $key = implode('|', [$route->category, $origin ?? '', $destination ?? '', $catName]);
                    $q = $rateQuoteService->quote($route->category, $origin ?? '', $destination ?? '', $catName);
                    if ($q) {
                        $rateOptions[$key] = $q;
                    }
                }
            }
        }

        $prefill = null;
        if ($request->filled('from_enquiry')) {
            $prefill = Enquiry::find($request->from_enquiry);
        }

        $pickupLocations = PickupLocation::where('is_active', true)->get();
        $tourPackages = TourPackage::where('is_active', true)->get();
        $bookingCategories = BookingCategory::where('is_active', true)->get();
        $corporateClients = $this->getCorporateClients();

        return view('admin.bookings.create', compact('customers', 'vehicles', 'drivers', 'locations', 'vehicleCategories', 'activeVehicleCategories', 'rateOptions', 'prefill', 'pickupLocations', 'tourPackages', 'bookingCategories', 'corporateClients'));
    }

    public function store(Request $request, BookingService $bookingService)
    {
        $customerMode = $request->input('customer_mode', 'existing');
        if ($request->input('customer_id') === 'new' || ! empty($request->input('new_customer_name'))) {
            $customerMode = 'new';
        }

        if (! $request->filled('booking_category')) {
            $request->merge(['booking_category' => 'MAN']);
        }

        $request->merge([
            'trip_type' => match ($request->input('rate_category')) {
                'airport' => 'Airport Transfer',
                'round_trip' => 'Round-Trip',
                'tour_package' => 'Tour Package',
                'ambulance', 'emergency' => 'Ambulance',
                'local_8h_80km', 'local_4h_40km' => 'Local Hourly',
                'oneway', 'outstation' => 'One-Way',
                default => 'One-Way',
            },
        ]);

        $rules = [
            'booking_category' => 'required|in:CORP,MAN,TP',
            'trip_type' => 'required|string',
            'pickup_location' => 'required|string|max:255',
            'via_location' => 'nullable|string|max:255',
            'destination' => 'required|string|max:255',
            'rate_category' => 'required|in:outstation,oneway,round_trip,tour_package,ambulance,emergency,airport,local_8h_80km,local_4h_40km',
            'vehicle_category' => 'required|string|max:80',
            'vehicle_model' => 'nullable|string|max:80',
            'vehicle_category_id' => 'nullable|exists:vehicle_categories,id',
            'vehicle_model_id' => 'nullable|exists:vehicle_models,id',
            'estimated_km' => 'nullable|required_if:rate_category,round_trip|numeric|min:0.01',
            'travel_date' => 'required|date',
            'travel_time' => 'required',
            'return_date' => 'nullable|date',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'vehicle_assignment_source' => 'nullable|in:own,third_party',
            'third_party_vehicle_number' => 'nullable|string|max:50',
            'third_party_vehicle_name' => 'nullable|string|max:100',
            'third_party_vendor_name' => 'nullable|string|max:255',
            'third_party_driver_name' => 'nullable|string|max:255',
            'third_party_driver_phone' => 'nullable|string|max:50',
            'third_party_agreed_amount' => 'nullable|numeric|min:0',
            'existing_tp_vehicle_id' => 'nullable|exists:vehicles,id',
            'total_amount' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'booking_status' => 'required|string',
            'payment_status' => 'required|string',
            'notes' => 'nullable|string',
            'company_name' => 'nullable|string|max:255',
            'corporate_contact_person' => 'nullable|string|max:255',
            'corporate_gstin' => 'nullable|string|max:50',
            'billing_address' => 'nullable|string',
            'travel_partner_name' => 'nullable|string|max:255',
            'travel_partner_phone' => 'nullable|string|max:50',
            'travel_partner_email' => 'nullable|email|max:255',
            'travel_partner_address' => 'nullable|string|max:500',
            'travel_partner_commission' => 'nullable|numeric|min:0',
            'travel_partner_notes' => 'nullable|string|max:1000',
            'is_custom_fare' => 'nullable|string|max:10',
            'from_enquiry_id' => 'nullable|exists:enquiries,id',
            'tour_package_id' => 'nullable|exists:tour_packages,id',
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
        ];

        if ($customerMode === 'new') {
            $rules['new_customer_name'] = 'required|string|max:255';
            $rules['new_customer_phone'] = 'required|string|max:20';
            $rules['new_customer_email'] = 'nullable|email|max:255';
            $rules['new_customer_city'] = 'nullable|string|max:100';
            $rules['new_customer_address'] = 'nullable|string|max:255';
        } else {
            $rules['customer_id'] = 'required|exists:users,id';
        }

        $data = $request->validate($rules);

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

        $data['is_admin_booking'] = true;
        $data['is_custom_fare'] = $request->boolean('is_custom_fare');
        $data['corporate_company_name'] = $request->input('company_name') ?: $request->input('corporate_company_name');
        $data['corporate_contact_person'] = $request->input('corporate_contact_person');
        $data['corporate_gstin'] = $request->input('corporate_gstin');
        $data['corporate_billing_address'] = $request->input('billing_address') ?: $request->input('corporate_billing_address');
        $data['travel_partner_name'] = $request->input('travel_partner_name');
        $data['travel_partner_phone'] = $request->input('travel_partner_phone');
        $data['travel_partner_email'] = $request->input('travel_partner_email');
        $data['travel_partner_address'] = $request->input('travel_partner_address');
        $data['travel_partner_commission'] = $request->filled('travel_partner_commission') ? (float) $request->input('travel_partner_commission') : null;
        $data['travel_partner_notes'] = $request->input('travel_partner_notes');

        if (isset($data['paid_amount']) && (float) $data['paid_amount'] > (float) $data['total_amount']) {
            throw ValidationException::withMessages([
                'paid_amount' => ['Paid amount cannot exceed total fare amount.'],
            ]);
        }

        if ($customerMode === 'new') {
            $rawPhone = trim($request->input('new_customer_phone'));
            $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
            $phone = ! empty($cleanPhone) ? $cleanPhone : $rawPhone;
            $email = trim($request->input('new_customer_email') ?? '');
            if (! $email) {
                $email = $phone ? $phone.'@vaishnavitours.in' : 'guest_'.uniqid().'@vaishnavitours.in';
            }

            // Check if user already exists with this phone or email
            $existingUser = null;
            if ($phone) {
                $existingUser = User::where('phone', $phone)->first();
            }
            if (! $existingUser && $email) {
                $existingUser = User::where('email', $email)->first();
            }

            if ($existingUser) {
                $user = $existingUser;
                if ($user->name === 'Traveler' || empty($user->name)) {
                    $user->update(['name' => $request->input('new_customer_name')]);
                }
            } else {
                $user = User::create([
                    'name' => $request->input('new_customer_name'),
                    'phone' => $phone,
                    'email' => $email,
                    'password' => bcrypt('Password@123'),
                    'role' => 'customer',
                    'is_active' => true,
                ]);

                Customer::create([
                    'user_id' => $user->id,
                    'city' => $request->input('new_customer_city') ?: $request->input('pickup_location') ?: 'Bilaspur',
                    'address' => $request->input('new_customer_address'),
                    'state' => 'Chhattisgarh',
                    'pincode' => '495001',
                ]);
            }
        } else {
            $user = User::findOrFail($data['customer_id']);
        }

        // Sync or create corporate customer profile for future selection
        if ($data['booking_category'] === 'CORP' && ! empty($data['corporate_company_name'])) {
            $custRec = Customer::where('user_id', $user->id)->first();
            if (! $custRec) {
                $custRec = new Customer(['user_id' => $user->id]);
            }
            $custRec->customer_type = 'corporate';
            $custRec->company_name = $data['corporate_company_name'];
            if (! empty($data['corporate_contact_person'])) {
                $custRec->contact_person = $data['corporate_contact_person'];
            }
            if (! empty($data['corporate_gstin'])) {
                $custRec->gstin = $data['corporate_gstin'];
            }
            if (! empty($data['corporate_billing_address'])) {
                $custRec->billing_address = $data['corporate_billing_address'];
            }
            $custRec->save();
        }

        // Handle Vehicle Assignment: Own vs Third-Party Travel Partner
        $vehicleSource = $request->input('vehicle_assignment_source', 'own');
        if ($vehicleSource === 'third_party') {
            $tpNumber = strtoupper(trim((string) $request->input('third_party_vehicle_number')));
            $existingTpId = $request->input('existing_tp_vehicle_id');

            if (! empty($tpNumber)) {
                $partnerName = $request->input('third_party_vendor_name') ?: $request->input('travel_partner_name');
                $partnerPhone = $request->input('third_party_driver_phone') ?: $request->input('travel_partner_phone');
                $agreedAmount = $request->filled('third_party_agreed_amount') ? (float) $request->input('third_party_agreed_amount') : null;
                $vehName = $request->input('third_party_vehicle_name') ?: 'Partner Cab ('.$tpNumber.')';

                $tpVehicle = Vehicle::where('registration_number', $tpNumber)->first();
                if (! $tpVehicle) {
                    $tpVehicle = Vehicle::create([
                        'name' => $vehName,
                        'registration_number' => $tpNumber,
                        'vehicle_type' => $data['vehicle_category'] ?? 'Sedan',
                        'vehicle_category_id' => $data['vehicle_category_id'] ?? null,
                        'seating_capacity' => 4,
                        'ac_non_ac' => 'AC',
                        'status' => 'Available',
                        'ownership_type' => 'third_party',
                        'is_third_party' => true,
                        'vendor_name' => $partnerName,
                        'vendor_phone' => $partnerPhone,
                        'vendor_agreed_amount' => $agreedAmount,
                        'vendor_balance_amount' => $agreedAmount ?? 0,
                        'vendor_paid_amount' => 0,
                        'vendor_payment_status' => ($agreedAmount && $agreedAmount > 0) ? 'Pending' : 'Settled',
                    ]);
                } else {
                    $tpVehicle->update([
                        'ownership_type' => 'third_party',
                        'is_third_party' => true,
                        'vendor_name' => $tpVehicle->vendor_name ?: $partnerName,
                        'vendor_phone' => $tpVehicle->vendor_phone ?: $partnerPhone,
                    ]);
                }
                $data['vehicle_id'] = $tpVehicle->id;
            } elseif ($existingTpId) {
                $data['vehicle_id'] = (int) $existingTpId;
            }

            if ($request->filled('third_party_driver_name')) {
                $drvName = trim($request->input('third_party_driver_name'));
                $drvPhone = trim($request->input('third_party_driver_phone') ?: '9999999999');
                $driver = Driver::where('mobile', $drvPhone)->first();
                if (! $driver) {
                    $driver = Driver::create([
                        'name' => $drvName,
                        'mobile' => $drvPhone,
                        'license_number' => 'TP-'.strtoupper(substr(md5(uniqid()), 0, 8)),
                        'status' => 'Available',
                        'is_active' => true,
                    ]);
                }
                $data['driver_id'] = $driver->id;
            }
        }

        $booking = $bookingService->createBooking($data, $user);

        // If specific admin overrides were provided:
        $total = (float) $booking->total_amount;
        $paid = (float) ($data['paid_amount'] ?? 0);
        $balance = max(0, $total - $paid);

        $booking->update([
            'booking_status' => $data['booking_status'],
            'payment_status' => $data['payment_status'],
            'total_amount' => $total,
            'paid_amount' => $paid,
            'balance_amount' => $balance,
            'vehicle_id' => $data['vehicle_id'] ?? $booking->vehicle_id,
            'driver_id' => $data['driver_id'] ?? null,
            'travel_partner_name' => $data['travel_partner_name'] ?? null,
            'travel_partner_phone' => $data['travel_partner_phone'] ?? null,
            'travel_partner_email' => $data['travel_partner_email'] ?? null,
            'travel_partner_address' => $data['travel_partner_address'] ?? null,
            'travel_partner_commission' => $data['travel_partner_commission'] ?? null,
            'travel_partner_notes' => $data['travel_partner_notes'] ?? null,
        ]);

        if (! empty($data['vehicle_id'])) {
            Vehicle::where('id', $data['vehicle_id'])->update(['status' => 'On Trip']);
        }

        if ($request->filled('from_enquiry_id')) {
            Enquiry::where('id', $request->from_enquiry_id)->update([
                'status' => 'Converted',
                'converted_booking_id' => $booking->id,
            ]);
        }

        return redirect()->route('admin.bookings.show', $booking->id)
            ->with('success', "Booking #{$booking->booking_id} [{$booking->booking_category}] created successfully for customer {$user->name}!");
    }

    public function show(Booking $booking)
    {
        $booking->load([
            'customer.customer',
            'vehicle',
            'driver',
            'trip',
            'payments',
            'invoice',
            'statusHistories.changer',
            'cancelledBy',
        ]);

        // Filter: Show only vehicles that are Available, plus current assigned vehicle
        $availableVehicles = Vehicle::where('status', 'Available')
            ->orWhere('id', $booking->vehicle_id)
            ->get();

        // Filter: Show only drivers that are Available, plus current assigned driver
        $availableDrivers = Driver::where('status', 'Available')
            ->orWhere('id', $booking->driver_id)
            ->get();

        return view('admin.bookings.show', compact('booking', 'availableVehicles', 'availableDrivers'));
    }

    public function edit(Booking $booking)
    {
        $customers = User::where('role', 'customer')->orderBy('name')->get();
        $vehicles = Vehicle::where('status', '!=', 'Inactive')->get();
        $drivers = Driver::where('status', '!=', 'Inactive')->get();
        $activeVehicleCategories = VehicleCategory::where('is_active', true)->with('activeModels')->orderBy('sort_order')->get();
        $corporateClients = $this->getCorporateClients();

        return view('admin.bookings.edit', compact('booking', 'customers', 'vehicles', 'drivers', 'activeVehicleCategories', 'corporateClients'));
    }

    public function update(Request $request, Booking $booking, PaymentService $paymentService)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:users,id',
            'trip_type' => 'required|string',
            'pickup_location' => 'required|string|max:255',
            'via_location' => 'nullable|string|max:255',
            'destination' => 'required|string|max:255',
            'vehicle_category' => 'nullable|string|max:80',
            'vehicle_model' => 'nullable|string|max:80',
            'vehicle_category_id' => 'nullable|exists:vehicle_categories,id',
            'vehicle_model_id' => 'nullable|exists:vehicle_models,id',
            'travel_date' => 'required|date',
            'travel_time' => 'required',
            'return_date' => 'nullable|date',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'total_amount' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'payment_status' => 'required|string',
            'booking_status' => 'required|string',
            'notes' => 'nullable|string',
            'travel_partner_name' => 'nullable|string|max:255',
            'travel_partner_phone' => 'nullable|string|max:50',
            'travel_partner_email' => 'nullable|email|max:255',
            'travel_partner_address' => 'nullable|string|max:500',
            'travel_partner_commission' => 'nullable|numeric|min:0',
            'travel_partner_notes' => 'nullable|string|max:1000',
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
        ]);

        $totalAmount = (float) $data['total_amount'];
        $paidAmount = (float) $data['paid_amount'];

        if ($paidAmount > $totalAmount) {
            throw ValidationException::withMessages([
                'paid_amount' => ['Paid amount cannot exceed total fare amount.'],
            ]);
        }

        $balance = max(0, $totalAmount - $paidAmount);
        $oldStatus = $booking->booking_status;
        $newStatus = $data['booking_status'];

        $categoryId = $data['vehicle_category_id'] ?? null;
        $categoryName = $data['vehicle_category'] ?? null;
        if ($categoryId && empty($categoryName)) {
            $catObj = VehicleCategory::find($categoryId);
            if ($catObj) {
                $categoryName = $catObj->name;
            }
        } elseif (! empty($categoryName) && empty($categoryId)) {
            $catObj = VehicleCategory::where('name', $categoryName)->first();
            if ($catObj) {
                $categoryId = $catObj->id;
            }
        }

        $modelId = $data['vehicle_model_id'] ?? null;
        $modelName = $data['vehicle_model'] ?? null;
        if ($modelId && empty($modelName)) {
            $modObj = VehicleModel::find($modelId);
            if ($modObj) {
                $modelName = $modObj->name;
            }
        }

        $pricingDetails = $booking->pricing_details ?? [];
        if ($categoryName) {
            $pricingDetails['vehicle_category'] = $categoryName;
        }
        if ($modelName) {
            $pricingDetails['vehicle_model'] = $modelName;
        }

        $updateFields = [
            'customer_id' => $data['customer_id'],
            'trip_type' => $data['trip_type'],
            'pickup_location' => $data['pickup_location'],
            'via_location' => $data['via_location'] ?? null,
            'destination' => $data['destination'],
            'vehicle_category' => $categoryName ?: $booking->vehicle_category,
            'vehicle_model' => $modelName ?: $booking->vehicle_model,
            'vehicle_category_id' => $categoryId ?: $booking->vehicle_category_id,
            'vehicle_model_id' => $modelId ?: $booking->vehicle_model_id,
            'pricing_details' => $pricingDetails,
            'travel_date' => $data['travel_date'],
            'travel_time' => $data['travel_time'],
            'return_date' => $data['return_date'] ?? null,
            'vehicle_id' => $data['vehicle_id'] ?? null,
            'driver_id' => $data['driver_id'] ?? null,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'balance_amount' => $balance,
            'payment_status' => $data['payment_status'],
            'booking_status' => $newStatus,
            'notes' => $data['notes'] ?? null,
            'travel_partner_name' => $data['travel_partner_name'] ?? $booking->travel_partner_name,
            'travel_partner_phone' => $data['travel_partner_phone'] ?? $booking->travel_partner_phone,
            'travel_partner_email' => $data['travel_partner_email'] ?? $booking->travel_partner_email,
            'travel_partner_address' => $data['travel_partner_address'] ?? $booking->travel_partner_address,
            'travel_partner_commission' => array_key_exists('travel_partner_commission', $data) ? $data['travel_partner_commission'] : $booking->travel_partner_commission,
            'travel_partner_notes' => $data['travel_partner_notes'] ?? $booking->travel_partner_notes,
        ];

        if (array_key_exists('pickup_latitude', $data)) {
            $updateFields['pickup_latitude'] = $data['pickup_latitude'];
            $updateFields['pickup_longitude'] = $data['pickup_longitude'] ?? null;
            $updateFields['pickup_place_id'] = $data['pickup_place_id'] ?? null;
        }
        if (array_key_exists('via_latitude', $data)) {
            $updateFields['via_latitude'] = $data['via_latitude'];
            $updateFields['via_longitude'] = $data['via_longitude'] ?? null;
            $updateFields['via_place_id'] = $data['via_place_id'] ?? null;
        }
        if (array_key_exists('destination_latitude', $data)) {
            $updateFields['destination_latitude'] = $data['destination_latitude'];
            $updateFields['destination_longitude'] = $data['destination_longitude'] ?? null;
            $updateFields['destination_place_id'] = $data['destination_place_id'] ?? null;
        }
        if (isset($data['route_distance_km']) && is_numeric($data['route_distance_km'])) {
            $updateFields['route_distance_km'] = (float) $data['route_distance_km'];
            $updateFields['route_duration_text'] = $data['route_duration_text'] ?? $booking->route_duration_text;
            $updateFields['route_duration_minutes'] = isset($data['route_duration_minutes']) ? (int) $data['route_duration_minutes'] : $booking->route_duration_minutes;
            $updateFields['route_geometry'] = $data['route_geometry'] ?? $booking->route_geometry;
            $updateFields['routed_at'] = now();
        }

        if ($request->filled('company_name') || $request->filled('corporate_company_name')) {
            $updateFields['corporate_company_name'] = $request->input('company_name') ?: $request->input('corporate_company_name');
        }
        if ($request->filled('corporate_contact_person')) {
            $updateFields['corporate_contact_person'] = $request->input('corporate_contact_person');
        }
        if ($request->filled('corporate_gstin')) {
            $updateFields['corporate_gstin'] = $request->input('corporate_gstin');
        }
        if ($request->filled('billing_address') || $request->filled('corporate_billing_address')) {
            $updateFields['corporate_billing_address'] = $request->input('billing_address') ?: $request->input('corporate_billing_address');
        }

        $booking->update($updateFields);

        // Sync or create corporate customer profile for future selection
        if (($booking->booking_category === 'CORP' || $request->input('booking_category') === 'CORP') && ! empty($updateFields['corporate_company_name'])) {
            $userId = $booking->customer_id;
            if ($userId) {
                $custRec = Customer::where('user_id', $userId)->first();
                if (! $custRec) {
                    $custRec = new Customer(['user_id' => $userId]);
                }
                $custRec->customer_type = 'corporate';
                $custRec->company_name = $updateFields['corporate_company_name'];
                if (! empty($updateFields['corporate_contact_person'])) {
                    $custRec->contact_person = $updateFields['corporate_contact_person'];
                }
                if (! empty($updateFields['corporate_gstin'])) {
                    $custRec->gstin = $updateFields['corporate_gstin'];
                }
                if (! empty($updateFields['corporate_billing_address'])) {
                    $custRec->billing_address = $updateFields['corporate_billing_address'];
                }
                $custRec->save();
            }
        }

        // Sync Trip
        if ($booking->trip) {
            $booking->trip->update([
                'vehicle_id' => $booking->vehicle_id,
                'driver_id' => $booking->driver_id,
                'status' => $newStatus,
                'trip_status' => $newStatus,
            ]);
        }

        // Record history if status changed
        if ($oldStatus !== $newStatus) {
            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'changed_by' => Auth::id(),
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'remarks' => "Details updated by administrator; status set to {$newStatus}.",
                'comment' => "Details updated by administrator; status set to {$newStatus}.",
                'created_at' => now(),
            ]);
        }

        $paymentService->syncInvoice($booking);

        return redirect()->route('admin.bookings.show', $booking->id)
            ->with('success', "Booking #{$booking->booking_id} updated successfully!");
    }

    /**
     * Action: Confirm Booking (Pending -> Confirmed)
     */
    public function confirm(Booking $booking, BookingService $bookingService)
    {
        if ($booking->booking_status !== 'Pending') {
            return back()->with('error', "Only Pending bookings can be confirmed. Current status: {$booking->booking_status}");
        }

        $bookingService->updateStatus($booking, 'Confirmed', 'Booking confirmed by central dispatch administrator.', Auth::user());

        return back()->with('success', "Booking #{$booking->booking_id} has been confirmed!");
    }

    /**
     * Action: Assign Vehicle (Available only, overlap checked)
     */
    public function assignVehicle(Request $request, Booking $booking, BookingService $bookingService)
    {
        $vehicleSource = $request->input('vehicle_source', 'own');
        if ($vehicleSource === 'third_party' && $request->filled('third_party_vehicle_number')) {
            $regNum = strtoupper(trim((string) $request->input('third_party_vehicle_number')));
            $tpVehicle = Vehicle::where('registration_number', $regNum)->first();
            if (! $tpVehicle) {
                $tpVehicle = Vehicle::create([
                    'name' => $request->input('third_party_vehicle_name') ?: 'Partner Cab ('.$regNum.')',
                    'registration_number' => $regNum,
                    'vehicle_type' => $booking->vehicle_category ?: 'Sedan',
                    'seating_capacity' => 4,
                    'ac_non_ac' => 'AC',
                    'status' => 'Available',
                    'ownership_type' => 'third_party',
                    'is_third_party' => true,
                    'vendor_name' => $request->input('third_party_vendor_name') ?: $booking->travel_partner_name,
                    'vendor_phone' => $request->input('third_party_vendor_phone') ?: $booking->travel_partner_phone,
                ]);
            } else {
                $tpVehicle->update([
                    'ownership_type' => 'third_party',
                    'is_third_party' => true,
                    'status' => 'Available',
                ]);
            }
            $vehicleId = $tpVehicle->id;
        } else {
            $request->validate([
                'vehicle_id' => 'required|exists:vehicles,id',
            ]);
            $vehicleId = (int) $request->vehicle_id;
        }

        try {
            $bookingService->assignVehicle($booking, $vehicleId, Auth::user());

            return back()->with('success', "Vehicle successfully allocated to Booking #{$booking->booking_id}!");
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * Action: Assign Driver (Available only, overlap checked, creates/activates Trip)
     */
    public function assignDriver(Request $request, Booking $booking, BookingService $bookingService)
    {
        $request->validate([
            'driver_id' => 'required|exists:drivers,id',
        ]);

        try {
            $bookingService->assignDriver($booking, (int) $request->driver_id, Auth::user());

            return back()->with('success', "Chauffeur successfully assigned and Trip activated for Booking #{$booking->booking_id}!");
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * Action: Quick Assign Fleet Vehicle and/or Chauffeur directly
     */
    public function assignVehicleDriver(Request $request, Booking $booking, BookingService $bookingService)
    {
        $vehicleSource = $request->input('vehicle_source', 'own');
        $vehicleId = $request->input('vehicle_id');
        $driverId = $request->input('driver_id');

        if ($vehicleSource === 'third_party' && $request->filled('third_party_vehicle_number')) {
            $regNum = strtoupper(trim((string) $request->input('third_party_vehicle_number')));
            $tpVehicle = Vehicle::where('registration_number', $regNum)->first();
            if (! $tpVehicle) {
                $tpVehicle = Vehicle::create([
                    'name' => $request->input('third_party_vehicle_name') ?: 'Partner Cab ('.$regNum.')',
                    'registration_number' => $regNum,
                    'vehicle_type' => $booking->vehicle_category ?: 'Sedan',
                    'seating_capacity' => 4,
                    'ac_non_ac' => 'AC',
                    'status' => 'Available',
                    'ownership_type' => 'third_party',
                    'is_third_party' => true,
                    'vendor_name' => $request->input('third_party_vendor_name') ?: $booking->travel_partner_name,
                    'vendor_phone' => $request->input('third_party_vendor_phone') ?: $booking->travel_partner_phone,
                ]);
            } else {
                $tpVehicle->update([
                    'ownership_type' => 'third_party',
                    'is_third_party' => true,
                    'status' => 'Available',
                ]);
            }
            $vehicleId = $tpVehicle->id;
        }

        if (empty($vehicleId) && empty($driverId)) {
            return back()->with('error', 'Please select at least a vehicle or a chauffeur to allocate.');
        }

        try {
            $parts = [];
            if (! empty($vehicleId)) {
                $bookingService->assignVehicle($booking, (int) $vehicleId, Auth::user());
                $parts[] = 'Vehicle allocated';
            }

            if (! empty($driverId)) {
                $bookingService->assignDriver($booking, (int) $driverId, Auth::user());
                $parts[] = 'Chauffeur assigned & Trip activated';
            }

            return back()->with('success', implode(' and ', $parts)." for Booking #{$booking->booking_id}!");
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * Action: Update Amount (Total Amount, Paid Amount, Balance calculation, intelligent Payment Status)
     */
    public function updateAmount(Request $request, Booking $booking, PaymentService $paymentService)
    {
        $request->validate([
            'total_amount' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
        ]);

        $total = (float) $request->total_amount;
        $paid = (float) $request->paid_amount;

        if ($paid > $total) {
            return back()->with('error', 'Validation Error: Paid Amount cannot be greater than Total Fare Amount.');
        }

        $balance = max(0, $total - $paid);

        $paymentStatus = 'Pending';
        if ($total > 0 && $paid >= $total) {
            $paymentStatus = 'Paid';
        } elseif ($paid > 0) {

            $paymentStatus = 'Partial';
        }

        $booking->update([
            'total_amount' => $total,
            'paid_amount' => $paid,
            'balance_amount' => $balance,
            'payment_status' => $paymentStatus,
        ]);

        $paymentService->syncInvoice($booking);

        return back()->with('success', "Billing amounts updated for Booking #{$booking->booking_id}! Total: ₹{$total}, Paid: ₹{$paid}, Balance: ₹{$balance}.");
    }

    /**
     * Action: Update Payment (Add direct payment record)
     */
    public function updatePayment(Request $request, Booking $booking, PaymentService $paymentService)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $amount = (float) $request->amount;
        $maxPayable = (float) $booking->total_amount - (float) $booking->paid_amount;

        if ($amount > $maxPayable) {
            return back()->with('error', "Cannot accept payment of ₹{$amount}. Remaining balance is ₹{$maxPayable}.");
        }

        $paymentService->recordPayment($booking, [
            'amount' => $amount,
            'payment_method' => $request->payment_method,
            'transaction_id' => $request->transaction_id ?: ('TXN-'.strtoupper(uniqid())),
            'status' => 'Success',
            'notes' => $request->notes,
        ], Auth::user());

        return back()->with('success', 'Payment of ₹'.number_format($amount, 2)." recorded successfully for Booking #{$booking->booking_id}!");
    }

    /**
     * Action: Controlled Status Transition
     */
    public function updateStatus(Request $request, Booking $booking, BookingService $bookingService)
    {
        $request->validate([
            'booking_status' => 'required|string',
            'remarks' => 'nullable|string|max:500',
        ]);

        $newStatus = $request->booking_status;
        $remarks = $request->remarks ?: $request->comment;

        try {
            $bookingService->updateStatus($booking, $newStatus, $remarks, Auth::user());

            return back()->with('success', "Booking #{$booking->booking_id} status transitioned to '{$newStatus}'!");
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * Action: Cancel Booking (Admin direct cancel or customer cancellation approval)
     */
    public function cancel(Request $request, Booking $booking, BookingService $bookingService)
    {
        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        $bookingService->cancelBooking($booking, $request->cancellation_reason, Auth::user());

        return back()->with('success', "Booking #{$booking->booking_id} has been cancelled.");
    }

    /**
     * Recalculate genuine road route, distance, legs, and duration using Google Maps Routing.
     */
    public function recalculateRoute(Booking $booking, GoogleMapsRoutingService $routingService)
    {
        $origin = (! empty($booking->pickup_latitude) && ! empty($booking->pickup_longitude))
            ? ['lat' => (float) $booking->pickup_latitude, 'lng' => (float) $booking->pickup_longitude, 'address' => $booking->pickup_location]
            : $booking->pickup_location;
        $destination = (! empty($booking->destination_latitude) && ! empty($booking->destination_longitude))
            ? ['lat' => (float) $booking->destination_latitude, 'lng' => (float) $booking->destination_longitude, 'address' => $booking->destination]
            : $booking->destination;
        $via = ! empty($booking->via_location)
            ? ((! empty($booking->via_latitude) && ! empty($booking->via_longitude))
                ? ['lat' => (float) $booking->via_latitude, 'lng' => (float) $booking->via_longitude, 'address' => $booking->via_location]
                : $booking->via_location)
            : null;
        $isRoundTrip = ($booking->trip_type === 'Round-Trip' || $booking->rate_category === 'round_trip');

        $res = $routingService->calculateRoadRoute($origin, $destination, $via, $isRoundTrip);

        if (empty($res['success'])) {
            return redirect()->route('admin.bookings.show', $booking->id)
                ->with('error', 'Routing calculation failed: '.($res['message'] ?? 'Unable to calculate road distance.'));
        }

        $booking->update([
            'route_distance_km' => $res['total_distance_km'] ?? $res['distance_km'],
            'route_duration_text' => $res['total_duration_text'] ?? $res['duration_text'],
            'route_duration_minutes' => $res['total_duration_minutes'] ?? $res['duration_minutes'],
            'route_legs' => $res['legs'] ?? [],
            'route_geometry' => $res['route_geometry'] ?? ($res['geometry'] ?? null),
            'routed_at' => now(),
        ]);

        $dist = $booking->route_distance_km;
        $dur = $booking->route_duration_text;

        return redirect()->route('admin.bookings.show', $booking->id)
            ->with('success', "Road route recalculated successfully! Driving distance: {$dist} KM ({$dur}).");
    }

    public function destroy(Booking $booking)
    {
        $id = $booking->booking_id;
        $booking->delete();

        return redirect()->route('admin.bookings.index')
            ->with('success', "Booking #{$id} has been deleted.");
    }

    protected function getCorporateClients()
    {
        $corporateClients = Customer::where('customer_type', 'corporate')
            ->orWhereNotNull('company_name')
            ->with('user')
            ->get()
            ->map(function ($cust) {
                return [
                    'id' => $cust->id,
                    'user_id' => $cust->user_id,
                    'company_name' => $cust->company_name,
                    'contact_person' => $cust->contact_person ?: ($cust->user?->name ?? ''),
                    'phone' => $cust->user?->phone ?? $cust->alternate_phone ?? '',
                    'email' => $cust->user?->email ?? '',
                    'gstin' => $cust->gstin ?? '',
                    'billing_address' => $cust->billing_address ?: $cust->address ?: '',
                    'city' => $cust->city ?? '',
                ];
            })
            ->filter(fn ($c) => ! empty($c['company_name']))
            ->unique('company_name')
            ->values();

        $existingCorpBookings = Booking::whereNotNull('corporate_company_name')
            ->where('corporate_company_name', '!=', '')
            ->with('customer')
            ->get();
        foreach ($existingCorpBookings as $b) {
            if (! $corporateClients->contains('company_name', $b->corporate_company_name)) {
                $corporateClients->push([
                    'id' => null,
                    'user_id' => $b->customer_id,
                    'company_name' => $b->corporate_company_name,
                    'contact_person' => $b->corporate_contact_person ?: ($b->customer?->name ?? ''),
                    'phone' => $b->corporate_phone ?: ($b->customer?->phone ?? ''),
                    'email' => $b->corporate_email ?: ($b->customer?->email ?? ''),
                    'gstin' => $b->corporate_gstin ?? '',
                    'billing_address' => $b->corporate_billing_address ?? '',
                    'city' => '',
                ]);
            }
        }

        return $corporateClients;
    }
}
