<?php

namespace App\Livewire;

use App\Models\Ticket;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Livewire\Component;

class Ticketing extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public bool $isReportModalOpen = false;
    public $selectedType = null;
    public $selectedSubType = null;
    public $showTicketForm = false;

    public function selectIssueType($type)
    {
        $this->selectedType = $type;
        $this->selectedSubType = null;
        $this->showTicketForm = false;

        // If type is not hardware, we might want to show the form directly
        if ($type !== 'hardware') {
            $this->showTicketForm = true;
        }
    }

    public function selectSubType($subType)
    {
        $this->selectedSubType = $subType;
        $this->showTicketForm = true;
    }

    public function resetForm()
    {
        $this->selectedType = null;
        $this->selectedSubType = null;
        $this->showTicketForm = false;
        $this->isReportModalOpen = false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Ticket::query())
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->defaultSort('created_at', 'desc');
    }

    public function render()
    {
        return view('livewire.ticketing');
    }
}
