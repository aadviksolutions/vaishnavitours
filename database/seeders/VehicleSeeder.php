<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $vehicles = [
            [
                'name' => 'Maruti Suzuki Dzire',
                'registration_number' => 'CG-10-AB-1204',
                'vehicle_type' => 'Sedan',
                'seating_capacity' => 4,
                'ac_non_ac' => 'AC',
                'per_km_rate' => 13.00,
                'per_hour_rate' => 220.00,
                'status' => 'Available',
                'image' => 'assets/images/sedan.jpg',
                'notes' => 'Prime comfort sedan with boot space for 2 large luggage bags.',
            ],
            [
                'name' => 'Maruti Suzuki Ertiga',
                'registration_number' => 'CG-10-XY-5601',
                'vehicle_type' => 'MUV',
                'seating_capacity' => 6,
                'ac_non_ac' => 'AC',
                'per_km_rate' => 17.00,
                'per_hour_rate' => 280.00,
                'status' => 'Available',
                'image' => 'assets/images/suv.jpg',
                'notes' => 'Spacious 6-seater MUV, ideal for family airport runs and intercity trips.',
            ],
            [
                'name' => 'Toyota Innova Crysta',
                'registration_number' => 'CG-10-TR-9988',
                'vehicle_type' => 'Innova Crysta',
                'seating_capacity' => 7,
                'ac_non_ac' => 'AC',
                'per_km_rate' => 22.00,
                'per_hour_rate' => 350.00,
                'status' => 'On Trip',
                'image' => 'assets/images/muv.jpg',
                'notes' => 'Premium captain seat luxury ride, best for long-distance highway travel.',
            ],
            [
                'name' => 'Force Tempo Traveller',
                'registration_number' => 'CG-10-TT-3344',
                'vehicle_type' => 'Tempo Traveller',
                'seating_capacity' => 14,
                'ac_non_ac' => 'AC',
                'per_km_rate' => 30.00,
                'per_hour_rate' => 500.00,
                'status' => 'Available',
                'image' => 'assets/images/traveller.jpg',
                'notes' => 'Spacious 14-seater tourist coach with pushback seats and overhead luggage carrier.',
            ],
            [
                'name' => 'Tata Tiago / WagonR',
                'registration_number' => 'CG-10-HG-7711',
                'vehicle_type' => 'Hatchback',
                'seating_capacity' => 4,
                'ac_non_ac' => 'AC',
                'per_km_rate' => 11.00,
                'per_hour_rate' => 180.00,
                'status' => 'Available',
                'image' => 'assets/images/sedan.jpg',
                'notes' => 'Economical city and short intercity ride with high fuel efficiency.',
            ],
        ];

        foreach ($vehicles as $vehicle) {
            Vehicle::firstOrCreate(
                ['registration_number' => $vehicle['registration_number']],
                $vehicle
            );
        }
    }
}
