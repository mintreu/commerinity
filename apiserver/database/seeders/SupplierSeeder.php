<?php

namespace Database\Seeders;

use App\Models\Ecommerce\Supplier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultSupplier = Supplier::factory()->create([
            'name' => fake()->company,
            'email' => fake()->companyEmail,
            'phone' => fake()->numerify('##########'),
            'contact_person' => fake()->name,
//            'gst_number' => '',
//            'tax_number',
            'is_active' => true,
        ]);
    }
}

