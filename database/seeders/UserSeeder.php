<?php

namespace Database\Seeders;


use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'email_verified_at' => \Carbon\Carbon::now(),
            'password' => bcrypt('password'),
        ]);

        \App\Models\User::create([
            'name' => 'Technician',
            'email' => 'technician@gmail.com',
            'email_verified_at' => \Carbon\Carbon::now(),
            'password' => bcrypt('password'),
        ]);

        \App\Models\User::create([
            'name' => 'Professor',
            'email' => 'professor@gmail.com',
            'email_verified_at' => \Carbon\Carbon::now(),
            'password' => bcrypt('password'),
        ]);

        //        \App\Models\User::factory(10)->create();
    }
}
