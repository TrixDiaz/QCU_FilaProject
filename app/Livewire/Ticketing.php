<?php

namespace App\Livewire;

use App\Models\Ticket;
use App\Models\Asset;
use App\Models\User;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

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
    public $asset_id = null; // Added for asset dropdown
    public $assigned_to = null; // Added for technician assignment
    public $classroom_id = null;
    public $section_id = null;
    public $classrooms = [];
    public $sections = [];

    // Control variables
    public $showTicketForm = false;
    public $assets = []; // Will hold available assets
    public $technicians = []; // Will hold technician users

    protected $listeners = ['close-modal' => 'resetForm'];

    protected $rules = [
        'title' => 'required|min:5',
        'description' => 'required|min:10',
        'priority' => 'required|in:low,medium,high',
        'asset_id' => 'nullable|exists:assets,id',
        'assigned_to' => 'nullable|exists:users,id',
        'classroom_id' => 'nullable|required_if:selectedType,classroom_request|exists:classrooms,id',
        'section_id' => 'nullable|required_if:selectedType,classroom_request|exists:sections,id',
    ];

    public function mount()
    {
        // Load assets and technicians when component is mounted
        $this->loadAssets();
        $this->loadTechnicians();
        $this->loadClassroomsAndSections();
    }

    protected function loadAssets()
    {
        $this->assets = Asset::all();
    }

    protected function loadTechnicians()
    {
        $this->technicians = User::whereHas('roles', function ($query) {
            $query->where('name', 'technician');
        })->get();
    }

    protected function loadClassroomsAndSections()
    {
        $this->classrooms = \App\Models\Classroom::all();
        $this->sections = \App\Models\Section::all();
    }

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

        // Filter assets based on selected subtype
        $this->filterAssetsBySubtype($subType);
    }

    /**
     * Filter assets based on the selected subtype
     */
    protected function filterAssetsBySubtype($subType)
    {
        // If hardware is selected, filter assets by the hardware type
        if ($this->selectedType === 'hardware') {
            // For 'other' hardware, show all hardware assets
            if ($subType === 'other') {
                $this->assets = Asset::whereHas('tags', function ($query) {
                    $query->where('name', 'like', 'hardware%');
                })->get();
            } else {
                // For specific hardware types (mouse, keyboard, monitor, etc.)
                $this->assets = Asset::whereHas('tags', function ($query) use ($subType) {
                    $query->where('name', $subType);
                })->get();
            }
        } else {
            // For non-hardware issues, show all assets
            $this->loadAssets();
        }
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
        $this->asset_id = null;
        $this->assigned_to = null;
        $this->classroom_id = null;
        $this->section_id = null;
        $this->showTicketForm = false;
        $this->resetErrorBag();

        // Reset the assets to show all assets
        $this->loadAssets();
    }

    public function submitTicket()
    {
        $this->validate();

        try {
            // Generate a unique ticket number
            $ticketNumber = $this->generateTicketNumber();

            // Determine ticket type based on selectedType
            $ticketType = match($this->selectedType) {
                'classroom_request' => 'classroom',
                'asset_request' => 'asset',
                'general_inquiry' => 'inquiry',
                default => 'incident'
            };

            // Create ticket
            Ticket::create([
                'ticket_number' => $ticketNumber,
                'title' => $this->title,
                'description' => $this->description,
                'priority' => $this->priority,
                'type' => $this->selectedType,
                'subtype' => $this->selectedSubType,
                'asset_id' => $this->asset_id,
                'assigned_to' => $this->assigned_to,
                'user_id' => Auth::id(),
                'created_by' => Auth::id(),
                'ticket_type' => $ticketType,
                'ticket_status' => 'open',
                'classroom_id' => $this->classroom_id,
                'section_id' => $this->section_id,
            ]);

            // Reset form
            $this->resetForm();

            // Close the modal with Alpine.js
            $this->dispatch('close-ticket-modal');

            // Dispatch both notification types for flexibility
            $this->dispatch('notify', [
                'message' => "Ticket {$ticketNumber} has been created successfully.",
                'type' => 'success'
            ]);

            // Show Filament notification
            Notification::make()
                ->title('Ticket Created')
                ->body("Ticket {$ticketNumber} has been created successfully.")
                ->success()
                ->send();
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'message' => 'Error creating ticket: ' . $e->getMessage(),
                'type' => 'error'
            ]);

            Notification::make()
                ->title('Error')
                ->body('Error creating ticket: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Generate a unique ticket number in the format INC-xxxxxx
     */
    protected function generateTicketNumber()
    {
        $prefix = match($this->selectedType) {
            'classroom_request' => 'CLS-',
            'asset_request' => 'AST-',
            'general_inquiry' => 'INQ-',
            default => 'INC-'
        };

        $randomPart = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        $ticketNumber = $prefix . $randomPart;

        // Check if this ticket number already exists
        while (Ticket::where('ticket_number', $ticketNumber)->exists()) {
            $randomPart = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $ticketNumber = $prefix . $randomPart;
        }

        return $ticketNumber;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Ticket::query())
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('title'),
                TextColumn::make('ticket_type'),
                TextColumn::make('subtype')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ticket_status'),
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
