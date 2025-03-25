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
            ['name' => 'Hardware', 'slug' => 'hardware', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Software', 'slug' => 'software', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'License', 'slug' => 'license', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Components', 'slug' => 'components', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Accessories', 'slug' => 'accessories', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Computer set', 'slug' => 'computer_set', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Others', 'slug' => 'others', 'created_at' => $timestamp, 'updated_at' => $timestamp],
        ];

        DB::table('categories')->insert($categories);
    }
}
