<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Country Code
    |--------------------------------------------------------------------------
    |
    | This defines the fallback ISO 3166-1 alpha-2 country code to use in
    | geolocation features when a country is not explicitly provided.
    | Set this to null to disable fallback behavior.
    |
    */

    'default_country' => 'IN',

    /*
    |--------------------------------------------------------------------------
    | Storage Configuration
    |--------------------------------------------------------------------------
    |
    | These options define how and where the geolocation data should be stored.
    | The 'disk' should match a filesystem disk defined in config/filesystems.php.
    | The 'path' is relative to the root of that disk.
    |
    */

    'storage' => [
        'disk' => 'local',
        'path' => 'data/geokit/countries/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Seeder Configuration
    |--------------------------------------------------------------------------
    |
    | This section allows you to configure how the geolocation data should
    | be seeded into your application. You can customize the seeder class
    | and limit the seeding to specific countries using their ISO codes.
    |
    */

    'seeder' => [
        'class' => \Mintreu\LaravelGeokit\Seeder\GeoKitSeeder::class,

        // ISO 3166-1 alpha-2 country codes to seed
        'countries' => [
            'IN', // India
            // Add additional country codes as needed
        ],
    ],

];
