<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Ticket;
use App\Models\AssetGroup;
use App\Models\Event;
use App\Models\User;
use App\Mail\TicketApproved;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ApprovalLivewire extends Component
{
    public $tickets = [];

    // Method to approve a ticket
    public function approveTicket($ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);

        // Get the necessary data from the ticket
        $option = $ticket->option ?? 'asset';
        $ticketType = $ticket->type ?? null;

        // Update ticket status to 'resolved'
        $ticket->update(['status' => 'resolved']);

        // Handle asset option
        if ($option === 'asset') {
            // Check if asset_id exists, if not, we cannot proceed with asset group creation
            if (!$ticket->asset_id) {
                session()->flash('warning', 'Cannot create asset group: Missing asset ID');
                $this->dispatch('refreshTickets');
                return;
            }

            // Get a valid classroom ID using various relationships
            $classroomId = null;

            // First try to get classroom from the subject
            if ($ticket->subject_id && $ticket->subject && $ticket->subject->classroom_id) {
                $classroomId = $ticket->subject->classroom_id;
            }
            // If not available, try to get from section
            else if ($ticket->section && $ticket->section->classroom_id) {
                $classroomId = $ticket->section->classroom_id;
            }
            // Last resort - fetch the first classroom as fallback
            else {
                $firstClassroom = \App\Models\Classroom::first();
                $classroomId = $firstClassroom ? $firstClassroom->id : 1;
            }

            // Debug output
            Log::info('Asset Group Creation', [
                'subject_id' => $ticket->subject_id,
                'asset_id' => $ticket->asset_id ?? 'null',
                'classroom_id' => $classroomId
            ]);

            // Generate asset code if needed
            $assetCode = null;
            if ($ticket->asset && $ticket->asset->asset_code) {
                $assetCode = $ticket->asset->asset_code;
            } else {
                // Generate fallback code
                $assetCode = substr(strtoupper(uniqid()), 0, 10);
                // Try to use the resource method if available
                try {
                    if (class_exists('\App\Filament\App\Resources\AssetResource')) {
                        $assetCode = \App\Filament\App\Resources\AssetResource::generateUniqueCode();
                    }
                } catch (\Exception $e) {
                    Log::error('Error generating asset code: ' . $e->getMessage());
                }
            }

            // Create new asset group with valid classroom_id
            AssetGroup::create([
                'asset_id' => $ticket->asset_id,
                'classroom_id' => $classroomId,
                'name' => $ticket->title ?? 'Asset Request',
                'code' => $assetCode,
                'status' => 'deploy',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update asset status to 'deploy'
            if ($ticket->asset) {
                $ticket->asset->update(['status' => 'deploy']);

                // Also update all related asset groups to 'deploy'
                AssetGroup::where('asset_id', $ticket->asset_id)
                    ->update(['status' => 'deploy']);
            }
        }
        // Handle classroom option
        else {
            // For classroom/event option, retrieve dates
            $startsAt = $ticket->starts_at;
            $endsAt = $ticket->ends_at;

            // Create event record for classroom option
            if ($ticketType === 'request' && $option === 'classroom') {
                $event = Event::create([
                    'professor_id' => $ticket->professor_id ?? Auth::id(),
                    'section_id' => $ticket->section_id,
                    'subject_id' => $ticket->subject_id,
                    'title' => $ticket->title ?? 'Classroom Request',
                    'color' => $ticket->color ?? '#a855f7',
                    'starts_at' => $startsAt ?? now(),
                    'ends_at' => $endsAt ?? now()->addHour(),
                    'created_at' => now(),
                    'updated_at' => now(),
                    'is_visible' => true,
                    'calendar_id' => 1,
                ]);

                // If classroom_id is available, associate it with the event
                $classroomId = null;
                if ($ticket->subject && $ticket->subject->classroom_id) {
                    $classroomId = $ticket->subject->classroom_id;
                } else if ($ticket && $ticket->classroom_id) {
                    $classroomId = $ticket->classroom_id;
                } else if ($ticket->section && $ticket->section->classroom_id) {
                    $classroomId = $ticket->section->classroom_id;
                }

                if ($classroomId) {
                    // Update the event with classroom information
                    $event->update(['classroom_id' => $classroomId]);

                    // Log successful calendar registration
                    Log::info('Calendar event created', [
                        'event_id' => $event->id,
                        'classroom_id' => $classroomId,
                        'title' => $ticket->title
                    ]);
                }
            }
        }

        // Send email notification to ticket creator
        if ($ticket->created_by) {
            $user = User::find($ticket->created_by);

            if ($user && $user->email) {
                Mail::to($user->email)
                    ->send(new TicketApproved([
                        'ticketTitle' => $ticket->title,
                        'ticketType' => $ticketType,
                        'ticketOption' => $option,
                        'userName' => $user->name,
                    ]));
            }
        }

        session()->flash('message', 'Ticket approved successfully.');
        $this->dispatch('refreshTickets');
    }

    // Method to decline/cancel a ticket
    public function cancelTicket($ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);

        // Delete the ticket directly
        $ticket->delete();

        session()->flash('message', 'Ticket declined and deleted.');
        $this->dispatch('refreshTickets');
    }

    public function mount()
    {
        $this->refreshTickets();
    }

    public function refreshTickets()
    {
        // Get tickets of type asset_request or classroom_request
        $this->tickets = Ticket::whereIn('type', ['asset_request', 'classroom_request'])
            ->with(['creator', 'asset', 'classroom', 'section'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function render()
    {
        return view('livewire.approval-livewire');
    }
}
