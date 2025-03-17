<div>
    <!-- Search Bar -->
    <div class="bg-white p-4 rounded shadow-sm mb-4">
        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search Classrooms</label>
        <input type="text" wire:model.live.debounce.300ms="search" id="search"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            placeholder="Search by classroom name, building or floor...">
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded shadow-sm mb-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="building" class="block text-sm font-medium text-gray-700">Building</label>
                <select wire:model.live="selectedBuilding" id="building"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Buildings</option>
                    @foreach ($buildingCounts as $building)
                        <option value="{{ $building->id }}">{{ $building->name }} ({{ $building->classrooms_count }}
                            rooms)</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="floor" class="block text-sm font-medium text-gray-700">Floor</label>
                <select wire:model.live="selectedFloor" id="floor"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    {{ count($floors) ? '' : 'disabled' }}>
                    <option value="">All Floors</option>
                    @foreach ($floors as $floor)
                        <option value="{{ $floor }}">{{ $floor }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-2 p-1 rounded-lg mb-4">
        <x-filament::icon-button size="md" icon="heroicon-o-queue-list" wire:click="setViewType('table')"
            :color="$viewType === 'table' ? 'primary' : 'gray'">
            <span class="sr-only">Table View</span>
        </x-filament::icon-button>
        <x-filament::icon-button size="md" icon="heroicon-o-squares-2x2" wire:click="setViewType('card')"
            :color="$viewType === 'card' ? 'primary' : 'gray'">
            <span class="sr-only">Card View</span>
        </x-filament::icon-button>
    </div>

    @if ($classrooms->isEmpty())
        <div class="text-center p-4 bg-white rounded shadow-sm">
            No classrooms found.
        </div>
    @else
        @if ($viewType === 'table')
            <!-- Table View -->
            <div class="overflow-x-auto col-span-full bg-white rounded shadow-sm">
                <table class="min-w-full w-full">
                    <thead>
                        <tr>
                            <th
                                class="px-6 py-3 border-b-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Name
                            </th>
                            <th
                                class="px-6 py-3 border-b-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Building
                            </th>
                            <th
                                class="px-6 py-3 border-b-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Floor
                            </th>
                            <th
                                class="px-6 py-3 border-b-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th
                                class="px-6 py-3 border-b-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($classrooms as $classroom)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium">{{ $classroom->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm">{{ $classroom->building->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm">{{ $classroom->floor ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $classroom->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $classroom->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-filament::button size="sm"
                                        wire:click="viewClassroomDetails({{ $classroom->id }})" tag="button">
                                        View Details
                                    </x-filament::button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <!-- Card View (Enhanced) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach ($classrooms as $classroom)
                    <div class="bg-white p-4 rounded shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-lg font-semibold">{{ $classroom->name }}</h3>
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $classroom->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $classroom->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="text-sm space-y-1">
                            <p><span class="font-medium">Building:</span> {{ $classroom->building->name ?? 'N/A' }}</p>
                            <p><span class="font-medium">Floor:</span> {{ $classroom->floor ?? 'N/A' }}</p>

                            @if ($classroom->capacity)
                                <p><span class="font-medium">Capacity:</span> {{ $classroom->capacity }} seats</p>
                            @endif
                        </div>

                        <div class="mt-3 flex justify-end">
                            <x-filament::button size="sm" wire:click="viewClassroomDetails({{ $classroom->id }})"
                                tag="button">
                                View Details
                            </x-filament::button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mt-4">
            {{ $classrooms->links() }}
        </div>
    @endif

    <!-- Classroom Details Modal -->
    @if ($showingClassroomDetails && $currentClassroom)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50 flex items-center justify-center">
            <div
                class="bg-white rounded-lg shadow-xl transform transition-all max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <div
                    class="bg-gray-50 px-4 py-3 border-b border-gray-200 sm:px-6 flex justify-between items-center sticky top-0 z-10">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        {{ $currentClassroom->name }} Details
                    </h3>
                    <button wire:click="closeClassroomDetails" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="px-4 py-4 sm:px-6">
                    <div class="bg-white p-4 rounded-lg shadow-sm mb-4">
                        <h4 class="text-lg font-medium mb-2">Classroom Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="mb-1"><span class="font-medium">Building:</span>
                                    {{ $currentClassroom->building->name ?? 'N/A' }}</p>
                                <p class="mb-1"><span class="font-medium">Floor:</span>
                                    {{ $currentClassroom->floor ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="mb-1"><span class="font-medium">Capacity:</span>
                                    {{ $currentClassroom->capacity ?? 'N/A' }} seats</p>
                                <p class="mb-1"><span class="font-medium">Status:</span>
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $currentClassroom->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $currentClassroom->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-4 rounded-lg shadow-sm">
                        <h4 class="text-lg font-medium mb-2">Schedule</h4>

                        @php
                            $hasSchedules = false;
                            foreach ($schedulesByDay as $day => $schedules) {
                                if (count($schedules) > 0) {
                                    $hasSchedules = true;
                                    break;
                                }
                            }
                        @endphp

                        @if (!$hasSchedules)
                            <p class="text-gray-500 italic">No schedules found for this classroom.</p>
                        @else
                            <div class="space-y-4">
                                @foreach ($schedulesByDay as $day => $schedules)
                                    @if (count($schedules) > 0)
                                        <div class="border-b pb-4">
                                            <h5 class="text-md font-medium mb-2 bg-gray-50 p-2 rounded">
                                                {{ $day }}</h5>
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full divide-y divide-gray-200">
                                                    <thead>
                                                        <tr>
                                                            <th
                                                                class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                Time</th>
                                                            <th
                                                                class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                Section</th>
                                                            <th
                                                                class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                Subject</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-white divide-y divide-gray-200">
                                                        @foreach ($schedules as $schedule)
                                                            <tr>
                                                                <td class="px-3 py-2 whitespace-nowrap text-sm">
                                                                    {{ date('h:i A', strtotime($schedule->start_time)) }}
                                                                    -
                                                                    {{ date('h:i A', strtotime($schedule->end_time)) }}
                                                                </td>
                                                                <td class="px-3 py-2 whitespace-nowrap text-sm">
                                                                    {{ $schedule->section->name ?? 'N/A' }}
                                                                </td>
                                                                <td class="px-3 py-2 whitespace-nowrap text-sm">
                                                                    {{ $schedule->subject ?? 'N/A' }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                    <x-filament::button wire:click="closeClassroomDetails" tag="button">
                        Close
                    </x-filament::button>
                </div>
            </div>
        </div>
    @endif
</div>
