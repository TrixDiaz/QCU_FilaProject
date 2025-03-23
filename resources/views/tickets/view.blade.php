<div class="p-4 space-y-4">
    <div>
        <h3 class="text-lg font-medium">Ticket Details</h3>
        <p class="text-sm text-gray-500">{{ $ticket->ticket_number }}</p>
    </div>

    <div class="space-y-2">
        <div>
            <span class="font-medium">Title:</span>
            <p>{{ $ticket->title }}</p>
        </div>
        
        <div>
            <span class="font-medium">Description:</span>
            <div class="prose dark:prose-invert">
                {!! is_array($ticket->description) ? Str::markdown(implode("\n", $ticket->description)) : Str::markdown($ticket->description) !!}
            </div>
        </div>

        <div>
            <span class="font-medium">Priority:</span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->priority === 'high' ? 'bg-red-100 text-red-800' : ($ticket->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                {{ ucfirst($ticket->priority) }}
            </span>
        </div>

        <div>
            <span class="font-medium">Status:</span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ match($ticket->ticket_status) {
                'open' => 'bg-blue-100 text-blue-800',
                'in_progress' => 'bg-yellow-100 text-yellow-800',
                'closed' => 'bg-green-100 text-green-800',
                'archived' => 'bg-gray-100 text-gray-800',
                default => 'bg-blue-100 text-blue-800'
            } }}">
                {{ ucfirst($ticket->ticket_status) }}
            </span>
        </div>

        <div>
            <span class="font-medium">Created:</span>
            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $ticket->created_at->format('M d, Y H:i') }}</p>
        </div>

        <div>
            <span class="font-medium">Assigned To:</span>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                {{ $ticket->assignedTo?->name ?? 'Unassigned' }}
            </p>
        </div>
    </div>
</div>