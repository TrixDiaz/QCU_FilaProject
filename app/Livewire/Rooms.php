<?php
namespace App\Livewire;

use App\Models\Classroom;
use App\Models\Building;
use App\Models\Subject;
use Livewire\Component;
use Livewire\WithPagination;

class Rooms extends Component
{
    use WithPagination;

    public $selectedBuilding = '';
    public $selectedFloor = '';
    public $selectedSchoolYear = '';
    public $selectedSemester = '';
    public $search = '';
    public $floors = [];
    public $buildingCounts = [];
    public $schoolYears = [];
    public $semesters = [];
    public $viewType = 'table'; // Default view type
    public $showingClassroomDetails = false;
    public $currentClassroom = null;
    public $schedulesByDay = [];
    
    // New properties for modal filters
    public $modalSelectedSchoolYear = '';
    public $modalSelectedSemester = '';
    
    // Add query string parameters
    protected $queryString = [
        'search' => ['except' => ''],
        'selectedBuilding' => ['except' => ''],
        'selectedFloor' => ['except' => ''],
        'selectedSchoolYear' => ['except' => ''],
        'selectedSemester' => ['except' => ''],
    ];

    public function mount()
    {
        $this->loadBuildingCounts();
        $this->loadSchoolYearsAndSemesters();
    }

    public function loadBuildingCounts()
    {
        // Get all buildings with classroom counts
        $this->buildingCounts = Building::withCount('classrooms')
            ->orderBy('name')
            ->get();
    }

    public function loadSchoolYearsAndSemesters()
    {
        // Get unique school years
        $this->schoolYears = Subject::distinct()
            ->pluck('school_year')
            ->filter()
            ->sort()
            ->values()
            ->toArray();

        // Get unique semesters
        $this->semesters = Subject::distinct()
            ->pluck('semester')
            ->filter()
            ->sort()
            ->values()
            ->toArray();
    }

    public function updatedSelectedBuilding($value)
    {
        $this->resetPage();
        $this->selectedFloor = '';

        // Load floors for the selected building
        if ($value) {
            $this->floors = Classroom::where('building_id', $value)
                ->distinct()
                ->pluck('floor')
                ->filter()
                ->sort()
                ->values()
                ->toArray();
        } else {
            $this->floors = [];
        }
    }

    public function updatedSelectedFloor()
    {
        $this->resetPage();
    }

    public function updatedSelectedSchoolYear()
    {
        $this->resetPage();
    }

    public function updatedSelectedSemester()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function setViewType($type)
    {
        $this->viewType = $type;
    }

    public function viewClassroomDetails($classroomId)
    {
        $this->currentClassroom = Classroom::with(['building', 'sections.subject'])
            ->find($classroomId);
            
        // Sync the modal filters with the main filters initially
        $this->modalSelectedSchoolYear = $this->selectedSchoolYear;
        $this->modalSelectedSemester = $this->selectedSemester;

        if ($this->currentClassroom) {
            $this->loadClassroomSchedules();
            $this->showingClassroomDetails = true;
        }
    }
    
    // New method to load schedules with filters
    public function loadClassroomSchedules()
    {
        if (!$this->currentClassroom) {
            return;
        }
        
        // Initialize days of the week
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $this->schedulesByDay = array_fill_keys($days, []);

        // Get all sections associated with this classroom
        $sections = $this->currentClassroom->sections()->with(['subject'])->get();

        // Filter subjects based on modal filters
        $subjectQuery = function($query) {
            if ($this->modalSelectedSchoolYear) {
                $query->where('school_year', $this->modalSelectedSchoolYear);
            }
            if ($this->modalSelectedSemester) {
                $query->where('semester', $this->modalSelectedSemester);
            }
            return $query;
        };

        // Collect all subjects from these sections
        foreach ($sections as $section) {
            $filteredSubjects = $section->subject()->where($subjectQuery)->get();
            
            foreach ($filteredSubjects as $subject) {
                // Make sure we have a day value
                if ($subject->day) {
                    // Format the time data
                    $startTime = optional($subject->lab_time_starts_at)->format('H:i:s');
                    $endTime = optional($subject->lab_time_ends_at)->format('H:i:s');

                    // Add to the appropriate day's schedule
                    $this->schedulesByDay[$subject->day][] = (object)[
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'section' => $section,
                        'subject' => $subject->name,
                        'subject_code' => $subject->subject_code,
                        'professor' => optional($subject->professor)->name ?? 'N/A',
                        'semester' => $subject->semester,
                        'school_year' => $subject->school_year
                    ];
                }
            }
        }
    }
    
    // New methods to handle modal filter changes
    public function updatedModalSelectedSchoolYear()
    {
        $this->loadClassroomSchedules();
    }
    
    public function updatedModalSelectedSemester()
    {
        $this->loadClassroomSchedules();
    }

    public function closeClassroomDetails()
    {
        $this->showingClassroomDetails = false;
        $this->currentClassroom = null;
        $this->modalSelectedSchoolYear = '';
        $this->modalSelectedSemester = '';
        $this->schedulesByDay = [];
    }

    public function render()
    {
        $classroomsQuery = Classroom::with('building');

        if ($this->selectedBuilding) {
            $classroomsQuery->where('building_id', $this->selectedBuilding);
        }

        if ($this->selectedFloor !== '' && $this->selectedFloor !== null) {
            $classroomsQuery->where('floor', $this->selectedFloor);
        }

        // Filter by school year and semester if selected
        if ($this->selectedSchoolYear || $this->selectedSemester) {
            $classroomsQuery->whereHas('sections.subject', function ($query) {
                if ($this->selectedSchoolYear) {
                    $query->where('school_year', $this->selectedSchoolYear);
                }
                if ($this->selectedSemester) {
                    $query->where('semester', $this->selectedSemester);
                }
            });
        }

        if ($this->search) {
            $search = '%' . $this->search . '%';
            $classroomsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', $search)
                    ->orWhereHas('building', function ($subQuery) use ($search) {
                        $subQuery->where('name', 'like', $search);
                    })
                    ->orWhere('floor', 'like', $search);
            });
        }

        return view('livewire.rooms', [
            'classrooms' => $classroomsQuery->paginate(10),
        ]);
    }
}