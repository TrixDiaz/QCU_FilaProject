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
            ['name' => 'asus', 'slug' => 'asus', 'is_active' => true],
            ['name' => 'dell', 'slug' => 'dell', 'is_active' => true],
            ['name' => 'lenovo', 'slug' => 'lenovo', 'is_active' => true],
            ['name' => 'apple', 'slug' => 'apple', 'is_active' => true],

        ];

        DB::table('brands')->insert($brands);
      //  \App\Models\Brand::factory(10)->create();
    }
}
