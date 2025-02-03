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
            ['name' => 'computer-case', 'slug' => 'computer-case', 'is_active' => true],
            ['name' => 'power-supply', 'slug' => 'power-supply', 'is_active' => true],
            ['name' => 'motherboard', 'slug' => 'motherboard', 'is_active' => true],
            ['name' => 'processor', 'slug' => 'processor', 'is_active' => true],
            ['name' => 'drive', 'slug' => 'drive', 'is_active' => true],
            ['name' => 'ram', 'slug' => 'ram', 'is_active' => true],
            ['name' => 'graphics-card', 'slug' => 'graphics-card', 'is_active' => true],
            ['name' => 'monitor', 'slug' => 'monitor', 'is_active' => true],
            ['name' => 'keyboard', 'slug' => 'keyboard', 'is_active' => true],
            ['name' => 'mouse', 'slug' => 'mouse', 'is_active' => true],
            ['name' => 'headphone', 'slug' => 'headphone', 'is_active' => true],
            ['name' => 'speaker', 'slug' => 'speaker', 'is_active' => true],
        ];

        DB::table('tags')->insert($tags);

//        \App\Models\Tag::factory(10)->create();
    }
}
