<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Ticket;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tickets = [
            [
                'title' => 'Printer not responding in Computer Lab 1',
                'description' => 'HP LaserJet printer in Computer Lab 1 is not responding to print commands. Error message shows "Printer Offline" despite being powered on.',
                'ticket_type' => 'hardware',
                'priority' => 'high',
                'section_id' => 1, // Computer Laboratory
                'status' => 'in progress'
            ],
            [
                'title' => 'MS Office not activating on faculty computer',
                'description' => 'Microsoft Office showing activation required error on faculty PC-231. License key verification needed.',
                'ticket_type' => 'software',
                'priority' => 'medium',
                'section_id' => 2, // Faculty Room
                'status' => 'pending'
            ],
            [
                'title' => 'Slow internet connection in Library',
                'description' => 'Students reporting extremely slow internet speed in Library computers. Affecting research work and online resource access.',
                'ticket_type' => 'network',
                'priority' => 'high',
                'section_id' => 3, // Library
                'status' => 'in progress'
            ],
            [
                'title' => 'Projector display issues in Room 405',
                'description' => 'Projector showing blurry images and occasionally flickering during presentations. Affecting classroom lectures.',
                'ticket_type' => 'hardware',
                'priority' => 'medium',
                'section_id' => 4, // Lecture Room
                'status' => 'pending'
            ],
            [
                'title' => 'Database error in Registrar system',
                'description' => 'Error accessing student records database. Staff unable to process enrollment requests.',
                'ticket_type' => 'software',
                'priority' => 'urgent',
                'section_id' => 5, // Registrar Office
                'status' => 'in progress'
            ],
            [
                'title' => 'Computer unit not booting in Lab 2',
                'description' => 'PC-15 in Computer Lab 2 showing black screen on startup. No BIOS screen appearing.',
                'ticket_type' => 'hardware',
                'priority' => 'medium',
                'section_id' => 1,
                'status' => 'completed'
            ],
            [
                'title' => 'WiFi connectivity issues in Faculty Lounge',
                'description' => 'Faculty members unable to connect to WiFi network in the lounge area. Authentication problems reported.',
                'ticket_type' => 'network',
                'priority' => 'high',
                'section_id' => 2,
                'status' => 'in progress'
            ],
            [
                'title' => 'Scanner malfunction in Admin office',
                'description' => 'Document scanner producing streaked images. Cleaning and calibration may be required.',
                'ticket_type' => 'hardware',
                'priority' => 'low',
                'section_id' => 5,
                'status' => 'pending'
            ],
            [
                'title' => 'Student portal login issues',
                'description' => 'Multiple students reporting inability to log into student portal. Password reset function not working.',
                'ticket_type' => 'software',
                'priority' => 'high',
                'section_id' => 3,
                'status' => 'in progress'
            ],
            [
                'title' => 'AC unit malfunction in Server Room',
                'description' => 'Server room temperature rising above normal levels. AC unit showing error code E4.',
                'ticket_type' => 'maintenance',
                'priority' => 'urgent',
                'section_id' => 1,
                'status' => 'in progress'
            ]
        ];

        foreach ($tickets as $ticket) {
            Ticket::create([
                'ticket_number' => 'TK-' . now()->format('Ym') . '-' . Str::random(6),
                'asset_id' => rand(1, 15),
                'user_id' => rand(1, 10),
                'assigned_to' => rand(11, 15),
                'section_id' => $ticket['section_id'],
                'title' => $ticket['title'],
                'description' => $ticket['description'],
                'ticket_type' => $ticket['ticket_type'],
                'priority' => $ticket['priority'],
                'due_date' => Carbon::now()->addDays(rand(1, 14)),
                'date_finished' => $ticket['status'] === 'completed' ? Carbon::now() : null,
                'status' => $ticket['status']
            ]);
        }
    }
        
    
}
