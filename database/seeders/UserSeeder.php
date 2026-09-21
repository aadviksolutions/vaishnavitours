<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin Account
        $admin = User::firstOrCreate(
            ['email' => 'admin@vaishnavitours.com'],
            [
                'name' => 'Vaishnavi Tours Admin',
                'phone' => '9893012345',
                'role' => 'admin',
                'password' => Hash::make('Password@123'),
                'is_active' => true,
            ]
        );

        // 2. Customers
        $customers = [
            [
                'name' => 'Rajesh Sharma',
                'email' => 'rajesh.sharma@gmail.com',
                'phone' => '9827112233',
                'address' => 'House 42, B.N City Colony, Jonki Road',
                'city' => 'Bilaspur',
                'state' => 'Chhattisgarh',
                'pincode' => '495001',
            ],
            [
                'name' => 'Amit Patel',
                'email' => 'amit.patel@outlook.com',
                'phone' => '9425234567',
                'address' => 'Flat 302, Green Valley Apartments, Mangla Chowk',
                'city' => 'Bilaspur',
                'state' => 'Chhattisgarh',
                'pincode' => '495001',
            ],
            [
                'name' => 'Priya Verma',
                'email' => 'priya.verma@gmail.com',
                'phone' => '9754123456',
                'address' => 'Plot 18, Vyas Nagar, Near High Court Road',
                'city' => 'Bilaspur',
                'state' => 'Chhattisgarh',
                'pincode' => '495001',
            ],
            [
                'name' => 'Vikramaditya Singh',
                'email' => 'vikram.singh@yahoo.com',
                'phone' => '9926123456',
                'address' => 'Bungalow 7, VIP Estate, Telibandha',
                'city' => 'Raipur',
                'state' => 'Chhattisgarh',
                'pincode' => '492001',
            ],
            [
                'name' => 'Neha Dewangan',
                'email' => 'neha.dewangan@gmail.com',
                'phone' => '9179123456',
                'address' => 'Sector 4, Balco Township',
                'city' => 'Korba',
                'state' => 'Chhattisgarh',
                'pincode' => '495684',
            ],
        ];

        foreach ($customers as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'role' => 'customer',
                    'password' => Hash::make('Password@123'),
                    'is_active' => true,
                ]
            );

            Customer::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'alternate_phone' => '9800000000',
                    'address' => $data['address'],
                    'city' => $data['city'],
                    'state' => $data['state'],
                    'pincode' => $data['pincode'],
                    'notes' => 'Verified regular customer.',
                ]
            );
        }
    }
}
