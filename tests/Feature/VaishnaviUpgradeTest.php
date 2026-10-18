<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Driver;
use App\Models\Enquiry;
use App\Models\Notification;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\BookingService;
use App\Services\InvoiceDocumentService;
use App\Services\TripDurationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class VaishnaviUpgradeTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_id_prefix_generation_for_all_four_categories(): void
    {
        $service = app(BookingService::class);

        $webId = $service->generateBookingId('WEB');
        $this->assertSame('WEB-000001', $webId);

        $corpId = $service->generateBookingId('CORP');
        $this->assertSame('CORP-000001', $corpId);

        $manId = $service->generateBookingId('MAN');
        $this->assertSame('MAN-000001', $manId);

        $tpId = $service->generateBookingId('TP');
        $this->assertSame('TP-000001', $tpId);
    }

    public function test_four_hour_advance_booking_notice_enforcement(): void
    {
        $service = app(BookingService::class);

        // Attempting to book 1 hour from now should throw ValidationException
        $shortNoticeDate = Carbon::now()->addHour()->format('Y-m-d');
        $shortNoticeTime = Carbon::now()->addHour()->format('H:i');

        $this->expectException(ValidationException::class);

        $service->validateAdvanceNotice($shortNoticeDate, $shortNoticeTime);
    }

    public function test_sufficient_advance_booking_notice_passes(): void
    {
        $service = app(BookingService::class);

        // Booking 6 hours from now should pass
        $validDate = Carbon::now()->addHours(6)->format('Y-m-d');
        $validTime = Carbon::now()->addHours(6)->format('H:i');

        $result = $service->validateAdvanceNotice($validDate, $validTime);
        $this->assertTrue($result);
    }

    public function test_trip_duration_service_calculates_days_and_hours(): void
    {
        $service = new TripDurationService;
        $pickupDate = Carbon::today()->format('Y-m-d');
        $returnDate = Carbon::today()->addDays(2)->format('Y-m-d');

        $calc = $service->calculate(
            $pickupDate,
            '09:00',
            $returnDate,
            '18:00'
        );

        $this->assertSame(3, $calc['calendar_days']);
        $this->assertSame(3, $calc['days']);
        $this->assertSame(57.0, $calc['total_hours']);
        $this->assertStringContainsString('3 Calendar Days', $calc['breakdown']);
    }

    public function test_invoice_service_calculates_5_percent_gst_and_exact_system_generated_note(): void
    {
        $user = User::create([
            'name' => 'Amit Sharma',
            'email' => 'amit@example.com',
            'phone' => '9876543210',
            'password' => bcrypt('Password@123'),
            'role' => 'customer',
        ]);

        $booking = Booking::create([
            'booking_id' => 'WEB-000001',
            'booking_category' => 'WEB',
            'customer_id' => $user->id,
            'trip_type' => 'One-Way',
            'pickup_location' => 'Bilaspur',
            'destination' => 'Raipur',
            'travel_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
            'travel_time' => '10:00',
            'total_amount' => 3150.00,
            'taxable_amount' => 3000.00,
            'gst_rate' => 5.00,
            'cgst_amount' => 75.00,
            'sgst_amount' => 75.00,
            'gst_amount' => 150.00,
            'paid_amount' => 3150.00,
            'balance_amount' => 0.00,
            'booking_status' => 'Confirmed',
            'payment_status' => 'Paid',
        ]);

        $service = app(InvoiceDocumentService::class);
        $invoice = $service->generateForBooking($booking);

        // 5% GST split: CGST 2.5%, SGST 2.5%
        $this->assertEquals(5.00, (float) $invoice->gst_percentage);
        $this->assertNotNull($invoice->cgst_amount);
        $this->assertNotNull($invoice->sgst_amount);
        $this->assertEquals((float) $invoice->cgst_amount, (float) $invoice->sgst_amount);

        // Exact legal note requirement
        $this->assertSame(
            'THIS IS A SYSTEM-GENERATED INVOICE. SIGNATURE NOT REQUIRED.',
            $invoice->footer_note
        );
    }

    public function test_trip_requires_zero_balance_or_credit_exception_for_closure(): void
    {
        $user = User::create([
            'name' => 'Corporate Client',
            'email' => 'corp@example.com',
            'phone' => '9876543211',
            'password' => bcrypt('Password@123'),
            'role' => 'customer',
        ]);

        $booking = Booking::create([
            'booking_id' => 'MAN-000001',
            'booking_category' => 'MAN',
            'customer_id' => $user->id,
            'trip_type' => 'One-Way',
            'pickup_location' => 'Bilaspur',
            'destination' => 'Korba',
            'travel_date' => Carbon::now()->addDays(1)->format('Y-m-d'),
            'travel_time' => '12:00',
            'total_amount' => 2000.00,
            'paid_amount' => 500.00,
            'balance_amount' => 1500.00,
            'booking_status' => 'Confirmed',
            'payment_status' => 'Pending',
        ]);

        $trip = Trip::create([
            'booking_id' => $booking->id,
            'status' => 'Completed',
            'trip_status' => 'Open',
            'journey_status' => 'Completed',
        ]);

        $this->assertFalse($trip->isFinanciallySettled());
        $this->assertTrue($trip->hasOutstandingBalance());
    }

    public function test_driver_assigned_vs_unassigned_scopes(): void
    {
        $driver1 = Driver::create([
            'name' => 'Ramesh Kumar',
            'mobile' => '9988776655',
            'license_number' => 'CG10-2020-001',
            'license_expiry' => Carbon::now()->addYears(2),
            'status' => 'Available',
        ]);

        $vehicle = Vehicle::create([
            'name' => 'Dzire',
            'registration_number' => 'CG-10-AB-9999',
            'vehicle_type' => 'Sedan',
            'seating_capacity' => 4,
            'ac_non_ac' => 'AC',
            'per_km_rate' => 12.00,
            'status' => 'Available',
            'insurance_expiry_date' => Carbon::now()->addMonths(6),
            'puc_expiry_date' => Carbon::now()->addMonths(6),
        ]);

        $driver2 = Driver::create([
            'name' => 'Suresh Verma',
            'mobile' => '9988776644',
            'license_number' => 'CG10-2020-002',
            'license_expiry' => Carbon::now()->addYears(2),
            'status' => 'On Duty',
            'assigned_vehicle_id' => $vehicle->id,
        ]);

        $unassigned = Driver::unassigned()->pluck('id')->toArray();
        $this->assertContains($driver1->id, $unassigned);
        $this->assertNotContains($driver2->id, $unassigned);

        $assigned = Driver::assigned()->pluck('id')->toArray();
        $this->assertContains($driver2->id, $assigned);
        $this->assertNotContains($driver1->id, $assigned);
    }

    public function test_travel_partner_booking_creation_and_attributes(): void
    {
        $user = User::create([
            'name' => 'Agent Guest',
            'email' => 'partner_guest@example.com',
            'phone' => '9123456789',
            'password' => bcrypt('Password@123'),
            'role' => 'customer',
        ]);

        $booking = Booking::create([
            'booking_id' => Booking::generateBookingId('TP'),
            'booking_category' => 'TP',
            'customer_id' => $user->id,
            'trip_type' => 'One-Way',
            'pickup_location' => 'Bilaspur',
            'destination' => 'Raipur Airport',
            'travel_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
            'travel_time' => '07:30',
            'total_amount' => 2500.00,
            'paid_amount' => 500.00,
            'balance_amount' => 2000.00,
            'booking_status' => 'Confirmed',
            'payment_status' => 'Partial',
            'travel_partner_name' => 'Sharma Travels & Agency',
            'travel_partner_phone' => '9827112233',
            'travel_partner_email' => 'contact@sharmatravels.in',
            'travel_partner_address' => 'Shop 12, Bus Stand Road, Bilaspur',
            'travel_partner_commission' => 300.00,
            'travel_partner_notes' => 'Referral booking, voucher #ST-9912',
        ]);

        $this->assertStringStartsWith('TP-', $booking->booking_id);
        $this->assertSame('TP', $booking->booking_category);
        $this->assertSame('Sharma Travels & Agency', $booking->travel_partner_name);
        $this->assertSame('9827112233', $booking->travel_partner_phone);
        $this->assertSame('Shop 12, Bus Stand Road, Bilaspur', $booking->travel_partner_address);
        $this->assertEquals(300.00, (float) $booking->travel_partner_commission);

        // Verify InvoiceDocumentService reflects travel partner in document notes
        $doc = app(InvoiceDocumentService::class)->forBooking($booking);
        $this->assertTrue(collect($doc['notes'])->some(fn ($n) => str_contains($n, 'Sharma Travels & Agency')));
    }

    public function test_custom_fare_booking_succeeds_even_when_route_rate_not_configured(): void
    {
        $admin = User::create([
            'name' => 'Dispatch Admin',
            'email' => 'admin@vaishnavitours.in',
            'phone' => '9244784443',
            'password' => bcrypt('Password@123'),
            'role' => 'admin',
        ]);

        $customer = User::create([
            'name' => 'Interstate Passenger',
            'email' => 'passenger@example.com',
            'phone' => '9998887776',
            'password' => bcrypt('Password@123'),
            'role' => 'customer',
        ]);

        $this->actingAs($admin);

        $bookingService = app(BookingService::class);

        // Raipur to Jharsuguda is not in rate routes table, but admin provides custom fare 5500.00
        $data = [
            'booking_category' => 'MAN',
            'trip_type' => 'One-Way',
            'rate_category' => 'outstation',
            'pickup_location' => 'Raipur',
            'destination' => 'Jharsuguda',
            'vehicle_category' => 'Sedan',
            'travel_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
            'travel_time' => '08:00',
            'total_amount' => 5500.00,
            'paid_amount' => 1000.00,
            'booking_status' => 'Confirmed',
            'payment_status' => 'Partial',
            'is_custom_fare' => true,
        ];

        $booking = $bookingService->createBooking($data, $customer);

        $this->assertNotNull($booking->id);
        $this->assertSame('Raipur', $booking->pickup_location);
        $this->assertSame('Jharsuguda', $booking->destination);
        $this->assertEquals(5500.00, (float) $booking->total_amount);
        $this->assertTrue($booking->pricing_details['custom_fare'] ?? false);
    }

    public function test_vehicle_number_not_shown_to_customer_during_booking(): void
    {
        $customer = User::create([
            'name' => 'Privacy Conscious Passenger',
            'email' => 'privacy@example.com',
            'phone' => '9876543219',
            'password' => bcrypt('Password@123'),
            'role' => 'customer',
        ]);

        $vehicle = Vehicle::create([
            'name' => 'Maruti Suzuki Dzire',
            'registration_number' => 'CG-10-SECRET-44',
            'vehicle_type' => 'Sedan',
            'seating_capacity' => 4,
            'ac_non_ac' => 'AC',
            'status' => 'Available',
        ]);

        $booking = Booking::create([
            'booking_id' => 'VT-WEB-7788',
            'booking_category' => 'WEB',
            'customer_id' => $customer->id,
            'trip_type' => 'One-Way',
            'pickup_location' => 'Bilaspur',
            'destination' => 'Raipur',
            'travel_date' => Carbon::now()->addDays(1)->format('Y-m-d'),
            'travel_time' => '10:00',
            'vehicle_id' => $vehicle->id,
            'booking_status' => 'Confirmed', // During booking stage
            'total_amount' => 2200.00,
            'paid_amount' => 0.00,
            'balance_amount' => 2200.00,
            'payment_status' => 'Pending',
        ]);

        $this->actingAs($customer);

        // 1. Customer Dashboard
        $dashResponse = $this->get('/customer/dashboard');
        $dashResponse->assertStatus(200);
        $dashResponse->assertDontSee('CG-10-SECRET-44');
        $dashResponse->assertSee('Shared on dispatch');

        // 2. Customer Bookings Index
        $indexResponse = $this->get('/customer/bookings');
        $indexResponse->assertStatus(200);
        $indexResponse->assertDontSee('CG-10-SECRET-44');
        $indexResponse->assertSee('Cab number shared on dispatch');

        // 3. Customer Booking Show
        $showResponse = $this->get('/customer/bookings/'.$booking->id);
        $showResponse->assertStatus(200);
        $showResponse->assertDontSee('CG-10-SECRET-44');
        $showResponse->assertSee('Vehicle number shared on dispatch');

        // 4. Once trip is Started, active cab plate is visible to customer
        $booking->update(['booking_status' => 'Started']);
        $startedResponse = $this->get('/customer/bookings/'.$booking->id);
        $startedResponse->assertStatus(200);
        $startedResponse->assertSee('CG-10-SECRET-44');
    }

    public function test_unconfigured_route_automatically_converts_to_query_and_creates_admin_notification(): void
    {
        $admin = User::create([
            'name' => 'Dispatch Command Admin',
            'email' => 'admin-query@vaishnavitours.in',
            'phone' => '9179484443',
            'password' => bcrypt('Password@123'),
            'role' => 'admin',
        ]);

        $customer = User::create([
            'name' => 'Unconfigured Route Customer',
            'email' => 'unconfigured@example.com',
            'phone' => '9131613010',
            'password' => bcrypt('Password@123'),
            'role' => 'customer',
        ]);

        $bookingService = app(BookingService::class);

        // Submit route without pre-configured pricing in rate_routes table
        $data = [
            'customer_name' => 'Unconfigured Route Customer',
            'mobile' => '9131613010',
            'email' => 'unconfigured@example.com',
            'trip_type' => 'One-Way',
            'rate_category' => 'outstation',
            'pickup_location' => 'Bilaspur',
            'destination' => 'Jagdalpur Remote Bastar Hub',
            'vehicle_category' => 'Sedan',
            'travel_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
            'travel_time' => '07:30',
            'terms_accepted' => '1',
        ];

        // Should NOT throw "Rate not configured" validation exception
        $booking = $bookingService->createBooking($data, $customer);

        $this->assertNotNull($booking->id);
        $this->assertSame('Bilaspur', $booking->pickup_location);
        $this->assertSame('Jagdalpur Remote Bastar Hub', $booking->destination);
        $this->assertEquals(0.00, (float) $booking->total_amount);
        $this->assertTrue($booking->pricing_details['is_query'] ?? false);
        $this->assertSame('unconfigured_route_query', $booking->pricing_details['quote_type'] ?? '');

        // 1. Verify Enquiry record was automatically created
        $enquiry = Enquiry::where('converted_booking_id', $booking->id)->first();
        $this->assertNotNull($enquiry);
        $this->assertSame('Bilaspur', $enquiry->pickup_location);
        $this->assertSame('Jagdalpur Remote Bastar Hub', $enquiry->destination);
        $this->assertSame('9131613010', $enquiry->phone);

        // 2. Verify Admin Notification was created
        $adminNotif = Notification::whereNull('user_id')
            ->where('type', 'query')
            ->latest()
            ->first();
        $this->assertNotNull($adminNotif);
        $this->assertStringContainsString('Jagdalpur Remote Bastar Hub', $adminNotif->message);

        // 3. Verify Admin Dashboard displays this query alert
        $this->actingAs($admin);
        $adminDash = $this->get('/admin/dashboard');
        $adminDash->assertStatus(200);
        $adminDash->assertSee('Urgent Route Queries');
        $adminDash->assertSee('Jagdalpur Remote Bastar Hub');
        $adminDash->assertSee('Unconfigured Route Customer');
    }

    public function test_other_location_booking_converts_to_query_without_price_and_shows_on_admin_dashboard(): void
    {
        $admin = User::create([
            'name' => 'Dispatch Admin 2',
            'email' => 'dispatch2@vaishnavitours.com',
            'phone' => '9131613012',
            'password' => bcrypt('Admin@123'),
            'role' => 'admin',
        ]);

        $postData = [
            'customer_name' => 'Other Location Customer',
            'mobile' => '9131613099',
            'email' => 'otherloc@example.com',
            'trip_type' => 'One-Way',
            'rate_category' => 'outstation',
            'pickup_location' => 'Other',
            'pickup_other' => 'Janjgir Champa Industrial Area',
            'destination' => 'Bilaspur',
            'vehicle_category' => 'Sedan',
            'travel_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
            'travel_time' => '10:00',
            'is_custom_query' => '1',
            'terms_accepted' => '1',
        ];

        // 1. Submit through the public booking store route
        $response = $this->post(route('booking.store'), $postData);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // 2. Verify booking is created as query with 0 price
        $booking = Booking::where('destination', 'Bilaspur')
            ->where('pickup_location', 'Janjgir Champa Industrial Area')
            ->first();

        $this->assertNotNull($booking);
        $this->assertSame('Janjgir Champa Industrial Area', $booking->pickup_location);
        $this->assertSame('Bilaspur', $booking->destination);
        $this->assertEquals(0.00, (float) $booking->total_amount);
        $this->assertTrue($booking->pricing_details['is_query'] ?? false);
        $this->assertSame('custom_location_query', $booking->pricing_details['quote_type'] ?? '');

        // 3. Verify Enquiry record was automatically created
        $enquiry = Enquiry::where('converted_booking_id', $booking->id)->first();
        $this->assertNotNull($enquiry);
        $this->assertSame('Janjgir Champa Industrial Area', $enquiry->pickup_location);
        $this->assertSame('Bilaspur', $enquiry->destination);
        $this->assertSame('9131613099', $enquiry->phone);
        $this->assertStringContainsString('Custom Location Query', $enquiry->subject);

        // 4. Verify Admin Notification
        $adminNotif = Notification::whereNull('user_id')
            ->where('type', 'query')
            ->where('title', 'like', '%Custom Location Query%')
            ->latest()
            ->first();
        $this->assertNotNull($adminNotif);
        $this->assertStringContainsString('Janjgir Champa Industrial Area', $adminNotif->title);

        // 5. Verify Admin Dashboard shows the query alert
        $this->actingAs($admin);
        $adminDash = $this->get('/admin/dashboard');
        $adminDash->assertStatus(200);
        $adminDash->assertSee('Urgent Route Queries');
        $adminDash->assertSee('Janjgir Champa Industrial Area');
        $adminDash->assertSee('Other Location Customer');
    }

    public function test_google_business_profile_and_reviews_integration(): void
    {
        // 1. Verify default config has resolved Vaishnavi Tours listing & share link
        $this->assertSame('https://share.google/0Z3hLSDusfgPwsJx0', config('vaishnavi.google_share_url'));
        $this->assertStringContainsString('/g/11tjf7l5kx', config('vaishnavi.google_maps_url'));
        $this->assertStringContainsString('Vaishnavi+Tours+Taxi+Cab+Service+Car+Rental', config('vaishnavi.google_maps_url'));
        $this->assertStringContainsString('#lrd=0x0:0x0,3,,,', config('vaishnavi.google_write_review_url'));
        $this->assertStringContainsString('#lrd=0x0:0x0,1,,,', config('vaishnavi.google_all_reviews_url'));
        $this->assertSame('4.9', (string) config('vaishnavi.google_rating'));

        // 2. Verify Home page displays verified Google Reviews section with working buttons
        $home = $this->get('/');
        $home->assertStatus(200);
        $home->assertSee('Google Verified Business');
        $home->assertSee('4.9');
        $home->assertSee('160+');
        $home->assertSee('Write a Review');
        $home->assertSee('Read All Reviews');
        $home->assertSee(config('vaishnavi.google_write_review_url'));
        $home->assertSee(config('vaishnavi.google_all_reviews_url'));

        // 3. Verify Feedback page displays Google Reviews banner
        $feedback = $this->get('/feedback');
        $feedback->assertStatus(200);
        $feedback->assertSee('Verified Google Profile');
        $feedback->assertSee('Write a Review on Google');
        $feedback->assertSee('Read on Google Maps');

        // 4. Verify Admin Settings allows updating and viewing Google Profile settings
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $adminSettings = $this->get('/admin/settings');
        $adminSettings->assertStatus(200);
        $adminSettings->assertSee('Google Business Profile &amp; Reviews Integration', false);
        $adminSettings->assertSee('/g/11tjf7l5kx');
        $adminSettings->assertSee('https://share.google/0Z3hLSDusfgPwsJx0');
    }
}
