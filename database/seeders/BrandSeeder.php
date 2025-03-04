<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timestamp = Carbon::now();

        $brands = [
            ['name' => 'asus', 'slug' => 'asus', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'dell', 'slug' => 'dell', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'lenovo', 'slug' => 'lenovo', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'apple', 'slug' => 'apple', 'created_at' => $timestamp, 'updated_at' => $timestamp],
        ];

        DB::table('brands')->insert($brands);
    }
}
