<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class BrandsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Brands::factory(10)->create();
    }
}
