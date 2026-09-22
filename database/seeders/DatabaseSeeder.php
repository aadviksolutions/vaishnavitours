<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * In production, only baseline production seeds are executed.
     * Demo data (fake customers, sample bookings) is never loaded in production.
     */
    public function run(): void
    {
        // Always run baseline production data (Settings, Rates, Baseline Admin)
        $this->call(ProductionSeeder::class);

        // In production environment, halt here to prevent demo data insertion
        if (app()->environment('production')) {
            $this->command->info('Production environment detected: Demo seeders skipped.');
            return;
        }

        // For local, testing, and staging environments, load demo records
        $this->call(DemoSeeder::class);
    }
}
