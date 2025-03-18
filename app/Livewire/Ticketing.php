<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Ticket as TicketModel;
use App\Models\Subject;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;
use League\Csv\Writer;
use League\Csv\Reader;
use SplTempFileObject;

class Ticketing extends Component
{
    use WithPagination;
    use WithFileUploads;

    // Update properties
    public $filterType = 'all';
    public $filterValue = '';
    public $ticketStatus = '';
    public $priority = '';
    public $assignedTo = '';
    public $search = '';
    public $perPage = 10;
    public $viewType = 'table';
    public $filteredCount = 0;
    public $totalTickets = 0; // Add this line

    // Form properties
    public $ticket_type = '';
    public $title = '';
    public $request_type = '';
    public $classroom_id = '';
    public $asset_id = '';
    public $section_id = '';
    public $subject_id = '';
    public $starts_at;
    public $ends_at;
    public $description = '';
    public $attachments = [];

    // Bulk action properties
    public $selected = [];
    public $selectAll = false;
    public $bulkAction = '';
    public $confirmingBulkDelete = false;
    public $importFile = null;
    public $showImportModal = false;
    public $showBulkEditModal = false;
    public $bulkEditData = [
        'ticket_status' => '',
        'priority' => '',
        'assigned_to' => '',
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'filterType' => ['except' => 'all'],
        'ticketStatus' => ['except' => ''],
        'priority' => ['except' => ''],
        'assignedTo' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    protected $listeners = ['refreshTickets' => '$refresh'];

    protected function rules()
    {
        return [
            'ticket_type' => 'required|in:request,incident',
            'title' => 'required|string|max:255',
            'request_type' => 'required_if:ticket_type,request|in:asset,classroom',
            'classroom_id' => 'required_if:request_type,classroom',
            'asset_id' => 'required_if:request_type,asset',
            'section_id' => 'required_if:request_type,classroom',
            'subject_id' => 'required_if:request_type,classroom',
            'priority' => 'required|in:low,medium,high',
            'description' => 'required|string',
            'attachments.*' => 'nullable|image|max:1024',
        ];
    }

    public function mount()
    {
        $this->totalTickets = TicketModel::count();
        $this->priority = 'low';
    }

    // Reset filters
    public function resetFilters()
    {
        $this->filterType = 'all';
        $this->filterValue = '';
        $this->ticketStatus = '';
        $this->priority = '';
        $this->assignedTo = '';
        $this->search = '';
        $this->resetPage();
    }

    // View type toggle
    public function setViewType($type)
    {
        $this->viewType = $type;
    }

    // Update ticket status
    public function updateTicketStatus($status, $ticketId)
    {
        $ticket = TicketModel::findOrFail($ticketId);
        $ticket->ticket_status = $status;
        $ticket->save();
        
        $this->dispatch('notify', [
            'message' => 'Ticket updated successfully',
            'type' => 'success'
        ]);
    }
    
    // Delete ticket
    public function deleteTicket($ticketId)
    {
        $ticket = TicketModel::findOrFail($ticketId);
        $ticket->delete();
        
        $this->dispatch('notify', [
            'message' => 'Ticket deleted successfully',
            'type' => 'success'
        ]);
    }

    // Bulk actions
    public function confirmBulkDelete()
    {
        if (empty($this->selected)) {
            $this->addError('bulkAction', 'Please select at least one ticket');
            return;
        }
        $this->confirmingBulkDelete = true;
    }

    public function doBulkDelete()
    {
        $count = count($this->selected);
        TicketModel::whereIn('id', $this->selected)->delete();
        $this->confirmingBulkDelete = false;
        $this->selected = [];
        $this->selectAll = false;
        
        $this->dispatch('notify', [
            'message' => $count . ' tickets deleted successfully',
            'type' => 'success'
        ]);
    }

    public function openBulkEditModal()
    {
        if (empty($this->selected)) {
            $this->addError('bulkAction', 'Please select at least one ticket');
            return;
        }
        $this->showBulkEditModal = true;
    }

    public function doBulkEdit()
    {
        $data = array_filter($this->bulkEditData);

        if (empty($data)) {
            $this->dispatch('notify', [
                'message' => 'Please select at least one field to update',
                'type' => 'error'
            ]);
            return;
        }

        $count = count($this->selected);
        TicketModel::whereIn('id', $this->selected)->update($data);

        $this->showBulkEditModal = false;
        $this->bulkEditData = [
            'ticket_status' => '',
            'priority' => '',
            'assigned_to' => '',
        ];
        
        $this->dispatch('notify', [
            'message' => $count . ' tickets updated successfully',
            'type' => 'success'
        ]);
    }

    // Import/Export functions
    public function openImportModal()
    {
        $this->showImportModal = true;
    }

    public function importTickets()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:csv,txt|max:1024',
        ]);

        try {
            $csv = Reader::createFromPath($this->importFile->getRealPath(), 'r');
            $csv->setHeaderOffset(0);

            $records = $csv->getRecords();
            $imported = 0;

            DB::beginTransaction();

            foreach ($records as $record) {
                TicketModel::create([
                    'ticket_number' => $record['ticket_number'] ?? 'TKT-' . uniqid(),
                    'title' => $record['title'] ?? 'Imported Ticket',
                    'ticket_status' => $record['status'] ?? 'open',
                    'priority' => $record['priority'] ?? 'medium',
                    'assigned_to' => $record['assigned_to'] ?? null,
                ]);

                $imported++;
            }

            DB::commit();

            $this->showImportModal = false;
            $this->importFile = null;
            
            $this->dispatch('notify', [
                'message' => $imported . ' tickets imported successfully',
                'type' => 'success'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('import', 'Error importing file: ' . $e->getMessage());
        }
    }

    public function exportTickets()
    {
        $query = TicketModel::query();

        // Apply all filters to the export
        if ($this->search) {
            $query->where(function($q) {
                $q->where('ticket_number', 'like', "%{$this->search}%")
                  ->orWhere('title', 'like', "%{$this->search}%");
            });
        }
        
        if ($this->ticketStatus) {
            $query->where('ticket_status', $this->ticketStatus);
        }
        
        if ($this->assignedTo) {
            $query->where('assigned_to', $this->assignedTo);
        }
        
        if ($this->priority) {
            $query->where('priority', $this->priority);
        }
        
        // If specific tickets are selected, only export those
        if (!empty($this->selected)) {
            $query->whereIn('id', $this->selected);
        }

        $tickets = $query->with(['assignedTo'])->get();

        $csv = Writer::createFromFileObject(new SplTempFileObject());

        $csv->insertOne([
            'Ticket Number',
            'Title',
            'Status',
            'Priority',
            'Assigned To'
        ]);

        foreach ($tickets as $ticket) {
            $csv->insertOne([
                $ticket->ticket_number,
                $ticket->title,
                $ticket->ticket_status,
                $ticket->priority,
                $ticket->assignedTo->name ?? 'Unassigned',
            ]);
        }

        $filename = 'tickets-export-' . date('Y-m-d') . '.csv';

        return response()->streamDownload(
            function () use ($csv) {
                echo $csv->getContent();
            },
            $filename,
            ['Content-Type' => 'text/csv']
        );
    }

    public function executeBulkAction()
    {
        if (empty($this->selected)) {
            $this->addError('bulkAction', 'Please select at least one ticket');
            return;
        }

        switch ($this->bulkAction) {
            case 'delete':
                $this->confirmBulkDelete();
                break;
            case 'edit':
                $this->openBulkEditModal();
                break;
            case 'export':
                return $this->exportTickets();
            default:
                $this->addError('bulkAction', 'Please select a valid action');
        }
    }

    // Render the component
    public function render()
    {
        $query = TicketModel::query()
            ->when(!auth()->user()->hasRole(['super_admin', 'admin', 'technician']), function ($query) {
                $query->where(function ($query) {
                    $query->where('created_by', auth()->id())
                        ->orWhere('assigned_to', auth()->id());
                });
            })
            ->when($this->search, function ($query) {
                $searchTerm = '%' . $this->search . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('ticket_number', 'like', $searchTerm)
                        ->orWhere('title', 'like', $searchTerm)
                        ->orWhere('description', 'like', $searchTerm);
                });
            })
            ->when($this->filterType === 'status' && $this->ticketStatus, fn($q) => $q->where('ticket_status', $this->ticketStatus))
            ->when($this->filterType === 'priority' && $this->priority, fn($q) => $q->where('priority', $this->priority))
            ->when($this->filterType === 'assigned' && $this->assignedTo, fn($q) => $q->where('assigned_to', $this->assignedTo))
            ->when($this->filterType === 'status-priority', function($q) {
                $q->when($this->ticketStatus, fn($q) => $q->where('ticket_status', $this->ticketStatus))
                    ->when($this->priority, fn($q) => $q->where('priority', $this->priority));
            })
            ->when($this->filterType === 'status-assigned', function($q) {
                $q->when($this->ticketStatus, fn($q) => $q->where('ticket_status', $this->ticketStatus))
                    ->when($this->assignedTo, fn($q) => $q->where('assigned_to', $this->assignedTo));
            });

        $tickets = $query->latest()->paginate($this->perPage);
        $this->filteredCount = $tickets->total();

        // Handle "Select All" checkboxes
        if ($this->selectAll) {
            $this->selected = $tickets->pluck('id')->map(fn($id) => (string) $id)->toArray();
        }

        $users = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['technician', 'admin']);
        })->pluck('name', 'id');

        return view('livewire.ticketing', [
            'tickets' => $tickets,
            'users' => $users,
            'ticketCount' => [
                'total' => $this->totalTickets,
                'open' => TicketModel::where('ticket_status', 'open')->count(),
                'in_progress' => TicketModel::where('ticket_status', 'in_progress')->count(),
                'resolved' => TicketModel::where('ticket_status', 'resolved')->count(),
                'closed' => TicketModel::where('ticket_status', 'closed')->count(),
            ]
        ]);
    }

    public function createTicket()
    {
        $this->validate();

        $ticket = new TicketModel();
        $ticket->ticket_number = ($this->ticket_type === 'request' ? 'REQ' : 'INC') . '-' . strtoupper(Str::random(8));
        $ticket->title = $this->title;
        $ticket->ticket_type = $this->ticket_type;
        $ticket->request_type = $this->request_type;
        $ticket->classroom_id = $this->classroom_id;
        $ticket->asset_id = $this->asset_id;
        $ticket->section_id = $this->section_id;
        $ticket->subject_id = $this->subject_id;
        $ticket->starts_at = $this->starts_at;
        $ticket->ends_at = $this->ends_at;
        $ticket->description = $this->description;
        $ticket->priority = $this->priority;
        $ticket->created_by = auth()->id();
        $ticket->ticket_status = 'open';
        
        // Assign to random technician
        $technician = User::whereHas('roles', function ($query) {
            $query->where('name', 'technician');
        })->inRandomOrder()->first();
        
        $ticket->assigned_to = $technician?->id;

        $ticket->save();

        if ($this->attachments) {
            foreach ($this->attachments as $attachment) {
                $ticket->addMedia($attachment)->toMediaCollection('attachments');
            }
        }

        $this->reset();
        $this->dispatch('notify', [
            'message' => 'Ticket created successfully',
            'type' => 'success'
        ]);
    }

    public function updatedFilterType()
    {
        $this->filterValue = '';
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterValue()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        if (!$value) {
            $this->selected = [];
        }
    }

    public function updatedTicketStatus()
    {
        $this->resetPage();
    }

    public function updatedPriority()
    {
        $this->resetPage();
    }

    public function updatedAssignedTo()
    {
        $this->resetPage();
    }

    public function viewTicket($ticketId)
    {
        return redirect()->route('filament.app.resources.tickets.view', ['record' => $ticketId]);
    }

    public function editTicket($ticketId)
    {
        return redirect()->route('filament.app.resources.tickets.edit', ['record' => $ticketId]);
    }

    public function confirmTicketDeletion($ticketId)
    {
        $this->dispatch('notify', [
            'type' => 'warning',
            'message' => 'Are you sure you want to delete this ticket?',
            'actions' => [
                [
                    'label' => 'Yes, Delete',
                    'method' => 'deleteTicket',
                    'parameters' => [$ticketId],
                ],
                [
                    'label' => 'Cancel',
                    'method' => 'cancelDeletion',
                ],
            ],
        ]);
    }

    public function cancelDeletion()
    {
        $this->dispatch('notify', [
            'type' => 'info',
            'message' => 'Deletion cancelled',
        ]);
    }
}