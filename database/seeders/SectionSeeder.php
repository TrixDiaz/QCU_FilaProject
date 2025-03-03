<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = [
            ['classroom_id' => 1, 'name' => 'section 1', 'is_active' => true],
            ['classroom_id' => 1, 'name' => 'section 2', 'is_active' => true],
            ['classroom_id' => 2, 'name' => 'section 3', 'is_active' => true],
            ['classroom_id' => 2, 'name' => 'section 4', 'is_active' => true],
            ['classroom_id' => 3, 'name' => 'section 5', 'is_active' => true],
        ];

        \Illuminate\Support\Facades\DB::table('sections')->insert($sections);
    }
}
