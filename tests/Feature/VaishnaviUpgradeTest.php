<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Driver;
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
}
