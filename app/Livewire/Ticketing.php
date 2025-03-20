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
use Illuminate\Support\Facades\Auth;

class Ticketing extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    // Form Fields
    public $selectedType = null;
    public $selectedSubType = null;
    public $title = '';
    public $description = '';
    public $priority = 'medium';

    // Control variables
    public $showTicketForm = false;

    protected $listeners = ['close-modal' => 'resetForm'];

    protected $rules = [
        'title' => 'required|min:5',
        'description' => 'required|min:10',
        'priority' => 'required|in:low,medium,high',
    ];

    public function selectIssueType($type)
    {
        $this->selectedType = $type;
        $this->selectedSubType = null;
        $this->showTicketForm = false;

        // All types now show a subtype selection before the form
        // No special condition needed anymore as we've made all types behave the same
    }

    public function selectSubType($subType)
    {
        $this->selectedSubType = $subType;
        $this->showTicketForm = true;

        // Auto-generate title and description based on selected type and subtype
        $this->generateTicketContent();
    }

    protected function generateTicketContent()
    {
        // Generate title based on type and subtype
        $this->title = ucfirst($this->selectedType) . ' Issue: ' . $this->getReadableSubtype();

        // Generate description based on type and subtype
        $this->description = $this->generateDescription();
    }

    protected function getReadableSubtype()
    {
        $subtypeLabels = [
            // Hardware
            'mouse' => 'Mouse',
            'keyboard' => 'Keyboard',
            'monitor' => 'Monitor',
            'other' => 'Other Hardware',

            // Internet
            'lan' => 'LAN Connection',
            'wifi' => 'WiFi Connection',

            // Application
            'word' => 'Microsoft Word',
            'chrome' => 'Google Chrome',
            'excel' => 'Microsoft Excel',
            'other_app' => 'Other Application'
        ];

        return $subtypeLabels[$this->selectedSubType] ?? ucfirst($this->selectedSubType);
    }

    protected function generateDescription()
    {
        $templates = [
            'hardware' => [
                'mouse' => "I'm experiencing an issue with my mouse. The problem started [when/after] and is affecting my ability to work efficiently. Details of the mouse: [brand/model if known]. The specific symptoms include: [cursor not moving/clicking issues/etc].",
                'keyboard' => "I'm having trouble with my keyboard. The issue began [when/after] and is impacting my work. Details of the keyboard: [brand/model if known]. The specific symptoms include: [keys not responding/sticky keys/etc].",
                'monitor' => "I'm facing problems with my monitor. The issue started [when/after] and is affecting my ability to work. Details of the monitor: [brand/model if known]. The specific symptoms include: [display issues/flickering/no signal/etc].",
                'other' => "I'm experiencing an issue with a hardware component. The problem began [when/after] and is impacting my work. Hardware details: [specify the hardware]. The specific symptoms include: [describe the issues]."
            ],
            'internet' => [
                'lan' => "I'm experiencing issues with my LAN connection. The problem started [when/after] and is affecting my ability to work online. The specific symptoms include: [no connectivity/slow speeds/intermittent connection/etc].",
                'wifi' => "I'm having trouble with the WiFi connection. The issue began [when/after] and is impacting my online activities. The specific symptoms include: [no connectivity/slow speeds/dropping connection/etc]."
            ],
            'application' => [
                'word' => "I'm experiencing problems with Microsoft Word. The issue started [when/after] and is affecting my document work. Version details: [Word version if known]. The specific symptoms include: [crashing/not saving/formatting issues/etc].",
                'chrome' => "I'm having issues with Google Chrome. The problem began [when/after] and is impacting my browsing experience. Version details: [Chrome version if known]. The specific symptoms include: [crashing/slow performance/rendering issues/etc].",
                'excel' => "I'm facing problems with Microsoft Excel. The issue started [when/after] and is affecting my spreadsheet work. Version details: [Excel version if known]. The specific symptoms include: [calculation errors/crashing/formatting issues/etc].",
                'other_app' => "I'm experiencing issues with an application. The problem began [when/after] and is impacting my work. Application details: [name and version]. The specific symptoms include: [describe the issues]."
            ]
        ];

        // Return the template if available, otherwise a generic template
        if (isset($templates[$this->selectedType][$this->selectedSubType])) {
            return $templates[$this->selectedType][$this->selectedSubType];
        }

        return "I'm experiencing an issue with " . ucfirst($this->selectedType) . " - " . $this->getReadableSubtype() . ". The problem started [when/after] and is affecting my work. The specific symptoms include: [describe the issues].";
    }

    public function resetForm()
    {
        $this->selectedType = null;
        $this->selectedSubType = null;
        $this->title = '';
        $this->description = '';
        $this->priority = 'medium';
        $this->showTicketForm = false;
        $this->resetErrorBag();
    }

    public function submitTicket()
    {
        $this->validate();

        try {
            // Create ticket
            Ticket::create([
                'title' => $this->title,
                'description' => $this->description,
                'priority' => $this->priority,
                'type' => $this->selectedType,
                'subtype' => $this->selectedSubType,
                'user_id' => Auth::id(),
                'status' => 'open',
            ]);

            // Reset form and show success message
            $this->resetForm();

            // Dispatch event to update Alpine.js state
            $this->dispatch('ticket-created', [
                'message' => 'Ticket created successfully!'
            ]);

            session()->flash('message', 'Ticket created successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Error creating ticket: ' . $e->getMessage());
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Ticket::query())
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('title'),
                TextColumn::make('type'),
                TextColumn::make('subtype'),
                TextColumn::make('status'),
                TextColumn::make('priority'),
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
