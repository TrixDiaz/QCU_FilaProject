<?php

namespace App\Filament\App\Resources\TicketResource\Pages;

use App\Filament\App\Resources\TicketResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;

class EditTicket extends EditRecord
{
    protected static string $resource = TicketResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function mount($record): void
    {
        parent::mount($record);

        // Prevent editing if the ticket is closed
        if ($this->record->ticket_ticket_status === 'closed') {
            Notification::make()
                ->title('Ticket is closed')
                ->danger()
                ->body('This ticket is closed and cannot be Edit.')
                ->send();

            $this->redirect(TicketResource::getUrl('index')); // Redirect to ticket list
        }
    }
    protected function getHeaderActions(): array
    {
        $actions = [];

        // Show "Mark as In Progress" only if ticket_status is "open"
        if ($this->record->ticket_status === 'open') {
            $actions[] = $this->markAsInProgressAction();
        }

        // Show "Mark as Resolved" only if ticket_status is "in_progress"
        if ($this->record->ticket_status === 'in_progress') {
            $actions[] = $this->markAsResolvedAction();
        }

        return $actions;
    }

    protected function markAsInProgressAction(): Action
    {
        return Action::make('mark_as_in_progress')
            ->label('Mark as In Progress')
            ->icon('heroicon-o-clock')
            ->requiresConfirmation()
            ->action(function () {
                $this->record->update([
                    'ticket_status' => 'in_progress',
                    'assigned_to' => auth()->id(), // Assign to the logged-in user
                ]);

                $this->notify('success', 'Ticket marked as In Progress and assigned to you.');
            })
            ->color('warning');
    }

    protected function markAsResolvedAction(): Action
    {
        return Action::make('mark_as_resolved')
            ->label('Mark as Resolved')
            ->icon('heroicon-o-check')
            ->requiresConfirmation()
            ->action(function () {
                $this->record->update([
                    'ticket_status' => 'resolved',  // Changed from 'in_progress' to 'resolved'
                    'resolved_at' => now(),  // Add resolved timestamp
                    'resolved_by' => auth()->id(),  // Add who resolved it
                ]);

                Notification::make()
                    ->title('Ticket Resolved')
                    ->success()
                    ->body('The ticket has been marked as resolved.')
                    ->send();

                $this->redirect($this->getResource()::getUrl('index'));
            })
            ->color('success')
            ->visible(fn() => !auth()->user()->hasRole('professor')); // Hide for professors
    }
}
