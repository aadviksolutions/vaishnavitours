<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Centralized SEO Configuration for Vaishnavi Tours
    |--------------------------------------------------------------------------
    | Source of truth for SEO metadata, Open Graph, Twitter Cards, Local SEO,
    | and Schema.org structured data.
    */

    'site_name' => 'Vaishnavi Tours',
    'canonical_domain' => env('APP_URL', 'https://vaishnavitours.vercel.app'),

    'default_title' => 'Bilaspur Taxi & Cab Service | 24/7 Car Rental | Vaishnavi Tours',
    'title_separator' => ' | ',
    'default_description' => 'Book reliable 24/7 taxi and cab service in Bilaspur, Chhattisgarh with Vaishnavi Tours. Local cabs, outstation travel to Raipur, Korba, Ambikapur, airport transfers to Raipur Airport. Transparent rates and verified chauffeurs.',
    'default_keywords' => 'taxi service bilaspur, cab service bilaspur, taxi booking bilaspur, outstation cab bilaspur, airport taxi bilaspur, car rental bilaspur, cab booking bilaspur chhattisgarh, one way taxi bilaspur',
    'default_image' => 'assets/images/hero-taxi.jpg',
    'locale' => 'en_IN',

    // Local SEO & Geo Coordinates (Mangal Chowk, Bilaspur, Chhattisgarh)
    'geo' => [
        'latitude' => '22.0797',
        'longitude' => '82.1409',
        'region' => 'IN-CT',
        'placename' => 'Bilaspur, Chhattisgarh',
    ],

    // Service Areas in Chhattisgarh
    'service_areas' => [
        'Bilaspur',
        'Raipur',
        'Korba',
        'Ambikapur',
        'Raigarh',
        'Durg',
        'Bhilai',
        'Champa',
        'Janjgir',
        'Ratanpur',
        'Amarkantak',
        'Jagdalpur',
    ],

    // Verified Airport Hubs
    'airports' => [
        [
            'name' => 'Swami Vivekananda Airport, Raipur (RPR)',
            'distance_from_bilaspur' => '135 km',
            'typical_duration' => '2.5 to 3 hours via NH 130',
        ],
        [
            'name' => 'Bilasa Devi Kevat Airport, Bilaspur (PAB)',
            'distance_from_bilaspur' => '12 km',
            'typical_duration' => '25 to 30 minutes',
        ],
    ],
];
