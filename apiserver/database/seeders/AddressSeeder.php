<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seed addresses for demo users.
 */
class AddressSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding demo user addresses...');

        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Run DemoUserSeeder first.');

            return;
        }

        $addresses = [
            [
                'title' => 'Home',
                'type' => 'home',
                'address_1' => '42, Sunrise Apartments',
                'address_2' => 'MG Road, Sector 15',
                'landmark' => 'Near Central Mall',
                'city' => 'Gurugram',
                'postal_code' => '122001',
                'state_code' => 'HR',
                'country_code' => 'IN',
                'default' => true,
            ],
            [
                'title' => 'Office',
                'type' => 'office',
                'address_1' => 'Tower B, Floor 5',
                'address_2' => 'Cyber City, DLF Phase 2',
                'landmark' => 'Near IFFCO Chowk Metro',
                'city' => 'Gurugram',
                'postal_code' => '122002',
                'state_code' => 'HR',
                'country_code' => 'IN',
                'default' => false,
            ],
            [
                'title' => 'Home',
                'type' => 'home',
                'address_1' => '15, Green Valley Society',
                'address_2' => 'Andheri West',
                'landmark' => 'Near DN Nagar Metro',
                'city' => 'Mumbai',
                'postal_code' => '400053',
                'state_code' => 'MH',
                'country_code' => 'IN',
                'default' => true,
            ],
            [
                'title' => 'Home',
                'type' => 'home',
                'address_1' => '78, Koramangala 4th Block',
                'address_2' => 'Near Forum Mall',
                'landmark' => 'Opposite to Jyoti Nivas College',
                'city' => 'Bangalore',
                'postal_code' => '560034',
                'state_code' => 'KA',
                'country_code' => 'IN',
                'default' => true,
            ],
            [
                'title' => 'Home',
                'type' => 'home',
                'address_1' => '23, Anna Nagar East',
                'address_2' => 'Near Roundtana',
                'landmark' => 'Behind Tower Park',
                'city' => 'Chennai',
                'postal_code' => '600040',
                'state_code' => 'TN',
                'country_code' => 'IN',
                'default' => true,
            ],
            [
                'title' => 'Home',
                'type' => 'home',
                'address_1' => '156, Salt Lake Sector V',
                'address_2' => 'Bidhan Nagar',
                'landmark' => 'Near City Centre 2',
                'city' => 'Kolkata',
                'postal_code' => '700091',
                'state_code' => 'WB',
                'country_code' => 'IN',
                'default' => true,
            ],
        ];

        $addressIndex = 0;

        foreach ($users as $user) {
            // Assign 1-2 addresses per user
            $numAddresses = $user->id <= 2 ? 2 : 1;

            for ($i = 0; $i < $numAddresses; $i++) {
                $addressData = $addresses[$addressIndex % count($addresses)];

                $user->addresses()->create([
                    ...$addressData,
                    'person_name' => $user->name,
                    'person_email' => $user->email,
                    'person_mobile' => $user->mobile,
                    'default' => $i === 0, // First address is default
                ]);

                $addressIndex++;
            }
        }

        $this->command->info('Seeded '.Address::count().' addresses for demo users.');
    }
}
