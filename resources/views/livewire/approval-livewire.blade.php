<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">

            <!-- Flash messages -->
            @if (session()->has('message'))
                <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-200 dark:text-green-800"
                    role="alert">
                    {{ session('message') }}
                </div>
            @endif

            @if (session()->has('warning'))
                <div class="p-4 mb-4 text-sm text-yellow-700 bg-yellow-100 rounded-lg dark:bg-yellow-200 dark:text-yellow-800"
                    role="alert">
                    {{ session('warning') }}
                </div>
            @endif

            <div class="p-6 text-gray-900 dark:text-gray-100">
                <h2 class="text-lg font-medium mb-4">Pending Ticket Approvals</h2>

                @if (count($tickets) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Ticket #</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Type</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Title</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Requested By</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Date</th>


                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                @foreach ($tickets as $ticket)
                                    <tr>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $ticket->ticket_number }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <span
                                                class="px-2 py-1 font-semibold text-xs rounded-full 
                                                {{ $ticket->ticket_type === 'asset_request' ? 'bg-blue-100 text-blue-800 dark:bg-blue-200 dark:text-blue-800' : 'bg-green-100 text-green-800 dark:bg-green-200 dark:text-green-800' }}">
                                                {{ ucfirst(str_replace('_', ' ', $ticket->ticket_type)) }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $ticket->title }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $ticket->creator ? $ticket->creator->name : 'Unknown' }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $ticket->created_at->format('M d, Y H:i') }}
                                        </td>
                                        {{-- <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            <div>
                                                @if ($ticket->ticket_type === 'asset_request' && $ticket->asset)
                                                    <span class="block">Asset: {{ $ticket->asset->name }}</span>
                                                @endif
                                                @if ($ticket->ticket_type === 'classroom_request' && $ticket->classroom)
                                                    <span class="block">Classroom:
                                                        {{ $ticket->classroom->name }}</span>
                                                    @if ($ticket->start_time && $ticket->end_time)
                                                        <span class="block">Time:
                                                            {{ $ticket->start_time->format('M d, Y H:i') }} -
                                                            {{ $ticket->end_time->format('H:i') }}</span>
                                                    @endif
                                                @endif
                                            </div>
                                        </td> --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <button wire:click="approveTicket({{ $ticket->id }})"
                                                    wire:loading.attr="disabled"
                                                    class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs transition-colors duration-150">
                                                    <span wire:loading.remove
                                                        wire:target="approveTicket({{ $ticket->id }})">Approve</span>
                                                    <span wire:loading
                                                        wire:target="approveTicket({{ $ticket->id }})">Processing...</span>
                                                </button>
                                                <button wire:click="cancelTicket({{ $ticket->id }})"
                                                    wire:loading.attr="disabled"
                                                    class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs transition-colors duration-150">
                                                    <span wire:loading.remove
                                                        wire:target="cancelTicket({{ $ticket->id }})">Decline</span>
                                                    <span wire:loading
                                                        wire:target="cancelTicket({{ $ticket->id }})">Processing...</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-md text-center">
                        <p class="text-gray-600 dark:text-gray-300">No pending requests found.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
