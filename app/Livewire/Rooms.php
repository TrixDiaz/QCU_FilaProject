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

            // Since we don't have a direct schedules relationship yet, let's derive them from sections
            // or use the Event model if available
            // Get active sections for this classroom that have events
            $sections = $this->currentClassroom->sections()->with(['classroom'])->get();

            // If you have an events table that contains the schedule information
            $events = \App\Models\Event::whereIn('section_id', $sections->pluck('id'))
                ->get()
                ->groupBy(function ($event) {
                    // Convert timestamp to day name
                    return date('l', strtotime($event->starts_at));
                });

            foreach ($events as $day => $dayEvents) {
                if (in_array($day, $days)) {
                    foreach ($dayEvents as $event) {
                        $section = $sections->firstWhere('id', $event->section_id);

                        $this->schedulesByDay[$day][] = (object)[
                            'start_time' => $event->starts_at,
                            'end_time' => $event->ends_at,
                            'section' => $section,
                            'subject' => $event->title
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
