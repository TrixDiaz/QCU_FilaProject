<?php

namespace App\Filament\App\Resources\TicketResource\Pages;

use App\Filament\App\Resources\TicketResource;
use App\Models\Approval;
use App\Models\Asset;
use App\Models\AssetGroup;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use App\Mail\TicketCreatedMail;
use App\Mail\TicketAssignedMail;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;

    protected function afterCreate(): void
    {
        $ticket = $this->record;
        $creator = User::find($ticket->created_by);
        $assignee = User::find($ticket->assigned_to);

        if ($ticket->ticket_type === 'request') {
            Approval::create([
                'ticket_id' => $ticket->id,
                'asset_id' => $ticket->asset_id,
                'professor_id' => $ticket->created_by,
                'section_id' => $ticket->section_id,
                'subject_id' => $ticket->subject_id,
                'option' => $ticket->option,
                'title' => $ticket->title,
                'starts_at' => $ticket->starts_at,
                'ends_at' => $ticket->ends_at,
                'status' => 'pending'
            ]);

            // Send emails for request ticket
            $this->sendNotificationEmails($ticket, $creator, $assignee);
        } elseif ($ticket->ticket_type === 'incident') {
            // Get the asset and update its status to inactive
            $asset = Asset::find($ticket->asset_id);

            if ($asset) {
                // Update asset status to inactive
                $asset->status = 'inactive';
                $asset->save();

                // Update related assetGroup status to inactive
                if ($asset->assetGroup) {
                    $asset->assetGroup->status = 'inactive';
                    $asset->assetGroup->save();
                }
            }

            // Send emails for incident ticket
            $this->sendNotificationEmails($ticket, $creator, $assignee);
        }
    }

    /**
     * Send notification emails to ticket creator and assignee
     */
    protected function sendNotificationEmails($ticket, $creator, $assignee): void
    {
        // Send email to creator
        if ($creator && $creator->email) {
            Mail::to($creator->email)->send(new TicketCreatedMail($ticket));
        }

        // Send email to assignee if assigned
        if ($assignee && $assignee->email) {
            Mail::to($assignee->email)->send(new TicketAssignedMail($ticket));
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
