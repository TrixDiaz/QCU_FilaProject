<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            ['name' => 'computer-case', 'is_active' => true],
            ['name' => 'power-supply', 'is_active' => true],
            ['name' => 'motherboard', 'is_active' => true],
            ['name' => 'processor', 'is_active' => true],
            ['name' => 'drive', 'is_active' => true],
            ['name' => 'ram', 'is_active' => true],
            ['name' => 'graphics-card', 'is_active' => true],
            ['name' => 'monitor', 'is_active' => true],
            ['name' => 'keyboard', 'is_active' => true],
            ['name' => 'mouse', 'is_active' => true],
            ['name' => 'headphone', 'is_active' => true],
            ['name' => 'speaker', 'is_active' => true],
        ];

        DB::table('tags')->insert($tags);

//        \App\Models\Tag::factory(10)->create();
    }
}
