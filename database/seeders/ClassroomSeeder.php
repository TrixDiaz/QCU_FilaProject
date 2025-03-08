<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Building;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ClassroomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $buildings = Building::all();
        $now = Carbon::now();

        $classrooms = [
            ['building_id' => $buildings[0]->id, 'name' => 'Classroom 1', 'slug' => Str::slug('Classroom 1'), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['building_id' => $buildings[0]->id, 'name' => 'Classroom 2', 'slug' => Str::slug('Classroom 2'), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['building_id' => $buildings[1]->id, 'name' => 'Classroom 3', 'slug' => Str::slug('Classroom 3'), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['building_id' => $buildings[1]->id, 'name' => 'Classroom 4', 'slug' => Str::slug('Classroom 4'), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['building_id' => $buildings[2]->id, 'name' => 'Classroom 5', 'slug' => Str::slug('Classroom 5'), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('classrooms')->insert($classrooms);
    }
}
