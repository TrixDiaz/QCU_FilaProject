<?php

namespace App\Filament\App\Resources\TicketResource\Pages;

use App\Filament\App\Resources\TicketResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;

    protected function getTableQuery(): ?Builder
    {
        return parent::getTableQuery()->orderByDesc('id');
    }

    public function getTabs(): array
    {
        $user = auth()->user();

        if (!$user->hasRole(['super-admin', 'admin', 'technician'])) {
            return [
                'open' => Tab::make('Open Tickets')
                    ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'open')),
                'in_progress' => Tab::make('In Progress Tickets')
                    ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'in_progress')),
                'pending' => Tab::make('Pending Tickets')
                    ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'pending')),
            ];
        }

        return [
            'all' => Tab::make('All Tickets'),
            'open' => Tab::make('Open Tickets')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'open')),
            'in_progress' => Tab::make('In Progress Tickets')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'in_progress')),
            'pending' => Tab::make('Pending Tickets')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'pending')),
            'resolved' => Tab::make('Resolved Tickets')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'resolved')),
            'closed' => Tab::make('Closed Tickets')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'closed')),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'open';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
