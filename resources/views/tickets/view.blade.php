<div class="p-4 space-y-4">
    <div class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <h3 class="text-sm font-medium text-gray-500">Ticket Number</h3>
                <p class="mt-1 text-sm text-gray-900">{{ $ticket->ticket_number }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500">Status</h3>
                <p class="mt-1 text-sm text-gray-900">{{ ucfirst($ticket->ticket_status) }}</p>
            </div>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-500">Title</h3>
            <p class="mt-1 text-sm text-gray-900">{{ $ticket->title }}</p>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-500">Description</h3>
            <div class="mt-1 text-sm text-gray-900 prose max-w-none">
                {!! nl2br(e($ticket->description)) !!}
            </div>
        </div>

        @if($ticket->type === 'classroom_request')
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Classroom</h3>
                    <p class="mt-1 text-sm text-gray-900">{{ $ticket->classroom?->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Section</h3>
                    <p class="mt-1 text-sm text-gray-900">{{ $ticket->section?->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Start Time</h3>
                    <p class="mt-1 text-sm text-gray-900">{{ $ticket->start_time ? $ticket->start_time->format('M d, Y H:i') : 'N/A' }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">End Time</h3>
                    <p class="mt-1 text-sm text-gray-900">{{ $ticket->end_time ? $ticket->end_time->format('M d, Y H:i') : 'N/A' }}</p>
                </div>
            </div>
        @endif

        @if($ticket->asset)
            <div class="mb-4">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Asset:</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ $ticket->asset->name }}
                    @if($ticket->asset->serial_number)
                        <span class="text-gray-500">(SN: {{ $ticket->asset->serial_number }})</span>
                    @endif
                </p>
            </div>
        @endif

        <div class="grid grid-cols-2 gap-4">
            <div>
                <h3 class="text-sm font-medium text-gray-500">Created By</h3>
                <p class="mt-1 text-sm text-gray-900">{{ $ticket->creator?->name ?? 'N/A' }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500">Assigned To</h3>
                <p class="mt-1 text-sm text-gray-900">{{ $ticket->assignedTo?->name ?? 'Unassigned' }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500">Created At</h3>
                <p class="mt-1 text-sm text-gray-900">{{ $ticket->created_at->format('M d, Y H:i') }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500">Updated At</h3>
                <p class="mt-1 text-sm text-gray-900">{{ $ticket->updated_at->format('M d, Y H:i') }}</p>
            </div>
        </div>
    </div>
</div>