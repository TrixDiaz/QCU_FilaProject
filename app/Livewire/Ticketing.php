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
        
        // Dispatch the template update event
        if ($this->description) {
            $this->dispatch('updateTemplate', ['template' => $this->description]);
        }
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
                'mouse' => "**Mouse Issue**\n- Location/Tag: [specify]\n- Problem: [describe issue]\n- Since: [date/time]\n- Steps Tried: [list steps]",
                'keyboard' => "**Keyboard Issue**\n- Location/Tag: [specify]\n- Problem: [describe issue]\n- Since: [date/time]\n- Steps Tried: [list steps]",
                'monitor' => "**Monitor Issue**\n- Location/Tag: [specify]\n- Problem: [describe issue]\n- Since: [date/time]\n- Steps Tried: [list steps]"
            ],
            'internet' => [
                'lan' => "**LAN Issue**\n- Location: [specify]\n- Problem: [describe issue]\n- Since: [date/time]\n- Steps Tried: [list steps]",
                'wifi' => "**WiFi Issue**\n- Location: [specify]\n- Network: [if known]\n- Problem: [describe issue]\n- Since: [date/time]"
            ],
            'application' => [
                'word' => "**MS Word Issue**\n- Problem: [describe issue]\n- Since: [date/time]\n- Steps Tried: [list steps]"
            ],
            'asset_request' => [
                'default' => "**Asset Request**\n- Item: [specify]\n- Quantity: [number]\n- Purpose: [brief explanation]\n- When Needed: [date]"
            ],
            'general_inquiry' => [
                'default' => "**General Inquiry**\n- Topic: [specify]\n- Question: [your inquiry]\n- Preferred Contact: [email/phone]"
            ],
            'classroom_request' => [
                'default' => "**Classroom Request**\n- Date: [specify]\n- Time: [start-end]\n- Purpose: [brief description]\n- Attendees: [number]"
            ]
        ];

        if (in_array($this->selectedType, ['asset_request', 'general_inquiry', 'classroom_request'])) {
            return $templates[$this->selectedType]['default'];
        }

        return $templates[$this->selectedType][$this->selectedSubType] ?? 
            "**{$this->getReadableSubtype()} Issue**\n- Problem: [describe issue]\n- Since: [date/time]\n- Steps Tried: [list steps]";
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
                TextColumn::make('ticket_number')
                    ->label('Ticket No.')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'high' => 'danger',
                        'medium' => 'warning',
                        'low' => 'success',
                        default => 'info'
                    }),
                TextColumn::make('ticket_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'info',
                        'in_progress' => 'warning',
                        'resolved' => 'success',
                        'closed' => 'success',
                        'archived' => 'gray',
                        default => 'info'
                    }),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('view')
                    ->icon('heroicon-m-eye')
                    ->color('info')
                    ->button()
                    ->modalContent(fn (Ticket $record) => view(
                        'tickets.view',
                        ['ticket' => $record]
                    )),
                \Filament\Tables\Actions\Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->color('warning')
                    ->button()
                    ->modalContent(fn (Ticket $record) => view(
                        'tickets.edit',
                        [
                            'ticket' => $record,
                            'technicians' => User::role('technician')->get()
                        ]
                    )),
                \Filament\Tables\Actions\Action::make('assign')
                    ->icon('heroicon-m-user-plus')
                    ->color('success')
                    ->button()
                    ->label(fn (Ticket $record) => 
                        is_null($record->assigned_to) ? 
                            (auth()->user()->hasRole('technician') ? 'Claim' : 'Assign') : 
                            'Reassign'
                    )
                    ->modalHeading(fn (Ticket $record) => 
                        is_null($record->assigned_to) ? 
                            (auth()->user()->hasRole('technician') ? 'Claim Ticket' : 'Assign Ticket') : 
                            'Reassign Ticket'
                    )
                    ->form([
                        Select::make('assign_type')
                            ->label('Assignment Type')
                            ->options([
                                'self' => 'Assign to myself',
                                'auto' => 'Auto-assign to available technician',
                                'specific' => 'Select specific technician'
                            ])
                            ->required()
                            ->reactive()
                            ->visible(fn () => auth()->user()->hasRole(['admin', 'supervisor'])),
                        Select::make('technician_id')
                            ->label('Select Technician')
                            ->options(fn () => User::role('technician')->pluck('name', 'id'))
                            ->visible(fn (Get $get) => $get('assign_type') === 'specific')
                            ->required(fn (Get $get) => $get('assign_type') === 'specific')
                    ])
                    ->visible(fn (Ticket $record) => 
                        auth()->user()->hasRole(['admin', 'supervisor']) || 
                        (auth()->user()->hasRole('technician') && $record->ticket_status === 'open')
                    )
                    ->action(function (array $data, Ticket $record): void {
                        // ... existing assign action code ...
                    })
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
            ])
            ->defaultSort('created_at', 'desc');
    }

    public function render()
    {
        return view('livewire.ticketing');
    }
}
