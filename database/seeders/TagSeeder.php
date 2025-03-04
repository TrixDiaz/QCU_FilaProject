<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timestamp = Carbon::now();

        $tags = [
            ['name' => 'Computer Case', 'slug' => 'computer-case', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Power Supply', 'slug' => 'power-supply', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Motherboard', 'slug' => 'motherboard', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Processor', 'slug' => 'processor', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Drive', 'slug' => 'drive', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Ram', 'slug' => 'ram', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Graphics Card', 'slug' => 'graphics-card', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Monitor', 'slug' => 'monitor', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Keyboard', 'slug' => 'keyboard', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Mouse', 'slug' => 'mouse', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Headphone', 'slug' => 'headphone', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Speaker', 'slug' => 'speaker', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Printer', 'slug' => 'printer', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Scanner', 'slug' => 'scanner', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Webcam', 'slug' => 'webcam', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Microphone', 'slug' => 'microphone', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Joystick', 'slug' => 'joystick', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Gamepad', 'slug' => 'gamepad', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Controller', 'slug' => 'controller', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Console', 'slug' => 'console', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Router', 'slug' => 'router', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Modem', 'slug' => 'modem', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Switch', 'slug' => 'switch', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Hub', 'slug' => 'hub', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Access Point', 'slug' => 'access-point', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Firewall', 'slug' => 'firewall', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Network Card', 'slug' => 'network-card', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Cable', 'slug' => 'cable', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Adapter', 'slug' => 'adapter', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Antenna', 'slug' => 'antenna', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Battery', 'slug' => 'battery', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Charger', 'slug' => 'charger', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Cooling Pad', 'slug' => 'cooling-pad', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Cooling Stand', 'slug' => 'cooling-stand', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Cooling Fan', 'slug' => 'cooling-fan', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Cooling System', 'slug' => 'cooling-system', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Cooling Unit', 'slug' => 'cooling-unit', 'created_at' => $timestamp, 'updated_at' => $timestamp],
        ];

        DB::table('tags')->insert($tags);
    }
}
