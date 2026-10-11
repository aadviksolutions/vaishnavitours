<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use App\Services\BookingService;
use App\Services\RateQuoteService;
use Database\Seeders\RateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateQuoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_official_routes_and_packages_return_configured_vehicle_rates(): void
    {
        $this->seed(RateSeeder::class);
        $expected = [
            ['airport', 'Bilaspur', 'Raipur Airport', [2200, 3000, 4500]],
            ['outstation', 'Bilaspur', 'Raigarh', [3000, 4000, 6500]],
            ['outstation', 'Raipur', 'Raigarh', [4500, 5500, 8500]],
            ['outstation', 'Raipur', 'Korba', [4500, 5500, 8500]],
            ['outstation', 'Bilaspur', 'Korba', [2200, 3000, 4500]],
            ['outstation', 'Bilaspur', 'Ambikapur', [4500, 5500, 8500]],
            ['outstation', 'Raipur', 'Ambikapur', [6500, 7500, 10800]],
            ['outstation', 'Bilaspur', 'Bhilai', [3000, 4000, 6500]],
            ['outstation', 'Raipur', 'Sambalpur', [4500, 6500, 9500]],
            ['outstation', 'Raigarh', 'Jharsuguda', [2200, 3500, 4500]],
            ['local_8h_80km', 'Bilaspur', 'Bilaspur', [2200, 2700, 3500]],
            ['local_4h_40km', 'Bilaspur', 'Bilaspur', [1600, 2200, 3000]],
        ];
        $vehicleCategories = ['Sedan', 'Ertiga', 'Innova Crysta'];
        $quoteService = app(RateQuoteService::class);
        $actual = [];

        foreach ($expected as [$category, $origin, $destination, $fares]) {
            foreach ($vehicleCategories as $index => $vehicleCategory) {
                $quote = $quoteService->quote($category, $origin, $destination, $vehicleCategory);
                $this->assertNotNull($quote, "Missing {$vehicleCategory} rate for {$origin} to {$destination}.");
                $actual[] = (float) $quote['total_amount'];

                if ($category === 'airport') {
                    $this->assertSame($vehicleCategory !== 'Innova Crysta', $quote['details']['gst_applicable']);
                    $this->assertSame(130, $quote['details']['included_km']);
                    $this->assertSame(3.0, $quote['details']['included_hours']);
                    $expectedAirportKm = ['Sedan' => 14.5, 'Ertiga' => 16.65, 'Innova Crysta' => 22.8];
                    $this->assertSame($expectedAirportKm[$vehicleCategory], $quote['details']['extra_km_rate']);
                    $this->assertSame('included', $quote['details']['toll_type']);
                    $this->assertSame('included', $quote['details']['parking_type']);
                }

                if ($category === 'airport' || $category === 'outstation') {
                    $reverseQuote = $quoteService->quote($category, $destination, $origin, $vehicleCategory);
                    $this->assertNotNull($reverseQuote);
                    $this->assertSame((float) $fares[$index], (float) $reverseQuote['total_amount']);
                }
            }
        }

        $this->assertSame(array_map('floatval', array_merge(...array_column($expected, 3))), $actual);
        $this->assertNull($quoteService->quote('outstation', 'Korba', 'Ambikapur', 'Sedan'));
    }

    public function test_all_nine_fixed_one_way_routes_limits_durations_and_no_invented_extra_rates(): void
    {
        $this->seed(RateSeeder::class);
        $quoteService = app(RateQuoteService::class);

        $routeSpecs = [
            ['Bilaspur', 'Raigarh', 160, null],
            ['Raipur', 'Raigarh', 260, null],
            ['Raipur', 'Korba', 260, null],
            ['Bilaspur', 'Korba', 100, null],
            ['Bilaspur', 'Ambikapur', 230, null],
            ['Raipur', 'Ambikapur', 350, null],
            ['Bilaspur', 'Bhilai', 150, 3.5],
            ['Raipur', 'Sambalpur', 260, 5.0],
            ['Raigarh', 'Jharsuguda', 100, 3.0],
        ];

        foreach ($routeSpecs as [$origin, $destination, $kmLimit, $duration]) {
            foreach (['Sedan', 'Ertiga', 'Innova Crysta'] as $vehicle) {
                $forward = $quoteService->quote('outstation', $origin, $destination, $vehicle);
                $this->assertNotNull($forward, "Route {$origin} -> {$destination} missing for {$vehicle}");
                $this->assertSame($kmLimit, $forward['details']['included_km']);
                $this->assertSame($duration, $forward['details']['included_hours']);
                $this->assertNull($forward['details']['extra_km_rate'], "Extra KM must not be invented for {$origin} -> {$destination}");
                $this->assertNull($forward['details']['extra_hour_rate'], "Extra hour must not be invented for {$origin} -> {$destination}");

                $reverse = $quoteService->quote('outstation', $destination, $origin, $vehicle);
                $this->assertNotNull($reverse, "Reverse {$destination} -> {$origin} missing for {$vehicle}");
                $this->assertSame($forward['total_amount'], $reverse['total_amount']);
            }
        }
    }

    public function test_strict_location_matching_does_not_confuse_raipur_with_raipur_airport(): void
    {
        $this->seed(RateSeeder::class);
        $quoteService = app(RateQuoteService::class);

        // Bilaspur to Raipur city is NOT a configured fixed outstation route (only Bilaspur <-> Raipur Airport is configured under airport)
        $cityQuote = $quoteService->quote('outstation', 'Bilaspur', 'Raipur', 'Sedan');
        $this->assertNull($cityQuote, 'Bilaspur to Raipur city must not match Bilaspur to Raipur Airport.');

        // Bilaspur to Raipur Airport matches airport transfer
        $airportQuote = $quoteService->quote('airport', 'Bilaspur', 'Raipur Airport', 'Sedan');
        $this->assertNotNull($airportQuote);
        $this->assertSame(2200.0, $airportQuote['total_amount']);

        // Reverse Raipur Airport to Bilaspur matches airport transfer
        $airportReverse = $quoteService->quote('airport', 'Raipur Airport', 'Bilaspur', 'Sedan');
        $this->assertNotNull($airportReverse);
        $this->assertSame(2200.0, $airportReverse['total_amount']);

        // Raipur Airport cannot match an outstation route like Raipur to Korba
        $airportKorba = $quoteService->quote('outstation', 'Raipur Airport', 'Korba', 'Sedan');
        $this->assertNull($airportKorba, 'Raipur Airport must not match Raipur city in outstation routes.');
    }

    public function test_round_trip_pricing_configuration_and_all_extra_charges(): void
    {
        $this->seed(RateSeeder::class);
        $quoteService = app(RateQuoteService::class);

        $sedan = $quoteService->quote('round_trip', 'Bilaspur', 'Raipur', 'Sedan', 100);
        $this->assertSame(2200.0, $sedan['total_amount']); // 1000 rent + 100*12
        $this->assertSame(1000.0, $sedan['details']['vehicle_rent']);
        $this->assertSame(12.0, $sedan['details']['per_km_rate']);
        $this->assertSame(300.0, $sedan['details']['night_charge']);
        $this->assertSame('extra', $sedan['details']['toll_type']);
        $this->assertSame('extra', $sedan['details']['parking_type']);
        $this->assertSame('extra', $sedan['details']['border_tax_type']);
        $this->assertSame('extra', $sedan['details']['driver_food_type']);
        $this->assertTrue($sedan['details']['is_estimate']);

        $ertiga = $quoteService->quote('round_trip', 'Bilaspur', 'Raipur', 'Ertiga', 100);
        $this->assertSame(2800.0, $ertiga['total_amount']); // 1500 rent + 100*13
        $this->assertSame(1500.0, $ertiga['details']['vehicle_rent']);
        $this->assertSame(13.0, $ertiga['details']['per_km_rate']);
        $this->assertSame(400.0, $ertiga['details']['night_charge']);
        $this->assertSame('extra', $ertiga['details']['driver_food_type']);

        $innova = $quoteService->quote('round_trip', 'Bilaspur', 'Raipur', 'Innova Crysta', 100);
        $this->assertSame(3400.0, $innova['total_amount']); // 1800 rent + 100*16
        $this->assertSame(1800.0, $innova['details']['vehicle_rent']);
        $this->assertSame(16.0, $innova['details']['per_km_rate']);
        $this->assertSame(500.0, $innova['details']['night_charge']);
        $this->assertSame('extra', $innova['details']['driver_food_type']);
    }

    public function test_local_rental_packages_inclusions_and_extra_rates(): void
    {
        $this->seed(RateSeeder::class);
        $quoteService = app(RateQuoteService::class);

        // 8 Hours / 80 KM
        $sedan8h = $quoteService->quote('local_8h_80km', 'Bilaspur', 'Bilaspur', 'Sedan');
        $this->assertSame(2200.0, $sedan8h['total_amount']);
        $this->assertSame(13.0, $sedan8h['details']['extra_km_rate']);
        $this->assertSame(150.0, $sedan8h['details']['extra_hour_rate']);
        $this->assertSame('extra', $sedan8h['details']['toll_type']);
        $this->assertSame('extra', $sedan8h['details']['parking_type']);

        $ertiga8h = $quoteService->quote('local_8h_80km', 'Bilaspur', 'Bilaspur', 'Ertiga');
        $this->assertSame(2700.0, $ertiga8h['total_amount']);
        $this->assertSame(15.0, $ertiga8h['details']['extra_km_rate']);
        $this->assertSame(200.0, $ertiga8h['details']['extra_hour_rate']);

        $innova8h = $quoteService->quote('local_8h_80km', 'Bilaspur', 'Bilaspur', 'Innova Crysta');
        $this->assertSame(3500.0, $innova8h['total_amount']);
        $this->assertSame(19.75, $innova8h['details']['extra_km_rate']);
        $this->assertSame(275.0, $innova8h['details']['extra_hour_rate']);

        // 4 Hours / 40 KM - extra charges must be null (not invented from 8h)
        $sedan4h = $quoteService->quote('local_4h_40km', 'Bilaspur', 'Bilaspur', 'Sedan');
        $this->assertSame(1600.0, $sedan4h['total_amount']);
        $this->assertNull($sedan4h['details']['extra_km_rate']);
        $this->assertNull($sedan4h['details']['extra_hour_rate']);
        $this->assertSame('extra', $sedan4h['details']['toll_type']);
        $this->assertSame('extra', $sedan4h['details']['parking_type']);

        $ertiga4h = $quoteService->quote('local_4h_40km', 'Bilaspur', 'Bilaspur', 'Ertiga');
        $this->assertSame(2200.0, $ertiga4h['total_amount']);
        $this->assertNull($ertiga4h['details']['extra_km_rate']);

        $innova4h = $quoteService->quote('local_4h_40km', 'Bilaspur', 'Bilaspur', 'Innova Crysta');
        $this->assertSame(3000.0, $innova4h['total_amount']);
        $this->assertNull($innova4h['details']['extra_km_rate']);
    }

    public function test_booking_service_prevents_client_fare_tampering(): void
    {
        $this->seed(RateSeeder::class);
        $customer = User::factory()->create(['role' => 'customer']);

        $bookingData = [
            'customer_name' => 'Test Traveler',
            'mobile' => '9876543210',
            'trip_type' => 'One-Way',
            'rate_category' => 'outstation',
            'pickup_location' => 'Bilaspur',
            'destination' => 'Korba',
            'vehicle_category' => 'Sedan',
            'travel_date' => now()->addDays(2)->format('Y-m-d'),
            'travel_time' => '10:00',
            'terms_accepted' => true,
            'total_amount' => 500.00, // Attacker attempts to pay ₹500 instead of ₹2,200
        ];

        $booking = app(BookingService::class)->createBooking($bookingData, $customer);

        // Server authoritative calculation overrides the client tampering
        $this->assertSame(2200.0, (float) $booking->total_amount);
        $this->assertSame(2200.0, (float) $booking->balance_amount);
        $this->assertSame('Sedan', $booking->pricing_details['vehicle_category']);
        $this->assertSame(100, $booking->pricing_details['included_km']);
    }

    public function test_historical_booking_fare_preservation(): void
    {
        $this->seed(RateSeeder::class);
        $customer = User::factory()->create(['role' => 'customer']);

        // Create historical booking with confirmed fare
        $booking = Booking::create([
            'booking_id' => 'VT-HIST-001',
            'customer_id' => $customer->id,
            'trip_type' => 'One-Way',
            'rate_category' => 'outstation',
            'pickup_location' => 'Bilaspur',
            'destination' => 'Korba',
            'vehicle_category' => 'Sedan',
            'travel_date' => now()->subMonths(2)->format('Y-m-d'),
            'travel_time' => '10:00',
            'total_amount' => 2000.00, // Historical rate from 2 months ago
            'paid_amount' => 2000.00,
            'balance_amount' => 0.00,
            'booking_status' => 'Completed',
            'payment_status' => 'Paid',
            'terms_accepted' => true,
            'pricing_details' => [
                'base_fare' => 2000.00,
                'total_amount' => 2000.00,
                'historical' => true,
            ],
        ]);

        // Rate changes or subsequent quotes must NOT affect historical booking
        $fresh = $booking->fresh();
        $this->assertSame(2000.0, (float) $fresh->total_amount);
        $this->assertSame(2000.0, (float) $fresh->pricing_details['total_amount']);
    }

    public function test_public_rates_filter_shows_the_configured_fare_and_missing_route_message(): void
    {
        $this->seed(RateSeeder::class);

        $this->get(route('rates', [
            'from' => 'Raipur',
            'to' => 'Korba',
            'rate_category' => 'outstation',
            'vehicle_category' => 'Sedan',
        ]))
            ->assertOk()
            ->assertSee('Raipur → Korba')
            ->assertSee('₹4,500.00')
            ->assertDontSee('₹13/km');

        $this->get(route('rates', [
            'from' => 'Korba',
            'to' => 'Ambikapur',
            'rate_category' => 'outstation',
            'vehicle_category' => 'Sedan',
        ]))
            ->assertOk()
            ->assertSee('Rate not configured. Please contact Vaishnavi Tours.');
    }
}
