<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $sections = [
            ['classroom_id' => 1, 'name' => 'section 1', 'slug' => Str::slug('section 1'), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['classroom_id' => 1, 'name' => 'section 2', 'slug' => Str::slug('section 2'), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['classroom_id' => 2, 'name' => 'section 3', 'slug' => Str::slug('section 3'), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['classroom_id' => 2, 'name' => 'section 4', 'slug' => Str::slug('section 4'), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['classroom_id' => 3, 'name' => 'section 5', 'slug' => Str::slug('section 5'), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('sections')->insert($sections);
    }
}
