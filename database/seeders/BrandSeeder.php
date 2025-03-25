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
            ['name' => 'Asus', 'slug' => 'asus', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Dell', 'slug' => 'dell', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Lenovo', 'slug' => 'lenovo', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Apple', 'slug' => 'apple', 'created_at' => $timestamp, 'updated_at' => $timestamp],
        ];

        DB::table('brands')->insert($brands);
    }
}
