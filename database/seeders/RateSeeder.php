<?php

namespace Database\Seeders;

use App\Models\RateRoute;
use App\Models\RateVehiclePrice;
use Illuminate\Database\Seeder;

class RateSeeder extends Seeder
{
    public function run(): void
    {
        $routes = [
            ['airport', 'Bilaspur', 'Raipur Airport', 130, 3, [2200, 3000, 4500], true],
            ['outstation', 'Bilaspur', 'Raigarh', 160, null, [3000, 4000, 6500], true],
            ['outstation', 'Raipur', 'Raigarh', 260, null, [4500, 5500, 8500], true],
            ['outstation', 'Raipur', 'Korba', 260, null, [4500, 5500, 8500], true],
            ['outstation', 'Bilaspur', 'Korba', 100, null, [2200, 3000, 4500], true],
            ['outstation', 'Bilaspur', 'Ambikapur', 230, null, [4500, 5500, 8500], true],
            ['outstation', 'Raipur', 'Ambikapur', 350, null, [6500, 7500, 10800], true],
            ['outstation', 'Bilaspur', 'Bhilai', 150, 3.5, [3000, 4000, 6500], true],
            ['outstation', 'Raipur', 'Sambalpur', 260, 5, [4500, 6500, 9500], true],
            ['outstation', 'Raigarh', 'Jharsuguda', 100, 3, [2200, 3500, 4500], true],
        ];

        foreach ($routes as [$category, $origin, $destination, $kmLimit, $hours, $fares, $sameReturn]) {
            $route = RateRoute::updateOrCreate(
                compact('category', 'origin', 'destination'),
                ['km_limit' => $kmLimit, 'included_hours' => $hours, 'return_same_rate' => $sameReturn, 'active' => true]
            );
            foreach (['Sedan', 'Ertiga', 'Innova Crysta'] as $index => $vehicleCategory) {
                $priceAttributes = ['base_fare' => $fares[$index]];
                if ($category === 'airport') {
                    $airportExtras = [
                        'Sedan' => ['gst_applicable' => true, 'extra_km_rate' => 14.50, 'extra_hour_rate' => 100, 'toll_type' => 'included', 'parking_type' => 'included'],
                        'Ertiga' => ['gst_applicable' => true, 'extra_km_rate' => 16.65, 'extra_hour_rate' => 150, 'toll_type' => 'included', 'parking_type' => 'included'],
                        'Innova Crysta' => ['extra_km_rate' => 22.80, 'extra_hour_rate' => 200, 'toll_type' => 'included', 'parking_type' => 'included'],
                    ];
                    $priceAttributes = array_merge($priceAttributes, $airportExtras[$vehicleCategory]);
                }
                RateVehiclePrice::updateOrCreate(
                    ['rate_route_id' => $route->id, 'vehicle_category' => $vehicleCategory],
                    $priceAttributes
                );
            }
        }

        $this->seedGlobalRates('round_trip', null, null, null, null, [
            ['vehicle_category' => 'Sedan', 'vehicle_rent' => 1000, 'per_km_rate' => 12, 'night_charge' => 300, 'toll_type' => 'extra', 'parking_type' => 'extra', 'border_tax_type' => 'extra', 'driver_food_type' => 'extra'],
            ['vehicle_category' => 'Ertiga', 'vehicle_rent' => 1500, 'per_km_rate' => 13, 'night_charge' => 400, 'toll_type' => 'extra', 'parking_type' => 'extra', 'border_tax_type' => 'extra'],
            ['vehicle_category' => 'Innova Crysta', 'vehicle_rent' => 1800, 'per_km_rate' => 16, 'night_charge' => 500, 'toll_type' => 'extra', 'parking_type' => 'extra', 'border_tax_type' => 'extra'],
        ]);

        $this->seedGlobalRates('local_8h_80km', 80, 8, null, null, [
            ['vehicle_category' => 'Sedan', 'base_fare' => 2200, 'extra_km_rate' => 13, 'extra_hour_rate' => 150, 'toll_type' => 'extra', 'parking_type' => 'extra'],
            ['vehicle_category' => 'Ertiga', 'base_fare' => 2700, 'extra_km_rate' => 15, 'extra_hour_rate' => 200, 'toll_type' => 'extra', 'parking_type' => 'extra'],
            ['vehicle_category' => 'Innova Crysta', 'base_fare' => 3500, 'extra_km_rate' => 19.75, 'extra_hour_rate' => 275, 'toll_type' => 'extra', 'parking_type' => 'extra'],
        ]);

        $this->seedGlobalRates('local_4h_40km', 40, 4, null, null, [
            ['vehicle_category' => 'Sedan', 'base_fare' => 1600],
            ['vehicle_category' => 'Ertiga', 'base_fare' => 2200],
            ['vehicle_category' => 'Innova Crysta', 'base_fare' => 3000],
        ]);
    }

    private function seedGlobalRates(string $category, ?int $kmLimit, ?float $hours, ?string $origin, ?string $destination, array $prices): void
    {
        $route = RateRoute::updateOrCreate(
            compact('category', 'origin', 'destination'),
            ['km_limit' => $kmLimit, 'included_hours' => $hours, 'return_same_rate' => false, 'active' => true]
        );

        foreach ($prices as $price) {
            RateVehiclePrice::updateOrCreate(
                ['rate_route_id' => $route->id, 'vehicle_category' => $price['vehicle_category']],
                $price
            );
        }
    }
}
