<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class DriverSeeder extends Seeder
{
    public function run(): void
    {
        $dzire = Vehicle::where('registration_number', 'CG-10-AB-1204')->first();
        $ertiga = Vehicle::where('registration_number', 'CG-10-XY-5601')->first();
        $innova = Vehicle::where('registration_number', 'CG-10-TR-9988')->first();
        $tempo = Vehicle::where('registration_number', 'CG-10-TT-3344')->first();

        $drivers = [
            [
                'name' => 'Rameshwar Sahu',
                'mobile' => '9827001122',
                'alternate_mobile' => '9827001123',
                'license_number' => 'CG10-20150012445',
                'license_expiry' => '2030-05-15',
                'address' => 'Mangla Chowk, Bilaspur, CG',
                'status' => 'Available',
                'assigned_vehicle_id' => $dzire?->id,
                'rating' => 4.90,
                'notes' => '10+ years driving experience, expert on Raipur Highway & Ratanpur route.',
            ],
            [
                'name' => 'Santosh Kumar Yadav',
                'mobile' => '9425002233',
                'alternate_mobile' => '9425002234',
                'license_number' => 'CG10-20160034112',
                'license_expiry' => '2029-11-20',
                'address' => 'Torwa, Near Railway Station, Bilaspur, CG',
                'status' => 'Available',
                'assigned_vehicle_id' => $ertiga?->id,
                'rating' => 4.85,
                'notes' => 'Courteous and punctual, specialized in airport transfers and night driving.',
            ],
            [
                'name' => 'Dilip Chandrakar',
                'mobile' => '9754003344',
                'alternate_mobile' => '9754003345',
                'license_number' => 'CG10-20180056778',
                'license_expiry' => '2032-02-10',
                'address' => 'Sarkanda, Bilaspur, CG',
                'status' => 'On Trip',
                'assigned_vehicle_id' => $innova?->id,
                'rating' => 5.00,
                'notes' => 'Senior chauffeur with extensive hill road experience to Mainpat & Amarkantak.',
            ],
            [
                'name' => 'Mahendra Bhagat',
                'mobile' => '9179004455',
                'alternate_mobile' => '9179004456',
                'license_number' => 'CG10-20200089123',
                'license_expiry' => '2031-08-25',
                'address' => 'Tifra Industrial Area, Bilaspur, CG',
                'status' => 'Available',
                'assigned_vehicle_id' => $tempo?->id,
                'rating' => 4.75,
                'notes' => 'Heavy transport passenger certified, excellent with group tours.',
            ],
        ];

        foreach ($drivers as $driver) {
            Driver::firstOrCreate(
                ['license_number' => $driver['license_number']],
                $driver
            );
        }
    }
}
