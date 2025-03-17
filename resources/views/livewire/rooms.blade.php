<div>
    <!-- Search Bar -->
    <div class="p-4 rounded shadow-sm">
        <label for="search" class="block text-sm font-medium mb-1">Search Classrooms</label>
        <x-filament::input.wrapper>
            <x-filament::input type="text" wire:model.live.debounce.300ms="search" id="search"
                placeholder="Search by classroom name, building or floor..." />
        </x-filament::input.wrapper>
    </div>

    <!-- Filters -->
    <div class="p-4 rounded shadow-sm mb-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="building" class="block text-sm font-medium">Building</label>
                <x-filament::input.wrapper>
                    <x-filament::input.select id="building" wire:model.live="selectedBuilding">
                        <option value="">All Buildings</option>
                        @foreach ($buildingCounts as $building)
                            <option value="{{ $building->id }}">{{ $building->name }} ({{ $building->classrooms_count }}
                                rooms)</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            <div>
                <label for="floor" class="block text-sm font-medium">Floor</label>
                <x-filament::input.wrapper>
                    <x-filament::input.select id="floor" wire:model.live="selectedFloor" :disabled="!count($floors)">
                        <option value="">All Floors</option>
                        @foreach ($floors as $floor)
                            <option value="{{ $floor }}">{{ $floor }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
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
        <div class="text-center p-4 rounded shadow-sm">
            No classrooms found.
        </div>
    @else
        @if ($viewType === 'table')
            <!-- Table View -->
            <div class="overflow-x-auto col-span-full rounded shadow-sm">
                <x-filament-tables::table class="min-w-full w-full">
                    <thead>
                        <x-filament-tables::row>
                            <x-filament-tables::header-cell
                                class="px-6 py-3 border-b-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Name
                            </x-filament-tables::header-cell>
                            <x-filament-tables::header-cell
                                class="px-6 py-3 border-b-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Building
                            </x-filament-tables::header-cell>
                            <x-filament-tables::header-cell
                                class="px-6 py-3 border-b-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Floor
                            </x-filament-tables::header-cell>
                            <x-filament-tables::header-cell
                                class="px-6 py-3 border-b-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </x-filament-tables::header-cell>
                            <x-filament-tables::header-cell
                                class="px-6 py-3 border-b-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </x-filament-tables::header-cell>
                        </x-filament-tables::row>
                    </thead>
                    <tbody class="">
                        @foreach ($classrooms as $classroom)
                            <x-filament-tables::row>
                                <x-filament-tables::cell class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium">{{ $classroom->name }}</div>
                                </x-filament-tables::cell>
                                <x-filament-tables::cell class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm">{{ $classroom->building->name ?? 'N/A' }}</div>
                                </x-filament-tables::cell>
                                <x-filament-tables::cell class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm">{{ $classroom->floor ?? 'N/A' }}</div>
                                </x-filament-tables::cell>
                                <x-filament-tables::cell class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $classroom->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $classroom->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </x-filament-tables::cell>
                                <x-filament-tables::cell class="px-6 py-4 whitespace-nowrap">
                                    <x-filament::button size="sm"
                                        wire:click="viewClassroomDetails({{ $classroom->id }})" tag="button">
                                        View Details
                                    </x-filament::button>
                                </x-filament-tables::cell>
                            </x-filament-tables::row>
                        @endforeach
                    </tbody>
                </x-filament-tables::table>
            </div>
        @else
            <!-- Card View (Enhanced) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach ($classrooms as $classroom)
                    <x-filament::section>
                        <div class="rounded shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex justify-between items-center mb-2">
                                <h3 class="text-lg font-semibold">{{ $classroom->name }}</h3>
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $classroom->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $classroom->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div class="text-sm space-y-1">
                                <p><span class="font-medium">Building:</span> {{ $classroom->building->name ?? 'N/A' }}
                                </p>
                                <p><span class="font-medium">Floor:</span> {{ $classroom->floor ?? 'N/A' }}</p>

                                @if ($classroom->capacity)
                                    <p><span class="font-medium">Capacity:</span> {{ $classroom->capacity }} seats</p>
                                @endif
                            </div>

                            <div class="mt-3 flex justify-end">
                                <x-filament::button size="sm"
                                    wire:click="viewClassroomDetails({{ $classroom->id }})" tag="button">
                                    View Details
                                </x-filament::button>
                            </div>
                        </div>
                    </x-filament::section>
                @endforeach
            </div>
        @endif

        <div class="mt-4">
            {{ $classrooms->links() }}
        </div>
    @endif

    <!-- Classroom Details Modal -->
    @if ($showingClassroomDetails && $currentClassroom)
        <x-filament::section class="dark:bg-gray-900">
            <div class="fixed inset-0 transition-opacity z-50 flex items-center justify-center">
                <div
                    class="rounded-lg shadow-xl transform transition-all max-w-4xl w-full max-h-[90vh] overflow-y-auto dark:bg-gray-800">
                    <div
                        class="px-4 py-3 sm:px-6 flex justify-between items-center sticky top-0 z-10 bg-white dark:bg-gray-800 dark:text-gray-100">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                            {{ $currentClassroom->name }} Details
                        </h3>
                        <button wire:click="closeClassroomDetails"
                            class="text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-gray-100">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="px-4 py-4 sm:px-6 dark:bg-gray-800">
                        <div class="p-4 rounded-lg shadow-sm mb-4 bg-white dark:bg-gray-700 dark:text-gray-100">
                            <h4 class="text-lg font-medium mb-2 dark:text-gray-100">Classroom Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="mb-1"><span class="font-medium dark:text-gray-200">Building:</span>
                                        <span
                                            class="dark:text-gray-300">{{ $currentClassroom->building->name ?? 'N/A' }}</span>
                                    </p>
                                    <p class="mb-1"><span class="font-medium dark:text-gray-200">Floor:</span>
                                        <span class="dark:text-gray-300">{{ $currentClassroom->floor ?? 'N/A' }}</span>
                                    </p>
                                </div>
                                <div>
                                    <p class="mb-1"><span class="font-medium dark:text-gray-200">Capacity:</span>
                                        <span class="dark:text-gray-300">{{ $currentClassroom->capacity ?? 'N/A' }}
                                            seats</span>
                                    </p>
                                    <p class="mb-1"><span class="font-medium dark:text-gray-200">Status:</span>
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $currentClassroom->is_active ? 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100' }}">
                                            {{ $currentClassroom->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-gray-700 p-4 rounded-lg shadow-sm">
                            <h4 class="text-lg font-medium mb-2 dark:text-gray-100">Schedule</h4>

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
                                <p class="text-gray-500 dark:text-gray-400 italic">No schedules found for this
                                    classroom.</p>
                            @else
                                <div class="space-y-4">
                                    @foreach ($schedulesByDay as $day => $schedules)
                                        @if (count($schedules) > 0)
                                            <div class="border-b dark:border-gray-600 pb-4">
                                                <h5
                                                    class="text-md font-medium mb-2 bg-gray-50 dark:bg-gray-600 p-2 rounded dark:text-gray-200">
                                                    {{ $day }}</h5>
                                                <div class="overflow-x-auto">
                                                    <x-filament-tables::table
                                                        class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                                        <thead>
                                                            <x-filament-tables::row class="dark:bg-gray-800">
                                                                <x-filament-tables::header-cell
                                                                    class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                                    Time</x-filament-tables::header-cell>
                                                                <x-filament-tables::header-cell
                                                                    class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                                    Section</x-filament-tables::header-cell>
                                                                <x-filament-tables::header-cell
                                                                    class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                                    Subject</x-filament-tables::header-cell>
                                                            </x-filament-tables::row>
                                                        </thead>
                                                        <tbody
                                                            class="bg-white dark:bg-gray-700 divide-y divide-gray-200 dark:divide-gray-600">
                                                            @foreach ($schedules as $schedule)
                                                                <x-filament-tables::row
                                                                    class="dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                                                    <x-filament-tables::cell
                                                                        class="px-3 py-2 whitespace-nowrap text-sm dark:text-gray-300">
                                                                        {{ date('h:i A', strtotime($schedule->start_time)) }}
                                                                        -
                                                                        {{ date('h:i A', strtotime($schedule->end_time)) }}
                                                                    </x-filament-tables::cell>
                                                                    <x-filament-tables::cell
                                                                        class="px-3 py-2 whitespace-nowrap text-sm dark:text-gray-300">
                                                                        {{ $schedule->section->name ?? 'N/A' }}
                                                                    </x-filament-tables::cell>
                                                                    <x-filament-tables::cell
                                                                        class="px-3 py-2 whitespace-nowrap text-sm dark:text-gray-300">
                                                                        {{ $schedule->subject ?? 'N/A' }}
                                                                    </x-filament-tables::cell>
                                                                </x-filament-tables::row>
                                                            @endforeach
                                                        </tbody>
                                                    </x-filament-tables::table>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <div
                        class="bg-gray-50 dark:bg-gray-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200 dark:border-gray-700">
                        <x-filament::button wire:click="closeClassroomDetails" tag="button"
                            class="dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
                            Close
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </x-filament::section>
    @endif
</div>
