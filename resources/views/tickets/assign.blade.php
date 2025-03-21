<div class="p-4">
    <div class="space-y-4">
        <div class="border-l-4 border-blue-500 p-4 bg-blue-50 dark:bg-blue-900">
            <div class="flex">
                <div class="ml-3">
                    <p class="text-sm text-blue-700 dark:text-blue-200">
                        Ticket #{{ $ticket->ticket_number }}<br>
                        {{ $ticket->title }}
                    </p>
                </div>
            </div>
        </div>

        @if($ticket->assigned_to)
            <div class="text-sm text-gray-600 dark:text-gray-300">
                Currently assigned to: {{ $ticket->assignedTo->name }}
            </div>
        @endif
    </div>
</div>