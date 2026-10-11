<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\Driver;
use App\Models\Enquiry;
use App\Models\Feedback;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\RateRoute;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportService
{
    /**
     * Get all 18 supported report categories metadata.
     */
    public function getCategories(): array
    {
        return [
            'bookings' => [
                'title' => 'Booking Reports',
                'icon' => '🚖',
                'badge' => 'Core Ops',
                'description' => 'Comprehensive booking logs, passenger trips, dispatch and fare status.',
                'orientation' => 'landscape',
            ],
            'customers' => [
                'title' => 'Customer Reports',
                'icon' => '👥',
                'badge' => 'CRM',
                'description' => 'Customer profiles, lifetime booking counts, billing history and outstanding balances.',
                'orientation' => 'landscape',
            ],
            'vehicles' => [
                'title' => 'Vehicle / Fleet Reports',
                'icon' => '🚗',
                'badge' => 'Fleet',
                'description' => 'Vehicle utilization, trip assignments, operational status and document expiries.',
                'orientation' => 'landscape',
            ],
            'drivers' => [
                'title' => 'Driver Reports',
                'icon' => '👨‍✈️',
                'badge' => 'Staff',
                'description' => 'Chauffeur rosters, trip history, cancellation records and driving licence expiries.',
                'orientation' => 'landscape',
            ],
            'trips' => [
                'title' => 'Trip Reports',
                'icon' => '🛣️',
                'badge' => 'Operations',
                'description' => 'Fulfilled trips, odometer start/end readings, actual kilometers and final fares.',
                'orientation' => 'landscape',
            ],
            'payments' => [
                'title' => 'Payment & Collection Reports',
                'icon' => '💳',
                'badge' => 'Finance',
                'description' => 'Advances, cash/UPI receipts, partial payments and recorded customer refunds.',
                'orientation' => 'landscape',
            ],
            'outstanding' => [
                'title' => 'Outstanding / Pending Payment Reports',
                'icon' => '⚠️',
                'badge' => 'Receivables',
                'description' => 'Unpaid balances, pending receivables and customer payment dues.',
                'orientation' => 'landscape',
            ],
            'revenue' => [
                'title' => 'Revenue & Business Summary',
                'icon' => '📊',
                'badge' => 'Executive',
                'description' => 'Aggregated daily, weekly and monthly gross billed, cash collected and receivables.',
                'orientation' => 'landscape',
            ],
            'routes' => [
                'title' => 'Route & Fare Reports',
                'icon' => '🗺️',
                'badge' => 'Pricing',
                'description' => 'Configured outstation, airport, round-trip and local hourly tariff schedules.',
                'orientation' => 'landscape',
            ],
            'tour_packages' => [
                'title' => 'Tour Package Reports',
                'icon' => '🏖️',
                'badge' => 'Tourism',
                'description' => 'Multi-destination tour reservations, package packages and vacation bookings.',
                'orientation' => 'landscape',
            ],
            'ambulance' => [
                'title' => 'Ambulance Service Reports',
                'icon' => '🚑',
                'badge' => '24/7 Emergency',
                'description' => 'Hospital transfers, emergency ambulance bookings and medical patient transit.',
                'orientation' => 'landscape',
            ],
            'cancellations_refunds' => [
                'title' => 'Cancellation & Refund Reports',
                'icon' => '❌',
                'badge' => 'Audit',
                'description' => 'Cancelled booking reasons, lost value and actual refund payments disbursed.',
                'orientation' => 'landscape',
            ],
            'advance_availability' => [
                'title' => 'Advance Booking & Availability Reports',
                'icon' => '🗓️',
                'badge' => 'Schedule',
                'description' => 'Future reservations, fleet & driver assignments and schedule conflict tracking.',
                'orientation' => 'landscape',
            ],
            'assignments' => [
                'title' => 'Driver & Vehicle Assignment Reports',
                'icon' => '📋',
                'badge' => 'Dispatch',
                'description' => 'Unassigned vs Assigned reservations and dispatch readiness breakdown.',
                'orientation' => 'landscape',
            ],
            'feedback_enquiries' => [
                'title' => 'Customer Feedback & Enquiry Reports',
                'icon' => '⭐',
                'badge' => 'Feedback',
                'description' => 'Customer satisfaction reviews, ratings and website portal inquiry messages.',
                'orientation' => 'landscape',
            ],
            'document_expiries' => [
                'title' => 'Document Expiry Reports',
                'icon' => '📄',
                'badge' => 'Compliance',
                'description' => 'Vehicles and drivers with expired or upcoming insurance, fitness, permit and licences.',
                'orientation' => 'landscape',
            ],
            'user_activity' => [
                'title' => 'User Activity & Audit Reports',
                'icon' => '🔍',
                'badge' => 'Security',
                'description' => 'Booking status transitions, dispatcher changes, timestamps and audit trails.',
                'orientation' => 'landscape',
            ],
            'invoices' => [
                'title' => 'Invoice Reports',
                'icon' => '🧾',
                'badge' => 'Tax & GST',
                'description' => 'Official GST tax invoices, invoice totals, tax rates and balance reconciliations.',
                'orientation' => 'landscape',
            ],
        ];
    }

    /**
     * Resolve date presets (Today, Yesterday, This Week, This Month, Previous Month, This Year, Custom).
     */
    public function resolveDateRange(Request $request): array
    {
        $preset = $request->input('preset', 'this_month');
        $now = Carbon::now();

        switch ($preset) {
            case 'today':
                $fromDate = $now->copy()->startOfDay()->toDateString();
                $toDate = $now->copy()->endOfDay()->toDateString();
                break;
            case 'yesterday':
                $fromDate = $now->copy()->subDay()->startOfDay()->toDateString();
                $toDate = $now->copy()->subDay()->endOfDay()->toDateString();
                break;
            case 'this_week':
                $fromDate = $now->copy()->startOfWeek()->toDateString();
                $toDate = $now->copy()->endOfWeek()->toDateString();
                break;
            case 'last_month':
                $fromDate = $now->copy()->subMonth()->startOfMonth()->toDateString();
                $toDate = $now->copy()->subMonth()->endOfMonth()->toDateString();
                break;
            case 'this_year':
                $fromDate = $now->copy()->startOfYear()->toDateString();
                $toDate = $now->copy()->endOfYear()->toDateString();
                break;
            case 'custom':
                $fromDate = $request->input('from_date', $now->copy()->startOfMonth()->toDateString());
                $toDate = $request->input('to_date', $now->toDateString());
                break;
            case 'this_month':
            default:
                $preset = 'this_month';
                $fromDate = $request->input('from_date', $now->copy()->startOfMonth()->toDateString());
                $toDate = $request->input('to_date', $now->toDateString());
                break;
        }

        // Validate date order
        if ($fromDate > $toDate) {
            $temp = $fromDate;
            $fromDate = $toDate;
            $toDate = $temp;
        }

        return [$fromDate, $toDate, $preset];
    }

    /**
     * Get Report Data including summaries, paginated rows, and exportable collections.
     */
    public function getReportData(string $category, Request $request, bool $forExport = false): array
    {
        [$fromDate, $toDate, $preset] = $this->resolveDateRange($request);
        $categories = $this->getCategories();
        $categoryMeta = $categories[$category] ?? $categories['bookings'];

        $method = 'get'.str_replace(' ', '', ucwords(str_replace('_', ' ', $category))).'Data';
        if (! method_exists($this, $method)) {
            $method = 'getBookingsData';
        }

        return $this->$method($request, $fromDate, $toDate, $preset, $categoryMeta, $forExport);
    }

    /**
     * Category 1: Booking Reports
     */
    private function getBookingsData(Request $request, string $fromDate, string $toDate, string $preset, array $categoryMeta, bool $forExport): array
    {
        $query = Booking::with(['customer', 'vehicle', 'driver', 'trip'])
            ->whereBetween('travel_date', [$fromDate, $toDate]);

        if ($request->filled('status')) {
            $query->where('booking_status', $request->status);
        }
        if ($request->filled('trip_type')) {
            $query->where('trip_type', $request->trip_type);
        }
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('booking_id', 'like', "%{$s}%")
                    ->orWhere('pickup_location', 'like', "%{$s}%")
                    ->orWhere('destination', 'like', "%{$s}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%"));
            });
        }

        // Summary metrics
        $totalCount = (clone $query)->count();
        $completedCount = (clone $query)->where('booking_status', 'Completed')->count();
        $totalFare = (clone $query)->where('booking_status', '!=', 'Cancelled')->sum('total_amount');
        $collected = (clone $query)->where('booking_status', '!=', 'Cancelled')->sum('paid_amount');
        $balance = (clone $query)->where('booking_status', '!=', 'Cancelled')->sum('balance_amount');

        $kpis = [
            ['label' => 'Total Bookings', 'value' => number_format($totalCount), 'icon' => '🚖', 'color' => '#2563EB'],
            ['label' => 'Completed Trips', 'value' => number_format($completedCount), 'icon' => '✅', 'color' => '#059669'],
            ['label' => 'Gross Billed (₹)', 'value' => '₹'.number_format($totalFare, 2), 'icon' => '💰', 'color' => '#0D9488'],
            ['label' => 'Collected (₹)', 'value' => '₹'.number_format($collected, 2), 'icon' => '💵', 'color' => '#16A34A'],
            ['label' => 'Balance Due (₹)', 'value' => '₹'.number_format($balance, 2), 'icon' => '⚠️', 'color' => '#DC2626'],
        ];

        $columns = [
            'booking_id' => 'Booking ID',
            'created_at' => 'Booking Date',
            'customer_name' => 'Customer Name',
            'customer_phone' => 'Contact',
            'pickup_location' => 'Pickup Location',
            'destination' => 'Drop Location',
            'trip_type' => 'Trip Type',
            'travel_date' => 'Travel Date',
            'travel_time' => 'Time',
            'vehicle' => 'Assigned Vehicle',
            'driver' => 'Assigned Driver',
            'booking_status' => 'Booking Status',
            'payment_status' => 'Payment Status',
            'total_amount' => 'Fare (₹)',
            'paid_amount' => 'Paid (₹)',
            'balance_amount' => 'Balance (₹)',
        ];

        $transform = function (Booking $b) {
            return [
                'booking_id' => $b->booking_id,
                'created_at' => $b->created_at?->format('d M Y') ?? 'N/A',
                'customer_name' => $b->customer?->name ?? 'Guest Passenger',
                'customer_phone' => $b->customer?->phone ?? 'N/A',
                'pickup_location' => $b->pickup_location,
                'destination' => $b->destination,
                'trip_type' => $b->trip_type,
                'travel_date' => $b->travel_date?->format('d M Y') ?? 'N/A',
                'travel_time' => $b->travel_time ?? 'N/A',
                'vehicle' => $b->vehicle ? "{$b->vehicle->name} ({$b->vehicle->registration_number})" : 'Unassigned',
                'driver' => $b->driver?->name ?? 'Unassigned',
                'booking_status' => $b->booking_status,
                'payment_status' => $b->payment_status,
                'total_amount' => number_format((float) $b->total_amount, 2, '.', ''),
                'paid_amount' => number_format((float) $b->paid_amount, 2, '.', ''),
                'balance_amount' => number_format((float) $b->balance_amount, 2, '.', ''),
            ];
        };

        return $this->formatResult($query, $fromDate, $toDate, $preset, $categoryMeta, $columns, $kpis, $transform, $forExport, $request);
    }

    /**
     * Category 2: Customer Reports
     */
    private function getCustomersData(Request $request, string $fromDate, string $toDate, string $preset, array $categoryMeta, bool $forExport): array
    {
        $query = User::where('role', 'customer')
            ->with(['customer', 'bookings', 'payments'])
            ->whereBetween('created_at', [Carbon::parse($fromDate)->startOfDay(), Carbon::parse($toDate)->endOfDay()]);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%");
            });
        }

        if ($request->input('activity') === 'active') {
            $query->has('bookings');
        } elseif ($request->input('activity') === 'no_bookings') {
            $query->doesntHave('bookings');
        }

        $allCustomers = (clone $query)->get();
        $totalCustomers = $allCustomers->count();
        $activeCustomers = $allCustomers->filter(fn ($u) => $u->bookings->count() > 0)->count();
        $totalBilled = $allCustomers->sum(fn ($u) => $u->bookings->where('booking_status', '!=', 'Cancelled')->sum('total_amount'));
        $totalPaid = $allCustomers->sum(fn ($u) => $u->bookings->where('booking_status', '!=', 'Cancelled')->sum('paid_amount'));
        $totalBalance = $allCustomers->sum(fn ($u) => $u->bookings->where('booking_status', '!=', 'Cancelled')->sum('balance_amount'));

        $kpis = [
            ['label' => 'Total Registered', 'value' => number_format($totalCustomers), 'icon' => '👥', 'color' => '#2563EB'],
            ['label' => 'Active Travelers', 'value' => number_format($activeCustomers), 'icon' => '🚖', 'color' => '#059669'],
            ['label' => 'Total Billed (₹)', 'value' => '₹'.number_format($totalBilled, 2), 'icon' => '💰', 'color' => '#0D9488'],
            ['label' => 'Total Paid (₹)', 'value' => '₹'.number_format($totalPaid, 2), 'icon' => '💵', 'color' => '#16A34A'],
            ['label' => 'Outstanding (₹)', 'value' => '₹'.number_format($totalBalance, 2), 'icon' => '⚠️', 'color' => '#DC2626'],
        ];

        $columns = [
            'name' => 'Customer Name',
            'phone' => 'Mobile / WhatsApp',
            'email' => 'Email',
            'city' => 'City',
            'created_at' => 'Joined Date',
            'total_bookings' => 'Total Bookings',
            'completed_trips' => 'Completed Trips',
            'cancelled_bookings' => 'Cancelled',
            'total_billed' => 'Total Billed (₹)',
            'total_paid' => 'Total Paid (₹)',
            'balance' => 'Outstanding Due (₹)',
        ];

        $transform = function (User $u) {
            $validBookings = $u->bookings->where('booking_status', '!=', 'Cancelled');

            return [
                'name' => $u->name,
                'phone' => $u->phone ?? 'N/A',
                'email' => $u->email,
                'city' => $u->customer?->city ?? 'Bilaspur',
                'created_at' => $u->created_at?->format('d M Y') ?? 'N/A',
                'total_bookings' => $u->bookings->count(),
                'completed_trips' => $u->bookings->where('booking_status', 'Completed')->count(),
                'cancelled_bookings' => $u->bookings->where('booking_status', 'Cancelled')->count(),
                'total_billed' => number_format((float) $validBookings->sum('total_amount'), 2, '.', ''),
                'total_paid' => number_format((float) $validBookings->sum('paid_amount'), 2, '.', ''),
                'balance' => number_format((float) $validBookings->sum('balance_amount'), 2, '.', ''),
            ];
        };

        return $this->formatResult($query, $fromDate, $toDate, $preset, $categoryMeta, $columns, $kpis, $transform, $forExport, $request);
    }

    /**
     * Category 3: Vehicle / Fleet Reports
     */
    private function getVehiclesData(Request $request, string $fromDate, string $toDate, string $preset, array $categoryMeta, bool $forExport): array
    {
        $query = Vehicle::withCount([
            'bookings as range_bookings_count' => fn ($q) => $q->whereBetween('travel_date', [$fromDate, $toDate]),
            'bookings as completed_trips_count' => fn ($q) => $q->whereBetween('travel_date', [$fromDate, $toDate])->where('booking_status', 'Completed'),
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('vehicle_type')) {
            $query->where('vehicle_type', $request->vehicle_type);
        }
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('registration_number', 'like', "%{$s}%")
                    ->orWhere('vehicle_type', 'like', "%{$s}%");
            });
        }

        $allVehicles = (clone $query)->get();
        $totalVehicles = $allVehicles->count();
        $activeVehicles = $allVehicles->where('status', 'Active')->count();
        $totalTrips = $allVehicles->sum('range_bookings_count');
        $expiringCount = $allVehicles->filter(fn ($v) => ! empty($v->getExpiringDocuments()))->count();

        $kpis = [
            ['label' => 'Total Fleet Size', 'value' => number_format($totalVehicles), 'icon' => '🚗', 'color' => '#2563EB'],
            ['label' => 'Active Ready Fleet', 'value' => number_format($activeVehicles), 'icon' => '✅', 'color' => '#059669'],
            ['label' => 'Assigned Trips in Period', 'value' => number_format($totalTrips), 'icon' => '🚖', 'color' => '#0D9488'],
            ['label' => 'Doc Expiry Alerts', 'value' => number_format($expiringCount), 'icon' => '⚠️', 'color' => '#DC2626'],
        ];

        $columns = [
            'name' => 'Vehicle Model',
            'registration_number' => 'Reg Number',
            'vehicle_type' => 'Category',
            'seating_capacity' => 'Seats',
            'status' => 'Status',
            'range_bookings_count' => 'Trips in Range',
            'completed_trips_count' => 'Completed Trips',
            'insurance_expiry' => 'Insurance Expiry',
            'fitness_expiry' => 'Fitness Expiry',
            'permit_expiry' => 'Permit Expiry',
            'puc_expiry' => 'PUC Expiry',
            'compliance' => 'Compliance Health',
        ];

        $transform = function (Vehicle $v) {
            $expiringDocs = $v->getExpiringDocuments();
            $hasExpired = collect($expiringDocs)->contains('status', 'expired');
            $compliance = $hasExpired ? 'EXPIRED' : (! empty($expiringDocs) ? 'EXPIRING SOON' : 'VALID');

            return [
                'name' => $v->name,
                'registration_number' => $v->registration_number,
                'vehicle_type' => $v->vehicle_type,
                'seating_capacity' => $v->seating_capacity ?? '4+1',
                'status' => $v->status,
                'range_bookings_count' => $v->range_bookings_count ?? 0,
                'completed_trips_count' => $v->completed_trips_count ?? 0,
                'insurance_expiry' => $v->insurance_expiry?->format('d M Y') ?? 'Not Recorded',
                'fitness_expiry' => $v->fitness_expiry?->format('d M Y') ?? 'Not Recorded',
                'permit_expiry' => $v->permit_expiry?->format('d M Y') ?? 'Not Recorded',
                'puc_expiry' => $v->puc_expiry?->format('d M Y') ?? 'Not Recorded',
                'compliance' => $compliance,
            ];
        };

        return $this->formatResult($query, $fromDate, $toDate, $preset, $categoryMeta, $columns, $kpis, $transform, $forExport, $request);
    }

    /**
     * Category 4: Driver Reports
     */
    private function getDriversData(Request $request, string $fromDate, string $toDate, string $preset, array $categoryMeta, bool $forExport): array
    {
        $query = Driver::withCount([
            'bookings as range_trips_count' => fn ($q) => $q->whereBetween('travel_date', [$fromDate, $toDate]),
            'bookings as completed_trips_count' => fn ($q) => $q->whereBetween('travel_date', [$fromDate, $toDate])->where('booking_status', 'Completed'),
            'bookings as cancelled_trips_count' => fn ($q) => $q->whereBetween('travel_date', [$fromDate, $toDate])->where('booking_status', 'Cancelled'),
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('mobile', 'like', "%{$s}%")
                    ->orWhere('license_number', 'like', "%{$s}%");
            });
        }

        $allDrivers = (clone $query)->get();
        $totalDrivers = $allDrivers->count();
        $activeDrivers = $allDrivers->where('status', 'Active')->count();
        $totalCompleted = $allDrivers->sum('completed_trips_count');
        $licenceAlerts = $allDrivers->filter(fn ($d) => in_array($d->getLicenseStatus()['status'] ?? '', ['expired', 'expiring_soon']))->count();

        $kpis = [
            ['label' => 'Total Chauffeurs', 'value' => number_format($totalDrivers), 'icon' => '👨‍✈️', 'color' => '#2563EB'],
            ['label' => 'Active Ready Drivers', 'value' => number_format($activeDrivers), 'icon' => '✅', 'color' => '#059669'],
            ['label' => 'Completed Trips in Range', 'value' => number_format($totalCompleted), 'icon' => '🚖', 'color' => '#0D9488'],
            ['label' => 'Licence Alerts', 'value' => number_format($licenceAlerts), 'icon' => '⚠️', 'color' => '#DC2626'],
        ];

        $columns = [
            'name' => 'Driver Name',
            'mobile' => 'Mobile / Contact',
            'license_number' => 'Licence Number',
            'license_expiry' => 'Licence Expiry',
            'status' => 'Status',
            'range_trips_count' => 'Assigned Trips',
            'completed_trips_count' => 'Completed Trips',
            'cancelled_trips_count' => 'Cancellations',
            'licence_status' => 'Licence Health',
        ];

        $transform = function (Driver $d) {
            $statusInfo = $d->getLicenseStatus();
            $statusText = strtoupper(str_replace('_', ' ', $statusInfo['status']));

            return [
                'name' => $d->name,
                'mobile' => $d->mobile,
                'license_number' => $d->license_number ?? 'N/A',
                'license_expiry' => $d->license_expiry?->format('d M Y') ?? 'Not Recorded',
                'status' => $d->status,
                'range_trips_count' => $d->range_trips_count ?? 0,
                'completed_trips_count' => $d->completed_trips_count ?? 0,
                'cancelled_trips_count' => $d->cancelled_trips_count ?? 0,
                'licence_status' => $statusText,
            ];
        };

        return $this->formatResult($query, $fromDate, $toDate, $preset, $categoryMeta, $columns, $kpis, $transform, $forExport, $request);
    }

    /**
     * Category 5: Trip Reports
     */
    private function getTripsData(Request $request, string $fromDate, string $toDate, string $preset, array $categoryMeta, bool $forExport): array
    {
        $query = Trip::with(['booking.customer', 'driver', 'vehicle'])
            ->whereHas('booking', fn ($b) => $b->whereBetween('travel_date', [$fromDate, $toDate]));

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->whereHas('booking', fn ($b) => $b->where('booking_id', 'like', "%{$s}%")->orWhere('pickup_location', 'like', "%{$s}%")->orWhere('destination', 'like', "%{$s}%"))
                    ->orWhereHas('driver', fn ($d) => $d->where('name', 'like', "%{$s}%"))
                    ->orWhereHas('vehicle', fn ($v) => $v->where('registration_number', 'like', "%{$s}%"));
            });
        }

        $allTrips = (clone $query)->get();
        $totalTrips = $allTrips->count();
        $completedTrips = $allTrips->where('status', 'Completed')->count();
        $totalKm = $allTrips->sum(function ($t) {
            return ($t->end_odometer && $t->start_odometer && $t->end_odometer >= $t->start_odometer)
                ? ($t->end_odometer - $t->start_odometer)
                : 0;
        });

        $kpis = [
            ['label' => 'Total Trips Logged', 'value' => number_format($totalTrips), 'icon' => '🛣️', 'color' => '#2563EB'],
            ['label' => 'Completed Trips', 'value' => number_format($completedTrips), 'icon' => '✅', 'color' => '#059669'],
            ['label' => 'Total KM Driven', 'value' => number_format($totalKm, 1).' KM', 'icon' => '🚗', 'color' => '#0D9488'],
        ];

        $columns = [
            'booking_id' => 'Booking ID',
            'customer' => 'Customer',
            'route' => 'Route (From - To)',
            'travel_date' => 'Date',
            'driver' => 'Driver',
            'vehicle' => 'Vehicle',
            'status' => 'Trip Status',
            'start_odometer' => 'Start KM',
            'end_odometer' => 'End KM',
            'actual_km' => 'Total KM Run',
            'fare' => 'Final Fare (₹)',
        ];

        $transform = function (Trip $t) {
            $kmRun = ($t->end_odometer && $t->start_odometer && $t->end_odometer >= $t->start_odometer)
                ? ($t->end_odometer - $t->start_odometer)
                : 'N/A';

            return [
                'booking_id' => $t->booking?->booking_id ?? 'N/A',
                'customer' => $t->booking?->customer?->name ?? 'Passenger',
                'route' => ($t->booking?->pickup_location ?? '').' → '.($t->booking?->destination ?? ''),
                'travel_date' => $t->booking?->travel_date?->format('d M Y') ?? 'N/A',
                'driver' => $t->driver?->name ?? 'Unassigned',
                'vehicle' => $t->vehicle ? "{$t->vehicle->name} ({$t->vehicle->registration_number})" : 'Unassigned',
                'status' => $t->status,
                'start_odometer' => $t->start_odometer !== null ? number_format($t->start_odometer) : 'Pending',
                'end_odometer' => $t->end_odometer !== null ? number_format($t->end_odometer) : 'Pending',
                'actual_km' => is_numeric($kmRun) ? number_format($kmRun, 1).' KM' : 'Pending',
                'fare' => number_format((float) ($t->booking?->total_amount ?? 0), 2, '.', ''),
            ];
        };

        return $this->formatResult($query, $fromDate, $toDate, $preset, $categoryMeta, $columns, $kpis, $transform, $forExport, $request);
    }

    /**
     * Category 6: Payment & Collection Reports
     */
    private function getPaymentsData(Request $request, string $fromDate, string $toDate, string $preset, array $categoryMeta, bool $forExport): array
    {
        $query = Payment::with(['booking.customer'])
            ->whereBetween('payment_date', [$fromDate, $toDate]);

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('transaction_id', 'like', "%{$s}%")
                    ->orWhereHas('booking', fn ($b) => $b->where('booking_id', 'like', "%{$s}%")->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$s}%")));
            });
        }

        $allPayments = (clone $query)->get();
        $totalCollected = $allPayments->where('status', 'Success')->where('payment_type', '!=', 'refund')->sum('amount');
        $totalRefunds = $allPayments->where('payment_type', 'refund')->sum('amount');
        $netCollection = $totalCollected - $totalRefunds;
        $totalTxns = $allPayments->count();

        $kpis = [
            ['label' => 'Total Transactions', 'value' => number_format($totalTxns), 'icon' => '💳', 'color' => '#2563EB'],
            ['label' => 'Gross Collections (₹)', 'value' => '₹'.number_format($totalCollected, 2), 'icon' => '💵', 'color' => '#16A34A'],
            ['label' => 'Refunds Processed (₹)', 'value' => '₹'.number_format($totalRefunds, 2), 'icon' => '↩️', 'color' => '#DC2626'],
            ['label' => 'Net Realized (₹)', 'value' => '₹'.number_format($netCollection, 2), 'icon' => '💰', 'color' => '#059669'],
        ];

        $columns = [
            'payment_date' => 'Payment Date',
            'transaction_id' => 'Trx Reference',
            'booking_id' => 'Booking ID',
            'customer' => 'Customer',
            'payment_type' => 'Payment Type',
            'payment_method' => 'Payment Method',
            'amount' => 'Amount (₹)',
            'status' => 'Status',
            'notes' => 'Notes',
        ];

        $transform = function (Payment $p) {
            return [
                'payment_date' => $p->payment_date ? Carbon::parse($p->payment_date)->format('d M Y') : $p->created_at?->format('d M Y'),
                'transaction_id' => $p->transaction_id ?? ('TXN-'.str_pad($p->id, 5, '0', STR_PAD_LEFT)),
                'booking_id' => $p->booking?->booking_id ?? 'N/A',
                'customer' => $p->booking?->customer?->name ?? 'Guest',
                'payment_type' => ucfirst($p->payment_type ?? 'Advance'),
                'payment_method' => strtoupper($p->payment_method ?? 'Cash'),
                'amount' => number_format((float) $p->amount, 2, '.', ''),
                'status' => $p->status,
                'notes' => $p->notes ?? '',
            ];
        };

        return $this->formatResult($query, $fromDate, $toDate, $preset, $categoryMeta, $columns, $kpis, $transform, $forExport, $request);
    }

    /**
     * Category 7: Outstanding / Pending Payment Reports
     */
    private function getOutstandingData(Request $request, string $fromDate, string $toDate, string $preset, array $categoryMeta, bool $forExport): array
    {
        $query = Booking::with(['customer', 'vehicle', 'driver'])
            ->where('booking_status', '!=', 'Cancelled')
            ->where('balance_amount', '>', 0)
            ->whereBetween('travel_date', [$fromDate, $toDate]);

        if ($request->filled('trip_type')) {
            $query->where('trip_type', $request->trip_type);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('booking_id', 'like', "%{$s}%")
                    ->orWhere('pickup_location', 'like', "%{$s}%")
                    ->orWhere('destination', 'like', "%{$s}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%"));
            });
        }

        $allOutstanding = (clone $query)->get();
        $totalCount = $allOutstanding->count();
        $totalBalance = $allOutstanding->sum('balance_amount');
        $totalFare = $allOutstanding->sum('total_amount');
        $totalAdvance = $allOutstanding->sum('paid_amount');

        $kpis = [
            ['label' => 'Unpaid Bookings', 'value' => number_format($totalCount), 'icon' => '⚠️', 'color' => '#DC2626'],
            ['label' => 'Outstanding Balance (₹)', 'value' => '₹'.number_format($totalBalance, 2), 'icon' => '💰', 'color' => '#E11D48'],
            ['label' => 'Total Billed (₹)', 'value' => '₹'.number_format($totalFare, 2), 'icon' => '🧾', 'color' => '#0D9488'],
            ['label' => 'Advance Collected (₹)', 'value' => '₹'.number_format($totalAdvance, 2), 'icon' => '💵', 'color' => '#16A34A'],
        ];

        $columns = [
            'booking_id' => 'Booking ID',
            'customer' => 'Customer Name',
            'contact' => 'Contact Number',
            'travel_date' => 'Travel Date',
            'route' => 'Route',
            'trip_type' => 'Trip Type',
            'total_amount' => 'Total Fare (₹)',
            'paid_amount' => 'Advance Paid (₹)',
            'balance_amount' => 'Balance Due (₹)',
            'payment_status' => 'Payment Status',
            'booking_status' => 'Booking Status',
        ];

        $transform = function (Booking $b) {
            return [
                'booking_id' => $b->booking_id,
                'customer' => $b->customer?->name ?? 'Guest',
                'contact' => $b->customer?->phone ?? 'N/A',
                'travel_date' => $b->travel_date?->format('d M Y') ?? 'N/A',
                'route' => "{$b->pickup_location} → {$b->destination}",
                'trip_type' => $b->trip_type,
                'total_amount' => number_format((float) $b->total_amount, 2, '.', ''),
                'paid_amount' => number_format((float) $b->paid_amount, 2, '.', ''),
                'balance_amount' => number_format((float) $b->balance_amount, 2, '.', ''),
                'payment_status' => $b->payment_status,
                'booking_status' => $b->booking_status,
            ];
        };

        return $this->formatResult($query, $fromDate, $toDate, $preset, $categoryMeta, $columns, $kpis, $transform, $forExport, $request);
    }

    /**
     * Category 8: Revenue & Business Summary
     */
    private function getRevenueData(Request $request, string $fromDate, string $toDate, string $preset, array $categoryMeta, bool $forExport): array
    {
        $groupBy = $request->input('group_by', 'daily');

        $query = Booking::whereBetween('travel_date', [$fromDate, $toDate])
            ->where('booking_status', '!=', 'Cancelled');

        $totalBookings = (clone $query)->count();
        $totalBilled = (clone $query)->sum('total_amount');
        $totalCollected = (clone $query)->sum('paid_amount');
        $totalOutstanding = (clone $query)->sum('balance_amount');
        $totalRefunds = Payment::whereBetween('payment_date', [$fromDate, $toDate])
            ->where('payment_type', 'refund')
            ->sum('amount');
        $netRealized = $totalCollected - $totalRefunds;

        $kpis = [
            ['label' => 'Total Bookings', 'value' => number_format($totalBookings), 'icon' => '🚖', 'color' => '#2563EB'],
            ['label' => 'Gross Billed (₹)', 'value' => '₹'.number_format($totalBilled, 2), 'icon' => '📈', 'color' => '#0D9488'],
            ['label' => 'Cash Collected (₹)', 'value' => '₹'.number_format($totalCollected, 2), 'icon' => '💵', 'color' => '#16A34A'],
            ['label' => 'Receivables (₹)', 'value' => '₹'.number_format($totalOutstanding, 2), 'icon' => '⚠️', 'color' => '#DC2626'],
            ['label' => 'Refunds (₹)', 'value' => '₹'.number_format($totalRefunds, 2), 'icon' => '↩️', 'color' => '#9333EA'],
            ['label' => 'Net Realized (₹)', 'value' => '₹'.number_format($netRealized, 2), 'icon' => '💰', 'color' => '#059669'],
        ];

        // Breakdown grouping
        if ($groupBy === 'vehicle_category') {
            $breakdown = (clone $query)
                ->selectRaw('vehicle_category as period, count(*) as bookings_count, sum(total_amount) as total_billed, sum(paid_amount) as total_collected, sum(balance_amount) as total_balance')
                ->groupBy('vehicle_category')
                ->orderBy('total_billed', 'desc')
                ->get();
        } elseif ($groupBy === 'trip_type') {
            $breakdown = (clone $query)
                ->selectRaw('trip_type as period, count(*) as bookings_count, sum(total_amount) as total_billed, sum(paid_amount) as total_collected, sum(balance_amount) as total_balance')
                ->groupBy('trip_type')
                ->orderBy('total_billed', 'desc')
                ->get();
        } else {
            // Default Daily
            $breakdown = (clone $query)
                ->selectRaw('travel_date as period, count(*) as bookings_count, sum(total_amount) as total_billed, sum(paid_amount) as total_collected, sum(balance_amount) as total_balance')
                ->groupBy('travel_date')
                ->orderBy('travel_date', 'desc')
                ->get();
        }

        $columns = [
            'period' => $groupBy === 'vehicle_category' ? 'Vehicle Category' : ($groupBy === 'trip_type' ? 'Trip Type' : 'Date / Period'),
            'bookings_count' => 'Bookings Count',
            'total_billed' => 'Gross Billed (₹)',
            'total_collected' => 'Collected (₹)',
            'total_balance' => 'Outstanding Due (₹)',
            'net_realized' => 'Net Realized (₹)',
        ];

        $transform = function ($item) use ($groupBy) {
            $periodLabel = $item->period;
            if ($groupBy === 'daily' && ! empty($item->period)) {
                $periodLabel = Carbon::parse($item->period)->format('d M Y');
            }

            return [
                'period' => $periodLabel ?: 'Uncategorized',
                'bookings_count' => $item->bookings_count,
                'total_billed' => number_format((float) $item->total_billed, 2, '.', ''),
                'total_collected' => number_format((float) $item->total_collected, 2, '.', ''),
                'total_balance' => number_format((float) $item->total_balance, 2, '.', ''),
                'net_realized' => number_format((float) $item->total_collected, 2, '.', ''),
            ];
        };

        // For export, return all rows
        $items = $breakdown->map($transform);
        $paginator = new LengthAwarePaginator(
            $items,
            $items->count(),
            100,
            1,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return [
            'meta' => $categoryMeta,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'preset' => $preset,
            'columns' => $columns,
            'kpis' => $kpis,
            'rows' => $items,
            'paginator' => $paginator,
            'exportRows' => $items,
        ];
    }

    /**
     * Category 9: Route & Fare Reports
     */
    private function getRoutesData(Request $request, string $fromDate, string $toDate, string $preset, array $categoryMeta, bool $forExport): array
    {
        $query = RateRoute::with('vehiclePrices');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('origin', 'like', "%{$s}%")
                    ->orWhere('destination', 'like', "%{$s}%")
                    ->orWhere('category', 'like', "%{$s}%");
            });
        }

        $allRoutes = (clone $query)->get();
        $totalRoutes = $allRoutes->count();
        $totalRateCards = $allRoutes->sum(fn ($r) => $r->vehiclePrices->count());

        $kpis = [
            ['label' => 'Active Routes', 'value' => number_format($totalRoutes), 'icon' => '🗺️', 'color' => '#2563EB'],
            ['label' => 'Vehicle Rate Quotes', 'value' => number_format($totalRateCards), 'icon' => '💰', 'color' => '#059669'],
        ];

        $columns = [
            'category' => 'Rate Category',
            'origin' => 'Origin / Pickup',
            'destination' => 'Drop Destination',
            'km_limit' => 'KM Limit',
            'included_hours' => 'Included Hours',
            'vehicle_category' => 'Vehicle Category',
            'base_fare' => 'Base Fare (₹)',
            'extra_km_rate' => 'Extra KM Rate (₹)',
            'extra_hour_rate' => 'Extra Hour Rate (₹)',
            'gst_applicable' => 'GST Applicable',
        ];

        // Flatten route * vehicle price
        $flattened = collect();
        foreach ($allRoutes as $route) {
            foreach ($route->vehiclePrices as $vp) {
                $flattened->push([
                    'category' => ucwords(str_replace('_', ' ', $route->category)),
                    'origin' => $route->origin ?? 'Any Location',
                    'destination' => $route->destination ?? 'Any Destination',
                    'km_limit' => $route->km_limit ? "{$route->km_limit} KM" : 'Distance Based',
                    'included_hours' => $route->included_hours ? "{$route->included_hours} Hours" : 'Per Route',
                    'vehicle_category' => $vp->vehicle_category,
                    'base_fare' => $vp->base_fare ? '₹'.number_format((float) $vp->base_fare, 2) : ($vp->vehicle_rent ? 'Rent: ₹'.number_format((float) $vp->vehicle_rent, 2) : 'Variable'),
                    'extra_km_rate' => $vp->extra_km_rate ? '₹'.number_format((float) $vp->extra_km_rate, 2).'/KM' : ($vp->per_km_rate ? '₹'.number_format((float) $vp->per_km_rate, 2).'/KM' : 'Included'),
                    'extra_hour_rate' => $vp->extra_hour_rate ? '₹'.number_format((float) $vp->extra_hour_rate, 2).'/hr' : 'N/A',
                    'gst_applicable' => $vp->gst_applicable ? 'Yes (+GST)' : 'Inclusive',
                ]);
            }
        }

        $page = (int) $request->input('page', 1);
        $perPage = 25;
        $paginator = new LengthAwarePaginator(
            $flattened->forPage($page, $perPage)->values(),
            $flattened->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return [
            'meta' => $categoryMeta,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'preset' => $preset,
            'columns' => $columns,
            'kpis' => $kpis,
            'rows' => $paginator->items(),
            'paginator' => $paginator,
            'exportRows' => $flattened,
        ];
    }

    /**
     * Category 10: Tour Package Reports
     */
    private function getTourPackagesData(Request $request, string $fromDate, string $toDate, string $preset, array $categoryMeta, bool $forExport): array
    {
        $query = Booking::with(['customer', 'vehicle', 'driver'])
            ->where(function ($q) {
                $q->where('rate_category', 'tour_package')
                    ->orWhere('trip_type', 'like', '%Tour%')
                    ->orWhere('notes', 'like', '%Tour%')
                    ->orWhere('notes', 'like', '%Package%');
            })
            ->whereBetween('travel_date', [$fromDate, $toDate]);

        if ($request->filled('status')) {
            $query->where('booking_status', $request->status);
        }
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('booking_id', 'like', "%{$s}%")
                    ->orWhere('pickup_location', 'like', "%{$s}%")
                    ->orWhere('destination', 'like', "%{$s}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$s}%"));
            });
        }

        $allTours = (clone $query)->get();
        $totalCount = $allTours->count();
        $totalRevenue = $allTours->where('booking_status', '!=', 'Cancelled')->sum('total_amount');
        $completedTours = $allTours->where('booking_status', 'Completed')->count();

        $kpis = [
            ['label' => 'Tour Package Bookings', 'value' => number_format($totalCount), 'icon' => '🏖️', 'color' => '#2563EB'],
            ['label' => 'Completed Tours', 'value' => number_format($completedTours), 'icon' => '✅', 'color' => '#059669'],
            ['label' => 'Total Tour Revenue (₹)', 'value' => '₹'.number_format($totalRevenue, 2), 'icon' => '💰', 'color' => '#0D9488'],
        ];

        $columns = [
            'booking_id' => 'Booking ID',
            'customer' => 'Customer Name',
            'contact' => 'Contact',
            'route' => 'Destinations / Route',
            'travel_date' => 'Start Date',
            'return_date' => 'Return Date',
            'vehicle' => 'Assigned Vehicle',
            'driver' => 'Chauffeur',
            'total_amount' => 'Package Cost (₹)',
            'paid_amount' => 'Paid (₹)',
            'balance_amount' => 'Balance (₹)',
            'booking_status' => 'Status',
        ];

        $transform = function (Booking $b) {
            return [
                'booking_id' => $b->booking_id,
                'customer' => $b->customer?->name ?? 'Tourist Guest',
                'contact' => $b->customer?->phone ?? 'N/A',
                'route' => "{$b->pickup_location} → {$b->destination}",
                'travel_date' => $b->travel_date?->format('d M Y') ?? 'N/A',
                'return_date' => $b->return_date?->format('d M Y') ?? 'Same Day',
                'vehicle' => $b->vehicle ? "{$b->vehicle->name} ({$b->vehicle->registration_number})" : 'Unassigned',
                'driver' => $b->driver?->name ?? 'Unassigned',
                'total_amount' => number_format((float) $b->total_amount, 2, '.', ''),
                'paid_amount' => number_format((float) $b->paid_amount, 2, '.', ''),
                'balance_amount' => number_format((float) $b->balance_amount, 2, '.', ''),
                'booking_status' => $b->booking_status,
            ];
        };

        return $this->formatResult($query, $fromDate, $toDate, $preset, $categoryMeta, $columns, $kpis, $transform, $forExport, $request);
    }

    /**
     * Category 11: Ambulance Service Reports
     */
    private function getAmbulanceData(Request $request, string $fromDate, string $toDate, string $preset, array $categoryMeta, bool $forExport): array
    {
        $query = Booking::with(['customer', 'vehicle', 'driver'])
            ->where(function ($q) {
                $q->where('trip_type', 'like', '%Ambulance%')
                    ->orWhere('rate_category', 'ambulance')
                    ->orWhere('notes', 'like', '%Ambulance%')
                    ->orWhere('destination', 'like', '%Hospital%')
                    ->orWhere('destination', 'like', '%AIIMS%');
            })
            ->whereBetween('travel_date', [$fromDate, $toDate]);

        if ($request->filled('status')) {
            $query->where('booking_status', $request->status);
        }
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('booking_id', 'like', "%{$s}%")
                    ->orWhere('pickup_location', 'like', "%{$s}%")
                    ->orWhere('destination', 'like', "%{$s}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$s}%"));
            });
        }

        $allAmbulances = (clone $query)->get();
        $totalCount = $allAmbulances->count();
        $fulfilled = $allAmbulances->where('booking_status', 'Completed')->count();
        $emergencyRevenue = $allAmbulances->where('booking_status', '!=', 'Cancelled')->sum('total_amount');

        $kpis = [
            ['label' => 'Emergency Dispatches', 'value' => number_format($totalCount), 'icon' => '🚑', 'color' => '#DC2626'],
            ['label' => 'Fulfilled Transfers', 'value' => number_format($fulfilled), 'icon' => '✅', 'color' => '#059669'],
            ['label' => 'Ambulance Revenue (₹)', 'value' => '₹'.number_format($emergencyRevenue, 2), 'icon' => '💰', 'color' => '#0D9488'],
        ];

        $columns = [
            'booking_id' => 'Dispatch ID',
            'customer' => 'Caller / Patient',
            'contact' => 'Emergency Contact',
            'pickup_location' => 'Pickup Location',
            'destination' => 'Hospital / Drop',
            'travel_date' => 'Date & Time',
            'vehicle' => 'Ambulance Vehicle',
            'driver' => 'Driver / Paramedic',
            'booking_status' => 'Status',
            'total_amount' => 'Fare (₹)',
            'paid_amount' => 'Paid (₹)',
        ];

        $transform = function (Booking $b) {
            return [
                'booking_id' => $b->booking_id,
                'customer' => $b->customer?->name ?? 'Emergency Patient',
                'contact' => $b->customer?->phone ?? 'N/A',
                'pickup_location' => $b->pickup_location,
                'destination' => $b->destination,
                'travel_date' => ($b->travel_date?->format('d M Y') ?? 'N/A').' '.($b->travel_time ?? ''),
                'vehicle' => $b->vehicle ? "{$b->vehicle->name} ({$b->vehicle->registration_number})" : 'Emergency Fleet',
                'driver' => $b->driver?->name ?? 'Emergency Driver',
                'booking_status' => $b->booking_status,
                'total_amount' => number_format((float) $b->total_amount, 2, '.', ''),
                'paid_amount' => number_format((float) $b->paid_amount, 2, '.', ''),
            ];
        };

        return $this->formatResult($query, $fromDate, $toDate, $preset, $categoryMeta, $columns, $kpis, $transform, $forExport, $request);
    }

    /**
     * Category 12: Cancellation & Refund Reports
     */
    private function getCancellationsRefundsData(Request $request, string $fromDate, string $toDate, string $preset, array $categoryMeta, bool $forExport): array
    {
        $query = Booking::with(['customer', 'cancelledBy', 'payments'])
            ->where('booking_status', 'Cancelled')
            ->whereBetween('travel_date', [$fromDate, $toDate]);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('booking_id', 'like', "%{$s}%")
                    ->orWhere('cancellation_reason', 'like', "%{$s}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%"));
            });
        }

        $allCancelled = (clone $query)->get();
        $totalCancelled = $allCancelled->count();
        $cancelledBookedValue = $allCancelled->sum('total_amount');
        $actualRefundedMoney = Payment::whereIn('booking_id', $allCancelled->pluck('id'))
            ->where('payment_type', 'refund')
            ->sum('amount');

        $kpis = [
            ['label' => 'Total Cancellations', 'value' => number_format($totalCancelled), 'icon' => '❌', 'color' => '#DC2626'],
            ['label' => 'Cancelled Booking Value (₹)', 'value' => '₹'.number_format($cancelledBookedValue, 2), 'icon' => '📉', 'color' => '#E11D48'],
            ['label' => 'Actual Money Refunded (₹)', 'value' => '₹'.number_format($actualRefundedMoney, 2), 'icon' => '↩️', 'color' => '#9333EA'],
        ];

        $columns = [
            'booking_id' => 'Booking ID',
            'customer' => 'Customer Name',
            'contact' => 'Contact',
            'travel_date' => 'Scheduled Date',
            'cancelled_at' => 'Cancelled On',
            'cancelled_by' => 'Cancelled By',
            'cancellation_reason' => 'Cancellation Reason',
            'total_amount' => 'Original Fare (₹)',
            'refund_amount' => 'Refund Paid (₹)',
            'cancellation_status' => 'Refund Status',
        ];

        $transform = function (Booking $b) {
            $refundPaid = $b->payments->where('payment_type', 'refund')->sum('amount');

            return [
                'booking_id' => $b->booking_id,
                'customer' => $b->customer?->name ?? 'Passenger',
                'contact' => $b->customer?->phone ?? 'N/A',
                'travel_date' => $b->travel_date?->format('d M Y') ?? 'N/A',
                'cancelled_at' => $b->cancelled_at?->format('d M Y, h:i A') ?? ($b->updated_at?->format('d M Y') ?? 'N/A'),
                'cancelled_by' => $b->cancelledBy?->name ?? 'Dispatcher / Customer',
                'cancellation_reason' => $b->cancellation_reason ?? 'Customer requested cancellation',
                'total_amount' => number_format((float) $b->total_amount, 2, '.', ''),
                'refund_amount' => number_format((float) $refundPaid, 2, '.', ''),
                'cancellation_status' => $b->cancellation_status ?? ($refundPaid > 0 ? 'Refunded' : 'No Refund'),
            ];
        };

        return $this->formatResult($query, $fromDate, $toDate, $preset, $categoryMeta, $columns, $kpis, $transform, $forExport, $request);
    }

    /**
     * Category 13: Advance Booking & Availability Reports
     */
    private function getAdvanceAvailabilityData(Request $request, string $fromDate, string $toDate, string $preset, array $categoryMeta, bool $forExport): array
    {
        $query = Booking::with(['customer', 'vehicle', 'driver'])
            ->whereBetween('travel_date', [$fromDate, $toDate]);

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('booking_id', 'like', "%{$s}%")
                    ->orWhere('pickup_location', 'like', "%{$s}%")
                    ->orWhere('destination', 'like', "%{$s}%");
            });
        }

        $allBookings = (clone $query)->get();
        $totalAdvance = $allBookings->count();
        $confirmed = $allBookings->whereIn('booking_status', ['Confirmed', 'Vehicle Assigned', 'Driver Assigned'])->count();
        $assignedBoth = $allBookings->filter(fn ($b) => $b->vehicle_id && $b->driver_id)->count();

        // Conflict check: group by date & vehicle
        $conflicts = 0;
        $vehicleDates = [];
        foreach ($allBookings->where('booking_status', '!=', 'Cancelled') as $b) {
            if ($b->vehicle_id && $b->travel_date) {
                $key = $b->vehicle_id.'_'.$b->travel_date->format('Y-m-d');
                if (isset($vehicleDates[$key])) {
                    $conflicts++;
                } else {
                    $vehicleDates[$key] = true;
                }
            }
        }

        $kpis = [
            ['label' => 'Scheduled Bookings', 'value' => number_format($totalAdvance), 'icon' => '🗓️', 'color' => '#2563EB'],
            ['label' => 'Confirmed Status', 'value' => number_format($confirmed), 'icon' => '✅', 'color' => '#059669'],
            ['label' => 'Fully Dispatched (Cab + Driver)', 'value' => number_format($assignedBoth), 'icon' => '🚖', 'color' => '#0D9488'],
            ['label' => 'Potential Schedule Conflicts', 'value' => number_format($conflicts), 'icon' => '⚠️', 'color' => $conflicts > 0 ? '#DC2626' : '#10B981'],
        ];

        $columns = [
            'travel_date' => 'Scheduled Date',
            'travel_time' => 'Time',
            'booking_id' => 'Booking ID',
            'customer' => 'Customer',
            'route' => 'Route',
            'vehicle' => 'Assigned Vehicle',
            'driver' => 'Assigned Chauffeur',
            'booking_status' => 'Booking Status',
            'conflict_flag' => 'Conflict Check',
        ];

        $transform = function (Booking $b) use (&$vehicleDates) {
            $hasConflict = false;
            if ($b->vehicle_id && $b->travel_date && $b->booking_status !== 'Cancelled') {
                $cnt = Booking::where('vehicle_id', $b->vehicle_id)
                    ->whereDate('travel_date', $b->travel_date)
                    ->where('id', '!=', $b->id)
                    ->where('booking_status', '!=', 'Cancelled')
                    ->count();
                $hasConflict = $cnt > 0;
            }

            return [
                'travel_date' => $b->travel_date?->format('d M Y') ?? 'N/A',
                'travel_time' => $b->travel_time ?? 'N/A',
                'booking_id' => $b->booking_id,
                'customer' => $b->customer?->name ?? 'Guest',
                'route' => "{$b->pickup_location} → {$b->destination}",
                'vehicle' => $b->vehicle ? "{$b->vehicle->name} ({$b->vehicle->registration_number})" : 'Pending Vehicle',
                'driver' => $b->driver?->name ?? 'Pending Driver',
                'booking_status' => $b->booking_status,
                'conflict_flag' => $hasConflict ? 'OVERLAP ALERT' : 'CLEAR',
            ];
        };

        return $this->formatResult($query, $fromDate, $toDate, $preset, $categoryMeta, $columns, $kpis, $transform, $forExport, $request);
    }

    /**
     * Category 14: Driver & Vehicle Assignment Reports
     */
    private function getAssignmentsData(Request $request, string $fromDate, string $toDate, string $preset, array $categoryMeta, bool $forExport): array
    {
        $query = Booking::with(['customer', 'vehicle', 'driver'])
            ->whereBetween('travel_date', [$fromDate, $toDate])
            ->where('booking_status', '!=', 'Cancelled');

        if ($request->input('assignment_status') === 'unassigned') {
            $query->whereNull('vehicle_id')->whereNull('driver_id');
        } elseif ($request->input('assignment_status') === 'vehicle_only') {
            $query->whereNotNull('vehicle_id')->whereNull('driver_id');
        } elseif ($request->input('assignment_status') === 'driver_only') {
            $query->whereNull('vehicle_id')->whereNotNull('driver_id');
        } elseif ($request->input('assignment_status') === 'fully_assigned') {
            $query->whereNotNull('vehicle_id')->whereNotNull('driver_id');
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('booking_id', 'like', "%{$s}%")
                    ->orWhere('pickup_location', 'like', "%{$s}%")
                    ->orWhere('destination', 'like', "%{$s}%");
            });
        }

        $allAssignments = (clone $query)->get();
        $totalCount = $allAssignments->count();
        $fullyAssigned = $allAssignments->filter(fn ($b) => $b->vehicle_id && $b->driver_id)->count();
        $missingVehicle = $allAssignments->filter(fn ($b) => ! $b->vehicle_id)->count();
        $missingDriver = $allAssignments->filter(fn ($b) => ! $b->driver_id)->count();

        $kpis = [
            ['label' => 'Active Bookings in Range', 'value' => number_format($totalCount), 'icon' => '📋', 'color' => '#2563EB'],
            ['label' => 'Fully Dispatched', 'value' => number_format($fullyAssigned), 'icon' => '✅', 'color' => '#059669'],
            ['label' => 'Missing Vehicle', 'value' => number_format($missingVehicle), 'icon' => '🚗', 'color' => '#E11D48'],
            ['label' => 'Missing Chauffeur', 'value' => number_format($missingDriver), 'icon' => '👨‍✈️', 'color' => '#DC2626'],
        ];

        $columns = [
            'booking_id' => 'Booking ID',
            'travel_date' => 'Travel Date',
            'customer' => 'Customer',
            'route' => 'Route',
            'vehicle_status' => 'Vehicle Status',
            'vehicle' => 'Assigned Vehicle',
            'driver_status' => 'Driver Status',
            'driver' => 'Assigned Driver',
            'booking_status' => 'Booking Status',
        ];

        $transform = function (Booking $b) {
            return [
                'booking_id' => $b->booking_id,
                'travel_date' => ($b->travel_date?->format('d M Y') ?? 'N/A').' '.($b->travel_time ?? ''),
                'customer' => $b->customer?->name ?? 'Guest',
                'route' => "{$b->pickup_location} → {$b->destination}",
                'vehicle_status' => $b->vehicle_id ? 'ASSIGNED' : 'UNASSIGNED',
                'vehicle' => $b->vehicle ? "{$b->vehicle->name} ({$b->vehicle->registration_number})" : 'None',
                'driver_status' => $b->driver_id ? 'ASSIGNED' : 'UNASSIGNED',
                'driver' => $b->driver?->name ?? 'None',
                'booking_status' => $b->booking_status,
            ];
        };

        return $this->formatResult($query, $fromDate, $toDate, $preset, $categoryMeta, $columns, $kpis, $transform, $forExport, $request);
    }

    /**
     * Category 15: Customer Feedback & Enquiry Reports
     */
    private function getFeedbackEnquiriesData(Request $request, string $fromDate, string $toDate, string $preset, array $categoryMeta, bool $forExport): array
    {
        $feedbacks = Feedback::with('booking')
            ->whereBetween('created_at', [Carbon::parse($fromDate)->startOfDay(), Carbon::parse($toDate)->endOfDay()])
            ->get();

        $enquiries = Enquiry::whereBetween('created_at', [Carbon::parse($fromDate)->startOfDay(), Carbon::parse($toDate)->endOfDay()])
            ->get();

        $avgRating = $feedbacks->count() > 0 ? round($feedbacks->avg('rating'), 1) : 5.0;
        $totalFeedbacks = $feedbacks->count();
        $totalEnquiries = $enquiries->count();

        $kpis = [
            ['label' => 'Customer Ratings Average', 'value' => "★ {$avgRating} / 5", 'icon' => '⭐', 'color' => '#EAB308'],
            ['label' => 'Reviews Received', 'value' => number_format($totalFeedbacks), 'icon' => '💬', 'color' => '#059669'],
            ['label' => 'Website Inquiries', 'value' => number_format($totalEnquiries), 'icon' => '✉️', 'color' => '#2563EB'],
        ];

        $columns = [
            'type' => 'Item Type',
            'date' => 'Date Received',
            'name' => 'Name',
            'contact' => 'Contact Phone / Email',
            'metric' => 'Rating / Subject',
            'message' => 'Comment / Message',
            'status' => 'Status',
        ];

        $combined = collect();

        foreach ($feedbacks as $f) {
            $combined->push([
                'type' => 'Customer Review',
                'date' => $f->created_at?->format('d M Y') ?? 'N/A',
                'name' => $f->customer_name,
                'contact' => 'Booking #'.($f->booking?->booking_id ?? 'N/A'),
                'metric' => str_repeat('★', $f->rating)." ({$f->rating}/5)",
                'message' => $f->comment ?? '',
                'status' => $f->is_approved ? 'Approved' : 'Pending Moderation',
            ]);
        }

        foreach ($enquiries as $e) {
            $combined->push([
                'type' => 'Website Lead / Inquiry',
                'date' => $e->created_at?->format('d M Y') ?? 'N/A',
                'name' => $e->name,
                'contact' => "{$e->phone} / {$e->email}",
                'metric' => $e->subject ?? 'General Inquiry',
                'message' => $e->message,
                'status' => ucfirst($e->status ?? 'New'),
            ]);
        }

        $page = (int) $request->input('page', 1);
        $perPage = 25;
        $paginator = new LengthAwarePaginator(
            $combined->forPage($page, $perPage)->values(),
            $combined->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return [
            'meta' => $categoryMeta,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'preset' => $preset,
            'columns' => $columns,
            'kpis' => $kpis,
            'rows' => $paginator->items(),
            'paginator' => $paginator,
            'exportRows' => $combined,
        ];
    }

    /**
     * Category 16: Document Expiry Reports
     */
    private function getDocumentExpiriesData(Request $request, string $fromDate, string $toDate, string $preset, array $categoryMeta, bool $forExport): array
    {
        $vehicles = Vehicle::all();
        $drivers = Driver::all();

        $rows = collect();
        $expiredCount = 0;
        $expiring30Count = 0;

        foreach ($vehicles as $v) {
            $docs = [
                'Insurance' => [$v->insurance_expiry, $v->insurance_number],
                'Fitness Certificate' => [$v->fitness_expiry, null],
                'Road Permit' => [$v->permit_expiry, null],
                'PUC Certificate' => [$v->puc_expiry, null],
            ];

            foreach ($docs as $docName => [$expiry, $docNo]) {
                if ($expiry) {
                    $status = 'VALID';
                    if ($expiry->isPast()) {
                        $status = 'EXPIRED';
                        $expiredCount++;
                    } elseif ($expiry->diffInDays(now()) <= 30) {
                        $status = 'EXPIRING IN 30 DAYS';
                        $expiring30Count++;
                    }

                    $rows->push([
                        'entity_type' => 'Vehicle',
                        'name' => "{$v->name} ({$v->registration_number})",
                        'document' => $docName,
                        'reference_no' => $docNo ?? $v->registration_number,
                        'expiry_date' => $expiry->format('d M Y'),
                        'days_remaining' => $expiry->isPast() ? ('Overdue by '.$expiry->diffInDays(now()).' days') : ($expiry->diffInDays(now()).' days left'),
                        'status' => $status,
                    ]);
                }
            }
        }

        foreach ($drivers as $d) {
            if ($d->licence_expiry) {
                $status = 'VALID';
                if ($d->licence_expiry->isPast()) {
                    $status = 'EXPIRED';
                    $expiredCount++;
                } elseif ($d->licence_expiry->diffInDays(now()) <= 30) {
                    $status = 'EXPIRING IN 30 DAYS';
                    $expiring30Count++;
                }

                $rows->push([
                    'entity_type' => 'Driver',
                    'name' => "{$d->name} (📞 {$d->mobile})",
                    'document' => 'Commercial Driving Licence',
                    'reference_no' => $d->license_number ?? 'N/A',
                    'expiry_date' => $d->licence_expiry->format('d M Y'),
                    'days_remaining' => $d->licence_expiry->isPast() ? ('Overdue by '.$d->licence_expiry->diffInDays(now()).' days') : ($d->licence_expiry->diffInDays(now()).' days left'),
                    'status' => $status,
                ]);
            }
        }

        if ($request->input('status') === 'expired') {
            $rows = $rows->where('status', 'EXPIRED')->values();
        } elseif ($request->input('status') === 'expiring_soon') {
            $rows = $rows->where('status', 'EXPIRING IN 30 DAYS')->values();
        }

        $kpis = [
            ['label' => 'Total Monitored Documents', 'value' => number_format($rows->count()), 'icon' => '📄', 'color' => '#2563EB'],
            ['label' => 'Expired Documents', 'value' => number_format($expiredCount), 'icon' => '❌', 'color' => '#DC2626'],
            ['label' => 'Expiring Within 30 Days', 'value' => number_format($expiring30Count), 'icon' => '⚠️', 'color' => '#EAB308'],
            ['label' => 'Fully Compliant Documents', 'value' => number_format(max(0, $rows->count() - $expiredCount - $expiring30Count)), 'icon' => '✅', 'color' => '#059669'],
        ];

        $columns = [
            'entity_type' => 'Entity',
            'name' => 'Vehicle / Driver Details',
            'document' => 'Document Type',
            'reference_no' => 'Reference / Lic. No',
            'expiry_date' => 'Expiry Date',
            'days_remaining' => 'Timeline',
            'status' => 'Compliance Status',
        ];

        $page = (int) $request->input('page', 1);
        $perPage = 25;
        $paginator = new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return [
            'meta' => $categoryMeta,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'preset' => $preset,
            'columns' => $columns,
            'kpis' => $kpis,
            'rows' => $paginator->items(),
            'paginator' => $paginator,
            'exportRows' => $rows,
        ];
    }

    /**
     * Category 17: User Activity & Audit Reports
     */
    private function getUserActivityData(Request $request, string $fromDate, string $toDate, string $preset, array $categoryMeta, bool $forExport): array
    {
        $query = BookingStatusHistory::with(['booking', 'changer'])
            ->whereBetween('created_at', [Carbon::parse($fromDate)->startOfDay(), Carbon::parse($toDate)->endOfDay()]);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('remarks', 'like', "%{$s}%")
                    ->orWhere('comment', 'like', "%{$s}%")
                    ->orWhereHas('booking', fn ($b) => $b->where('booking_id', 'like', "%{$s}%"))
                    ->orWhereHas('changer', fn ($u) => $u->where('name', 'like', "%{$s}%"));
            });
        }

        $allActivities = (clone $query)->get();
        $totalActivities = $allActivities->count();
        $dispatchedCount = $allActivities->whereIn('new_status', ['Vehicle Assigned', 'Driver Assigned', 'Trip Started'])->count();

        $kpis = [
            ['label' => 'Total Audit Log Entries', 'value' => number_format($totalActivities), 'icon' => '🔍', 'color' => '#2563EB'],
            ['label' => 'Dispatch Actions Logged', 'value' => number_format($dispatchedCount), 'icon' => '🚖', 'color' => '#059669'],
        ];

        $columns = [
            'created_at' => 'Timestamp',
            'booking_id' => 'Booking ID',
            'changer' => 'Action By',
            'old_status' => 'Previous Status',
            'new_status' => 'Updated Status',
            'remarks' => 'Remarks / System Event',
        ];

        $transform = function (BookingStatusHistory $h) {
            return [
                'created_at' => $h->created_at?->format('d M Y, h:i A') ?? 'N/A',
                'booking_id' => $h->booking?->booking_id ?? 'N/A',
                'changer' => $h->changer ? "{$h->changer->name} ({$h->changer->role})" : 'System Dispatcher',
                'old_status' => $h->old_status ?? 'Initial Creation',
                'new_status' => $h->new_status,
                'remarks' => $h->remarks ?? ($h->comment ?? 'Status transitioned successfully.'),
            ];
        };

        return $this->formatResult($query, $fromDate, $toDate, $preset, $categoryMeta, $columns, $kpis, $transform, $forExport, $request);
    }

    /**
     * Category 18: Invoice Reports
     */
    private function getInvoicesData(Request $request, string $fromDate, string $toDate, string $preset, array $categoryMeta, bool $forExport): array
    {
        $query = Invoice::with(['booking', 'customer'])
            ->whereBetween('issued_at', [Carbon::parse($fromDate)->startOfDay(), Carbon::parse($toDate)->endOfDay()]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('invoice_number', 'like', "%{$s}%")
                    ->orWhereHas('booking', fn ($b) => $b->where('booking_id', 'like', "%{$s}%"))
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%"));
            });
        }

        $allInvoices = (clone $query)->get();
        $totalInvoices = $allInvoices->count();
        $totalTaxable = $allInvoices->sum('subtotal');
        $totalGst = $allInvoices->sum('tax_amount');
        $totalInvoiced = $allInvoices->sum('total_amount');
        $totalPaid = $allInvoices->sum('paid_amount');
        $totalBalance = $allInvoices->sum('balance_amount');

        $kpis = [
            ['label' => 'Total Invoices Issued', 'value' => number_format($totalInvoices), 'icon' => '🧾', 'color' => '#2563EB'],
            ['label' => 'Taxable Subtotal (₹)', 'value' => '₹'.number_format($totalTaxable, 2), 'icon' => '📦', 'color' => '#0D9488'],
            ['label' => 'GST Taxes (₹)', 'value' => '₹'.number_format($totalGst, 2), 'icon' => '🏛️', 'color' => '#9333EA'],
            ['label' => 'Gross Invoiced (₹)', 'value' => '₹'.number_format($totalInvoiced, 2), 'icon' => '💰', 'color' => '#059669'],
            ['label' => 'Invoiced Due (₹)', 'value' => '₹'.number_format($totalBalance, 2), 'icon' => '⚠️', 'color' => '#DC2626'],
        ];

        $columns = [
            'invoice_number' => 'Invoice Number',
            'issued_at' => 'Invoice Date',
            'booking_id' => 'Booking ID',
            'customer' => 'Customer Name',
            'contact' => 'Contact',
            'subtotal' => 'Taxable Subtotal (₹)',
            'tax_amount' => 'GST (₹)',
            'total_amount' => 'Grand Total (₹)',
            'paid_amount' => 'Paid (₹)',
            'balance_amount' => 'Balance (₹)',
            'status' => 'Status',
        ];

        $transform = function (Invoice $inv) {
            return [
                'invoice_number' => $inv->invoice_number,
                'issued_at' => $inv->issued_at?->format('d M Y') ?? 'N/A',
                'booking_id' => $inv->booking?->booking_id ?? 'N/A',
                'customer' => $inv->customer?->name ?? 'Guest Passenger',
                'contact' => $inv->customer?->phone ?? 'N/A',
                'subtotal' => number_format((float) $inv->subtotal, 2, '.', ''),
                'tax_amount' => number_format((float) $inv->tax_amount, 2, '.', ''),
                'total_amount' => number_format((float) $inv->total_amount, 2, '.', ''),
                'paid_amount' => number_format((float) $inv->paid_amount, 2, '.', ''),
                'balance_amount' => number_format((float) $inv->balance_amount, 2, '.', ''),
                'status' => $inv->status,
            ];
        };

        return $this->formatResult($query, $fromDate, $toDate, $preset, $categoryMeta, $columns, $kpis, $transform, $forExport, $request);
    }

    /**
     * Common result formatter with pagination and export mapper.
     */
    private function formatResult(
        Builder $query,
        string $fromDate,
        string $toDate,
        string $preset,
        array $categoryMeta,
        array $columns,
        array $kpis,
        callable $transform,
        bool $forExport,
        ?Request $request = null
    ): array {
        if ($forExport) {
            $allRows = (clone $query)->get()->map($transform);

            return [
                'meta' => $categoryMeta,
                'fromDate' => $fromDate,
                'toDate' => $toDate,
                'preset' => $preset,
                'columns' => $columns,
                'kpis' => $kpis,
                'rows' => $allRows,
                'paginator' => null,
                'exportRows' => $allRows,
            ];
        }

        $paginator = (clone $query)->paginate(25);
        if ($request) {
            $paginator->appends($request->query());
        } elseif (app()->bound('request')) {
            $paginator->withQueryString();
        }
        $rows = collect($paginator->items())->map($transform);

        return [
            'meta' => $categoryMeta,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'preset' => $preset,
            'columns' => $columns,
            'kpis' => $kpis,
            'rows' => $rows,
            'paginator' => $paginator,
            'exportRows' => null,
        ];
    }

    /**
     * Export to Excel (.xlsx) using OpenSpout with formula injection protection.
     */
    public function exportExcel(string $category, Request $request): StreamedResponse
    {
        $data = $this->getReportData($category, $request, true);
        $title = $data['meta']['title'];
        $fromDate = $data['fromDate'];
        $toDate = $data['toDate'];
        $filename = strtolower(str_replace([' ', '/', '&'], ['-', '-', '-'], $title))."-{$fromDate}-to-{$toDate}.xlsx";

        return response()->streamDownload(function () use ($data, $title, $fromDate, $toDate) {
            $writer = new Writer;
            $writer->openToFile('php://output');

            // Metadata banner
            $writer->addRow(Row::fromValues(['VAISHNAVI TOURS & TAXI SERVICE — OFFICIAL BUSINESS REPORT']));
            $writer->addRow(Row::fromValues(['Report Category:', $title]));
            $writer->addRow(Row::fromValues(['Report Date Period:', "{$fromDate} to {$toDate}"]));
            $writer->addRow(Row::fromValues(['Generated On:', now()->format('d M Y, h:i:s A')]));
            $writer->addRow(Row::fromValues([])); // Blank line

            // Summary KPIs
            if (! empty($data['kpis'])) {
                $writer->addRow(Row::fromValues(['--- SUMMARY TOTALS & KPIS ---']));
                foreach ($data['kpis'] as $kpi) {
                    $writer->addRow(Row::fromValues([$kpi['label'], $kpi['value']]));
                }
                $writer->addRow(Row::fromValues([]));
            }

            // Headers
            $headers = array_values($data['columns']);
            $writer->addRow(Row::fromValues($headers));

            // Data rows with Formula Injection sanitization
            foreach ($data['exportRows'] as $row) {
                $rowArray = (array) $row;
                $formattedCells = [];
                foreach (array_keys($data['columns']) as $colKey) {
                    $val = $rowArray[$colKey] ?? '';
                    // Prevent formula injection
                    if (is_string($val) && strlen($val) > 0 && in_array($val[0], ['=', '+', '-', '@'], true)) {
                        $val = "'".$val;
                    }
                    $formattedCells[] = $val;
                }
                $writer->addRow(Row::fromValues($formattedCells));
            }

            $writer->close();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Export to PDF (.pdf) using DomPDF.
     */
    public function exportPdf(string $category, Request $request)
    {
        $data = $this->getReportData($category, $request, true);
        $title = $data['meta']['title'];
        $fromDate = $data['fromDate'];
        $toDate = $data['toDate'];
        $orientation = $data['meta']['orientation'] ?? 'landscape';

        $filename = strtolower(str_replace([' ', '/', '&'], ['-', '-', '-'], $title))."-{$fromDate}-to-{$toDate}.pdf";

        $pdf = Pdf::loadView('admin.reports.pdf', compact('data', 'category', 'title', 'fromDate', 'toDate'))
            ->setPaper('a4', $orientation);

        return $pdf->download($filename);
    }
}
