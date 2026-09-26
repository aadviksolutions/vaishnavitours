<?php

namespace Tests\Feature;

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

    public function test_round_trip_quote_uses_only_the_configured_vehicle_rent_and_distance_tariff(): void
    {
        $this->seed(RateSeeder::class);

        $quote = app(RateQuoteService::class)->quote('round_trip', 'Raipur', 'Raigarh', 'Ertiga', 100);

        $this->assertSame(2800.0, $quote['total_amount']);
        $this->assertSame(1500.0, $quote['details']['vehicle_rent']);
        $this->assertSame(13.0, $quote['details']['per_km_rate']);
        $this->assertSame('extra', $quote['details']['toll_type']);

        $sedanQuote = app(RateQuoteService::class)->quote('round_trip', 'Bilaspur', 'Raipur', 'Sedan', 100);
        $innovaQuote = app(RateQuoteService::class)->quote('round_trip', 'Bilaspur', 'Raipur', 'Innova Crysta', 100);
        $this->assertSame(2200.0, $sedanQuote['total_amount']);
        $this->assertSame(3400.0, $innovaQuote['total_amount']);

        $localSedan = app(RateQuoteService::class)->quote('local_8h_80km', 'Raigarh', 'Raigarh', 'Sedan');
        $localInnova = app(RateQuoteService::class)->quote('local_8h_80km', 'Raipur', 'Raipur', 'Innova Crysta');
        $shortErtiga = app(RateQuoteService::class)->quote('local_4h_40km', 'Korba', 'Korba', 'Ertiga');
        $this->assertSame(13.0, $localSedan['details']['extra_km_rate']);
        $this->assertSame(150.0, $localSedan['details']['extra_hour_rate']);
        $this->assertSame(19.75, $localInnova['details']['extra_km_rate']);
        $this->assertSame(275.0, $localInnova['details']['extra_hour_rate']);
        $this->assertNull($shortErtiga['details']['extra_km_rate']);
        $this->assertNull($shortErtiga['details']['extra_hour_rate']);
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
