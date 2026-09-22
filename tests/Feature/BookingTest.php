<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use App\Models\Vehicle;
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
        $response->assertSee('Maruti Suzuki Dzire');
        $response->assertSee('Sedan');
    }

    public function test_get_booking_page_empty_vehicles_shows_graceful_message(): void
    {
        // When no vehicles in database and fallback is bypassed or empty
        $response = $this->get('/booking');

        $response->assertStatus(200);
        $response->assertDontSee('500 - Server Error');
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
            'travel_date',
            'travel_time',
            'vehicle_id',
        ]);
    }

    public function test_post_booking_past_date_validation(): void
    {
        $response = $this->from('/booking')->post('/booking', [
            'customer_name' => 'Demo Customer',
            'mobile' => '9876543210',
            'trip_type' => 'One-Way',
            'pickup_location' => 'Mangla Chowk, Bilaspur',
            'destination' => 'Raipur Airport',
            'travel_date' => '2020-01-01',
            'travel_time' => '10:00',
            'vehicle_id' => '1',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/booking');
        $response->assertSessionHasErrors(['travel_date']);
    }

    public function test_post_booking_creates_booking_for_guest(): void
    {
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
            'trip_type' => 'One-Way',
            'pickup_location' => 'Mangla Chowk, Bilaspur',
            'destination' => 'Raipur Airport',
            'travel_date' => now()->addDays(2)->format('Y-m-d'),
            'travel_time' => '09:30',
            'vehicle_id' => $vehicle->id,
            'notes' => 'Need child booster seat',
        ];

        $response = $this->post('/booking', $postData);

        $booking = Booking::where('pickup_location', 'Mangla Chowk, Bilaspur')->first();
        $this->assertNotNull($booking);
        $this->assertEquals('Pending', $booking->booking_status);
        $this->assertMatchesRegularExpression('/^VT-\d+$/', $booking->booking_id);
        $this->assertEquals($vehicle->id, $booking->vehicle_id);

        $response->assertRedirect(route('booking.success', $booking->id));

        // Follow redirect to booking success page
        $successResponse = $this->get(route('booking.success', $booking->id));
        $successResponse->assertStatus(200);
        $successResponse->assertSee($booking->booking_id);
        $successResponse->assertSee('Guest Traveler');
        $successResponse->assertSee('Booking Request Received');
    }

    public function test_post_booking_associates_with_authenticated_customer(): void
    {
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
            'trip_type' => 'Round-Trip',
            'pickup_location' => 'Vyapar Vihar, Bilaspur',
            'destination' => 'Amarkantak',
            'travel_date' => now()->addDays(3)->format('Y-m-d'),
            'travel_time' => '06:00',
            'vehicle_id' => $vehicle->id,
        ];

        $response = $this->actingAs($user)->post('/booking', $postData);

        $booking = Booking::where('destination', 'Amarkantak')->first();
        $this->assertNotNull($booking);
        $this->assertEquals($user->id, $booking->customer_id);

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
        ]);

        $id2 = Booking::generateBookingId();
        $this->assertEquals('VT-1002', $id2);
    }
}
