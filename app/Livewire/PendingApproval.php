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

class PendingApproval extends BaseWidget implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Pending Approval')
            ->query(\App\Models\Approval::query())
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('ticket.ticket_number'),
                \Filament\Tables\Columns\TextColumn::make('ticket.ticket_type')->label('Ticket Type'),
                \Filament\Tables\Columns\TextColumn::make('ticket.option')->label('Ticket Option'),
                \Filament\Tables\Columns\TextColumn::make('status')->badge()->extraAttributes(['class' => 'capitalize']),
            ])
            ->emptyStateHeading('No Tickets yet')
            ->defaultPaginationPageOption(5)
            ->paginationPageOptions([5, 10, 25, 50])
            ->striped();
    }
}
