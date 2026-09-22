<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionSeeder extends Seeder
{
    /**
     * Seed production-safe required baseline data.
     * Never seeds fake/demo customer records, bookings, or simulated invoices.
     */
    public function run(): void
    {
        // 1. Essential Company Settings (Contacts, address, terms)
        $this->call([
            SettingSeeder::class,
            RateSeeder::class,
        ]);

        // 2. Baseline Admin Account (Creates if not present; never overwrites existing)
        $adminEmail = env('ADMIN_EMAIL', 'admin@vaishnavitours.com');
        $adminPassword = env('ADMIN_PASSWORD', 'ChangeMeImmediately#2026');
        $adminName = env('ADMIN_NAME', 'Vaishnavi Tours Admin');

        User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => $adminName,
                'phone' => env('ADMIN_PHONE', null),
                'role' => 'admin',
                'password' => Hash::make($adminPassword),
                'is_active' => true,
            ]
        );
    }
}
