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
        $this->call(UserSeeder::class);
        $this->call(BuildingSeeder::class);
        $this->call(ClassroomSeeder::class);
        $this->call(SectionSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(BrandSeeder::class);
        $this->call(AssetSeeder::class);
        $this->call(TagSeeder::class);
        $this->call(AssetTagSeeder::class);
//        $this->call(TicketSeeder::class);
    }
}
