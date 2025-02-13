<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\Ticket;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::pluck('id')->toArray();
        $assets = Asset::pluck('id')->toArray();
        $sections = Section::pluck('id')->toArray();

        // Generate 50 tickets with proper foreign key assignments
        Ticket::factory(50)->create([
            'created_by' => function () use ($users) {
                return collect($users)->random();
            },
            'assigned_to' => function () use ($users) {
                return collect($users)->random();
            },
            'asset_id' => function () use ($assets) {
                return collect($assets)->random();
            },
            'section_id' => function () use ($sections) {
                return collect($sections)->random();
            },
        ]);
    }
}
