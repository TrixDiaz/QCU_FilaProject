<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'hardware', 'is_active' => true],
            ['name' => 'software', 'is_active' => true],
            ['name' => 'license', 'is_active' => true],
            ['name' => 'components', 'is_active' => true],

        ];

        DB::table('categories')->insert($categories);
       // \App\Models\Category::factory(10)->create();
    }
}
