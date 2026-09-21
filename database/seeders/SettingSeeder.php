<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'company_name', 'value' => 'Vaishnavi Tours', 'group' => 'general'],
            ['key' => 'company_tagline', 'value' => '24/7 Car Rentals, Cabs & Travel Service', 'group' => 'general'],
            ['key' => 'phone_primary', 'value' => '+91 98930 12345', 'group' => 'contact'],
            ['key' => 'phone_secondary', 'value' => '+91 94250 54321', 'group' => 'contact'],
            ['key' => 'emergency_phone', 'value' => '+91 98930 99999', 'group' => 'contact'],
            ['key' => 'whatsapp_number', 'value' => '919893012345', 'group' => 'contact'],
            ['key' => 'contact_email', 'value' => 'info@vaishnavitours.com', 'group' => 'contact'],
            ['key' => 'support_email', 'value' => 'support@vaishnavitours.com', 'group' => 'contact'],
            ['key' => 'address_line_1', 'value' => 'B.N City Colony, Jonki Road', 'group' => 'address'],
            ['key' => 'address_line_2', 'value' => 'Mangla Chowk', 'group' => 'address'],
            ['key' => 'city', 'value' => 'Bilaspur', 'group' => 'address'],
            ['key' => 'state', 'value' => 'Chhattisgarh', 'group' => 'address'],
            ['key' => 'pincode', 'value' => '495001', 'group' => 'address'],
            ['key' => 'gstin', 'value' => '22AAAAA0000A1Z5', 'group' => 'billing'],
            ['key' => 'booking_terms', 'value' => 'Toll and parking charges extra if applicable. Driver night allowance applicable between 10:00 PM and 06:00 AM.', 'group' => 'booking'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
