<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ClassroomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $buildings = \App\Models\Building::all();

        $classrooms = [
            ['building_id' => $buildings[0]->id, 'name' => 'Classroom 1', 'is_active' => true],
            ['building_id' => $buildings[0]->id, 'name' => 'Classroom 2', 'is_active' => true],
            ['building_id' => $buildings[1]->id, 'name' => 'Classroom 3', 'is_active' => true],
            ['building_id' => $buildings[1]->id, 'name' => 'Classroom 4', 'is_active' => true],
            ['building_id' => $buildings[2]->id, 'name' => 'Classroom 5', 'is_active' => true],
        ];

        \Illuminate\Support\Facades\DB::table('classrooms')->insert($classrooms);

    }
}
