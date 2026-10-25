<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\RateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_booking_page_loads_with_vehicles(): void
    {
        Vehicle::create([
            'name' => 'Maruti Suzuki Dzire',
            'registration_number' => 'CG-10-AB-1204',
            'vehicle_type' => 'Sedan',
            'seating_capacity' => 4,
            'ac_non_ac' => 'AC',
            'per_km_rate' => 13.00,
            'per_hour_rate' => 220.00,
            'status' => 'Available',
        ]);

        $response = $this->get('/booking');

        $response->assertStatus(200);
        $response->assertSee('Book Your Cab with Vaishnavi Tours');
        $response->assertSee('name="vehicle_category"', false);
        $response->assertSee('Select pickup city');
        $response->assertSee('terms_accepted');
        $response->assertSee(route('terms-and-conditions'));
        $response->assertSee(route('cancellation-refund-policy'));
    }

    public function test_get_booking_page_empty_vehicles_shows_graceful_message(): void
    {
        $response = $this->get('/booking');

        $response->assertStatus(200);
        $response->assertDontSee('500 - Server Error');
    }

    public function test_terms_and_conditions_page_renders_all_sections(): void
    {
        $response = $this->get('/terms-and-conditions');

        $response->assertStatus(200);
        $response->assertSee('Taxi Service Cancellation Policy', false);
        $response->assertSee('1. Booking Confirmation', false);
        $response->assertSee('2. Cancellation Policy', false);
        $response->assertSee('3. Rescheduling', false);
        $response->assertSee('4. Waiting', false);
        $response->assertSee('5. Fare', false);
        $response->assertSee('6. Vehicle', false);
        $response->assertSee('7. Customer Responsibilities', false);
        $response->assertSee('8. Driver', false);
        $response->assertSee('9. Unavoidable Circumstances', false);
        $response->assertSee('10. General Terms', false);
        $response->assertSee('Privacy Policy', false);
        $response->assertSee('applicable transaction or processing charges, if any', false);
    }

    public function test_cancellation_policy_page_renders_properly(): void
    {
        $response = $this->get('/cancellation-refund-policy');

        $response->assertStatus(200);
        $response->assertSee('Taxi Booking Cancellation', false);
        $response->assertSee('applicable transaction or processing charges, if any', false);
        $response->assertSee(route('terms-and-conditions'));
    }

    public function test_post_booking_validation_fails_safely_without_500(): void
    {
        $response = $this->from('/booking')->post('/booking', []);

        $response->assertStatus(302);
        $response->assertRedirect('/booking');
        $response->assertSessionHasErrors([
            'customer_name',
            'mobile',
            'pickup_location',
            'destination',
            'rate_category',
            'vehicle_category',
            'travel_date',
            'travel_time',
            'terms_accepted',
        ]);
    }

    public function test_post_booking_fails_when_terms_not_accepted(): void
    {
        $vehicle = Vehicle::create([
            'name' => 'Maruti Suzuki Dzire',
            'registration_number' => 'CG-10-AB-1204',
            'vehicle_type' => 'Sedan',
            'seating_capacity' => 4,
            'ac_non_ac' => 'AC',
            'per_km_rate' => 13.00,
            'per_hour_rate' => 220.00,
            'status' => 'Available',
        ]);

        $response = $this->from('/booking')->post('/booking', [
            'customer_name' => 'Demo Customer',
            'mobile' => '9876543210',
            'trip_type' => 'One-Way',
            'rate_category' => 'airport',
            'vehicle_category' => 'Sedan',
            'pickup_location' => 'Mangal Chowk, Bilaspur',
            'destination' => 'Raipur Airport',
            'travel_date' => now()->addDay()->format('Y-m-d'),
            'travel_time' => '10:00',
            'vehicle_id' => $vehicle->id,
            // terms_accepted missing
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/booking');
        $response->assertSessionHasErrors(['terms_accepted']);
    }

    public function test_post_booking_past_date_validation(): void
    {
        $response = $this->from('/booking')->post('/booking', [
            'customer_name' => 'Demo Customer',
            'mobile' => '9876543210',
            'trip_type' => 'One-Way',
            'pickup_location' => 'Mangal Chowk, Bilaspur',
            'destination' => 'Raipur Airport',
            'travel_date' => '2020-01-01',
            'travel_time' => '10:00',
            'vehicle_id' => '1',
            'terms_accepted' => '1',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/booking');
        $response->assertSessionHasErrors(['travel_date']);
    }

    public function test_post_booking_creates_booking_for_guest(): void
    {
        $this->seed(RateSeeder::class);
        $vehicle = Vehicle::create([
            'name' => 'Toyota Innova Crysta',
            'registration_number' => 'CG-10-TR-9988',
            'vehicle_type' => 'Innova Crysta',
            'seating_capacity' => 7,
            'ac_non_ac' => 'AC',
            'per_km_rate' => 22.00,
            'per_hour_rate' => 350.00,
            'status' => 'Available',
        ]);

        $postData = [
            'customer_name' => 'Guest Traveler',
            'mobile' => '9876543211',
            'email' => 'guest@example.com',
            'trip_type' => 'Airport Transfer',
            'rate_category' => 'airport',
            'vehicle_category' => 'Innova Crysta',
            'pickup_location' => 'Bilaspur',
            'destination' => 'Raipur Airport',
            'travel_date' => now()->addDays(2)->format('Y-m-d'),
            'travel_time' => '09:30',
            'vehicle_id' => $vehicle->id,
            'total_amount' => 1,
            'notes' => 'Need child booster seat',
            'terms_accepted' => '1',
        ];

        $response = $this->post('/booking', $postData);

        $booking = Booking::where('pickup_location', 'Bilaspur')->first();
        $this->assertNotNull($booking);
        $this->assertEquals('Pending', $booking->booking_status);
        $this->assertMatchesRegularExpression('/^(VT|WEB)-\\d+$/', $booking->booking_id);
        $this->assertEquals($vehicle->id, $booking->vehicle_id);
        $this->assertSame('airport', $booking->rate_category);
        $this->assertSame('4500.00', $booking->total_amount);
        $this->assertSame(true, $booking->pricing_details['gst_applicable'] === false);
        $this->assertTrue($booking->terms_accepted);
        $this->assertNotNull($booking->terms_accepted_at);
        $this->assertEquals('1.0', $booking->terms_version);

        $response->assertRedirect(route('booking.success', $booking->id));

        // Follow redirect to booking success page
        $successResponse = $this->get(route('booking.success', $booking->id));
        $successResponse->assertStatus(200);
        $successResponse->assertSee($booking->booking_id);
        $successResponse->assertSee('Guest Traveler');
        $successResponse->assertSee('Booking Request Received');
        $successResponse->assertSee(route('terms-and-conditions'));
    }

    public function test_post_booking_associates_with_authenticated_customer(): void
    {
        $this->seed(RateSeeder::class);
        $user = User::factory()->create([
            'name' => 'Registered Customer',
            'phone' => '9123456780',
            'role' => 'customer',
        ]);

        $vehicle = Vehicle::create([
            'name' => 'Maruti Suzuki Dzire',
            'registration_number' => 'CG-10-AB-1204',
            'vehicle_type' => 'Sedan',
            'seating_capacity' => 4,
            'ac_non_ac' => 'AC',
            'per_km_rate' => 13.00,
            'per_hour_rate' => 220.00,
            'status' => 'Available',
        ]);

        $postData = [
            'customer_name' => $user->name,
            'mobile' => $user->phone,
            'trip_type' => 'One-Way',
            'rate_category' => 'outstation',
            'vehicle_category' => 'Sedan',
            'pickup_location' => 'Bilaspur',
            'destination' => 'Raigarh',
            'travel_date' => now()->addDays(3)->format('Y-m-d'),
            'travel_time' => '06:00',
            'vehicle_id' => $vehicle->id,
            'terms_accepted' => '1',
        ];

        $response = $this->actingAs($user)->post('/booking', $postData);

        $booking = Booking::where('destination', 'Raigarh')->first();
        $this->assertNotNull($booking);
        $this->assertEquals($user->id, $booking->customer_id);
        $this->assertTrue($booking->terms_accepted);
        $this->assertNotNull($booking->terms_accepted_at);

        $response->assertRedirect(route('booking.success', $booking->id));
    }

    public function test_post_booking_unconfigured_route_converts_to_quotation_query(): void
    {
        $response = $this->from('/booking')->post('/booking', [
            'customer_name' => 'Guest Traveler',
            'mobile' => '9876543212',
            'trip_type' => 'One-Way',
            'rate_category' => 'outstation',
            'vehicle_category' => 'Sedan',
            'pickup_location' => 'Korba',
            'destination' => 'Ambikapur',
            'travel_date' => now()->addDay()->format('Y-m-d'),
            'travel_time' => '08:00',
            'terms_accepted' => '1',
        ]);

        $booking = Booking::where('destination', 'Ambikapur')->first();
        $this->assertNotNull($booking);
        $this->assertEquals(0.00, (float) $booking->total_amount);
        $this->assertTrue($booking->pricing_details['is_query'] ?? false);
        $response->assertRedirect(route('booking.success', $booking->id));
    }

    public function test_booking_id_generation_sequential(): void
    {
        $id1 = Booking::generateBookingId();
        $this->assertEquals('VT-1001', $id1);

        $user = User::factory()->create();
        Booking::create([
            'booking_id' => $id1,
            'customer_id' => $user->id,
            'trip_type' => 'One-Way',
            'pickup_location' => 'Point A',
            'destination' => 'Point B',
            'travel_date' => now()->addDay(),
            'travel_time' => '10:00',
            'total_amount' => 1500,
            'paid_amount' => 0,
            'balance_amount' => 1500,
            'payment_status' => 'Pending',
            'booking_status' => 'Pending',
            'terms_accepted' => true,
        ]);

        $id2 = Booking::generateBookingId();
        $this->assertEquals('VT-1002', $id2);
    }

    public function test_post_booking_one_way_with_stale_return_date_succeeds_without_error(): void
    {
        $this->seed(RateSeeder::class);

        $travelDate = now()->addDays(10)->format('Y-m-d');
        $staleReturnDate = now()->addDay()->format('Y-m-d'); // Prior to travel_date

        $response = $this->post('/booking', [
            'customer_name' => 'One Way Traveler',
            'mobile' => '9876543210',
            'trip_type' => 'One-Way',
            'rate_category' => 'outstation',
            'vehicle_category' => 'Sedan',
            'pickup_location' => 'Bilaspur',
            'destination' => 'Korba',
            'travel_date' => $travelDate,
            'travel_time' => '08:00',
            'return_date' => $staleReturnDate,
            'return_time' => '18:00',
            'terms_accepted' => '1',
        ]);

        $response->assertSessionHasNoErrors();
        $user = User::where('name', 'One Way Traveler')->first();
        $this->assertNotNull($user);
        $booking = Booking::where('customer_id', $user->id)->first();
        $this->assertNotNull($booking);
        $this->assertNull($booking->return_date);
    }

    public function test_post_booking_round_trip_validates_return_date(): void
    {
        $this->seed(RateSeeder::class);

        $travelDate = now()->addDays(5)->format('Y-m-d');
        $invalidReturnDate = now()->addDays(2)->format('Y-m-d'); // Before travel date

        // 1. Invalid return date (before travel_date)
        $response = $this->from('/booking')->post('/booking', [
            'customer_name' => 'Round Trip Traveler',
            'mobile' => '9876543210',
            'trip_type' => 'Round-Trip',
            'rate_category' => 'round_trip',
            'vehicle_category' => 'Sedan',
            'estimated_km' => 300,
            'pickup_location' => 'Bilaspur',
            'destination' => 'Raipur',
            'travel_date' => $travelDate,
            'travel_time' => '08:00',
            'return_date' => $invalidReturnDate,
            'return_time' => '20:00',
            'terms_accepted' => '1',
        ]);

        $response->assertSessionHasErrors(['return_date']);
        $this->assertEquals(
            'The return date must be on or after the travel date.',
            session('errors')->first('return_date')
        );

        // 2. Missing return date on Round-Trip
        $response2 = $this->from('/booking')->post('/booking', [
            'customer_name' => 'Round Trip Traveler',
            'mobile' => '9876543210',
            'trip_type' => 'Round-Trip',
            'rate_category' => 'round_trip',
            'vehicle_category' => 'Sedan',
            'estimated_km' => 300,
            'pickup_location' => 'Bilaspur',
            'destination' => 'Raipur',
            'travel_date' => $travelDate,
            'travel_time' => '08:00',
            'return_date' => '',
            'terms_accepted' => '1',
        ]);

        $response2->assertSessionHasErrors(['return_date']);
        $this->assertEquals(
            'Please select a return date for your round trip journey.',
            session('errors')->first('return_date')
        );

        // 3. Valid return date on Round-Trip succeeds
        $validReturnDate = now()->addDays(7)->format('Y-m-d');
        $response3 = $this->post('/booking', [
            'customer_name' => 'Round Trip Traveler',
            'mobile' => '9876543210',
            'trip_type' => 'Round-Trip',
            'rate_category' => 'round_trip',
            'vehicle_category' => 'Sedan',
            'estimated_km' => 300,
            'pickup_location' => 'Bilaspur',
            'destination' => 'Raipur',
            'travel_date' => $travelDate,
            'travel_time' => '08:00',
            'return_date' => $validReturnDate,
            'return_time' => '20:00',
            'terms_accepted' => '1',
        ]);

        $response3->assertSessionHasNoErrors();
        $user = User::where('name', 'Round Trip Traveler')->first();
        $this->assertNotNull($user);
        $booking = Booking::where('customer_id', $user->id)->first();
        $this->assertNotNull($booking);
        $this->assertEquals($validReturnDate, $booking->return_date->format('Y-m-d'));
    }

    public function test_booking_with_current_date_and_time_succeeds(): void
    {
        $this->seed(RateSeeder::class);
        $vehicle = Vehicle::create([
            'name' => 'Swift Dzire',
            'registration_number' => 'CG-10-TR-1234',
            'vehicle_type' => 'Sedan',
            'seating_capacity' => 4,
            'ac_non_ac' => 'AC',
            'per_km_rate' => 14.00,
            'per_hour_rate' => 200.00,
            'status' => 'Available',
        ]);

        $currentDate = now('Asia/Kolkata')->format('Y-m-d');
        $currentTime = now('Asia/Kolkata')->format('H:i');

        $postData = [
            'customer_name' => 'Instant Booker',
            'mobile' => '9876543299',
            'email' => 'instant@example.com',
            'trip_type' => 'Airport Transfer',
            'rate_category' => 'airport',
            'vehicle_category' => 'Sedan',
            'pickup_location' => 'Bilaspur',
            'destination' => 'Raipur Airport',
            'travel_date' => $currentDate,
            'travel_time' => $currentTime,
            'vehicle_id' => $vehicle->id,
            'terms_accepted' => '1',
        ];

        $response = $this->post('/booking', $postData);
        $response->assertSessionHasNoErrors();
        $user = User::where('name', 'Instant Booker')->first();
        $this->assertNotNull($user);
        $booking = Booking::where('customer_id', $user->id)->first();
        $this->assertNotNull($booking);
        $this->assertEquals($currentDate, $booking->travel_date->format('Y-m-d'));
        $this->assertEquals($currentTime, substr($booking->travel_time, 0, 5));
    }

    public function test_booking_page_and_home_page_have_current_date_and_time(): void
    {
        $today = now('Asia/Kolkata')->format('Y-m-d');

        $homeRes = $this->get('/');
        $homeRes->assertStatus(200);
        $homeRes->assertSee('name="travel_date"', false);
        $homeRes->assertSee('name="travel_time"', false);
        $homeRes->assertSee('value="'.$today.'"', false);
        $this->assertMatchesRegularExpression('/id="travelTimeInput"[^>]*value="[0-2][0-9]:[0-5][0-9]"/', $homeRes->getContent());

        $bookingRes = $this->get('/booking');
        $bookingRes->assertStatus(200);
        $bookingRes->assertSee('name="travel_date"', false);
        $bookingRes->assertSee('name="travel_time"', false);
        $bookingRes->assertSee('value="'.$today.'"', false);
        $this->assertMatchesRegularExpression('/id="travelTimeInput"[^>]*value="[0-2][0-9]:[0-5][0-9]"/', $bookingRes->getContent());
    }
}
