<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Official Business Details
    |--------------------------------------------------------------------------
    | Source of truth for business contact details provided by the business owner.
    */
    'business_name' => env('BUSINESS_NAME', 'Vaishnavi Tours'),
    'tagline' => env('BUSINESS_TAGLINE', '24/7 Car Rentals, Cabs & Travel Service'),

    // Calling Numbers
    'phone_primary' => '9244784443',
    'phone_secondary' => '9179484443',
    'phone_primary_tel' => '+919244784443',
    'phone_secondary_tel' => '+919179484443',

    // WhatsApp
    'whatsapp' => '9244784443',
    'whatsapp_link' => 'https://wa.me/919244784443',

    // Official Address
    'address' => 'Mangal Chowk, Bilaspur',
    'city' => 'Bilaspur',
    'state' => 'Chhattisgarh',
    'pincode' => '495001',

    'ambulance_services' => [
        [
            'title' => 'Ambulance with Body Freezer',
            'icon' => 'snowflake',
            'description' => 'Dedicated ambulance transportation with body freezer facility for safe and respectful transportation of deceased persons.',
            'features' => [
                'Body Freezer Facility',
                'Safe & Respectful Transportation',
                'Local & Outstation Transfer',
                '24/7 Assistance',
            ],
        ],
        [
            'title' => 'Ambulance with Ventilator',
            'icon' => 'wind',
            'description' => 'Ambulance transportation equipped to support patients who require ventilator-assisted transfer, subject to vehicle and medical-support availability.',
            'features' => [
                'Ventilator Support',
                'Patient Transfer Assistance',
                'Emergency & Non-Emergency Transfer',
                'Trained Support Availability',
            ],
        ],
        [
            'title' => 'ICU Ambulance',
            'icon' => 'activity',
            'description' => 'Advanced ambulance transportation for patients requiring higher-level medical support during transfer, subject to ambulance and medical equipment availability.',
            'features' => [
                'ICU Ambulance Setup',
                'Critical Patient Transfer',
                'Medical Equipment Support',
                'Local & Outstation Service',
            ],
        ],
        [
            'title' => 'Dead Body Transfer',
            'icon' => 'heart',
            'description' => 'Safe, respectful and dignified transportation support for deceased persons, including local and long-distance transfers.',
            'features' => [
                'Dignified Transportation',
                'Safe Handling',
                'Local & Outstation Transfer',
                'Assistance During Transfer',
            ],
        ],
    ],

    // Verified Emails
    'contact_email' => 'info@vaishnavitours.com',
    'support_email' => 'support@vaishnavitours.com',
    'gstin' => '22DXEPS5353C1ZN',
    'bank_account_name' => env('BANK_ACCOUNT_NAME'),
    'bank_ifsc_code' => env('BANK_IFSC_CODE'),
    'bank_account_number' => env('BANK_ACCOUNT_NUMBER'),
    'bank_name' => env('BANK_NAME'),
    'booking_terms' => env('BOOKING_TERMS', 'Toll plaza taxes, state border permits & parking fees are extra if applicable. Night driving allowance applies between 10:00 PM and 06:00 AM.'),
];
