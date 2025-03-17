<?php

namespace App\Livewire;

use App\Models\Classroom;
use App\Models\Building;
use Livewire\Component;
use Livewire\WithPagination;

class Rooms extends Component
{
    use WithPagination;

    public $selectedBuilding = '';
    public $selectedFloor = '';
    public $search = '';
    public $floors = [];
    public $buildingCounts = [];
    public $viewType = 'table'; // Default view type
    public $showingClassroomDetails = false;
    public $currentClassroom = null;
    public $schedulesByDay = [];

    public function mount()
    {
        $this->loadBuildingCounts();
    }

    public function loadBuildingCounts()
    {
        // Get all buildings with classroom counts
        $this->buildingCounts = Building::withCount('classrooms')
            ->orderBy('name')
            ->get();
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

        if ($this->currentClassroom) {
            // Initialize days of the week
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            $this->schedulesByDay = array_fill_keys($days, []);

            // Get all sections associated with this classroom
            $sections = $this->currentClassroom->sections()->with(['subject'])->get();

            // Collect all subjects from these sections
            foreach ($sections as $section) {
                foreach ($section->subject as $subject) {
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
                            'professor' => optional($subject->professor)->name ?? 'N/A'
                        ];
                    }
                }
            }

            $this->showingClassroomDetails = true;
        }
    }

    public function closeClassroomDetails()
    {
        $this->showingClassroomDetails = false;
        $this->currentClassroom = null;
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
