<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    /**
     * Seed development and demo data (customers, vehicles, drivers, bookings).
     * Strictly intended for local and staging development only.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            VehicleSeeder::class,
            DriverSeeder::class,
            BookingSeeder::class,
        ]);
    }
}
