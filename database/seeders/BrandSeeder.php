<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            ['name' => 'asus', 'is_active' => true],
            ['name' => 'dell', 'is_active' => true],
            ['name' => 'lenovo', 'is_active' => true],
            ['name' => 'apple', 'is_active' => true],

        ];

        DB::table('brands')->insert($brands);
      //  \App\Models\Brand::factory(10)->create();
    }
}
