<?php

namespace App\Livewire;

use App\Models\Ticket;
use App\Models\Classroom;
use App\Models\Section;
use Livewire\Component;

class TicketEdit extends Component
{
    public Ticket $ticket;
    public $title;
    public $description;
    public $priority;
    public $ticket_status;
    public $classroom_id;
    public $section_id;
    public $start_time;
    public $end_time;
    
    public $classrooms = [];
    public $sections = [];

    protected $rules = [
        'title' => 'required|min:5|max:255',
        'description' => 'required|min:10',
        'priority' => 'required|in:low,medium,high',
        'ticket_status' => 'required|in:open,in_progress,closed,archived',
        'classroom_id' => 'nullable|exists:classrooms,id',
        'section_id' => 'nullable|exists:sections,id',
        'start_time' => 'nullable|date',
        'end_time' => 'nullable|date|after:start_time',
    ];

    public function mount(Ticket $ticket)
    {
        $this->ticket = $ticket;
        $this->title = $ticket->title;
        $this->description = $ticket->description;
        $this->priority = $ticket->priority;
        $this->ticket_status = $ticket->ticket_status;
        $this->classroom_id = $ticket->classroom_id;
        $this->section_id = $ticket->section_id;
        $this->start_time = $ticket->start_time;
        $this->end_time = $ticket->end_time;

        if ($ticket->type === 'classroom_request') {
            $this->classrooms = Classroom::all();
            $this->sections = Section::all();
        }
    }

    public function save()
    {
        $this->validate();

        $this->ticket->update([
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'ticket_status' => $this->ticket_status,
            'classroom_id' => $this->classroom_id,
            'section_id' => $this->section_id,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
        ]);

        session()->flash('message', 'Ticket updated successfully.');
        return redirect()->route('tickets.index');
    }

    public function render()
    {
        return view('livewire.ticket-edit');
    }
}