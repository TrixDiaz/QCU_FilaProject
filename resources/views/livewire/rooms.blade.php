<div>
    <style>
        /* Add these styles to your CSS file */

        .modal-schedule-container {
            max-height: 60vh;
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: thin;
            scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
        }

        .modal-schedule-container::-webkit-scrollbar {
            width: 6px;
        }

        .modal-schedule-container::-webkit-scrollbar-track {
            background: transparent;
        }

        .modal-schedule-container::-webkit-scrollbar-thumb {
            background-color: rgba(156, 163, 175, 0.5);
            border-radius: 20px;
        }

        .day-header-sticky {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .table-container {
            max-height: 200px;
            overflow-y: auto;
        }

        .table-container-new {
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
    {{-- Create New Schedule --}}
    <div class="flex justify-end items-center gap-2 my-4">
        <x-filament::button href="{{ route('filament.app.resources.subjects.create') }}" tag="a" size="sm"
            color="primary">
            Create New Schedule
        </x-filament::button>

    </div>
    <!-- Search Bar -->
    <x-filament::section>
        <div class="rounded shadow-sm">
            <label for="search" class="block text-sm font-medium mb-2">Search Classrooms</label>
            <x-filament::input.wrapper>
                <x-filament::input type="text" wire:model.live.debounce.300ms="search" id="search"
                    placeholder="Search by classroom name, building or floor..." />
            </x-filament::input.wrapper>
        </div>
    </x-filament::section>

    <!-- Filters -->
    <x-filament::section class="my-4">
        <div class="rounded shadow-sm mb-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label for="building" class="block text-sm font-medium mb-2">Building</label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select id="building" wire:model.live="selectedBuilding">
                            <option value="">All Buildings</option>
                            @foreach ($buildingCounts as $building)
                                <option value="{{ $building->id }}">{{ $building->name }}
                                    ({{ $building->classrooms_count }}
                                    rooms)
                                </option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                <div>
                    <label for="floor" class="block text-sm font-medium mb-2">Floor</label>
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
    </x-filament::section>


    <div class="flex items-center gap-2 p-1 rounded-lg mb-4">
        <p class="capitalize">{{ $viewType }} Layout</p>
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
            <x-filament::section>
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
                                    <x-filament-tables::cell class="px-6 py-4 whitespace-nowrap"><span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $classroom->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $classroom->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </x-filament-tables::cell>
                                    <x-filament-tables::cell class="px-6 py-4 whitespace-nowrap gap-2">
                                        <x-filament::button size="sm"
                                            wire:click="viewClassroomDetails({{ $classroom->id }})" tag="button">
                                            View Details
                                        </x-filament::button>

                                        @if (auth()->user()->hasRole(['admin', 'super_admin', 'technician']))
                                            <x-filament::button size="sm"
                                                wire:click="viewClassroomAssets({{ $classroom->id }})" tag="button">
                                                View Assets
                                            </x-filament::button>
                                            <x-filament::button size="sm"
                                                href="{{ route('filament.app.resources.subjects.edit', $classroom) }}"
                                                tag="a">
                                                Edit Schedule
                                            </x-filament::button>
                                        @endif

                                    </x-filament-tables::cell>
                                </x-filament-tables::row>
                            @endforeach
                        </tbody>
                    </x-filament-tables::table>
                </div>
            </x-filament::section>
        @else
            <!-- Card View (Enhanced) -->
            <x-filament::section>
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
                                    <p><span class="font-medium">Building:</span>
                                        {{ $classroom->building->name ?? 'N/A' }}
                                    </p>
                                    <p><span class="font-medium">Floor:</span> {{ $classroom->floor ?? 'N/A' }}</p>

                                    @if ($classroom->capacity)
                                        <p><span class="font-medium">Capacity:</span> {{ $classroom->capacity }} seats
                                        </p>
                                    @endif
                                </div>

                                <div class="mt-3 flex justify-end gap-2">
                                    <x-filament::button size="xs"
                                        wire:click="viewClassroomDetails({{ $classroom->id }})" tag="button">
                                        View Details
                                    </x-filament::button>
                                    <x-filament::button size="sm"
                                        wire:click="viewClassroomAssets({{ $classroom->id }})" tag="button">
                                        View Assets
                                    </x-filament::button>
                                    <x-filament::button size="xs"
                                        href="{{ route('filament.app.resources.subjects.edit', $classroom) }}"
                                        tag="a">
                                        Edit Schedule
                                    </x-filament::button>
                                </div>
                            </div>
                        </x-filament::section>
                    @endforeach
                </div>
            </x-filament::section>
        @endif

        <div class="mt-4">
            {{ $classrooms->links() }}
        </div>
    @endif


    <!-- Classroom Assets Modal -->
    @if ($showingClassroomAssets && $currentClassroom)
        <x-filament::section class="dark:bg-gray-900">
            <div x-data="{
                show: false
            }" x-init="setTimeout(() => show = true, 50);" x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                class="fixed inset-0 transition-opacity z-50 flex items-center justify-center bg-gray-900 bg-opacity-50"
                style="display: none;">
                <div
                    class="rounded-lg shadow-xl transform transition-all w-full max-w-6xl max-h-[90vh] overflow-hidden dark:bg-gray-800 bg-white flex flex-col">
                    <!-- Header - Sticky -->
                    <div
                        class="px-4 py-3 sm:px-6 flex justify-between items-center sticky top-0 z-10 bg-white dark:bg-gray-800 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                            {{ $currentClassroom->name }} Assets
                        </h3>
                        <button @click="show = false; setTimeout(() => $wire.closeClassroomAssets(), 200)"
                            class="text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-gray-100 transition-colors duration-200">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Main Content - Scrollable -->
                    <div class="flex-1 overflow-y-auto relative">
                        <div class="px-4 py-4 sm:px-6 dark:bg-gray-800">
                            <!-- Asset Groups Table -->
                            <div x-show="show" x-transition:enter="transition ease-out delay-300 duration-300"
                                x-transition:enter-start="opacity-0 transform translate-y-4"
                                x-transition:enter-end="opacity-100 transform translate-y-0"
                                class="bg-white dark:bg-gray-700 p-4 rounded-lg shadow-sm">
                                <h4 class="text-lg font-medium mb-4 dark:text-gray-100">Asset Details</h4>

                                @if ($currentClassroom->assetGroups->isEmpty())
                                    <p class="text-gray-500 dark:text-gray-400 italic">No assets found for this
                                        classroom.</p>
                                @else
                                    <div
                                        class="overflow-y-auto h-[400px] rounded-md border border-gray-200 dark:border-gray-600 w-full">
                                        <table class="w-full divide-y divide-gray-200 dark:divide-gray-600">
                                            <thead class="bg-gray-50 dark:bg-gray-800 sticky top-0 z-10">
                                                <tr>
                                                    <th scope="col"
                                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                        Name
                                                    </th>
                                                    <th scope="col"
                                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                        Category
                                                    </th>
                                                    <th scope="col"
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                        Brand
                                                    </th>
                                                    <th scope="col"
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                        Serial No.
                                                    </th>
                                                    <th scope="col"
                                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                        Status
                                                    </th>
                                                    <th scope="col"
                                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                        Expiry Date
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody
                                                class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                                @foreach ($currentClassroom->assetGroups as $assetGroup)
                                                    <tr>
                                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                                            {{ $assetGroup->name }}
                                                        </td>
                                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                                            {{ $assetGroup->assets->category->name ?? 'N/A' }}
                                                        </td>
                                                        <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">
                                                            {{ $assetGroup->assets->brand->name ?? 'N/A' }}
                                                        </td>
                                                        <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">
                                                            {{ $assetGroup->assets->serial_number ?? 'N/A' }}
                                                        </td>
                                                        <td class="px-4 py-3 text-sm">
                                                            <span
                                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                                {{ $assetGroup->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100' }}">
                                                                {{ ucfirst($assetGroup->status ?? 'N/A') }}
                                                            </span>
                                                        </td>
                                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                                            {{ $assetGroup->expiry_date ? \Carbon\Carbon::parse($assetGroup->expiry_date)->format('M d, Y') : 'N/A' }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Footer - Sticky -->
                    <div x-show="show" x-transition:enter="transition ease-out delay-450 duration-300"
                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        class="bg-gray-50 dark:bg-gray-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200 dark:border-gray-700 sticky bottom-0 gap-2">
                        <x-filament::button href="{{ route('publicAssetsGroups', $currentClassroom->id) }}"
                            tag="a" target="_blank" color="secondary" class="mr-2">
                            View on page
                        </x-filament::button>
                        {{-- <x-filament::button wire:click="showDeployComputerModal">
                            Deploy Computer Set
                        </x-filament::button> --}}
                        <x-filament::button @click="show = false; setTimeout(() => $wire.closeClassroomAssets(), 200)"
                            tag="button"
                            class="dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600 transition-colors duration-200">
                            Close
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </x-filament::section>
    @endif
    <!-- Classroom Details Modal -->
    @if ($showingClassroomDetails && $currentClassroom)
        <x-filament::section class="dark:bg-gray-900">
            <div x-data="{
                show: false,
                scheduleExpanded: {}
            }" x-init="setTimeout(() => show = true, 50);
            @foreach($schedulesByDay as $day => $schedules)
            scheduleExpanded['{{ $day }}'] = false;
            @endforeach" x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                class="fixed inset-0 transition-opacity z-50 flex items-center justify-center bg-gray-900 bg-opacity-50"
                style="display: none;">
                <div
                    class="rounded-lg shadow-xl transform transition-all w-full max-w-4xl max-h-[90vh] overflow-hidden dark:bg-gray-800 bg-white flex flex-col">
                    <!-- Header - Sticky -->
                    {{-- <div
                        class="px-4 py-3 sm:px-6 flex justify-between items-center sticky top-0 z-10 bg-white dark:bg-gray-800 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                            {{ $currentClassroom->name }} Details
                        </h3>
                        <button @click="show = false; setTimeout(() => $wire.closeClassroomDetails(), 200)"
                            class="text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-gray-100 transition-colors duration-200">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div> --}}

                    <!-- Main Content - Scrollable -->
                    <div class="flex-1 overflow-y-auto relative">
                        <div class="px-4 py-4 sm:px-6 dark:bg-gray-800">
                            <!-- Classroom Info Card -->
                            <div x-show="show" x-transition:enter="transition ease-out delay-150 duration-300"
                                x-transition:enter-start="opacity-0 transform translate-y-4"
                                x-transition:enter-end="opacity-100 transform translate-y-0"
                                class="p-4 rounded-lg shadow-sm mb-4 bg-white dark:bg-gray-700 dark:text-gray-100">
                                <h4 class="text-lg font-medium mb-2 dark:text-gray-100">Classroom Information</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <p class="mb-1"><span
                                                class="font-medium dark:text-gray-200">Building:</span>
                                            <span
                                                class="dark:text-gray-300">{{ $currentClassroom->building->name ?? 'N/A' }}</span>
                                        </p>
                                        <p class="mb-1"><span class="font-medium dark:text-gray-200">Floor:</span>
                                            <span
                                                class="dark:text-gray-300">{{ $currentClassroom->floor ?? 'N/A' }}</span>
                                        </p>
                                    </div>
                                    <div>
                                        <p class="mb-1"><span class="font-medium dark:text-gray-200">Room: </span>
                                            <span
                                                class="dark:text-gray-300">{{ $currentClassroom->name ?? 'N/A' }}</span>
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

                            <!-- Schedule Filters (New) -->
                            <div x-show="show" x-transition:enter="transition ease-out delay-200 duration-300"
                                x-transition:enter-start="opacity-0 transform translate-y-4"
                                x-transition:enter-end="opacity-100 transform translate-y-0"
                                class="p-4 rounded-lg shadow-sm mb-4 bg-white dark:bg-gray-700 dark:text-gray-100">
                                <h4 class="text-lg font-medium mb-2 dark:text-gray-100">Schedule Filters</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="modalSchoolYear"
                                            class="block text-sm font-medium mb-2 dark:text-gray-200">School
                                            Year</label>
                                        <x-filament::input.wrapper>
                                            <x-filament::input.select id="modalSchoolYear"
                                                wire:model.live="modalSelectedSchoolYear">
                                                <option value="">All School Years</option>
                                                @foreach ($schoolYears as $year)
                                                    <option value="{{ $year }}">{{ $year }}</option>
                                                @endforeach
                                            </x-filament::input.select>
                                        </x-filament::input.wrapper>
                                    </div>
                                    <div>
                                        <label for="modalSemester"
                                            class="block text-sm font-medium mb-2 dark:text-gray-200">Semester</label>
                                        <x-filament::input.wrapper>
                                            <x-filament::input.select id="modalSemester"
                                                wire:model.live="modalSelectedSemester">
                                                <option value="">All Semesters</option>
                                                @foreach ($semesters as $sem)
                                                    <option value="{{ $sem }}">{{ $sem }}</option>
                                                @endforeach
                                            </x-filament::input.select>
                                        </x-filament::input.wrapper>
                                    </div>
                                </div>
                            </div>

                            <!-- Schedule Card -->
                            <!-- In the Schedule Card section -->
                            <div x-show="show" x-transition:enter="transition ease-out delay-300 duration-300"
                                x-transition:enter-start="opacity-0 transform translate-y-4"
                                x-transition:enter-end="opacity-100 transform translate-y-0"
                                class="bg-white dark:bg-gray-700 p-4 rounded-lg shadow-sm">
                                <div class="flex justify-between items-center mb-2">
                                    <h4 class="text-lg font-medium dark:text-gray-100">Schedule</h4>
                                    <x-filament::button size="sm" wire:click="exportSchedule"
                                        icon="heroicon-o-document-arrow-down">
                                        Export
                                    </x-filament::button>
                                </div>
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
                                    <div class="modal-schedule-container">
                                        @foreach ($schedulesByDay as $day => $schedules)
                                            @if (count($schedules) > 0)
                                                <div>
                                                    <!-- Day Header - Clickable to expand/collapse -->
                                                    <h5 @click="scheduleExpanded['{{ $day }}'] = !scheduleExpanded['{{ $day }}']"
                                                        class="text-md font-medium mb-2 bg-gray-50 dark:bg-gray-600 p-2 rounded day-header-sticky dark:text-gray-200 flex justify-between items-center cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-500 transition-colors">
                                                        <span>{{ $day }}</span>
                                                        <svg :class="scheduleExpanded['{{ $day }}'] ?
                                                            'transform rotate-180' : ''"
                                                            class="w-5 h-5 transition-transform duration-200"
                                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                        </svg>
                                                    </h5>

                                                    <!-- Table container with fixed height -->
                                                    <div x-show="scheduleExpanded['{{ $day }}']"
                                                        x-transition:enter="transition ease-out duration-200"
                                                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                                                        x-transition:enter-end="opacity-100 transform translate-y-0"
                                                        x-transition:leave="transition ease-in duration-150"
                                                        x-transition:leave-start="opacity-100 transform translate-y-0"
                                                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                                                        class="relative mt-2">
                                                        <div
                                                            class="rounded-md border border-gray-200 dark:border-gray-600 overflow-hidden">
                                                            <!-- Fixed width table structure for consistent layout -->
                                                            <div class="w-full">
                                                                <!-- Header with full width -->
                                                                <div
                                                                    class="bg-gray-100 dark:bg-gray-800 sticky top-0 z-10 w-full">
                                                                    <table
                                                                        class="w-full divide-y divide-gray-200 dark:divide-gray-600">
                                                                        <thead>
                                                                            <tr>
                                                                                <th
                                                                                    class="w-1/5 px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                                                    Time
                                                                                </th>
                                                                                <th
                                                                                    class="w-1/5 px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                                                    Section
                                                                                </th>
                                                                                <th class="w-1/5 px-3 py-2class="w-1/5
                                                                                    px-3 py-2 text-left text-xs
                                                                                    font-medium text-gray-500
                                                                                    dark:text-gray-300 uppercase
                                                                                    tracking-wider">
                                                                                    Subject
                                                                                </th>
                                                                                <th
                                                                                    class="w-1/5 px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                                                    School Year
                                                                                </th>
                                                                                <th
                                                                                    class="w-1/5 px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                                                    Semester
                                                                                </th>
                                                                            </tr>
                                                                        </thead>
                                                                    </table>
                                                                </div>

                                                                <!-- Table Body with same width as header -->
                                                                <div class="w-full">
                                                                    <table
                                                                        class="w-full divide-y divide-gray-200 dark:divide-gray-600">
                                                                        <tbody
                                                                            class="bg-white dark:bg-gray-700 divide-y divide-gray-200 dark:divide-gray-600">
                                                                            @foreach ($schedules as $schedule)
                                                                                <tr
                                                                                    class="dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                                                                    <td
                                                                                        class="w-1/5 px-3 py-2 whitespace-nowrap text-sm dark:text-gray-300">
                                                                                        {{ date('h:i A', strtotime($schedule->start_time)) }}
                                                                                        -
                                                                                        {{ date('h:i A', strtotime($schedule->end_time)) }}
                                                                                    </td>
                                                                                    <td
                                                                                        class="w-1/5 px-3 py-2 whitespace-nowrap text-sm dark:text-gray-300">
                                                                                        {{ $schedule->section->name ?? 'N/A' }}
                                                                                    </td>
                                                                                    <td
                                                                                        class="w-1/5 px-3 py-2 whitespace-nowrap text-sm dark:text-gray-300">
                                                                                        {{ $schedule->subject ?? 'N/A' }}
                                                                                    </td>
                                                                                    <td
                                                                                        class="w-1/5 px-3 py-2 whitespace-nowrap text-sm dark:text-gray-300">
                                                                                        {{ $schedule->school_year ?? 'N/A' }}
                                                                                    </td>
                                                                                    <td
                                                                                        class="w-1/5 px-3 py-2 whitespace-nowrap text-sm dark:text-gray-300">
                                                                                        {{ $schedule->semester ?? 'N/A' }}
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Footer - Sticky -->
                    <div x-show="show" x-transition:enter="transition ease-out delay-450 duration-300"
                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        class="bg-gray-50 dark:bg-gray-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200 dark:border-gray-700 sticky bottom-0">
                        <x-filament::button @click="show = false; setTimeout(() => $wire.closeClassroomDetails(), 200)"
                            tag="button"
                            class="dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600 transition-colors duration-200">
                            Close
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </x-filament::section>
    @endif

    <!-- Deploy Computer Set Modal -->
    @if ($showingDeployComputerModal)
        <x-filament::section class="dark:bg-gray-900">
            <div x-data="{
                show: false
            }" x-init="setTimeout(() => show = true, 50);" x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                class="fixed inset-0 transition-opacity z-50 flex items-center justify-center bg-gray-900 bg-opacity-50"
                style="display: none;">
                <div
                    class="rounded-lg shadow-xl transform transition-all w-full max-w-lg max-h-[90vh] overflow-hidden dark:bg-gray-800 bg-white flex flex-col">
                    <!-- Header - Sticky -->
                    <div
                        class="px-4 py-3 sm:px-6 flex justify-between items-center sticky top-0 z-10 bg-white dark:bg-gray-800 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                            Deploy Computer Set to {{ $currentClassroom->name }}
                        </h3>
                        <button @click="show = false; setTimeout(() => $wire.closeDeployComputerModal(), 200)"
                            class="text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-gray-100 transition-colors duration-200">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Main Content - Scrollable -->
                    <div class="flex-1 overflow-y-auto relative p-4">
                        <form wire:submit="deployComputerSet">
                            <!-- Classroom Field (Disabled/Read-only) -->
                            <div class="mb-4">
                                <label for="classroom">Classroom</label>
                                <x-filament::input.wrapper>
                                    <x-filament::input type="text" id="classroom"
                                        value="{{ $currentClassroom->name }} ({{ $currentClassroom->building->name ?? 'N/A' }}, Floor {{ $currentClassroom->floor ?? 'N/A' }})"
                                        disabled class="cursor-not-allowed bg-gray-100 dark:bg-gray-700" />
                                </x-filament::input.wrapper>
                            </div>

                            <!-- Asset Selection -->
                            <div class="mb-4">
                                <label for="asset_id" required>Select Computer</label>
                                <x-filament::input.wrapper>
                                    <x-filament::input.select id="asset_id" wire:model.live="assetId" required>
                                        <option value="">Select a computer</option>
                                        @foreach ($availableAssets as $asset)
                                            <option value="{{ $asset->id }}">
                                                {{ $asset->name }} ({{ $asset->brand->name ?? 'N/A' }}, SN:
                                                {{ $asset->serial_number }})
                                            </option>
                                        @endforeach
                                    </x-filament::input.select>
                                    @error('assetId')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </x-filament::input.wrapper>
                            </div>

                            <!-- Group Name -->
                            <div class="mb-4">
                                <label for="group_name" required>Group Name</label>
                                <x-filament::input.wrapper>
                                    <x-filament::input type="text" id="group_name" wire:model="groupName"
                                        placeholder="Computer Set 1" required :disabled="!empty($assetId)" :class="!empty($assetId)
                                            ? 'cursor-not-allowed bg-gray-100 dark:bg-gray-700'
                                            : ''" />
                                    @error('groupName')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </x-filament::input.wrapper>
                                @if (!empty($assetId))
                                    <p class="mt-1 text-xs text-gray-500">Auto-generated from the selected computer</p>
                                @endif
                            </div>

                            <!-- Group Code -->
                            <div class="mb-4">
                                <label for="group_code" required>Group Code</label>
                                <x-filament::input.wrapper>
                                    <x-filament::input type="text" id="group_code" wire:model="groupCode"
                                        placeholder="COMP-001" required :disabled="!empty($assetId)" :class="!empty($assetId)
                                            ? 'cursor-not-allowed bg-gray-100 dark:bg-gray-700'
                                            : ''" />
                                    @error('groupCode')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </x-filament::input.wrapper>
                                @if (!empty($assetId))
                                    <p class="mt-1 text-xs text-gray-500">Auto-generated from the selected computer</p>
                                @endif
                            </div>

                            <!-- Status -->
                            <div class="mb-4">
                                <label for="status" required>Status</label>
                                <x-filament::input.wrapper>
                                    <x-filament::input.select id="status" wire:model="status" required>
                                        <option value="active">Active</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="broken">broken</option>
                                    </x-filament::input.select>
                                    @error('status')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </x-filament::input.wrapper>
                            </div>
                        </form>
                    </div>

                    <!-- Footer - Sticky -->
                    <div x-show="show" x-transition:enter="transition ease-out delay-450 duration-300"
                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        class="bg-gray-50 dark:bg-gray-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200 dark:border-gray-700 sticky bottom-0 gap-2">
                        <x-filament::button type="button" wire:click="deployComputerSet"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="deployComputerSet">Deploy Computer Set</span>
                            <span wire:loading wire:target="deployComputerSet">Processing...</span>
                        </x-filament::button>
                        <x-filament::button
                            @click="show = false; setTimeout(() => $wire.closeDeployComputerModal(), 200)"
                            color="gray" tag="button">
                            Cancel
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </x-filament::section>
    @endif
</div>
