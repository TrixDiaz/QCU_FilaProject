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
    ];
    public $moduleFields = [
        'inventory' => ['category_id', 'brand_id', 'name', 'asset_code', 'serial_number', 'expiry_date', 'status'],
        'users' => ['name', 'email', 'approval_status', 'created_at', 'updated_at'],
        'classroom_assets' => ['classroom_id', 'asset_group_id', 'name', 'code', 'status', 'quantity'],
        'classroom_schedule' => ['classroom_id', 'subject_name', 'subject_code', 'professor', 'day', 'start_time', 'end_time', 'school_year', 'semester'],
        'attendance' => ['subject_id', 'terminal_number', 'student_full_name', 'student_email', 'student_number', 'peripherals', 'remarks', 'created_at']
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
                        'users' => 'Users',
                        'classroom_assets' => 'Classroom Assets',
                        'classroom_schedule' => 'Classroom Schedule',
                        'attendance' => 'Attendance Records',
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
                            ->options(fn () => in_array($this->selectedModule, ['classroom_schedule', 'attendance']) ? 
                                Subject::distinct()->pluck('semester', 'semester')->toArray() : [])
                            ->columns(3)
                            ->visible(fn () => in_array($this->selectedModule, ['classroom_schedule', 'attendance']))
                            ->live(),

                            CheckboxList::make('filters.professors')
                            ->label('Professors')
                            ->options(fn () => $this->selectedModule === 'attendance' ? 
                                User::where('name', 'like', '%professor%')->pluck('name', 'id')->toArray() : [])
                            ->columns(3)
                            ->visible(fn () => $this->selectedModule === 'attendance')
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
            'inventory' => ['name', 'category_id', 'brand_id', 'status'],
            'users' => ['name', 'email', 'approval_status'],
            'classroom_assets' => ['classroom_id', 'name', 'code', 'quantity'],
            'classroom_schedule' => ['classroom_id', 'subject_name', 'professor', 'day', 'start_time', 'end_time', 'school_year', 'semester'],
            'attendance' => ['student_full_name', 'student_number', 'terminal_number', 'subject_id', 'peripherals', 'remarks', 'created_at'],
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
                'users' => $this->queryUsers(),
                'classroom_assets' => $this->queryClassroomAssets(),
                'classroom_schedule' => $this->queryClassroomSchedule(),
                'attendance' => $this->queryAttendance(),
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
        
        // Add necessary relationships based on selected fields
        if (in_array('category_id', $this->selectedFields)) {
            $query->with('category');
        }
        
        if (in_array('brand_id', $this->selectedFields)) {
            $query->with('brand');
        }

        $allNull = Asset::whereNotNull('created_at')->count() == 0;

        if (!$allNull && !empty($this->filters['date_from']) && !empty($this->filters['date_to'])) {
            try {
                $startDate = \Carbon\Carbon::parse($this->filters['date_from'])->startOfDay();
                $endDate = \Carbon\Carbon::parse($this->filters['date_to'])->endOfDay();
                $query->whereBetween('created_at', [$startDate, $endDate]);
            } catch (\Exception $e) {
                Notification::make()->title('Date Error')->body('Invalid date format. Using default date range.')->warning()->send();
            }
        }

        if (!empty($this->filters['categories'])) {
            $query->whereIn('category_id', $this->filters['categories']);
        }

        if (!empty($this->filters['brands'])) {
            $query->whereIn('brand_id', $this->filters['brands']);
        }

        return $query;
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
        // Using AssetGroup instead of Asset directly based on your Rooms.php structure
        $query = AssetGroup::query()->with(['assets.category', 'assets.brand', 'classroom']);
        
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
        
        // Transform results for display purposes
        $query->get()->map(function ($assetGroup) {
            // Add fields for display
            $assetGroup->setAttribute('classroom_name', optional($assetGroup->classroom)->name ?? 'N/A');
            return $assetGroup;
        });
        
        return $query;
    }

    private function queryClassroomSchedule()
{
    // Use Subject model for schedules 
    $query = Subject::query()->with(['professor']);
    
    // Filter by school year if specified
    if (!empty($this->filters['school_years'])) {
        $query->whereIn('school_year', $this->filters['school_years']);
    }
    
    // Filter by semester if specified
    if (!empty($this->filters['semesters'])) {
        $query->whereIn('semester', $this->filters['semesters']);
    }
    
    // Filter by classroom if specified
    if (!empty($this->filters['classrooms'])) {
        $query->whereHas('classroom', function ($q) {
            $q->whereIn('id', $this->filters['classrooms']);
        });
    }
    
    // Date filtering
    if (!empty($this->filters['date_from']) && !empty($this->filters['date_to'])) {
        try {
            $startDate = \Carbon\Carbon::parse($this->filters['date_from'])->startOfDay();
            $endDate = \Carbon\Carbon::parse($this->filters['date_to'])->endOfDay();
            $query->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            });
        } catch (\Exception $e) {
            Notification::make()->title('Date Error')->body('Invalid date format. Using default date range.')->warning()->send();
        }
    }
    
    // Get the subjects and transform to the format we need
    $subjects = $query->get();
    $scheduleCollection = collect();
    
    foreach ($subjects as $subject) {
        // Create a schedule data object for each subject
        $scheduleData = new \stdClass();
        $scheduleData->classroom_id = $subject->classroom_id ?? null;
        $scheduleData->subject_name = $subject->name;
        $scheduleData->subject_code = $subject->subject_code;
        $scheduleData->professor = optional($subject->professor)->name ?? 'N/A';
        $scheduleData->day = $subject->day;
        $scheduleData->start_time = optional($subject->lab_time_starts_at)->format('H:i:s');
        $scheduleData->end_time = optional($subject->lab_time_ends_at)->format('H:i:s');
        $scheduleData->school_year = $subject->school_year;
        $scheduleData->semester = $subject->semester;
        
        // Add to our collection
        $scheduleCollection->push($scheduleData);
    }
    
    // Return the collection as a query builder that will get executed
    return new class($scheduleCollection) {
        protected $collection;
        
        public function __construct($collection) {
            $this->collection = $collection;
        }
        
        public function get() {
            return $this->collection;
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