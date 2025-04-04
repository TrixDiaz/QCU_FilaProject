<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Asset;
use App\Models\User;
use App\Models\Classroom;
use App\Models\Subject; // Using Subject instead of Schedule for classroom schedules
use App\Models\AssetGroup; // Added for classroom assets
use Livewire\WithPagination;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Ticket;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class ReportBuilder extends Component implements HasForms
{
    use WithPagination;
    use InteractsWithForms;

    public $selectedModule = 'inventory';
    public $selectedFields = [];
    public $filters = [
        'date_from' => null,
        'date_to' => null,
        'categories' => [],
        'brands' => [],
        'classrooms' => [],
        'school_years' => [],
        'semesters' => [],
        'professors' => [], 
        'terminals' => [],
        'ticket_statuses' => [], // Added for ticket reporting
        'ticket_types' => [], // Added for ticket reporting
        'ticket_priorities' => [], // Added for ticket reporting

    ];
    public $moduleFields = [
        'inventory' => ['category', 'brand', 'name', 'asset_code', 'serial_number', 'expiry_date', 'status'],
        'asset_count' => ['name', 'total_count', 'remarks'],
        'users' => ['name', 'email', 'approval_status', 'created_at', 'updated_at'],
        'classroom_assets' => ['classroom_id', 'asset_group_id', 'name', 'code', 'status', 'quantity'],
        'classroom_schedule' => ['classroom_id', 'subject_name', 'subject_code', 'professor', 'day', 'start_time', 'end_time', 'school_year', 'semester'],
        'attendance' => ['subject_id', 'terminal_number', 'student_full_name', 'student_email', 'student_number', 'peripherals', 'remarks', 'created_at'],
        'tickets' => ['ticket_number', 'title', 'description', 'ticket_type', 'ticket_status', 'priority', 'created_by', 'assigned_to', 'start_time', 'end_time', 'classroom_id', 'asset_id'], // Added ticket fields
    ];
    public $availableFields = [];
    public $reportTitle = '';
    public $isGenerating = false;
    public $reportGenerated = false;
    public $data;
    public $showReport = false;
    protected $paginationTheme = 'bootstrap';

    private $baseColumns = ['id'];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('reportTitle')
                    ->label('Report Title')
                    ->placeholder('Enter report title')
                    ->live(),

                Select::make('selectedModule')
                    ->label('Select Module')
                    ->options([
                        'inventory' => 'Inventory',
                        'asset_count' => 'Asset Count',
                        'users' => 'Users',
                        'classroom_assets' => 'Classroom Assets',
                        'classroom_schedule' => 'Classroom Schedule',
                        'attendance' => 'Attendance Records',
                        'tickets' => 'Tickets',
                    ])
                    ->live(),

                Fieldset::make('Date Range')
                    ->schema([
                        DatePicker::make('filters.date_from')->label('Date From')->placeholder('Select start date')->live(),
                        DatePicker::make('filters.date_to')->label('Date To')->placeholder('Select end date')->live(),
                    ])->columns(2),

                Fieldset::make('Additional Filters')
                    ->schema([
                        CheckboxList::make('filters.categories')
                            ->label('Categories')
                            ->options(fn () => $this->selectedModule === 'inventory' ? Category::pluck('name', 'id')->toArray() : [])
                            ->columns(3)
                            ->visible(fn () => $this->selectedModule === 'inventory')
                            ->live(),

                        CheckboxList::make('filters.brands')
                            ->label('Brands')
                            ->options(fn () => $this->selectedModule === 'inventory' ? Brand::pluck('name', 'id')->toArray() : [])
                            ->columns(3)
                            ->visible(fn () => $this->selectedModule === 'inventory')
                            ->live(),
                            
                        CheckboxList::make('filters.classrooms')
                            ->label('Classrooms')
                            ->options(fn () => in_array($this->selectedModule, ['classroom_assets', 'classroom_schedule']) ? Classroom::pluck('name', 'id')->toArray() : [])
                            ->columns(3)
                            ->visible(fn () => in_array($this->selectedModule, ['classroom_assets', 'classroom_schedule']))
                            ->live(),
                            
                            CheckboxList::make('filters.school_years')
                            ->label('School Years')
                            ->options(fn () => in_array($this->selectedModule, ['classroom_schedule', 'attendance']) ? 
                                Subject::distinct()->pluck('school_year', 'school_year')->toArray() : [])
                            ->columns(3)
                            ->visible(fn () => in_array($this->selectedModule, ['classroom_schedule', 'attendance']))
                            ->live(),
                            
                            CheckboxList::make('filters.semesters')
                            ->label('Semesters')
                            ->options(fn () => in_array($this->selectedModule, ['inventory', 'classroom_schedule', 'attendance']) ? 
                                Subject::distinct()->pluck('semester', 'semester')->toArray() : [])
                            ->columns(3)
                            ->visible(fn () => in_array($this->selectedModule, ['inventory', 'classroom_schedule', 'attendance']))
                            ->live(),

                            CheckboxList::make('filters.professors')
                            ->label('Professors')
                            ->options(fn () => $this->selectedModule === 'attendance' ? 
                                User::where('name', 'like', '%professor%')->pluck('name', 'id')->toArray() : [])
                            ->columns(3)
                            ->visible(fn () => $this->selectedModule === 'attendance')
                            ->live(),

                            CheckboxList::make('filters.ticket_statuses')
                    ->label('Ticket Statuses')
                    ->options(fn () => $this->selectedModule === 'tickets' ? 
                        ['open' => 'Open', 'pending' => 'Pending', 'in progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed'] : [])
                    ->columns(3)
                    ->visible(fn () => $this->selectedModule === 'tickets')
                    ->live(),

                CheckboxList::make('filters.ticket_types')
                    ->label('Ticket Types')
                    ->options(fn () => $this->selectedModule === 'tickets' ? 
                        ['request' => 'Request', 'classroom' => 'Classroom'] : [])
                    ->columns(3)
                    ->visible(fn () => $this->selectedModule === 'tickets')
                    ->live(),

                CheckboxList::make('filters.ticket_priorities')
                    ->label('Ticket Priorities')
                    ->options(fn () => $this->selectedModule === 'tickets' ? 
                        ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'] : [])
                    ->columns(3)
                    ->visible(fn () => $this->selectedModule === 'tickets')
                    ->live(),
                    ]),

                Fieldset::make('Fields to Display')
                    ->schema([
                        CheckboxList::make('selectedFields')
                            ->options(fn () => array_combine($this->availableFields, array_map('ucfirst', str_replace('_', ' ', $this->availableFields))))
                            ->columns(3)
                            ->live()
                            ->required(),
                    ]),
            ]);
    }

    public function mount()
    {
        $this->initializeFields();
        $this->reportGenerated = false;
        $this->data = collect([]);
        $this->showReport = false;
    }

    public function initializeFields()
    {
        $this->availableFields = $this->moduleFields[$this->selectedModule];

        $this->selectedFields = match ($this->selectedModule) {
            'inventory' => ['name', 'category', 'brand', 'status', 'asset_code'],
            'asset_count' => ['name', 'total_count', 'remarks'],
            'users' => ['name', 'email', 'approval_status'],
            'classroom_assets' => ['classroom_id', 'name', 'code', 'quantity'],
            'classroom_schedule' => ['classroom_id', 'subject_name', 'professor', 'day', 'start_time', 'end_time', 'school_year', 'semester'],
            'attendance' => ['student_full_name', 'student_number', 'terminal_number', 'subject_id', 'peripherals', 'remarks', 'created_at'],
            'tickets' => ['ticket_number', 'title', 'ticket_type', 'ticket_status', 'priority', 'created_by', 'assigned_to', 'start_time'], // Default ticket fields
            default => [],
        };

        $this->filters['date_from'] = now()->subDays(90)->format('Y-m-d');
        $this->filters['date_to'] = now()->format('Y-m-d');

        $this->form->fill([
            'reportTitle' => 'Generated Report',
            'selectedModule' => $this->selectedModule,
            'filters' => $this->filters,
            'selectedFields' => $this->selectedFields, 
        ]);
    }

    public function updatedSelectedModule()
    {
        $this->initializeFields();
        $this->reportGenerated = false;
        $this->showReport = false;
        $this->resetPage();
    }

    public function updatedFilters()
    {
        $this->resetPage();
        $this->reportGenerated = false;
    }

    public function updatedSelectedFields()
    {
        $this->reportGenerated = false;
    }

    public function runReport()
    {
        if (empty($this->selectedFields)) {
            Notification::make()->title('Error')->body('Please select at least one field')->danger()->send();
            return;
        }

        try {
            $query = match ($this->selectedModule) {
                'inventory' => $this->queryInventory(),
                'asset_count' => $this->queryAssetCount(),
                'users' => $this->queryUsers(),
                'classroom_assets' => $this->queryClassroomAssets(),
                'classroom_schedule' => $this->queryClassroomSchedule(),
                'attendance' => $this->queryAttendance(),
                'tickets' => $this->queryTickets(), // Added ticket query
                default => null,
            };

            if (!$query) {
                Notification::make()->title('Error')->body('Invalid module selection')->danger()->send();
                return;
            }

            // Execute the query
            $results = $query->get();
            
            if ($results->isEmpty()) {
                $this->data = collect([]);
                $this->reportGenerated = false;
                $this->showReport = false;
                Notification::make()->title('No Data')->body('No records found')->warning()->send();
            } else {
                $this->data = $results;
                $this->reportGenerated = true;
                $this->showReport = true;
                Notification::make()->title('Success')->body('Found ' . $results->count() . ' records')->success()->send();
            }

        } catch (\Exception $e) {
            logger('Report generation error: ' . $e->getMessage());
            $this->reportGenerated = false;
            $this->showReport = false;
            $this->data = collect([]);
            Notification::make()->title('Error')->body('Failed to generate report: ' . $e->getMessage())->danger()->send();
        }
    }

    private function queryInventory()
{
    $query = Asset::query();
    
    // Add debug logging
    logger('Starting inventory query, total asset count: ' . Asset::count());
    
    // Add necessary relationships based on selected fields
    if (in_array('category', $this->selectedFields)) {
        $query->with('category');
    }
    
    if (in_array('brand', $this->selectedFields)) {
        $query->with('brand');
    }

    // Apply date filters if provided
    if (!empty($this->filters['date_from']) && !empty($this->filters['date_to'])) {
        try {
            $startDate = \Carbon\Carbon::parse($this->filters['date_from'])->startOfDay();
            $endDate = \Carbon\Carbon::parse($this->filters['date_to'])->endOfDay();
            
            if (\Schema::hasColumn('assets', 'created_at')) {
                $hasCreatedAt = Asset::whereNotNull('created_at')->exists();
                
                if ($hasCreatedAt) {
                    $query->where(function($q) use ($startDate, $endDate) {
                        $q->whereBetween('created_at', [$startDate, $endDate])
                          ->orWhereNull('created_at');
                    });
                    logger('Applied date filter between ' . $startDate . ' and ' . $endDate);
                } else {
                    logger('Skipped date filter as no records have created_at values');
                }
            } else {
                logger('Skipped date filter as created_at column does not exist');
            }
        } catch (\Exception $e) {
            logger('Date filter error: ' . $e->getMessage());
            Notification::make()->title('Date Error')->body('Invalid date format. Using default date range.')->warning()->send();
        }
    }

    // Apply category filter if provided
    if (!empty($this->filters['categories'])) {
        $query->whereIn('category_id', $this->filters['categories']);
        logger('Applied category filter: ' . implode(', ', $this->filters['categories']));
    }

    // Apply brand filter if provided
    if (!empty($this->filters['brands'])) {
        $query->whereIn('brand_id', $this->filters['brands']);
        logger('Applied brand filter: ' . implode(', ', $this->filters['brands']));
    }
    
    // Apply semester filter if provided (and if relevant to inventory)
    if (!empty($this->filters['semesters']) && \Schema::hasColumn('assets', 'semester')) {
        $query->whereIn('semester', $this->filters['semesters']);
        logger('Applied semester filter: ' . implode(', ', $this->filters['semesters']));
    }
    
    // Log the final query for debugging
    $sqlWithBindings = $query->toSql();
    $bindings = $query->getBindings();
    foreach ($bindings as $binding) {
        $value = is_string($binding) ? "'" . $binding . "'" : $binding;
        $sqlWithBindings = preg_replace('/\?/', $value, $sqlWithBindings, 1);
    }
    logger('Final inventory query: ' . $sqlWithBindings);
    logger('Expected record count: ' . $query->count());

    // Create a custom collection class that implements get()
    return new class($query) {
        protected $query;
        
        public function __construct($query) {
            $this->query = $query;
        }
        
        public function get() {
            $results = $this->query->get();
            logger('Actual results count from inventory: ' . $results->count());
            
            return $results->map(function ($asset) {
                // Handle expiry_date to leave column empty when null
                $expiryDate = null;
                
                if (!empty($asset->expiry_date) && $asset->expiry_date !== 'N/A') {
                    try {
                        if ($asset->expiry_date instanceof \Carbon\Carbon || $asset->expiry_date instanceof \DateTime) {
                            $expiryDate = $asset->expiry_date->format('Y-m-d');
                        } elseif (is_string($asset->expiry_date) && strtotime($asset->expiry_date) !== false) {
                            $expiryDate = \Carbon\Carbon::parse($asset->expiry_date)->format('Y-m-d');
                        }
                    } catch (\Exception $e) {
                        logger('Error parsing expiry_date for asset ID ' . $asset->id . ': ' . $e->getMessage());
                    }
                }
                
                return (object) [
                    'id' => $asset->id,
                    'name' => $asset->name,
                    'category' => optional($asset->category)->name,
                    'brand' => optional($asset->brand)->name,
                    'status' => $asset->status,
                    'asset_code' => $asset->asset_code ?? 'N/A',
                    'serial_number' => $asset->serial_number ?? 'N/A',
                    'expiry_date' => $expiryDate,
                ];
            });
        }
    };
}


    private function queryUsers()
    {
        $query = User::query();
        
        if (!empty($this->filters['date_from']) && !empty($this->filters['date_to'])) {
            try {
                $startDate = \Carbon\Carbon::parse($this->filters['date_from'])->startOfDay();
                $endDate = \Carbon\Carbon::parse($this->filters['date_to'])->endOfDay();
                $query->whereBetween('created_at', [$startDate, $endDate]);
            } catch (\Exception $e) {
                Notification::make()->title('Date Error')->body('Invalid date format. Using default date range.')->warning()->send();
            }
        }
        
        return $query;
    }

    private function queryClassroomAssets()
    {
        $query = AssetGroup::query()
            ->with(['classroom']); // We only need classroom relationship
        
        if (!empty($this->filters['classrooms'])) {
            $query->whereIn('classroom_id', $this->filters['classrooms']);
        }
        
        if (!empty($this->filters['date_from']) && !empty($this->filters['date_to'])) {
            try {
                $startDate = \Carbon\Carbon::parse($this->filters['date_from'])->startOfDay();
                $endDate = \Carbon\Carbon::parse($this->filters['date_to'])->endOfDay();
                $query->whereBetween('created_at', [$startDate, $endDate]);
            } catch (\Exception $e) {
                Notification::make()->title('Date Error')->body('Invalid date format. Using default date range.')->warning()->send();
            }
        }
        
        return new class($query) {
            protected $query;
            
            public function __construct($query) {
                $this->query = $query;
            }
            
            public function get() {
                return $this->query->get()->map(function ($assetGroup) {
                    return (object) [
                        'id' => $assetGroup->id,
                        'classroom_id' => optional($assetGroup->classroom)->name,
                        'name' => $assetGroup->name,
                        'code' => $assetGroup->code,
                        'quantity' => 1, // Set to 1 since each record represents one asset group
                        'asset_group_id' => $assetGroup->id,
                        'status' => $assetGroup->status
                    ];
                });
            }
        };
    }

    private function queryClassroomSchedule()
{
    $query = Subject::query()
        ->with(['professor', 'classroom']); // Loading classroom relationship
    
    // Filter by school year if specified
    if (!empty($this->filters['school_years'])) {
        $query->whereIn('school_year', $this->filters['school_years']);
    }
    
    // Filter by semester if specified
    if (!empty($this->filters['semesters'])) {
        $query->whereIn('semester', $this->filters['semesters']);
    }
    
    // Filter by classroom if specified - THIS IS THE PROBLEMATIC PART
    if (!empty($this->filters['classrooms'])) {
        // Check if the column exists in the subjects table
        if (Schema::hasColumn('subjects', 'classroom_id')) {
            $query->whereIn('classroom_id', $this->filters['classrooms']);
        } else {
            // Use a different approach if the column doesn't exist
            // For example, we might need to join with another table
            // or filter after retrieving the results
            
            // Since we don't know the exact structure, let's capture all and filter later
            logger('classroom_id column not found in subjects table. Will filter post-query.');
        }
    }
    
    // Date filtering
    if (!empty($this->filters['date_from']) && !empty($this->filters['date_to'])) {
        try {
            $startDate = \Carbon\Carbon::parse($this->filters['date_from'])->startOfDay();
            $endDate = \Carbon\Carbon::parse($this->filters['date_to'])->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } catch (\Exception $e) {
            Notification::make()->title('Date Error')->body('Invalid date format. Using default date range.')->warning()->send();
        }
    }
    
    return new class($query, $this->filters['classrooms'] ?? []) {
        protected $query;
        protected $classroomFilters;
        
        public function __construct($query, $classroomFilters) {
            $this->query = $query;
            $this->classroomFilters = $classroomFilters;
        }
        
        public function get() {
            $results = $this->query->get();
            
            // If we have classroom filters but couldn't apply them in the query,
            // filter the results here
            if (!empty($this->classroomFilters)) {
                $results = $results->filter(function($subject) {
                    // Try to get the classroom ID from the relationship
                    $classroomId = $subject->classroom_id ?? 
                                  (isset($subject->classroom) ? $subject->classroom->id : null);
                    
                    // Keep this record if no classroom ID (show as unassigned)
                    // or if the ID is in our filter list
                    return !$classroomId || in_array($classroomId, $this->classroomFilters);
                });
            }
            
            return $results->map(function ($subject) {
                return (object) [
                    'classroom_id' => optional($subject->classroom)->name ?? 'Unassigned',
                    'subject_name' => $subject->name,
                    'subject_code' => $subject->subject_code,
                    'professor' => optional($subject->professor)->name ?? 'Unassigned',
                    'day' => $subject->day,
                    'start_time' => optional($subject->lab_time_starts_at)->format('H:i:s'),
                    'end_time' => optional($subject->lab_time_ends_at)->format('H:i:s'),
                    'school_year' => $subject->school_year,
                    'semester' => $subject->semester
                ];
            });
        }
    };
}

    private function queryAttendance()
    {
        // Start with base query
        $query = \App\Models\Attendance::query()->with(['subject.professor']);
        
        // Date range filtering
        if (!empty($this->filters['date_from']) && !empty($this->filters['date_to'])) {
            try {
                $startDate = \Carbon\Carbon::parse($this->filters['date_from'])->startOfDay();
                $endDate = \Carbon\Carbon::parse($this->filters['date_to'])->endOfDay();
                $query->whereBetween('created_at', [$startDate, $endDate]);
            } catch (\Exception $e) {
                Notification::make()->title('Date Error')->body('Invalid date format. Using default date range.')->warning()->send();
            }
        }
        
        // School year filtering
        if (!empty($this->filters['school_years'])) {
            $query->forSchoolYear($this->filters['school_years']);
        }
        
        // Semester filtering
        if (!empty($this->filters['semesters'])) {
            $query->forSemester($this->filters['semesters']);
        }
        
        // Professor filtering
        if (!empty($this->filters['professors'])) {
            $query->forProfessor($this->filters['professors']);
        }
        
        // Transform results for display
        $attendanceCollection = collect();
        $attendanceData = $query->get();
        
        foreach ($attendanceData as $record) {
            $data = new \stdClass();
            $data->student_full_name = $record->student_full_name;
            $data->student_email = $record->student_email;
            $data->student_number = $record->student_number;
            $data->terminal_number = $record->terminal_number;
            $data->subject_id = $record->subject_id;
            $data->subject_name = optional($record->subject)->name ?? 'N/A';
            $data->subject_code = optional($record->subject)->subject_code ?? 'N/A';
            $data->peripherals = $record->peripherals;
            $data->remarks = $record->remarks ?? '';
            $data->created_at = optional($record->created_at)->format('Y-m-d H:i:s') ?? 'N/A';
            $data->professor_name = optional($record->subject->professor)->name ?? 'N/A';
            $data->school_year = optional($record->subject)->school_year ?? 'N/A';
            $data->semester = optional($record->subject)->semester ?? 'N/A';
            
            $attendanceCollection->push($data);
        }
        
        // Return the collection wrapped in a class that implements a get() method
        return new class($attendanceCollection) {
            protected $collection;
            
            public function __construct($collection) {
                $this->collection = $collection;
            }
            
            public function get() {
                return $this->collection;
            }
        };
    }

    private function queryTickets()
{
    $query = Ticket::query()
        ->with(['creator', 'assignedTo', 'classroom', 'asset']);
    
    // Date range filtering
    if (!empty($this->filters['date_from']) && !empty($this->filters['date_to'])) {
        try {
            $startDate = \Carbon\Carbon::parse($this->filters['date_from'])->startOfDay();
            $endDate = \Carbon\Carbon::parse($this->filters['date_to'])->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } catch (\Exception $e) {
            Notification::make()->title('Date Error')->body('Invalid date format. Using default date range.')->warning()->send();
        }
    }
    
    // Ticket status filtering
    if (!empty($this->filters['ticket_statuses'])) {
        $query->whereIn('ticket_status', $this->filters['ticket_statuses']);
    }
    
    // Open tickets filter
    if (!empty($this->filters['open']) && $this->filters['open'] === true) {
        $query->whereNotIn('ticket_status', ['Closed', 'Resolved']);
    }
    
    // Ticket type filtering
    if (!empty($this->filters['ticket_types'])) {
        $query->whereIn('ticket_type', $this->filters['ticket_types']);
    }
    
    // Ticket priority filtering
    if (!empty($this->filters['ticket_priorities'])) {
        $query->whereIn('priority', $this->filters['ticket_priorities']);
    }
    
    // Transform results for display
    return new class($query) {
        protected $query;
        
        public function __construct($query) {
            $this->query = $query;
        }
        
        public function get() {
            return $this->query->get()->map(function ($ticket) {
                return (object) [
                    'ticket_number' => $ticket->ticket_number,
                    'title' => $ticket->title,
                    'description' => $ticket->description,
                    'ticket_type' => $ticket->ticket_type,
                    'ticket_status' => $ticket->ticket_status,
                    'priority' => $ticket->priority,
                    'created_by' => optional($ticket->creator)->name ?? 'N/A',
                    'assigned_to' => optional($ticket->assignedTo)->name ?? 'N/A',
                    'classroom_id' => optional($ticket->classroom)->name ?? 'N/A',
                    'asset_id' => optional($ticket->asset)->name ?? 'N/A',
                    'start_time' => optional($ticket->start_time)->format('Y-m-d H:i:s') ?? 'N/A',
                    'end_time' => optional($ticket->end_time)->format('Y-m-d H:i:s') ?? 'N/A',
                    'created_at' => optional($ticket->created_at)->format('Y-m-d H:i:s') ?? 'N/A',
                ];
            });
        }
    };
}

    private function queryAssetCount()
{
    // Group assets by name and calculate total count
    $assetCounts = Asset::select('name')
        ->selectRaw('COUNT(*) as total_count')
        ->groupBy('name')
        ->get()
        ->map(function ($asset) {
            return (object) [
                'name' => $asset->name,
                'total_count' => $asset->total_count,
                'remarks' => $this->generateRemarks($asset->total_count)
            ];
        })
        // Sort by total count in descending order
        ->sortByDesc('total_count');

    // Return a custom class that implements get() method
    return new class($assetCounts) {
        protected $collection;
        
        public function __construct($collection) {
            $this->collection = $collection;
        }
        
        public function get() {
            return $this->collection;
        }
    };
}

// Helper method to generate remarks based on count
private function generateRemarks($count)
{
    if ($count <= 1) return 'Low inventory';
    if ($count <= 5) return 'Moderate inventory';
    if ($count <= 10) return 'Good stock';
    return 'Excess inventory';
}

    public function printReport()
    {
        $this->dispatch('openPrintPreview');
    }

    public function render()
    {
        return view('livewire.report-builder', [
            'displayData' => $this->data instanceof Collection ? $this->data : collect([]),
            'showReport' => $this->showReport
        ]);
    }
}