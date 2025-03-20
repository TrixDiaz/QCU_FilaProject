<div x-data="{
    showImportModal: false,
    showBulkEditModal: false,
    confirmingBulkDelete: false,
    toggleAllCheckboxes() {
        if ($wire.selectAll) {
            $wire.selected = [];
            $wire.selectAll = false;
        } else {
            $wire.selectAll = true;
        }
    }
}">
    <x-filament::section class="my-4">
        <div class="grid grid-cols-1 gap-6 w-full">
            <div x-data="{ isSearchOpen: false }" class="col-span-full">
                <div x-show="!isSearchOpen" x-transition class="space-y-4 w-full">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-xl font-bold">Support Tickets</h2>
                            <div class="text-sm capitalize">
                                @if($filterType == 'all')
                                    Showing all {{ $filteredCount }} tickets
                                @elseif($filterType == 'status' && $ticketStatus)
                                    Showing {{ $filteredCount }} {{ $ticketStatus }} tickets
                                @elseif($filterType == 'priority' && $priority)
                                    Showing {{ $filteredCount }} {{ $priority }} priority tickets
                                @elseif($filterType == 'assigned' && $assignedTo)
                                    Showing {{ $filteredCount }} tickets assigned to {{ $users[$assignedTo] ?? 'Unknown' }}
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            {{-- View Toggle and Filter --}}
                            <div class="flex flex-wrap items-center gap-4">
                                <div class="flex items-center gap-2 p-1 rounded-lg">
                                    <x-filament::icon-button 
                                        size="md" 
                                        icon="heroicon-o-queue-list"
                                        wire:click="setViewType('table')" 
                                        :color="$viewType === 'table' ? 'primary' : 'gray'"
                                    >
                                        <span class="sr-only">Table View</span>
                                    </x-filament::icon-button>
                                    <x-filament::icon-button 
                                        size="md" 
                                        icon="heroicon-o-squares-2x2"
                                        wire:click="setViewType('card')" 
                                        :color="$viewType === 'card' ? 'primary' : 'gray'"
                                    >
                                        <span class="sr-only">Card View</span>
                                    </x-filament::icon-button>
                                </div>

                                <x-filament::input.wrapper>
                                    <x-filament::input.select 
                                        wire:model.live="filterType"
                                        class="rounded border-gray-300 shadow-sm"
                                    >
                                        <option value="all">All Filters</option>
                                        <option value="status">Filter by Status</option>
                                        <option value="priority">Filter by Priority</option>
                                        <option value="assigned">Filter by Assigned To</option>
                                        <option value="status-priority">Filter by Status & Priority</option>
                                        <option value="status-assigned">Filter by Status & Assigned</option>
                                    </x-filament::input.select>
                                </x-filament::input.wrapper>

                                @if($filterType === 'status' || $filterType === 'status-priority' || $filterType === 'status-assigned')
                                    <x-filament::input.wrapper>
                                        <x-filament::input.select
                                            wire:model.live="ticketStatus"
                                            class="rounded border-gray-300 shadow-sm"
                                        >
                                            <option value="">Select Status</option>
                                            <option value="open">Open</option>
                                            <option value="in_progress">In Progress</option>
                                            <option value="resolved">Resolved</option>
                                            <option value="closed">Closed</option>
                                        </x-filament::input.select>
                                    </x-filament::input.wrapper>
                                @endif

                                @if($filterType === 'priority' || $filterType === 'status-priority')
                                    <x-filament::input.wrapper>
                                        <x-filament::input.select
                                            wire:model.live="priority"
                                            class="rounded border-gray-300 shadow-sm"
                                        >
                                            <option value="">Select Priority</option>
                                            <option value="low">Low</option>
                                            <option value="medium">Medium</option>
                                            <option value="high">High</option>
                                        </x-filament::input.select>
                                    </x-filament::input.wrapper>
                                @endif

                                @if($filterType === 'assigned' || $filterType === 'status-assigned')
                                    <x-filament::input.wrapper>
                                        <x-filament::input.select
                                            wire:model.live="assignedTo"
                                            class="rounded border-gray-300 shadow-sm"
                                        >
                                            <option value="">Select User</option>
                                            @foreach($users as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        </x-filament::input.select>
                                    </x-filament::input.wrapper>
                                @endif

                                @if($filterType !== 'all')
                                    <x-filament::button
                                        wire:click="resetFilters"
                                        color="danger"
                                        size="sm"
                                        icon="heroicon-o-x-mark"
                                        class="self-center"
                                    >
                                        Reset Filters
                                    </x-filament::button>
                                @endif
                            </div>

                            {{-- Search and Create Buttons --}}
                            <x-filament::icon-button 
                                icon="heroicon-m-magnifying-glass" 
                                @click="isSearchOpen = true"
                                label="Search" 
                                tooltip="Open Search" 
                            />

                            <x-filament::button
                                tag="a"
                                href="{{ route('filament.app.resources.tickets.create') }}"
                                icon="heroicon-m-plus"
                                tooltip="Create New Ticket"
                            >
                                New
                            </x-filament::button>
                        </div>
                    </div>
                </div>

                {{-- Search Bar --}}
                <div x-show="isSearchOpen" x-transition class="w-full">
                    <div class="flex items-center gap-3">
                        <x-filament::input.wrapper class="flex-1">
                            <x-slot name="prefix">
                                <span class="text-gray-500">Search by: Ticket Number, Title, Description</span>
                            </x-slot>

                            <x-filament::input
                                type="text"
                                wire:model.live.debounce.500ms="search"
                                placeholder="Search tickets..."
                                class="w-full"
                                @keydown.escape.window="isSearchOpen = false"
                            />

                            <x-slot name="suffix">
                                <button type="button" @click="isSearchOpen = false">
                                    <x-heroicon-m-x-mark class="w-5 h-5" />
                                </button>
                            </x-slot>
                        </x-filament::input.wrapper>
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>

    @if($viewType === 'card')
        {{-- Card View Implementation --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($tickets as $ticket)
                <x-filament::section :class="in_array((string) $ticket->id, $selected) ? 'ring-2 ring-primary-500' : ''">
                    <div class="p-4">
                        <div class="absolute top-2 left-2">
                            <x-filament::input.checkbox
                                wire:model.live="selected"
                                value="{{ $ticket->id }}"
                                wire:key="card-checkbox-{{ $ticket->id }}"
                            />
                        </div>

                        <div class="flex justify-between mb-2 mt-6">
                            <x-filament::badge color="secondary">
                                {{ $ticket->ticket_number }}
                            </x-filament::badge>
                            <x-filament::badge :color="match($ticket->ticket_status) {
                                'open' => 'success',
                                'in_progress' => 'warning',
                                'resolved' => 'info',
                                'closed' => 'secondary',
                                default => 'gray'
                            }">
                                {{ ucfirst($ticket->ticket_status) }}
                            </x-filament::badge>
                        </div>

                        <h4 class="text-lg font-semibold">{{ $ticket->title }}</h4>

                        <div class="mt-2">
                            <x-filament::badge :color="match($ticket->priority) {
                                'high' => 'danger',
                                'medium' => 'warning',
                                'low' => 'success',
                                default => 'gray'
                            }">
                                Priority: {{ ucfirst($ticket->priority) }}
                            </x-filament::badge>
                        </div>

                        <div class="mt-3">
                            <p class="text-sm text-gray-600">
                                <span class="font-medium">Assigned To:</span>
                                {{ $ticket->assignedUser?->name ?? 'Unassigned' }}
                            </p>
                            <p class="text-sm text-gray-600">
                                <span class="font-medium">Created:</span>
                                {{ $ticket->created_at->format('M d, Y H:i') }}
                            </p>
                        </div>

                        <div class="mt-4 flex justify-end gap-2">
                            @unless(auth()->user()->hasRole('professor'))
                                <x-filament::button
                                    wire:click="editTicket({{ $ticket->id }})"
                                    size="sm"
                                    color="warning"
                                >
                                    Edit
                                </x-filament::button>
                            @endunless
                        </div>
                    </div>
                </x-filament::section>
            @empty
                <div class="col-span-full py-10 text-center">
                    <p class="text-lg">No tickets found matching your filter criteria.</p>
                    @if($filterType != 'all')
                        <x-filament::button 
                            wire:click="$set('filterType', 'all')"
                            class="mt-2"
                        >
                            Show all tickets
                        </x-filament::button>
                    @endif
                </div>
            @endforelse
        </div>
    @else
        {{-- Table View Implementation --}}
        <div class="overflow-x-auto rounded-lg">
            <x-filament-tables::table class="min-w-full divide-y col-span-full">
                <thead class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                    <x-filament-tables::row>
                        <x-filament-tables::header-cell scope="col" class="w-12 px-4 py-3">
                            <x-filament::input.checkbox 
                                wire:model.live="selectAll" 
                                @click="toggleAllCheckboxes()"
                                id="select-all-table" 
                                class="rounded" 
                            />
                        </x-filament-tables::header-cell>

                        @if(count($selected) >= 2)
                            <x-filament-tables::header-cell scope="col" colspan="7" class="px-3 py-3">
                                <div class="flex items-center gap-4">
                                    <span class="font-medium text-sm">{{ count($selected) }} selected</span>

                                    <x-filament::button wire:click="$set('selected', [])" size="sm" color="danger" outline>
                                        Clear
                                    </x-filament::button>

                                    <x-filament::input.wrapper>
                                        <x-filament::input.select wire:model.live="bulkAction" class="rounded border-gray-300 shadow-sm">
                                            <option value="">Bulk Actions</option>
                                            <option value="delete">Delete Selected</option>
                                            <option value="edit">Edit Selected</option>
                                            <option value="export">Export Selected</option>
                                        </x-filament::input.select>
                                    </x-filament::input.wrapper>

                                    <x-filament::button wire:click="executeBulkAction" :disabled="empty($bulkAction)">
                                        Apply
                                    </x-filament::button>
                                </div>
                            </x-filament-tables::header-cell>
                        @else
                            <x-filament-tables::header-cell scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                Ticket Information
                            </x-filament-tables::header-cell>
                            <x-filament-tables::header-cell scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                Type
                            </x-filament-tables::header-cell>
                            <x-filament-tables::header-cell scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                Status
                            </x-filament-tables::header-cell>
                            <x-filament-tables::header-cell scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                Priority
                            </x-filament-tables::header-cell>
                            <x-filament-tables::header-cell scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                Assigned To
                            </x-filament-tables::header-cell>
                            <x-filament-tables::header-cell scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                Created At
                            </x-filament-tables::header-cell>
                        @endif

                        <x-filament-tables::header-cell scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">
                            Actions
                        </x-filament-tables::header-cell>
                    </x-filament-tables::row>
                </thead>
                <tbody class="divide-y">
                    @forelse($tickets as $ticket)
                        <x-filament-tables::row :class="in_array((string) $ticket->id, $selected) ? 'bg-primary-50' : ''">
                            <x-filament-tables::cell class="w-12 px-4 py-4 whitespace-nowrap">
                                <x-filament::input.checkbox 
                                    wire:model.live="selected"
                                    value="{{ $ticket->id }}"
                                    wire:key="table-checkbox-{{ $ticket->id }}"
                                    :class="in_array((string) $ticket->id, $selected) ? 'bg-primary-500' : ''"
                                />
                            </x-filament-tables::cell>
                            <x-filament-tables::cell class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <div class="text-sm font-medium text-gray-900">{{ $ticket->ticket_number }}</div>
                                    <div class="text-sm text-gray-500">{{ $ticket->title }}</div>
                                </div>
                            </x-filament-tables::cell>
                            <x-filament-tables::cell class="px-6 py-4 whitespace-nowrap">
                                <x-filament::badge color="secondary">
                                    <span class="capitalize">{{ $ticket->ticket_type }}</span>
                                </x-filament::badge>
                            </x-filament-tables::cell>
                            <x-filament-tables::cell class="px-6 py-4 whitespace-nowrap">
                                <x-filament::badge :color="match($ticket->ticket_status) {
                                    'open' => 'success',
                                    'in_progress' => 'warning',
                                    'resolved' => 'info',
                                    'closed' => 'secondary',
                                    default => 'gray'
                                }">
                                    <span class="capitalize">{{ $ticket->ticket_status }}</span>
                                </x-filament::badge>
                            </x-filament-tables::cell>
                            <x-filament-tables::cell class="px-6 py-4 whitespace-nowrap">
                                <x-filament::badge :color="match($ticket->priority) {
                                    'high' => 'danger',
                                    'medium' => 'warning',
                                    'low' => 'success',
                                    default => 'gray'
                                }">
                                    <span class="capitalize">{{ $ticket->priority }}</span>
                                </x-filament::badge>
                            </x-filament-tables::cell>
                            <x-filament-tables::cell class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $ticket->assignedUser?->name ?? 'Unassigned' }}</div>
                            </x-filament-tables::cell>
                            <x-filament-tables::cell class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $ticket->created_at->format('M d, Y H:i') }}</div>
                            </x-filament-tables::cell>
                            <x-filament-tables::cell class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    @unless(auth()->user()->hasRole('professor'))
                                        <x-filament::button
                                            size="sm"
                                            color="warning"
                                            wire:click="editTicket({{ $ticket->id }})"
                                            class="hover:underline"
                                        >
                                            Edit
                                        </x-filament::button>

                                        <x-filament::button
                                            size="sm"
                                            color="danger"
                                            wire:click="confirmTicketDeletion({{ $ticket->id }})"
                                            class="hover:underline"
                                        >
                                            Delete
                                        </x-filament::button>
                                    @endunless
                                </div>
                            </x-filament-tables::cell>
                        </x-filament-tables::row>
                    @empty
                        <tr>
                            <x-filament-tables::cell colspan="8" class="px-6 py-10 text-center">
                                <p class="text-lg">No tickets found matching your filter criteria.</p>
                                @if($filterType != 'all')
                                    <x-filament::button 
                                        wire:click="$set('filterType', 'all')"
                                        class="mt-2 text-primary-600 hover:text-primary-900 hover:underline"
                                    >
                                        Show all tickets
                                    </x-filament::button>
                                @endif
                            </x-filament-tables::cell>
                        </tr>
                    @endforelse
                </tbody>
            </x-filament-tables::table>
        </div>
    @endif

    {{-- Pagination Controls --}}
    <div class="mt-6">
        <div class="flex justify-between items-center">
            <div>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="perPage" class="rounded border-gray-300 shadow-sm">
                        <option value="10">10 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                        <option value="100">100 per page</option>
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            <div class="fi-ta-pagination px-3 py-3 sm:px-6">
                {{ $tickets->links() }}
            </div>
        </div>
    </div>

    {{-- Custom Notification Component --}}
    <div
        x-data="{ show: false, message: '', type: '' }"
        @notify.window="show = true; message = $event.detail.message; type = $event.detail.type; setTimeout(() => { show = false }, 3000)"
        class="fixed bottom-4 right-4 z-50"
    >
        <div
            x-show="show"
            x-transition
            :class="{
                'bg-green-500': type === 'success',
                'bg-red-500': type === 'error',
                'bg-yellow-500': type === 'warning'
            }"
            class="rounded-lg p-4 text-white shadow-lg"
        >
            <p x-text="message"></p>
        </div>
    </div>
</div>