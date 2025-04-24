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
use Illuminate\Support\Facades\Log;

class Ticketing extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected const WORKING_HOURS = [
        'start' => '07:00',
        'end' => '21:00'
    ];

    // Form Fields
    public $selectedType;
    public $selectedSubType;
    public $selectedClassroom = null;
    public $selectedTerminal = null;
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
    public $terminal_number = null;

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
        'terminal_number' => 'required_if:selectedType,internet',
        'classroom_id' => 'required_if:selectedType,internet'
    ];

    public function getRules()
    {
        $rules = $this->rules;

        if (in_array($this->selectedType, ['hardware', 'internet'])) {
            $rules['classroom_id'] = 'required|exists:classrooms,id';
            $rules['selectedTerminal'] = 'required|string';
            $rules['terminal_number'] = 'required|string';
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
                function ($attribute, $value, $fail) {
                    $start = Carbon::parse($value);
                    $now = Carbon::now();

                    // Allow same day bookings but prevent past times
                    if ($start->format('Y-m-d') === $now->format('Y-m-d')) {
                        if ($start->format('H:i') < $now->format('H:i')) {
                            $fail('Start time cannot be in the past.');
                        }
                    } elseif ($start->startOfDay()->lt($now->startOfDay())) {
                        $fail('Start date cannot be in the past.');
                    }
                }
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
        $now = Carbon::now();
        $this->start_time = $now->format('Y-m-d\TH:i');
        $this->end_time = $now->addHour()->format('Y-m-d\TH:i');

        $this->loadInitialData();

        // Log user info
        $user = Auth::user();
        if ($user) {
            Log::info('User info:', [
                'userId' => $user->id,
                'name' => $user->name
            ]);
        }
    }

    protected function loadInitialData()
    {
        $this->loadAssets();
        $this->loadTechnicians();
        $this->loadClassroomsAndSections();

        // Add debug logging
        $techCount = 0;
        $classCount = 0;
        $sectCount = 0;

        if (is_object($this->technicians) && method_exists($this->technicians, 'count')) {
            $techCount = $this->technicians->count();
        } elseif (is_array($this->technicians)) {
            $techCount = count($this->technicians);
        }

        if (is_object($this->classrooms) && method_exists($this->classrooms, 'count')) {
            $classCount = $this->classrooms->count();
        } elseif (is_array($this->classrooms)) {
            $classCount = count($this->classrooms);
        }

        if (is_object($this->sections) && method_exists($this->sections, 'count')) {
            $sectCount = $this->sections->count();
        } elseif (is_array($this->sections)) {
            $sectCount = count($this->sections);
        }

        Log::info('Initial data loaded:', [
            'technicians' => $techCount,
            'classrooms' => $classCount,
            'sections' => $sectCount
        ]);
    }

    protected function loadAssets()
    {
        $this->assets = Asset::all();
    }

    protected function loadTechnicians()
    {
        $this->technicians = User::role('technician')->where('id', '!=', Auth::id())->get();
    }

    protected function loadClassroomsAndSections()
    {
        $this->classrooms = Classroom::select('id', 'name')->get();
        $this->sections = Section::select('id', 'name')->get();
    }

    public function selectIssueType($type)
    {
        $this->selectedType = $type;
        $this->selectedSubType = null;
        $this->showTicketForm = false;
        $this->asset_id = null; // Reset asset_id when changing issue type
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
        try {
            Log::info('Selecting terminal:', ['terminal' => $terminal]);
            
            // Add validation to ensure terminal is in a valid format
            if (!is_string($terminal) || trim($terminal) === '') {
                throw new \Exception('Invalid terminal value');
            }
            
            $this->selectedTerminal = "T-{$terminal}";
            $this->terminal_number = $this->selectedTerminal;
            $this->generateTicketContent();
        } catch (\Exception $e) {
            $this->handleError($e, 'selectTerminal', 'Error selecting terminal');
        }
    }

    public function selectClassroom($classroom)
    {
        try {
            Log::info('Selecting classroom:', ['classroom' => $classroom]);
            
            if (!is_string($classroom) || trim($classroom) === '') {
                throw new \Exception('Invalid classroom value');
            }
            
            $this->selectedClassroom = $classroom;
            $this->classroom_id = Classroom::where('name', $classroom)->value('id');

            if (!$this->classroom_id) {
                Log::warning('Classroom not found:', ['classroom_name' => $classroom]);
                throw new \Exception('Selected classroom not found');
            }
        } catch (\Exception $e) {
            $this->handleError($e, 'selectClassroom', 'Error selecting classroom');
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
        Log::info('Property updated:', [
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
            $now = Carbon::now();

            // Check if end time is before or equal to start time
            if ($end->lte($start)) {
                $this->addError('end_time', 'End time must be after start time');
                return;
            }

            // Only validate time if it's today
            if ($start->format('Y-m-d') === $now->format('Y-m-d')) {
                if ($start->format('H:i') < $now->format('H:i')) {
                    $this->addError('start_time', 'Start time cannot be in the past');
                    return;
                }
            } elseif ($start->startOfDay()->lt($now->startOfDay())) {
                $this->addError('start_time', 'Start date cannot be in the past');
                return;
            }

            // Improved time conflict check query
            $existingBookings = Ticket::where('classroom_id', $this->classroom_id)
                ->where('type', 'classroom_request')
                ->where('ticket_status', '!=', 'cancelled')
                ->where(function ($query) use ($start, $end) {
                    // Start time falls within existing booking
                    $query->where(function ($q) use ($start, $end) {
                        $q->where('start_time', '<=', $start)
                          ->where('end_time', '>', $start);
                    })
                    // End time falls within existing booking
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('start_time', '<', $end)
                          ->where('end_time', '>=', $end);
                    })
                    // Booking completely contains the existing booking
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('start_time', '>=', $start)
                          ->where('end_time', '<=', $end);
                    });
                })
                ->get();

            if ($existingBookings->isNotEmpty()) {
                $this->timeConflictExists = true;
                // Format the conflicting bookings for display
                $conflictingTimes = $existingBookings->map(function ($booking) {
                    return [
                        'start' => Carbon::parse($booking->start_time)->format('h:i A'),
                        'end' => Carbon::parse($booking->end_time)->format('h:i A'),
                        'section' => optional($booking->section)->name ?? 'Unknown Section'
                    ];
                });

                $this->addError('time_conflict', 'Time slot conflicts with existing bookings:');
                foreach ($conflictingTimes as $conflict) {
                    $this->addError(
                        'time_conflict_details',
                        "{$conflict['section']}: {$conflict['start']} - {$conflict['end']}"
                    );
                }
            } else {
                $this->timeConflictExists = false;
                $this->resetErrorBag(['time_conflict', 'time_conflict_details']);
            }
        } catch (\Exception $e) {
            Log::error('Error checking time conflict: ' . $e->getMessage());
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
        $now = Carbon::now();
        
        $this->reset([
            'selectedType',
            'selectedSubType',
            'selectedTerminal',
            'selectedClassroom',
            'terminal_number',
            'title',
            'description',
            'priority',
            'asset_id',
            'assigned_to',
            'classroom_id',
            'section_id',
            'start_time',
            'end_time',
            'timeConflictExists',
            'showTicketForm'
        ]);

        // Set default dates after reset
        $this->start_time = $now->format('Y-m-d\TH:i');
        $this->end_time = $now->copy()->addHour()->format('Y-m-d\TH:i');

        $this->resetErrorBag();

        // Refresh local collections
        $this->loadAssets();
        $this->loadClassroomsAndSections();

        // Debug log for form reset
        Log::info('Form reset, asset_id is now: ' . $this->asset_id);
    }

    // Add this method to handle manual assignment
    public function manualAssignTechnician($technicianId)
    {
        try {
            $technician = User::findOrFail($technicianId);

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

        $technician = null;

        // Find the technician in the list
        foreach ($this->technicians as $tech) {
            if ($tech['id'] == $this->assigned_to) {
                $technician = $tech;
                break;
            }
        }

        return $technician ? $technician['name'] : 'Unassigned';
    }

    // Improve ticket submission validation and error handling
    public function submitTicket()
    {
        try {
            // First validate the input before starting transaction
            $this->validate($this->getRules());
            
            // Check working hours for classroom and asset requests
            if (in_array($this->selectedType, ['classroom_request', 'asset_request'])) {
                $this->validateWorkingHours(now());
            }
            
            // Validate classroom and terminal if needed
            if (in_array($this->selectedType, ['hardware', 'internet'])) {
                if (!$this->validateClassroomAndTerminal()) {
                    return;
                }
            }
            
            // Start transaction after all validation passes
            DB::beginTransaction();
            
            // Generate ticket number
            $ticketNumber = $this->generateTicketNumber();
            
            // Create ticket
            $ticket = Ticket::create([
                'ticket_number' => $ticketNumber,
                'title' => $this->title,
                'description' => $this->description,
                'priority' => $this->priority,
                'ticket_status' => 'open',
                'type' => $this->selectedType,
                'subtype' => $this->selectedSubType,
                'ticket_type' => in_array($this->selectedType, ['classroom_request', 'asset_request', 'general_inquiry']) 
                    ? 'request' 
                    : 'incident',
                'classroom_id' => $this->classroom_id,
                'terminal_number' => $this->selectedTerminal,
                'assigned_to' => $this->assigned_to,
                'created_by' => Auth::id(),
                'asset_id' => $this->asset_id,
                'section_id' => $this->section_id,
            ]);

            // Handle specific request types
            if ($this->selectedType === 'classroom_request') {
                $this->validateClassroomRequest($ticket);
            } else if ($this->selectedType === 'asset_request') {
                $this->validateAssetRequest($ticket);
            }

            $ticket->save();
            
            DB::commit();

            // Reset form and show success notification
            $this->resetForm();
            $this->dispatch('close-ticket-modal');

            Notification::make()
                ->title('Ticket Created')
                ->body("Ticket #{$ticketNumber} has been created successfully")
                ->success()
                ->send();
                
        } catch (\Exception $e) {
            // Only roll back if transaction was started
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            
            Log::error('Ticket creation failed: ' . $e->getMessage());
            
            Notification::make()
                ->title('Error')
                ->body($e->getMessage()) // Fixed method name
                ->danger()
                ->send();
        }
    }

    protected function validateWorkingHours($currentTime)
    {
        $timeNow = Carbon::parse($currentTime->format('H:i'));
        $workingStart = Carbon::parse(self::WORKING_HOURS['start']);
        $workingEnd = Carbon::parse(self::WORKING_HOURS['end']);

        if ($timeNow->lt($workingStart) || $timeNow->gt($workingEnd)) {
            throw new \Exception('Requests can only be submitted between 7:00 AM and 9:00 PM');
        }
    }

    protected function validateClassroomRequest($ticket)
    {
        try {
            // Validate time slot
            if ($this->timeConflictExists) {
                throw new \Exception('Time slot is already booked');
            }

            // Parse times
            $start = Carbon::parse($this->start_time);
            $end = Carbon::parse($this->end_time);
            $now = Carbon::now();

            // Validate working hours for booking time
            $startTime = Carbon::parse($start->format('H:i'));
            $endTime = Carbon::parse($end->format('H:i'));
            $workingStart = Carbon::parse(self::WORKING_HOURS['start']);
            $workingEnd = Carbon::parse(self::WORKING_HOURS['end']);

            if ($startTime->lt($workingStart) || $endTime->gt($workingEnd)) {
                throw new \Exception('Classroom bookings are only allowed between 7:00 AM and 9:00 PM');
            }

            // Validate start time
            if ($start->lt($now)) {
                throw new \Exception('Start time cannot be in the past');
            }

            // Validate end time 
            if ($end->lte($start)) {
                throw new \Exception('End time must be after start time');
            }

            // Maximum booking duration (e.g. 8 hours)
            $maxDuration = 8;
            if ($start->diffInHours($end) > $maxDuration) {
                throw new \Exception("Booking cannot exceed {$maxDuration} hours");
            }

            // Update ticket with validated times and section
            $ticket->update([
                'start_time' => $start,
                'end_time' => $end,
                'section_id' => $this->section_id
            ]);

        } catch (\Exception $e) {
            Log::error('Classroom request validation failed: ' . $e->getMessage());
            throw $e; // Re-throw to be caught by parent try-catch
        }
    }

    // Add a function to validate asset requests
    protected function validateAssetRequest($ticket)
    {
        if (!$this->asset_id) {
            throw new \Exception('Asset is required for asset requests');
        }

        // Validate working hours for asset request submission
        $now = Carbon::now();
        $currentTime = Carbon::parse($now->format('H:i'));
        $workingStart = Carbon::parse(self::WORKING_HOURS['start']);
        $workingEnd = Carbon::parse(self::WORKING_HOURS['end']);

        if ($currentTime->lt($workingStart) || $currentTime->gt($workingEnd)) {
            throw new \Exception('Asset requests can only be submitted between 7:00 AM and 9:00 PM');
        }

        Log::info('Validated asset request:', ['asset_id' => $this->asset_id]);
    }

    /**
     * Generate a unique ticket number in the format PREFIX-SUBPREFIX-RANDOM
     */
    protected function generateTicketNumber()
    {
        // Determine ticket type based on the selected type
        $ticketType = match ($this->selectedType) {
            'classroom_request', 'asset_request', 'general_inquiry' => 'request',
            'hardware', 'internet', 'application' => 'incident',
            default => 'incident'
        };
        
        // Set prefix based on ticket type
        $prefix = $ticketType === 'request' ? 'REQ' : 'INC';

        // Add specific sub-prefix based on ticket type
        $subPrefix = match ($this->selectedType) {
            'classroom_request' => 'CLS',
            'asset_request' => 'AST',
            'general_inquiry' => 'INQ',
            'hardware' => 'HW',
            'internet' => 'NET',
            'application' => 'APP',
            default => ''
        };

        // Generate random part
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $randomPart = '';
        
        do {
            $randomPart = '';
            for ($i = 0; $i < 8; $i++) {
                $randomPart .= $characters[random_int(0, strlen($characters) - 1)];
            }
            // Format: PREFIX-SUBPREFIX-RANDOM
            // Example: REQ-CLS-12AB34CD or INC-HW-56EF78GH
            $ticketNumber = "{$prefix}-{$subPrefix}-{$randomPart}";
        } while (Ticket::where('ticket_number', $ticketNumber)->exists());

        return $ticketNumber;
    }

    protected function canManageTicket(Ticket $ticket): bool 
    {
        $user = Auth::user();
        
        // Admin can manage all tickets
        if ($user && $user->hasRole('admin')) {
            return true;
        }
        
        // Users can only manage their own tickets or tickets assigned to them
        return $user && ($ticket->created_by === $user->id || $ticket->assigned_to === $user->id);
    }

    // Update the assign action in the table configuration
    public function table(Table $table): Table
    {
        // Start with base ticket query
        $baseQuery = Ticket::query()
            ->with(['assignedTo', 'creator', 'classroom', 'section'])
            ->latest();

        // Filter tickets based on user
        $user = Auth::user();

        // Set basic permissions (can be extended by checking user permissions in the database)
        $userId = $user ? $user->id : null;
        $isAdmin = $user && $user->hasRole('admin'); // More accurate role check

        if ($userId && !$isAdmin) {
            // Limit normal users to see only their tickets
            $baseQuery->where(function ($query) use ($userId) {
                $query->where('created_by', $userId)
                    ->orWhere('assigned_to', $userId)
                    ->orWhereNull('assigned_to');
            });
        }

        return $table
            ->query($baseQuery)
            ->columns([
                TextColumn::make('ticket_number')
                    ->label('Ticket No.')
                    ->searchable()
                    ->sortable()
                    ->size('sm')
                    ->color('primary'),

                TextColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'hardware' => 'Hardware',
                        'internet' => 'Internet',
                        'application' => 'Application',
                        'asset_request' => 'Asset Request',
                        'classroom_request' => 'Classroom Request', 
                        'general_inquiry' => 'General Inquiry',
                        default => ucfirst($state)
                    })
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'hardware' => 'danger',
                        'internet' => 'warning',
                        'application' => 'info',
                        'asset_request' => 'success',
                        'classroom_request' => 'primary',
                        'general_inquiry' => 'gray',
                        default => 'secondary'
                    })
                    ->size('sm'),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->size('sm')
                    ->wrap(),

                TextColumn::make('priority')
                    ->badge()
                    ->size('sm')
                    ->color(fn(string $state): string => match ($state) {
                        'high' => 'danger',
                        'medium' => 'warning',
                        'low' => 'success',
                        default => 'info'
                    }),

                TextColumn::make('ticket_status')
                    ->label('Status')
                    ->badge()
                    ->size('sm')
                    ->color(fn(string $state): string => match ($state) {
                        'open' => 'info',
                        'in_progress' => 'warning',
                        'resolved' => 'success',
                        'closed' => 'success',
                        default => 'info'
                    }),

                TextColumn::make('assignedTo.name')
                    ->label('Assigned To')
                    ->formatStateUsing(fn($record) => $record->assigned_to ? $record->assignedTo?->name : 'Unassigned')
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas(
                            'assignedTo',
                            fn($q) =>
                            $q->where('name', 'like', "%{$search}%")
                        );
                    })
                    ->sortable()
                    ->size('sm'),

                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->size('sm'),
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
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
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
                // Separate assign action
                Action::make('assign')
                    ->icon('heroicon-m-user-plus')
                    ->color('success')
                    ->modalWidth('md')
                    ->label(
                        fn(Ticket $record) =>
                        is_null($record->assigned_to) ? 'Assign' : 'Reassign'
                    )
                    ->visible(function (Ticket $record) use ($userId) {
                        $ticketIsAssignable = !in_array($record->ticket_status, ['closed', 'archived']);
                        $canAssignTicket = is_null($record->assigned_to) || $record->assigned_to === $userId;
                        return $ticketIsAssignable && $canAssignTicket;
                    })
                    ->modalContent(fn(Ticket $record) => view(
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
                            'assigned_to' => Auth::id(),
                            'ticket_status' => 'in_progress'
                        ]);

                        Notification::make()
                            ->title('Ticket assigned successfully')
                            ->success()
                            ->send();
                    }),

                // Action group for other actions
                ActionGroup::make([
                    Action::make('view')
                        ->icon('heroicon-m-eye')
                        ->color('info')
                        ->modalContent(fn(Ticket $record) => view(
                            'tickets.view',
                            [
                                'ticket' => $record->load([
                                    'classroom', 
                                    'section', 
                                    'assignedTo', 
                                    'creator', 
                                    'asset' => function($query) {
                                        $query->select('id', 'name', 'serial_number'); // Remove asset_tag from selection
                                    }
                                ]),
                                'classrooms' => Classroom::all(),
                                'sections' => Section::all(),
                            ]
                        ))
                        ->modalWidth('md')
                        ->modalSubmitAction(false)
                        ->modalCancelAction(fn($action) => $action->label('Close')),

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
                                ->rows(4)
                                ->readOnly(),
                            Select::make('asset_id')
                                ->label('Asset')
                                ->options(fn() => Asset::query()
                                    ->get()
                                    ->mapWithKeys(function ($asset) {
                                        $label = $asset->name;
                                        if ($asset->serial_number) {
                                            $label .= " (SN: {$asset->serial_number})";
                                        }
                                        return [$asset->id => $label];
                                    }))
                                ->nullable()
                                ->visible(fn(Ticket $record) => in_array($record->type, ['hardware', 'asset_request']))
                                ->default(fn(Ticket $record) => $record->asset_id)
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
                                ->options(fn() => User::role('technician')->pluck('name', 'id'))
                                ->nullable()
                                ->placeholder('-- Unassigned --')
                                ->disabled(),
                            Select::make('classroom_id')
                                ->label('Classroom')
                                ->options(fn() => Classroom::pluck('name', 'id'))
                                ->nullable()
                                ->visible(fn(Ticket $record) => $record->type === 'classroom_request')
                                ->default(null) // Add default value
                                ->disabled(),

                            Select::make('section_id')
                                ->label('Section')
                                ->options(fn() => Section::pluck('name', 'id'))
                                ->nullable()
                                ->visible(fn(Ticket $record) => $record->type === 'classroom_request')
                                ->default(null) // Add default value
                                ->disabled(),

                            DatePicker::make('start_time')
                                ->label('Start Time')
                                ->nullable()
                                ->visible(fn(Ticket $record) => $record->type === 'classroom_request')
                                ->default(null) // Add default value
                                ->disabled(),

                            DatePicker::make('end_time')
                                ->label('End Time')
                                ->nullable()
                                ->visible(fn(Ticket $record) => $record->type === 'classroom_request')
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
                                'asset_id' => $record->asset_id ?? null, // Add this line
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
                        })
                        ->visible(fn (Ticket $record) => $this->canManageTicket($record)),
                        
                    Action::make('delete')
                        ->icon('heroicon-m-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Delete Ticket')
                        ->modalDescription('Are you sure you want to delete this ticket? This cannot be undone.')
                        ->modalSubmitActionLabel('Yes, delete')
                        ->action(function (Ticket $record): void {
                            try {
                                DB::beginTransaction();
                                
                                $record->delete();
                                
                                DB::commit();
                                
                                Notification::make()
                                    ->title('Ticket Deleted')
                                    ->success()
                                    ->send();
                                    
                            } catch (\Exception $e) {
                                DB::rollBack();
                                Log::error('Delete failed: ' . $e->getMessage());
                                
                                Notification::make()
                                    ->title('Delete Failed')
                                    ->body('Unable to delete the ticket. Please try again.')
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->visible(fn (Ticket $record) => $this->canManageTicket($record))
                ])
                    ->tooltip('Actions')
                    ->icon('heroicon-m-ellipsis-vertical')
            ])
            ->defaultSort('created_at', 'desc');
    }

    protected function handleError(\Exception $e, string $context, string $userMessage = null)
    {
        Log::error("Error in {$context}: " . $e->getMessage());

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
