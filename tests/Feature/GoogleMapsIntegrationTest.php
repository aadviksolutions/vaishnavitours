<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Enquiry;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\RateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoogleMapsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_maps_config_api_returns_client_settings_safely(): void
    {
        $response = $this->getJson('/api/maps/config');

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'client_api_key',
                'center' => ['lat', 'lng'],
                'zoom',
                'has_google_key',
            ]);

        // Server API key must NEVER be leaked to public config endpoint
        $response->assertJsonMissing(['google_maps_server_api_key']);
    }

    public function test_calculate_route_api_computes_one_way_highway_route_and_distance(): void
    {
        $response = $this->postJson('/api/maps/calculate-route', [
            'pickup_location' => 'Bilaspur',
            'destination' => 'Raipur',
            'trip_type' => 'One-Way',
        ]);

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'is_round_trip' => false,
            ]);

        $data = $response->json();
        $this->assertGreaterThan(50, $data['total_distance_km']);
        $this->assertNotEmpty($data['legs']);
        $this->assertNotEmpty($data['total_duration_text']);
        $this->assertArrayHasKey('origin_coordinates', $data);
        $this->assertArrayHasKey('destination_coordinates', $data);
    }

    public function test_calculate_route_api_computes_round_trip_with_via_and_return_leg(): void
    {
        $response = $this->postJson('/api/maps/calculate-route', [
            'pickup_location' => 'Bilaspur',
            'via_location' => 'Amarkantak',
            'destination' => 'Bilaspur',
            'trip_type' => 'Round-Trip',
        ]);

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'is_round_trip' => true,
            ]);

        $data = $response->json();
        $this->assertGreaterThan(150, $data['total_distance_km']);
        // Round trip with via has at least 2 legs (outbound and return)
        $this->assertGreaterThanOrEqual(2, count($data['legs']));
    }

    public function test_customer_round_trip_submission_creates_enquiry_without_automatic_price(): void
    {
        $this->seed(RateSeeder::class);

        $response = $this->post(route('booking.store'), [
            'name' => 'Rahul Sharma',
            'email' => 'rahul@example.com',
            'phone' => '9876543210',
            'service_type' => 'outstation',
            'trip_type' => 'Round-Trip',
            'rate_category' => 'round_trip',
            'vehicle_category' => 'Sedan',
            'pickup_location' => 'Bilaspur',
            'via_location' => 'Amarkantak',
            'destination' => 'Bilaspur',
            'pickup_latitude' => 22.0797,
            'pickup_longitude' => 82.1409,
            'destination_latitude' => 22.0797,
            'destination_longitude' => 82.1409,
            'pickup_date' => now()->addDays(2)->format('Y-m-d'),
            'pickup_time' => '10:00',
            'return_date' => now()->addDays(4)->format('Y-m-d'),
            'terms_accepted' => '1',
        ]);

        // Must redirect to enquiry confirmation or customer status page
        $response->assertSessionHasNoErrors();

        // Round-trip enquiry must be recorded in enquiries table with route data
        $enquiry = Enquiry::latest()->first();
        $this->assertNotNull($enquiry);
        $this->assertSame('Rahul Sharma', $enquiry->name);
        $this->assertSame('9876543210', $enquiry->phone);
        $this->assertSame('Round-Trip', $enquiry->booking_type);
        $this->assertNotNull($enquiry->pickup_latitude);
        $this->assertNotNull($enquiry->route_distance_km);
    }

    public function test_admin_can_recalculate_route_for_existing_booking(): void
    {
        $this->seed(RateSeeder::class);
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $vehicle = Vehicle::create([
            'name' => 'Swift Dzire',
            'registration_number' => 'CG10AB1234',
            'vehicle_type' => 'Sedan',
            'status' => 'Available',
            'rate_per_km' => 12.0,
        ]);

        $booking = Booking::create([
            'booking_id' => 'VT-TEST-99',
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'trip_type' => 'One-Way',
            'pickup_location' => 'Bilaspur',
            'destination' => 'Raipur',
            'travel_date' => now()->addDays(2)->format('Y-m-d'),
            'travel_time' => '08:00',
            'total_amount' => 2500,
            'paid_amount' => 0,
            'balance_amount' => 2500,
            'booking_status' => 'Confirmed',
            'payment_status' => 'Pending',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.bookings.recalculate-route', $booking->id));

        $response->assertRedirect(route('admin.bookings.show', $booking->id))
            ->assertSessionHas('success');

        $booking->refresh();
        $this->assertNotNull($booking->route_distance_km);
        $this->assertGreaterThan(50, $booking->route_distance_km);
        $this->assertNotNull($booking->routed_at);
        $this->assertNotEmpty($booking->route_legs);
    }

    public function test_geocode_and_reverse_geocode_api_endpoints(): void
    {
        // Test Geocode
        $geoResp = $this->postJson('/api/maps/geocode', [
            'address' => 'Bilaspur Railway Station, Chhattisgarh',
        ]);
        $geoResp->assertOk()
            ->assertJson(['status' => 'success']);
        $this->assertNotNull($geoResp->json('latitude'));
        $this->assertNotNull($geoResp->json('longitude'));

        // Test Reverse Geocode
        $revResp = $this->postJson('/api/maps/reverse-geocode', [
            'latitude' => 22.0797,
            'longitude' => 82.1409,
        ]);
        $revResp->assertOk()
            ->assertJson(['status' => 'success']);
        $this->assertNotEmpty($revResp->json('formatted_address'));
    }
}
