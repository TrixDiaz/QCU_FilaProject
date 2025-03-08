<?php

namespace App\Livewire;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TicketSummary extends BaseWidget implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Ticket Summary')
            ->query(\App\Models\Ticket::query()->orderBy('priority', 'desc'))
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('ticket_number'),
                \Filament\Tables\Columns\TextColumn::make('ticket_type'),
                \Filament\Tables\Columns\TextColumn::make('priority'),
                \Filament\Tables\Columns\TextColumn::make('ticket_status')->badge()->extraAttributes(['class' => 'capitalize']),
            ])
            ->emptyStateHeading('No Tickets yet')
            ->defaultPaginationPageOption(5)
            ->paginationPageOptions([5, 10, 25, 50])
            ->striped();
    }
}
