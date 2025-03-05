<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timestamp = Carbon::now();

        $categories = [
            ['name' => 'hardware', 'slug' => 'hardware', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'software', 'slug' => 'software', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'license', 'slug' => 'license', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'components', 'slug' => 'components', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'accessories', 'slug' => 'accessories', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'others', 'slug' => 'others', 'created_at' => $timestamp, 'updated_at' => $timestamp],
        ];

        DB::table('categories')->insert($categories);
    }
}
