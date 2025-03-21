<?php

namespace App\Livewire;

use App\Models\Asset;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Classroom;
use App\Models\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

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
    public $asset_id = null;
    public $assigned_to = null;
    public $classroom_id = null;
    public $section_id = null;
    
    // Data Collections
    public $classrooms = [];
    public $sections = [];
    public $assets = [];
    public $technicians = [];
    
    // Control Variables
    public $showTicketForm = false;
    
    protected $listeners = ['close-modal' => 'resetForm'];

    protected $rules = [
        'title' => 'required|min:5|max:255',
        'description' => 'required|min:10|max:65535',
        'priority' => 'required|in:low,medium,high',
        'asset_id' => 'nullable|exists:assets,id',
        'assigned_to' => 'nullable|exists:users,id',
        'classroom_id' => 'nullable|required_if:selectedType,classroom_request|exists:classrooms,id',
        'section_id' => 'nullable|required_if:selectedType,classroom_request|exists:sections,id',
    ];

    public function mount()
    {
        $this->loadInitialData();
    }

    protected function loadInitialData()
    {
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
        $this->technicians = User::whereHas('roles', fn($query) => 
            $query->where('name', 'technician')
        )->get();
    }

    protected function loadClassroomsAndSections()
    {
        $this->classrooms = Classroom::all();
        $this->sections = Section::all();
    }

    public function selectIssueType($type)
    {
        $this->selectedType = $type;
        $this->selectedSubType = null;
        $this->showTicketForm = false;
    }

    public function selectSubType($subType)
    {
        $this->selectedSubType = $subType;
        $this->showTicketForm = true;
        $this->generateTicketContent();
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

    protected function formatDescription($description)
    {
        if (empty($description)) {
            return '';
        }
        
        if (is_array($description)) {
            return implode("\n", $description);
        }
        
        return trim($description);
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

    // Update the submitTicket method
    public function submitTicket()
    {
        $this->validate();

        try {
            $ticketNumber = $this->generateTicketNumber();
            $ticketType = match ($this->selectedType) {
                'asset_request', 'classroom_request', 'general_inquiry' => 'request',
                default => 'incident'
            };

            // For professors, ticket starts with no assignment
            $isTeacherRole = auth()->user()->hasRole('professor');
            
            $ticket = Ticket::create([
                'ticket_number' => $ticketNumber,
                'title' => $this->title,
                'description' => $this->formatDescription($this->description),
                'priority' => $this->priority,
                'type' => $this->selectedType,
                'subtype' => $this->selectedSubType,
                'asset_id' => $this->asset_id,
                'assigned_to' => $isTeacherRole ? null : $this->assigned_to, // No assignment for professors
                'created_by' => Auth::id(),
                'ticket_type' => $ticketType,
                'ticket_status' => 'open', // Always starts as open
                'classroom_id' => $this->classroom_id,
                'section_id' => $this->section_id,
            ]);

            $this->resetForm();
            $this->dispatch('close-ticket-modal');

            Notification::make()
                ->title('Ticket Created')
                ->body("Ticket {$ticketNumber} has been created successfully.")
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Error creating ticket: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Generate a unique ticket number in the format INC-XXXXXXXX or REQ-XXXXXXXX
     */
    protected function generateTicketNumber()
    {
        $isRequest = in_array($this->selectedType, ['classroom_request', 'asset_request', 'general_inquiry']);
        $basePrefix = $isRequest ? 'REQ-' : 'INC-';
        
        $subPrefix = match ($this->selectedType) {
            'classroom_request' => 'CLS-',
            'asset_request' => 'AST-',
            'general_inquiry' => 'INQ-',
            'hardware' => 'HW-',
            'internet' => 'NET-',
            'application' => 'APP-',
            default => ''
        };

        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $randomPart = '';
        
        do {
            $randomPart = '';
            for ($i = 0; $i < 8; $i++) {
                $randomPart .= $characters[random_int(0, strlen($characters) - 1)];
            }
            $ticketNumber = $basePrefix . $subPrefix . $randomPart;
        } while (Ticket::where('ticket_number', $ticketNumber)->exists());

        return $ticketNumber;
    }

    // Update the assign action in the table configuration
    public function table(Table $table): Table
    {
        return $table
            ->query(Ticket::query()->latest()) // Add latest() to sort by created_at in descending order
            ->columns([
                TextColumn::make('ticket_number')->searchable()->sortable(),
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'high' => 'danger',
                        'medium' => 'warning',
                        'low' => 'success',
                    }),
                TextColumn::make('ticket_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'info',
                        'in_progress' => 'warning',
                        'closed' => 'success',
                        'archived' => 'gray',
                    }),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'closed' => 'Closed',
                        'archived' => 'Archived',
                    ])
            ])
            ->actions([
                ViewAction::make()
                    ->icon('heroicon-m-eye')
                    ->modalContent(fn (Ticket $record) => view(
                        'tickets.view',
                        ['ticket' => $record]
                    )),
                EditAction::make()
                    ->icon('heroicon-m-pencil-square')
                    ->modalContent(fn (Ticket $record) => view(
                        'tickets.edit',
                        [
                            'ticket' => $record,
                            'technicians' => User::whereHas('roles', function ($query) {
                                $query->where('name', 'technician');
                            })->get()
                        ]
                    )),
                Action::make('assign')
                    ->icon('heroicon-m-user-plus')
                    ->color('success')
                    ->button()
                    ->label(fn (Ticket $record) => 
                        is_null($record->assigned_to) ? 
                            (auth()->user()->hasRole('technician') ? 'Claim Ticket' : 'Assign') : 
                            'Reassign'
                    )
                    ->modalHeading(fn (Ticket $record) => 
                        is_null($record->assigned_to) ? 
                            (auth()->user()->hasRole('technician') ? 'Claim Ticket' : 'Assign Ticket') : 
                            'Reassign Ticket'
                    )
                    ->modalDescription(fn (Ticket $record) => "Ticket #{$record->ticket_number}")
                    ->form(function (Ticket $record) {
                        // Only show assignment type for admins and supervisors
                        if (auth()->user()->hasRole(['admin', 'supervisor'])) {
                            return [
                                Select::make('assign_type')
                                    ->label('Assignment Type')
                                    ->options([
                                        'self' => 'Assign to myself',
                                        'auto' => 'Auto-assign to available technician',
                                        'specific' => 'Select specific technician'
                                    ])
                                    ->required()
                                    ->reactive(),
                                Select::make('technician_id')
                                    ->label('Select Technician')
                                    ->options(fn () => User::whereHas('roles', fn($query) => 
                                        $query->where('name', 'technician')
                                    )->pluck('name', 'id'))
                                    ->visible(fn (Get $get) => $get('assign_type') === 'specific')
                                    ->required(fn (Get $get) => $get('assign_type') === 'specific')
                            ];
                        }
                        return []; // Empty form for technician claim action
                    })
                    ->action(function (array $data, Ticket $record): void {
                        try {
                            // For technician claim action
                            if (auth()->user()->hasRole('technician') && empty($data)) {
                                $record->update([
                                    'assigned_to' => auth()->id(),
                                    'ticket_status' => 'in_progress'
                                ]);

                                Notification::make()
                                    ->title('Ticket Claimed')
                                    ->body("You have claimed ticket #{$record->ticket_number}")
                                    ->success()
                                    ->send();
                                return;
                            }

                            // For admin/supervisor assign action
                            $assignee_id = match($data['assign_type']) {
                                'self' => auth()->id(),
                                'auto' => User::whereHas('roles', fn($query) => 
                                    $query->where('name', 'technician')
                                )
                                ->withCount(['assignedTickets' => fn($query) => 
                                    $query->whereIn('ticket_status', ['open', 'in_progress'])
                                ])
                                ->orderBy('assigned_tickets_count')
                                ->first()?->id,
                                'specific' => $data['technician_id'],
                            };

                            if (!$assignee_id) {
                                throw new \Exception('No available technician found');
                            }

                            $technician = User::findOrFail($assignee_id);
                            $record->update([
                                'assigned_to' => $assignee_id,
                                'ticket_status' => 'in_progress'
                            ]);
                            
                            Notification::make()
                                ->title('Ticket Assigned')
                                ->body("Ticket #{$record->ticket_number} assigned to {$technician->name}")
                                ->success()
                                ->send();

                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error')
                                ->body('Failed to assign ticket: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (Ticket $record) => 
                        auth()->user()->hasRole(['admin', 'supervisor']) || 
                        (auth()->user()->hasRole('technician') && $record->ticket_status === 'open')
                    ),
            ])
            ->bulkActions([
                BulkAction::make('archive')
                    ->label('Archive Selected')
                    ->icon('heroicon-m-archive-box') // Changed from o-archive to m-archive-box
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Archive Selected Tickets')
                    ->modalDescription('Are you sure you want to archive the selected tickets? This action can be reversed.')
                    ->modalSubmitActionLabel('Yes, archive them')
                    ->action(function (Collection $records) {
                        $records->each(function ($record) {
                            $record->update(['ticket_status' => 'archived']);
                        });
                        Notification::make()
                            ->title('Tickets Archived Successfully')
                            ->success()
                            ->send();
                    }),
                BulkAction::make('delete')
                    ->label('Delete Selected')
                    ->icon('heroicon-m-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Delete Selected Tickets')
                    ->modalDescription('Are you sure you want to delete the selected tickets? This cannot be undone.')
                    ->modalSubmitActionLabel('Yes, delete them')
                    ->action(function (Collection $records) {
                        $records->each->delete();
                        Notification::make()
                            ->title('Tickets Deleted Successfully')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public function render()
    {
        return view('livewire.ticketing');
    }
}
