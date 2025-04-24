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
use Livewire\Attributes\On;

class TicketSummary extends BaseWidget implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected int $pollInterval = 10; // Auto-refresh every 10 seconds
    
    // Listen for refreshTable event
    #[On('refreshTable')]
    public function refresh(): void
    {
        $this->resetTable();
    }
    
    // Also refresh on dashboard events
    #[On('dashboard-updated')]
    public function refreshDashboard(): void
    {
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Ticket Summary')
            ->query(
                \App\Models\Ticket::query()
                    ->orderBy('created_at', 'desc') // Show newest first
                    ->orderBy('priority', 'desc') // Then by priority
            )
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('ticket_number'),
                \Filament\Tables\Columns\TextColumn::make('ticket_type'),
                \Filament\Tables\Columns\TextColumn::make('priority'),
                \Filament\Tables\Columns\TextColumn::make('ticket_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'info',
                        'in_progress' => 'warning',
                        'resolved' => 'success',
                        'closed' => 'success',
                        default => 'gray',
                    })
                    ->extraAttributes(['class' => 'capitalize']),
            ])
            ->emptyStateHeading('No Tickets yet')
            ->defaultPaginationPageOption(5)
            ->paginationPageOptions([5, 10, 25, 50])
            ->striped()
            ->poll() // Enable automatic polling
            ->defaultSort('created_at', 'desc'); // Default sort by newest
    }
}
