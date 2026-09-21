<?php

namespace Database\Seeders;

use App\Models\Rate;
use Illuminate\Database\Seeder;

class RateSeeder extends Seeder
{
    public function run(): void
    {
        $rates = [
            [
                'vehicle_name' => 'Maruti Suzuki Dzire',
                'vehicle_type' => 'Sedan',
                'trip_type' => 'Outstation Round-Trip',
                'rate' => 13.00,
                'extra_km_rate' => 13.00,
                'waiting_charge' => 100.00,
                'night_charge' => 300.00,
                'notes' => 'Min. 250 KM/day. Toll taxes, state road taxes & parking charged at actuals.',
            ],
            [
                'vehicle_name' => 'Maruti Suzuki Ertiga',
                'vehicle_type' => 'MUV',
                'trip_type' => 'Outstation Round-Trip',
                'rate' => 17.00,
                'extra_km_rate' => 17.00,
                'waiting_charge' => 150.00,
                'night_charge' => 350.00,
                'notes' => 'Min. 250 KM/day. Spacious 6-passenger comfort with ample luggage space.',
            ],
            [
                'vehicle_name' => 'Toyota Innova Crysta',
                'vehicle_type' => 'SUV / MUV',
                'trip_type' => 'Outstation Round-Trip',
                'rate' => 22.00,
                'extra_km_rate' => 22.00,
                'waiting_charge' => 200.00,
                'night_charge' => 400.00,
                'notes' => 'Min. 250 KM/day. Executive captain seats with climate control AC.',
            ],
            [
                'vehicle_name' => 'Force Tempo Traveller',
                'vehicle_type' => 'Traveller',
                'trip_type' => 'Outstation Round-Trip',
                'rate' => 30.00,
                'extra_km_rate' => 30.00,
                'waiting_charge' => 300.00,
                'night_charge' => 500.00,
                'notes' => 'Min. 300 KM/day. 14+1 seating with pushback luxury seats and sound system.',
            ],
            [
                'vehicle_name' => 'Prime Sedan (Dzire / Etios)',
                'vehicle_type' => 'Sedan',
                'trip_type' => 'Local City Rental (8 Hr / 80 KM)',
                'rate' => 2200.00,
                'extra_km_rate' => 13.00,
                'waiting_charge' => 180.00,
                'night_charge' => 300.00,
                'notes' => 'Within Bilaspur city limits. Includes 8 hours and 80 kilometers.',
            ],
            [
                'vehicle_name' => 'Spacious MUV (Ertiga)',
                'vehicle_type' => 'MUV',
                'trip_type' => 'Local City Rental (8 Hr / 80 KM)',
                'rate' => 2800.00,
                'extra_km_rate' => 17.00,
                'waiting_charge' => 220.00,
                'night_charge' => 350.00,
                'notes' => 'Within Bilaspur city limits. Ideal for family weddings and shopping.',
            ],
            [
                'vehicle_name' => 'Innova Crysta Luxury',
                'vehicle_type' => 'SUV / MUV',
                'trip_type' => 'Local City Rental (8 Hr / 80 KM)',
                'rate' => 3600.00,
                'extra_km_rate' => 22.00,
                'waiting_charge' => 250.00,
                'night_charge' => 400.00,
                'notes' => 'Premium VIP comfort for corporate delegations and events.',
            ],
        ];

        foreach ($rates as $r) {
            Rate::firstOrCreate(
                [
                    'vehicle_name' => $r['vehicle_name'],
                    'trip_type' => $r['trip_type'],
                ],
                $r
            );
        }
    }
}
