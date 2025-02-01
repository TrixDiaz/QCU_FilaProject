<?php

namespace Database\Seeders;


// use Illuminate\Database\Console\Seeds\WithoutModelEvents;


use App\Models\Category;
use App\Models\Classroom;
use App\Models\Section;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);;
        $this->call(SectionSeeder::class);
        $this->call(AssetSeeder::class);
    }
}
