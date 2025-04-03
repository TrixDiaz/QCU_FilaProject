<?php

namespace App\Livewire;

use App\Models\Asset;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Classroom;
use App\Models\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Get;
use Filament\Tables\Actions\ActionGroup;
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
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\Indicator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Ticketing extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    // Form Fields
    public $selectedType;
    public $selectedSubType;
    public $selectedClassroom = null; // Updated
    public $selectedTerminal = null; // Updated
    public $title;
    public $description;
    public $priority = 'low';
    public $asset_id;
    public $assigned_to;
    public $classroom_id = null;
    public $section_id = null;
    public $start_time;
    public $end_time;
    public $timeConflictExists = false;
    public $assigned_technician = null;
    public $terminal = null;

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
        'assigned_to' => 'required|exists:users,id',
        'selectedType' => 'required|in:hardware,internet,application,asset_request,classroom_request,general_inquiry',
        'selectedSubType' => 'required_unless:selectedType,asset_request,classroom_request,general_inquiry',
        'selectedTerminal' => 'nullable|string',
        'selectedClassroom' => 'required_with:selectedTerminal',
        'terminal' => 'required_if:selectedType,internet',
        'classroom_id' => 'required_if:selectedType,internet'
    ];

    public function getRules()
    {
        $rules = $this->rules;

        if (in_array($this->selectedType, ['hardware', 'internet'])) {
            $rules['classroom_id'] = 'required|exists:classrooms,id';
            $rules['selectedTerminal'] = 'required|string';
        }

        // Add conditional validation for asset requests
        if ($this->selectedType === 'asset_request') {
            $rules['asset_id'] = 'required|exists:assets,id';
        }

        // Add conditional validation for classroom requests
        if ($this->selectedType === 'classroom_request') {
            $rules = array_merge($rules, $this->getClassroomRequestRules());
        }

        // Add conditional validation for internet issues
        if ($this->selectedType === 'internet') {
            $rules['selectedTerminal'] = 'required|string';
            $rules['classroom_id'] = 'required|exists:classrooms,id';
        }

        return $rules;
    }

    // Add these new validation rules for classroom requests
    protected function getClassroomRequestRules()
    {
        return [
            'classroom_id' => 'required|exists:classrooms,id',
            'section_id' => 'required|exists:sections,id',
            'start_time' => [
                'required',
                'date',
                'after:now',
            ],
            'end_time' => [
                'required',
                'date',
                'after:start_time',
            ],
        ];
    }

    public function mount()
    {
        $this->loadInitialData();
        
        // Add these debug lines
        $user = auth()->user();
        \Log::info('User role check:', [
            'userId' => $user->id,
            'name' => $user->name,
            'hasTechnicianRole' => $user->hasRole('technician'),
            'roles' => $user->roles->pluck('name')
        ]);
    }

    protected function loadInitialData()
    {
        $this->loadAssets();
        $this->loadTechnicians();
        $this->loadClassroomsAndSections();
        
        // Add debug logging
        \Log::info('Initial data loaded:', [
            'technicians' => $this->technicians->count(),
            'classrooms' => $this->classrooms->count(),
            'sections' => $this->sections->count()
        ]);
    }

    protected function loadAssets()
    {
        $this->assets = Asset::all();
    }

    protected function loadTechnicians()
    {
        try {
            if (!\Spatie\Permission\Models\Role::where('name', 'technician')->exists()) {
                \Log::warning('Technician role does not exist');
                $this->technicians = collect();
                return;
            }

            $this->technicians = User::role('technician')
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
        } catch (\Exception $e) {
            $this->handleError(
                $e,
                'loadTechnicians',
                'Error loading technicians. Please try again.'
            );
            $this->technicians = collect();
        }
    }

    protected function loadClassroomsAndSections()
    {
        try {
            // Load all classrooms
            $this->classrooms = Classroom::select('id', 'name')
                ->orderBy('name')
                ->get();

            // Load all sections
            $this->sections = Section::select('id', 'name')
                ->orderBy('name')
                ->get();

        } catch (\Exception $e) {
            \Log::error('Error loading classrooms and sections: ' . $e->getMessage());
            $this->classrooms = collect();
            $this->sections = collect();
        }
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

    public function selectTerminal($terminal)
    {
        \Log::info('Selecting terminal:', ['terminal' => $terminal]);
        $this->selectedTerminal = $terminal;
        $this->terminal = $terminal;
        $this->generateTicketContent();
    }

    public function selectClassroom($classroom)
    {
        \Log::info('Selecting classroom:', ['classroom' => $classroom]);
        $this->selectedClassroom = $classroom;
        $this->classroom_id = Classroom::where('name', $classroom)->value('id');
        
        if (!$this->classroom_id) {
            \Log::warning('Classroom not found:', ['classroom_name' => $classroom]);
        }
    }

    public function updatedClassroomId()
    {
        $this->checkTimeConflict();
    }

    public function updatedStartTime()
    {
        $this->checkTimeConflict();
    }

    public function updatedEndTime()
    {
        $this->checkTimeConflict();
    }

    public function updated($property)
    {
        \Log::info('Property updated:', [
            'property' => $property,
            'value' => $this->$property
        ]);
    }

    protected function checkTimeConflict()
    {
        $this->timeConflictExists = false;

        if (!$this->classroom_id || !$this->start_time || !$this->end_time) {
            return;
        }

        try {
            $start = Carbon::parse($this->start_time);
            $end = Carbon::parse($this->end_time);

            if ($end->lte($start)) {
                $this->addError('end_time', 'End time must be after start time');
                return;
            }

            if ($start->lt(now())) {
                $this->addError('start_time', 'Start time cannot be in the past');
                return;
            }

            $existingBookings = Ticket::where('classroom_id', $this->classroom_id)
                ->where('ticket_type', 'classroom_request')
                ->where('ticket_status', '!=', 'cancelled')
                ->where(function ($query) use ($start, $end) {
                    $query->where(function ($q) use ($start, $end) {
                        $q->whereBetween('start_time', [$start, $end])
                            ->orWhereBetween('end_time', [$start, $end])
                            ->orWhere(function ($q) use ($start, $end) {
                                $q->where('start_time', '<=', $start)
                                    ->where('end_time', '>=', $end);
                            });
                    });
                })
                ->exists();

            $this->timeConflictExists = $existingBookings;

        } catch (\Exception $e) {
            \Log::error('Error checking time conflict: ' . $e->getMessage());
            $this->addError('time_conflict', 'Error checking availability. Please try again.');
        }
    }

    protected function validateClassroomAndTerminal()
    {
        if (in_array($this->selectedType, ['hardware', 'internet'])) {
            if (!$this->classroom_id) {
                $this->addError('classroom', 'Please select a classroom');
                return false;
            }
            if (!$this->selectedTerminal) {
                $this->addError('terminal', 'Please select a terminal');
                return false;
            }
        }
        return true;
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
        $currentTime = now()->format('Y-m-d H:i');
        $terminalInfo = $this->selectedTerminal ? " at {$this->selectedTerminal}" : "";

        $templates = [
            'hardware' => [
                'mouse' => "Mouse{$terminalInfo} is not functioning properly (reported on {$currentTime}), with symptoms including unresponsive movement, cursor freezing, and non-working clicks.",
                'keyboard' => "Keyboard{$terminalInfo} has multiple non-responding keys and system recognition issues (reported on {$currentTime}).",
                'monitor' => "Monitor{$terminalInfo} is experiencing display issues including flickering and signal problems (reported on {$currentTime}).",
                'other' => "Hardware device{$terminalInfo} requires technical assessment due to malfunction (reported on {$currentTime})."
            ],
            'internet' => [
                'lan' => "Wired connection{$terminalInfo} is experiencing connectivity issues including slow speeds and connection drops (reported on {$currentTime}).",
                'wifi' => "Wi-Fi connection{$terminalInfo} has weak signal strength and frequent disconnections (reported on {$currentTime})."
            ],
            'application' => [
                'word' => "Microsoft Word application{$terminalInfo} is not launching properly and experiencing frequent crashes (reported on {$currentTime}).",
                'chrome' => "Google Chrome browser{$terminalInfo} is having performance issues including slow page loading and frequent crashes (reported on {$currentTime}).",
                'excel' => "Microsoft Excel{$terminalInfo} is experiencing calculation errors and file saving problems (reported on {$currentTime}).",
                'other_app' => "Application{$terminalInfo} is experiencing performance issues and requires technical support (reported on {$currentTime})."
            ],
            'asset_request' => [
                'default' => "Requesting new asset for daily operations to improve workflow efficiency (submitted on {$currentTime})."
            ],
            'general_inquiry' => [
                'default' => "General inquiry regarding technical support and system access (submitted on {$currentTime})."
            ],
            'classroom_request' => [
                'default' => "Requesting classroom booking for regular class session with standard computer laboratory setup (submitted on {$currentTime})."
            ]
        ];

        if (in_array($this->selectedType, ['asset_request', 'general_inquiry', 'classroom_request'])) {
            return $templates[$this->selectedType]['default'];
        }

        return $templates[$this->selectedType][$this->selectedSubType] ??
            "Issue with {$this->getReadableSubtype()}{$terminalInfo} reported on {$currentTime} requires technical support and assessment.";
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
        $this->selectedTerminal = null;
        $this->title = '';
        $this->description = '';
        $this->priority = 'medium';
        $this->asset_id = null;
        $this->assigned_to = null;
        $this->classroom_id = null;
        $this->section_id = null;
        $this->start_time = null;
        $this->end_time = null;
        $this->timeConflictExists = false;
        $this->showTicketForm = false;
        $this->resetErrorBag();

        // Reset the assets to show all assets
        $this->loadAssets();
    }

    // Add this method to handle manual assignment
    public function manualAssignTechnician($technicianId)
    {
        try {
            $technician = User::role('technician')->findOrFail($technicianId);
            
            $this->assigned_to = $technician->id;
            $this->assigned_technician = $technician->name;
            
            $this->dispatch('notify', [
                'message' => "Ticket manually assigned to {$technician->name}",
                'type' => 'success'
            ]);

        } catch (\Exception $e) {
            $this->handleError(
                $e,
                'manualAssignTechnician',
                'Error assigning technician. Please try again.'
            );
        }
    }

    // Add a getter method for the assigned technician name
    public function getAssignedTechnicianNameProperty()
    {
        if (!$this->assigned_to) {
            return 'Unassigned';
        }
        
        return optional($this->technicians->firstWhere('id', $this->assigned_to))->name ?? 'Unassigned';
    }

    // Improve ticket submission validation
    public function submitTicket()
    {
        $rules = $this->getRules();
        $this->validate($rules);

        try {
            DB::beginTransaction();

            // Add debug logging
            \Log::info('Ticket submission data:', [
                'type' => $this->selectedType,
                'subtype' => $this->selectedSubType,
                'classroom' => $this->selectedClassroom,
                'terminal' => $this->selectedTerminal,
                'classroom_id' => $this->classroom_id
            ]);

            $ticket = new Ticket();
            $ticket->ticket_number = $this->generateTicketNumber();
            $ticket->title = $this->title;
            $ticket->description = $this->description;
            $ticket->priority = $this->priority;
            $ticket->ticket_status = 'open';
            $ticket->type = $this->selectedType;
            $ticket->subtype = $this->selectedSubType;
            $ticket->classroom_id = $this->classroom_id;
            $ticket->terminal = $this->selectedTerminal;
            $ticket->assigned_to = $this->assigned_to;
            $ticket->created_by = auth()->id();

            // Add classroom request specific fields
            if ($this->selectedType === 'classroom_request') {
                $ticket->section_id = $this->section_id;
                $ticket->start_time = $this->start_time;
                $ticket->end_time = $this->end_time;
            }

            // Add logging for debugging
            \Log::info('Submitting ticket:', [
                'type' => $this->selectedType,
                'subtype' => $this->selectedSubType,
                'terminal' => $this->selectedTerminal,
                'classroom' => $this->classroom_id
            ]);

            $ticket->save();

            DB::commit();

            $this->reset();
            Notification::make()
                ->title('Success')
                ->body('Ticket submitted successfully!')
                ->success()
                ->send();
            
            $this->dispatch('close-ticket-modal');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error submitting ticket: ' . $e->getMessage());
            Notification::make()
                ->title('Error')
                ->body('Error submitting ticket: ' . $e->getMessage())
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
        // Start with base ticket query
        $baseQuery = Ticket::query()
            ->with(['assignedTo', 'creator', 'classroom', 'section'])
            ->latest();

        // Filter tickets based on user role
        if (auth()->user()->hasRole('professor')) {
            // Professors can only see tickets they created
            $baseQuery->where('created_by', auth()->id());
        } elseif (auth()->user()->hasRole('technician')) {
            // Technicians can see tickets assigned to them or unassigned tickets
            $baseQuery->where(function ($query) {
                $query->where('assigned_to', auth()->id())
                    ->orWhereNull('assigned_to');
            });
        }
        // Admins/supervisors can see all tickets (no additional filter needed)

        return $table
            ->query($baseQuery)
            ->columns([
                TextColumn::make('ticket_number')
                    ->label('Ticket No.')
                    ->searchable()
                    ->sortable()
                    ->size('sm')
                    ->color('primary'),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->size('sm')
                    ->wrap(),

                TextColumn::make('priority')
                    ->badge()
                    ->size('sm')
                    ->color(fn (string $state): string => match ($state) {
                        'high' => 'danger',
                        'medium' => 'warning',
                        'low' => 'success',
                        default => 'info'
                    }),

                TextColumn::make('ticket_status')
                    ->label('Status')
                    ->badge()
                    ->size('sm')
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'info',
                        'in_progress' => 'warning',
                        'resolved' => 'success',
                        'closed' => 'success',
                        'archived' => 'gray',
                        default => 'info'
                    }),

                TextColumn::make('assignedTo.name')
                    ->label('Assigned To')
                    ->formatStateUsing(fn ($record) => $record->assigned_to ? $record->assignedTo?->name : 'Unassigned')
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('assignedTo', fn ($q) =>
                            $q->where('name', 'like', "%{$search}%")
                        );
                    })
                    ->sortable()
                    ->size('sm'),

                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->visible(fn () => !auth()->user()->hasRole('professor')),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->size('sm')
            ])
            ->striped()
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100])
            ->filters([
                SelectFilter::make('priority')
                    ->options([
                        'high' => 'High',
                        'medium' => 'Medium',
                        'low' => 'Low',
                    ])
                    ->placeholder('All Priorities')
                    ->label('Priority')
                    ->indicator('Priority'),

                SelectFilter::make('ticket_status')
                    ->options([
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'resolved' => 'Resolved',
                        'closed' => 'Closed',
                        'archived' => 'Archived',
                    ])
                    ->placeholder('All Statuses')
                    ->label('Status')
                    ->indicator('Status'),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Created From'),
                        DatePicker::make('created_until')
                            ->label('Created Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['created_from'] ?? null) {
                            $indicators[] = Indicator::make('Created from ' . Carbon::parse($data['created_from'])->toFormattedDateString())
                                ->removeField('created_from');
                        }

                        if ($data['created_until'] ?? null) {
                            $indicators[] = Indicator::make('Created until ' . Carbon::parse($data['created_until'])->toFormattedDateString())
                                ->removeField('created_until');
                        }

                        return $indicators;
                    }),

                SelectFilter::make('assigned_to')
                    ->relationship('assignedTo', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('All Technicians')
                    ->label('Assigned To')
                    ->indicator('Assigned'),

                SelectFilter::make('type')
                    ->options([
                        'hardware' => 'Hardware',
                        'internet' => 'Internet',
                        'application' => 'Application',
                        'asset_request' => 'Asset Request',
                        'classroom_request' => 'Classroom Request',
                        'general_inquiry' => 'General Inquiry',
                    ])
                    ->placeholder('All Categories')
                    ->label('Category')
                    ->indicator('Category'),
            ])
            ->filtersFormColumns(3)
            ->actions([
                ActionGroup::make([
                    Action::make('view')
                        ->icon('heroicon-m-eye')
                        ->color('info')
                        ->modalContent(fn (Ticket $record) => view(
                            'tickets.view',
                            [
                                'ticket' => $record->load(['classroom', 'section', 'assignedTo', 'creator']),
                                'classrooms' => Classroom::all(),
                                'sections' => Section::all(),
                            ]
                        ))
                        ->modalWidth('md')
                        ->modalSubmitAction(false)
                        ->modalCancelAction(fn ($action) => $action->label('Close')),

                    Action::make('edit')
                        ->icon('heroicon-m-pencil-square')
                        ->color('warning')
                        ->modalWidth('md')
                        ->form([
                            TextInput::make('title')
                                ->required()
                                ->maxLength(255)
                                ->readOnly(),
                            Textarea::make('description')
                                ->required()
                                ->rows(4),
                            Select::make('asset_id')
                                ->label('Asset')
                                ->options(fn () => Asset::pluck('name', 'id'))
                                ->nullable()
                                ->visible(fn (Ticket $record) => $record->type === 'hardware')
                                ->default(null) // Add default value
                                ->disabled(),
                            Select::make('priority')
                                ->options([
                                    'low' => 'Low',
                                    'medium' => 'Medium',
                                    'high' => 'High',
                                ])
                                ->required()
                                ->disabled(),
                            Select::make('ticket_status')
                                ->options([
                                    'open' => 'Open',
                                    'in_progress' => 'In Progress',
                                    'closed' => 'Closed',
                                    'archived' => 'Archived',
                                ])
                                ->required()
                                ->disabled(),
                            Select::make('assigned_to')
                                ->label('Assigned To')
                                ->options(fn () => User::role('technician')->pluck('name', 'id'))
                                ->nullable()
                                ->placeholder('-- Unassigned --')
                                ->disabled(),
                            Select::make('classroom_id')
                                ->label('Classroom')
                                ->options(fn () => Classroom::pluck('name', 'id'))
                                ->nullable()
                                ->visible(fn (Ticket $record) => $record->type === 'classroom_request')
                                ->default(null) // Add default value
                                ->disabled(),

                            Select::make('section_id')
                                ->label('Section')
                                ->options(fn () => Section::pluck('name', 'id'))
                                ->nullable()
                                ->visible(fn (Ticket $record) => $record->type === 'classroom_request')
                                ->default(null) // Add default value
                                ->disabled(),

                            DatePicker::make('start_time')
                                ->label('Start Time')
                                ->nullable()
                                ->visible(fn (Ticket $record) => $record->type === 'classroom_request')
                                ->default(null) // Add default value
                                ->disabled(),

                            DatePicker::make('end_time')
                                ->label('End Time')
                                ->nullable()
                                ->visible(fn (Ticket $record) => $record->type === 'classroom_request')
                                ->default(null) // Add default value
                                ->disabled(),
                        ])
                        ->fillForm(function (Ticket $record): array {
                            return [
                                'title' => $record->title,
                                'description' => $record->description,
                                'priority' => $record->priority,
                                'ticket_status' => $record->ticket_status,
                                'assigned_to' => $record->assigned_to,
                                'classroom_id' => $record->classroom_id ?? null,
                                'section_id' => $record->section_id ?? null,
                                'start_time' => $record->start_time ?? null,
                                'end_time' => $record->end_time ?? null,
                            ];
                        })
                        ->action(function (Ticket $record, array $data): void {
                            // Filter out null values for non-classroom requests
                            if ($record->type !== 'classroom_request') {
                                unset($data['classroom_id'], $data['section_id'], $data['start_time'], $data['end_time']);
                            }

                            $record->update(array_filter($data, function ($value) {
                                return !is_null($value);
                            }));

                            Notification::make()
                                ->title('Ticket Updated Successfully')
                                ->success()
                                ->send();
                        }),

                    Action::make('assign')
                        ->icon('heroicon-m-user-plus')
                        ->color('success')
                        ->modalWidth('md')
                        ->label(fn (Ticket $record) =>
                            is_null($record->assigned_to) ? 'Assign' : 'Reassign'
                        )
                        // Modify the visibility condition
                        ->visible(function (Ticket $record) {
                            $userIsTechnician = auth()->user()->hasRole('technician');
                            $ticketIsAssignable = !in_array($record->ticket_status, ['closed', 'archived']);
                            $canAssignTicket = is_null($record->assigned_to) || $record->assigned_to === auth()->id();
                            
                            // Add debug logging
                            \Log::info('Assign button visibility check:', [
                                'userIsTechnician' => $userIsTechnician,
                                'ticketIsAssignable' => $ticketIsAssignable,
                                'canAssignTicket' => $canAssignTicket,
                                'ticketStatus' => $record->ticket_status,
                                'assignedTo' => $record->assigned_to,
                                'authId' => auth()->id()
                            ]);
                            
                            return $userIsTechnician && $ticketIsAssignable && $canAssignTicket;
                        })
                        ->modalContent(fn (Ticket $record) => view(
                            'tickets.assign',
                            ['ticket' => $record]
                        ))
                        ->form([
                            Select::make('assign_type')
                                ->label('Assignment Type')
                                ->options([
                                    'self' => 'Assign to myself'
                                ])
                                ->default('self')
                                ->required()
                        ])
                        ->action(function (Ticket $record, array $data): void {
                            $record->update([
                                'assigned_to' => auth()->id(),
                                'ticket_status' => 'in_progress'
                            ]);

                            Notification::make()
                                ->title('Ticket assigned successfully')
                                ->success()
                                ->send();
                        })
                ])
                    ->tooltip('Actions')
                    ->icon('heroicon-m-ellipsis-vertical')
            ])
            ->bulkActions([
                BulkAction::make('archive')
                    ->label('Archive Selected')
                    ->icon('heroicon-m-archive-box')
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

    protected function handleError(\Exception $e, string $context, string $userMessage = null)
    {
        \Log::error("Error in {$context}: " . $e->getMessage());
        
        if ($userMessage) {
            $this->dispatch('notify', [
                'message' => $userMessage,
                'type' => 'error'
            ]);
        }
    }

    public function render()
    {
        return view('livewire.ticketing');
    }
}