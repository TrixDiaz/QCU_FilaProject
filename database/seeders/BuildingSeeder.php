<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BuildingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $building = [
            ['name' => 'building 1', 'slug' => 'building-1', 'is_active' => true],
            ['name' => 'building 2', 'slug' => 'building-2', 'is_active' => true],
            ['name' => 'building 3', 'slug' => 'building-3', 'is_active' => true],
            ['name' => 'building 4', 'slug' => 'building-4', 'is_active' => true],
        ];

        \Illuminate\Support\Facades\DB::table('buildings')->insert($building);
       // \App\Models\Building::factory(10)->create();
    }
}
