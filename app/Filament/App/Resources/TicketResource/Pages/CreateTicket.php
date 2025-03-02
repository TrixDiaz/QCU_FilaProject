<?php

namespace App\Filament\App\Resources\TicketResource\Pages;

use App\Filament\App\Resources\TicketResource;
use App\Models\Approval;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;

    protected function afterCreate(): void
    {
        $ticket = $this->record;

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
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
