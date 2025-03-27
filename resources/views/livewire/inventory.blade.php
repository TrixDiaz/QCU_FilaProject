<div x-data="{
    showImportModal: false,
    showBulkEditModal: false,
    confirmingBulkDelete: @entangle('confirmingBulkDelete'),
    toggleAllCheckboxes() {
        if ($wire.selectAll) {
            $wire.selected = [];
            $wire.selectAll = false;
        } else {
            $wire.selectAll = true;
        }
    }
}">

    <!-- Bulk Delete Confirmation Modal -->
    <div x-show="confirmingBulkDelete"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl max-w-md w-full">
            <h3 class="text-lg font-medium mb-4">Confirm Bulk Archive</h3>

            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <h4 class="text-sm font-medium mb-2">Archiving the following assets:</h4>
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($selected as $assetId)
                        @php
                            $asset = \App\Models\Asset::find($assetId);
                        @endphp
                        <li class="text-sm text-gray-700 dark:text-gray-300">
                            {{ $asset->name }} ({{ $asset->asset_code }})
                        </li>
                    @endforeach
                </ul>
            </div>
            <p>Are you sure you want to archive the selected {{ count($selected) }} assets? This action cannot be
                undone.</p>
            <div class="mt-6 flex justify-end space-x-3">
                <x-filament::button type="button" color="gray" @click="confirmingBulkDelete = false">
                    Cancel
                </x-filament::button>
                <x-filament::button type="button" color="danger" wire:click="doBulkDelete">
                    Archive Assets
                </x-filament::button>
            </div>
        </div>
    </div>

    <!-- Bulk Edit Modal -->
    <div x-data="{ showBulkEditModal: @entangle('showBulkEditModal') }">
        <div x-show="showBulkEditModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl max-w-3xl w-full">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">Bulk Edit Assets</h3>
                    <button @click="showBulkEditModal = false" class="text-gray-400 hover:text-gray-600">
                        <x-heroicon-o-x-mark class="w-6 h-6" />
                    </button>
                </div>

                <form wire:submit.prevent="saveBulkEdit">
                    <div class="space-y-4">
                        <!-- List of Assets Being Edited -->
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <h4 class="text-sm font-medium mb-2">Editing the following assets:</h4>
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($selected as $assetId)
                                    @php
                                        $asset = \App\Models\Asset::find($assetId);
                                    @endphp
                                    <li class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ $asset->name }} ({{ $asset->asset_code }})
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <!-- Status Field -->
                        <div>
                            <label for="bulkStatus" class="block text-sm font-medium">Status</label>
                            <x-filament::input.wrapper>
                                <x-filament::input.select id="bulkStatus" wire:model="bulkEditData.status">
                                    <option value="">No change</option>

                                    <option value="broken">Broken</option>
                                    <option value="maintenance">Maintenance</option>
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>

                        <!-- Deployment Field -->
                        <div>
                            <label for="bulkDeploy" class="block text-sm font-medium">Deploy to Classroom</label>
                            <x-filament::input.wrapper>
                                <x-filament::input.select id="bulkDeploy" wire:model="bulkEditData.deployClassroom">
                                    <option value="">No change</option>
                                    @foreach ($classrooms as $classroom)
                                        <option value="{{ $classroom->id }}">
                                            {{ $classroom->building->name }} - {{ $classroom->name }} (Floor
                                            {{ $classroom->floor }})
                                        </option>
                                    @endforeach
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-6 flex justify-end space-x-3">
                            <x-filament::button type="button" color="gray" @click="showBulkEditModal = false">
                                Cancel
                            </x-filament::button>
                            <x-filament::button type="submit">
                                <span wire:loading.remove wire:target="saveBulkEdit">Save Changes</span>
                                <span wire:loading wire:target="saveBulkEdit">Saving...</span>
                            </x-filament::button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <x-filament::section class="my-4">
        {{-- Search and Create Asset --}}
        <div class="grid grid-cols-1 gap-6 w-full">
            {{-- Combined search and filter container --}}
            <div x-data="{ isSearchOpen: false }" class="col-span-full">
                {{-- Filters and View Layout - visible when search is closed --}}
                <div x-show="!isSearchOpen" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 transform scale-100"
                    x-transition:leave-end="opacity-0 transform scale-95" class="space-y-4 w-full">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-xl font-bold">Inventory Assets</h2>
                            <div>
                                <div class="text-sm capitalize">
                                    @if ($filterType == 'all')
                                        Showing all {{ $filteredCount }} assets
                                    @elseif($filterType == 'brand' && $filterValue)
                                        Showing {{ $filteredCount }} assets from
                                        {{ $brands->firstWhere('id', $filterValue)->name ?? '' }}
                                    @elseif($filterType == 'category' && $filterValue)
                                        Showing {{ $filteredCount }} assets in
                                        {{ $categories->firstWhere('id', $filterValue)->name ?? '' }}
                                    @elseif($filterType == 'tag' && $filterValue)
                                        Showing {{ $filteredCount }} assets with tag
                                        "{{ $tags->firstWhere('id', $filterValue)->name ?? '' }}"
                                    @elseif($filterType == 'brand-category' && $filterBrand && $filterCategory)
                                        Showing {{ $filteredCount }} assets from
                                        {{ $brands->firstWhere('id', $filterBrand)->name ?? '' }}
                                        in {{ $categories->firstWhere('id', $filterCategory)->name ?? '' }}
                                    @elseif($filterType == 'brand-tag' && $filterBrand && $filterTag)
                                        Showing {{ $filteredCount }} assets from
                                        {{ $brands->firstWhere('id', $filterBrand)->name ?? '' }}
                                        with tag "{{ $tags->firstWhere('id', $filterTag)->name ?? '' }}"
                                    @elseif($filterType == 'category-brand-tag' && $filterCategory && $filterBrand && $filterTag)
                                        Showing {{ $filteredCount }}
                                        {{ $categories->firstWhere('id', $filterCategory)->name ?? '' }}
                                        assets
                                        from {{ $brands->firstWhere('id', $filterBrand)->name ?? '' }}
                                        with tag "{{ $tags->firstWhere('id', $filterTag)->name ?? '' }}"
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            {{-- View Layout and Filter --}}
                            <div class="flex flex-wrap items-center gap-4">
                                <div class="flex items-center gap-2 p-1 rounded-lg">
                                    <x-filament::icon-button size="md" icon="heroicon-o-queue-list"
                                        wire:click="setViewType('table')" :color="$viewType === 'table' ? 'primary' : 'gray'">
                                        <span class="sr-only">Table View</span>
                                    </x-filament::icon-button>
                                    <x-filament::icon-button size="md" icon="heroicon-o-squares-2x2"
                                        wire:click="setViewType('card')" :color="$viewType === 'card' ? 'primary' : 'gray'">
                                        <span class="sr-only">Card View</span>
                                    </x-filament::icon-button>
                                </div>
                                <div class="flex flex-wrap items-center gap-3">
                                    <x-filament::input.wrapper>
                                        <x-filament::input.select wire:model.live="filterType"
                                            class="rounded border-gray-300 shadow-sm">
                                            <option value="all">Filters</option>
                                            <option value="brand">Filter by Brand</option>
                                            <option value="category">Filter by Category</option>
                                            <option value="tag">Filter by Tag</option>
                                            <option value="brand-category">Filter by Brand & Category</option>
                                            <option value="brand-tag">Filter by Brand & Tag</option>
                                            <option value="category-brand-tag">Filter by Category, Brand & Tag
                                            </option>
                                        </x-filament::input.select>
                                    </x-filament::input.wrapper>

                                    @if ($filterType == 'brand')
                                        <x-filament::input.wrapper>
                                            <x-filament::input.select wire:model.live="filterValue"
                                                class="rounded border-gray-300 shadow-sm">
                                                <option value="">Select Brand</option>
                                                @foreach ($brands as $brand)
                                                    <option class="capitalize" value="{{ $brand->id }}">
                                                        {{ $brand->name }}
                                                    </option>
                                                @endforeach
                                            </x-filament::input.select>
                                        </x-filament::input.wrapper>
                                    @elseif($filterType == 'category')
                                        <x-filament::input.wrapper>
                                            <x-filament::input.select wire:model.live="filterValue"
                                                class="rounded border-gray-300 shadow-sm">
                                                <option value="">Select Category</option>
                                                @foreach ($categories as $category)
                                                    <option class="capitalize" value="{{ $category->id }}">
                                                        {{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </x-filament::input.select>
                                        </x-filament::input.wrapper>
                                    @elseif($filterType == 'tag')
                                        <x-filament::input.wrapper>
                                            <x-filament::input.select wire:model.live="filterValue"
                                                class="rounded border-gray-300 shadow-sm">
                                                <option value="">Select Tag</option>
                                                @foreach ($tags as $tag)
                                                    <option class="capitalize" value="{{ $tag->id }}">
                                                        {{ $tag->name }}
                                                    </option>
                                                @endforeach
                                            </x-filament::input.select>
                                        </x-filament::input.wrapper>
                                    @elseif($filterType == 'brand-category')
                                        <x-filament::input.wrapper>
                                            <x-filament::input.select wire:model.live="filterBrand"
                                                class="rounded border-gray-300 shadow-sm">
                                                <option value="">Select Brand</option>
                                                @foreach ($brands as $brand)
                                                    <option class="capitalize" value="{{ $brand->id }}">
                                                        {{ $brand->name }}
                                                    </option>
                                                @endforeach
                                            </x-filament::input.select>
                                        </x-filament::input.wrapper>
                                        <x-filament::input.wrapper>
                                            <x-filament::input.select wire:model.live="filterCategory"
                                                class="rounded border-gray-300 shadow-sm">
                                                <option value="">Select Category</option>
                                                @foreach ($categories as $category)
                                                    <option class="capitalize" value="{{ $category->id }}">
                                                        {{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </x-filament::input.select>
                                        </x-filament::input.wrapper>
                                    @elseif($filterType == 'brand-tag')
                                        <x-filament::input.wrapper>
                                            <x-filament::input.select wire:model.live="filterBrand"
                                                class="rounded border-gray-300 shadow-sm">
                                                <option value="">Select Brand</option>
                                                @foreach ($brands as $brand)
                                                    <option class="capitalize" value="{{ $brand->id }}">
                                                        {{ $brand->name }}
                                                    </option>
                                                @endforeach
                                            </x-filament::input.select>
                                        </x-filament::input.wrapper>
                                        <x-filament::input.wrapper>
                                            <x-filament::input.select wire:model.live="filterTag"
                                                class="rounded border-gray-300 shadow-sm">
                                                <option value="">Select Tag</option>
                                                @foreach ($tags as $tag)
                                                    <option class="capitalize" value="{{ $tag->id }}">
                                                        {{ $tag->name }}
                                                    </option>
                                                @endforeach
                                            </x-filament::input.select>
                                        </x-filament::input.wrapper>
                                    @elseif($filterType == 'category-brand-tag')
                                        <x-filament::input.wrapper>
                                            <x-filament::input.select wire:model.live="filterCategory"
                                                class="rounded border-gray-300 shadow-sm">
                                                <option value="">Select Category</option>
                                                @foreach ($categories as $category)
                                                    <option class="capitalize" value="{{ $category->id }}">
                                                        {{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </x-filament::input.select>
                                        </x-filament::input.wrapper>
                                        <x-filament::input.wrapper>
                                            <x-filament::input.select wire:model.live="filterBrand"
                                                class="rounded border-gray-300 shadow-sm">
                                                <option value="">Select Brand</option>
                                                @foreach ($brands as $brand)
                                                    <option class="capitalize" value="{{ $brand->id }}">
                                                        {{ $brand->name }}
                                                    </option>
                                                @endforeach
                                            </x-filament::input.select>
                                        </x-filament::input.wrapper>
                                        <x-filament::input.wrapper>
                                            <x-filament::input.select wire:model.live="filterTag"
                                                class="rounded border-gray-300 shadow-sm">
                                                <option value="">Select Tag</option>
                                                @foreach ($tags as $tag)
                                                    <option class="capitalize" value="{{ $tag->id }}">
                                                        {{ $tag->name }}
                                                    </option>
                                                @endforeach
                                            </x-filament::input.select>
                                        </x-filament::input.wrapper>
                                    @endif

                                    @if ($filterType !== 'all')
                                        <x-filament::button color="danger" size="sm" wire:click="resetFilters"
                                            class="self-center" icon="heroicon-o-x-mark">
                                            Reset Filters
                                        </x-filament::button>
                                    @endif
                                </div>
                            </div>
                            <x-filament::icon-button icon="heroicon-m-magnifying-glass" @click="isSearchOpen = true"
                                label="Search" size="lg" tooltip="Open Search" />
                            <x-filament::button href="{{ route('filament.app.resources.assets.create') }}"
                                tag="a" tooltip="Create New Asset" icon="heroicon-m-plus">
                                New
                            </x-filament::button>


                            <div>
                                <!-- Import Button and Modal -->
                                <div x-data="{ showImportModal: false, importSuccess: false }" class="relative">
                                    <!-- Import Button -->
                                    <x-filament::button @click="showImportModal = true"
                                        icon="heroicon-o-arrow-up-tray">
                                        Import Excel
                                    </x-filament::button>

                                    <!-- Import Modal with Light/Dark Mode Support -->
                                    <div x-show="showImportModal"
                                        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4 transition radius-5"
                                        style="display: none;">
                                        <div
                                            class="bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-2xl shadow-2xl p-6 sm:p-8 w-full max-w-md transition">
                                            <!-- Header -->
                                            <div class="flex justify-between items-center mb-4 sm:mb-6">
                                                <h3 class="text-2xl font-bold">Import Assets from Excel</h3>
                                                <button @click="showImportModal = false"
                                                    class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white focus:outline-none">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </div>

                                            <form wire:submit.prevent="importAssets" class="space-y-6">
                                                <!-- Download Link -->
                                                <div class="text-center mb-4">
                                                    <a href="/example-csv">Download Example CSV</a>
                                                </div>

                                                <!-- File Input -->
                                                <div>
                                                    <label for="importFile"
                                                        class="block text-sm font-medium text-gray-200 dark:text-gray-300 mb-2">Excel
                                                        File</label>
                                                    <input type="file" id="importFile" accept=".xlsx,.xls,.csv"
                                                        wire:model="importFile"
                                                        class="w-full p-3 border rounded-lg bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500 focus:outline-none transition">
                                                </div>

                                                <!-- Action Buttons -->
                                                <div class="flex justify-end space-x-3">
                                                    <div>
                                                        <!-- Import Button and Modal -->
                                                        <div x-data="{ showImportModal: false, importSuccess: false }" class="relative">
                                                            <!-- Import Button -->
                                                            <x-filament::button @click="showImportModal = true"
                                                                icon="heroicon-o-arrow-up-tray">
                                                                Import Excel
                                                            </x-filament::button>

                                                            <!-- Import Modal with Light/Dark Mode Support -->
                                                            <div x-show="showImportModal"
                                                                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4 transition radius-5"
                                                                style="display: none;">
                                                                <div
                                                                    class="bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-2xl shadow-2xl p-6 sm:p-8 w-full max-w-md transition">
                                                                    <!-- Header -->
                                                                    <div
                                                                        class="flex justify-between items-center mb-4 sm:mb-6">
                                                                        <h3 class="text-2xl font-bold">Import Assets
                                                                            from Excel</h3>
                                                                        <button @click="showImportModal = false"
                                                                            class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white focus:outline-none">
                                                                            <svg class="w-6 h-6" fill="none"
                                                                                stroke="currentColor"
                                                                                viewBox="0 0 24 24"
                                                                                xmlns="http://www.w3.org/2000/svg">
                                                                                <path stroke-linecap="round"
                                                                                    stroke-linejoin="round"
                                                                                    stroke-width="2"
                                                                                    d="M6 18L18 6M6 6l12 12"></path>
                                                                            </svg>
                                                                        </button>
                                                                    </div>

                                                                    <form wire:submit.prevent="importAssets"
                                                                        class="space-y-6">
                                                                        <!-- Download Link -->
                                                                        <div class="text-left mb-4">
                                                                            <a href="/example-csv"
                                                                                class="text-blue-600 hover:underline">
                                                                                ↓ Download Example CSV
                                                                            </a>
                                                                        </div>

                                                                        <!-- File Input -->
                                                                        <div>
                                                                            <label for="importFile"
                                                                                class="block text-sm font-medium text-gray-200 dark:text-gray-300 mb-2">Excel
                                                                                File</label>
                                                                            <input type="file" id="importFile"
                                                                                accept=".xlsx,.xls,.csv"
                                                                                wire:model="importFile"
                                                                                class="w-full p-3 border rounded-lg bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-purple-500 focus:outline-none transition">
                                                                        </div>

                                                                        <!-- Action Buttons -->
                                                                        <div class="flex justify-end space-x-3">
                                                                            <!-- Cancel Button -->
                                                                            <button type="button"
                                                                                @click="showImportModal = false"
                                                                                wire:click="resetImportFile"
                                                                                class="px-6 py-2.5 bg-red-500 text-white font-semibold rounded-lg hover:bg-red-600 focus:ring-2 focus:ring-red-300 focus:outline-none shadow-md transition">
                                                                                Cancel
                                                                            </button>

                                                                            <!-- Import Button (Primary) -->
                                                                            <button type="submit"
                                                                                class="px-6 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-300 focus:outline-none shadow-md transition">
                                                                                Import
                                                                            </button>
                                                                        </div>

                                                                        <!-- Error Message -->
                                                                        <div class="mt-2">
                                                                            @error('importFile')
                                                                                <div
                                                                                    class="text-red-600 text-sm bg-red-50 border border-red-300 rounded-lg px-4 py-2">
                                                                                    {{ $message }}
                                                                                </div>
                                                                            @enderror
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Success Message -->
                                                        @if (session()->has('message'))
                                                            <div x-data="{ show: true }" x-show="show"
                                                                class="fixed top-4 right-4 bg-green-500 text-white pl-4 pr-2 py-2 rounded-lg shadow-lg z-50 flex items-center">
                                                                <span class="mr-4">{{ session('message') }}</span>
                                                                <button @click="show = false"
                                                                    class="text-white hover:text-green-100 focus:outline-none">
                                                                    <svg class="w-5 h-5" fill="none"
                                                                        stroke="currentColor" viewBox="0 0 24 24"
                                                                        xmlns="http://www.w3.org/2000/svg">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round" stroke-width="2"
                                                                            d="M6 18L18 6M6 6l12 12"></path>
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        @endif
                                                    </div>

                                                </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Search Bar - visible when search is open --}}
                                <div x-show="isSearchOpen" x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 transform scale-95"
                                    x-transition:enter-end="opacity-100 transform scale-100"
                                    x-transition:leave="transition ease-in duration-200"
                                    x-transition:leave-start="opacity-100 transform scale-100"
                                    x-transition:leave-end="opacity-0 transform scale-95" class="w-full">
                                    <div class="flex items-center gap-3">
                                        <x-filament::input.wrapper class="flex-1">
                                            <x-slot name="prefix">
                                                <span class="text-gray-500">Search by: Asset Name, Brand, Category,
                                                    Serial Number,
                                                    Asset Code</span>
                                            </x-slot>

                                            <x-filament::input type="text" class="w-full"
                                                wire:model.live.debounce.500ms='search'
                                                @keydown.escape.window="isSearchOpen = false" />

                                            <x-slot name="suffix">
                                                <button type="button" @click="isSearchOpen = false"
                                                    class="text-gray-400 hover:text-gray-600">
                                                    <x-heroicon-m-x-mark class="w-5 h-5" />
                                                </button>
                                            </x-slot>
                                        </x-filament::input.wrapper>

                                        <x-filament::button href="{{ route('filament.app.resources.assets.create') }}"
                                            tag="a" tooltip="Create New Asset" icon="heroicon-m-plus">
                                            New
                                        </x-filament::button>
                                    </div>
                                </div>
                            </div>
                        </div>
    </x-filament::section>

    <section>
        {{-- Counts for all available Asset --}}
        <div></div>

        {{-- Counts for all Deployed Asset  --}}
        <div></div>
    </section>
    <x-filament::section>
        {{-- Table Header --}}
        <div class="flex justify-between items-center mb-2">
            <div>
                @if ($filterType == 'brand' && $filterValue)
                    <h3 class="text-lg font-medium mb-4 capitalize">
                        {{ $brands->firstWhere('id', $filterValue)->name ?? 'Brand' }} Assets
                    </h3>
                @elseif($filterType == 'category' && $filterValue)
                    <h3 class="text-lg font-medium mb-4">
                        {{ $categories->firstWhere('id', $filterValue)->name ?? 'Category' }} Assets
                    </h3>
                @elseif($filterType == 'tag' && $filterValue)
                    <h3 class="text-lg font-medium mb-4">
                        Assets tagged with "{{ $tags->firstWhere('id', $filterValue)->name ?? 'Tag' }}"
                    </h3>
                @elseif($filterType == 'brand-category' && $filterBrand && $filterCategory)
                    <h3 class="text-lg font-medium mb-4">
                        {{ $brands->firstWhere('id', $filterBrand)->name ?? 'Brand' }}
                        {{ $categories->firstWhere('id', $filterCategory)->name ?? 'Category' }} Assets
                    </h3>
                @elseif($filterType == 'brand-tag' && $filterBrand && $filterTag)
                    <h3 class="text-lg font-medium mb-4">
                        {{ $brands->firstWhere('id', $filterBrand)->name ?? 'Brand' }} Assets
                        with tag "{{ $tags->firstWhere('id', $filterTag)->name ?? 'Tag' }}"
                    </h3>
                @elseif($filterType == 'category-brand-tag' && $filterCategory && $filterBrand && $filterTag)
                    <h3 class="text-lg font-medium mb-4">
                        {{ $categories->firstWhere('id', $filterCategory)->name ?? 'Category' }} Assets
                        from {{ $brands->firstWhere('id', $filterBrand)->name ?? 'Brand' }}
                        with tag "{{ $tags->firstWhere('id', $filterTag)->name ?? 'Tag' }}"
                    </h3>
                @else
                    <h3 class="text-lg font-medium mb-4">All Assets</h3>
                @endif
            </div>
        </div>

        <section class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            {{-- Card for Available Assets --}}
            <x-filament::section>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-medium">Available Assets</h3>
                        <p class="text-sm text-gray-500">Assets ready for deployment</p>
                    </div>
                    <div class="bg-primary-100 dark:bg-primary-800 rounded-full px-4 py-2">
                        <span class="text-2xl font-bold text-primary-700 dark:text-primary-300">
                            {{ $assets->where('status', 'available')->count() }}
                        </span>
                    </div>
                </div>

                {{-- Mini Stats Grid --}}
                <div class="grid grid-cols-2 gap-4">
                    @foreach ($categories as $category)
                        <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-lg">
                            <h4 class="text-sm font-medium capitalize">{{ $category->name }}</h4>
                            <p class="text-2xl font-semibold">
                                {{ $assets->where('status', 'available')->where('category_id', $category->id)->count() }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>

            {{-- Card for Deployed Assets --}}
            <!-- Replace the existing Card for Deployed Assets section with this updated version -->
            <x-filament::section>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-medium">Deployed Assets</h3>
                        <p class="text-sm text-gray-500">Assets currently in use</p>
                    </div>
                    <div class="bg-success-100 dark:bg-success-800 rounded-full px-4 py-2">
                        <span class="text-2xl font-bold text-success-700 dark:text-success-300">
                            {{ $assets->where('status', 'deployed')->count() }}
                        </span>
                    </div>
                </div>

                {{-- Deployment Location Stats --}}
                <div class="space-y-3">
                    @forelse ($deploymentStats as $stat)
                        <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div>
                                <h4 class="text-sm font-medium">{{ $stat['classroom']->building->name }} - Room
                                    {{ $stat['classroom']->name }}</h4>
                                <p class="text-xs text-gray-500">Floor {{ $stat['classroom']->floor }}</p>
                            </div>
                            <span class="text-lg font-semibold">{{ $stat['count'] }}</span>
                        </div>
                    @empty
                        <div class="text-center p-4 text-gray-500">
                            No assets currently deployed
                        </div>
                    @endforelse
                </div>
            </x-filament::section>
        </section>

        @if ($viewType === 'card')
            <!-- Card View with Improved Checkboxes -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 relative">
                @if (count($selected) >= 2)
                    <div
                        class="col-span-full sticky top-0 z-10 bg-white dark:bg-gray-800 p-3 shadow-md rounded-lg mb-4 border border-primary-100">
                        <div class="flex justify-between items-center gap-4 flex-wrap">
                            <div class="flex flex-row items-center gap-4">
                                <span class="text-sm font-medium">{{ count($selected) }} selected</span>

                                <x-filament::button wire:click="$set('selected', [])" size="sm" color="danger"
                                    outline>
                                    Clear
                                </x-filament::button>

                                <x-filament::input.wrapper>
                                    <x-filament::input.select wire:model.live="bulkAction"
                                        class="rounded border-gray-300 shadow-sm">
                                        <option value="">Bulk Actions</option>
                                        <option value="delete">Delete Selected</option>
                                        <option value="edit">Edit Selected</option>
                                        <option value="export">Export Selected</option>
                                    </x-filament::input.select>
                                </x-filament::input.wrapper>

                                <x-filament::button wire:click="executeBulkAction" :disabled="empty($bulkAction)">
                                    Apply
                                </x-filament::button>

                                @error('bulkAction')
                                    <span class="text-danger-500">{{ $message ?? 'Please select an action' }}</span>
                                @enderror
                            </div>
                            <div class="flex flex-row justify-end items-center gap-4">
                                <x-filament::button @click="showImportModal = true" color="success"
                                    icon="heroicon-o-arrow-up-tray">
                                    Import
                                </x-filament::button>
                                <x-filament::button wire:click="exportAssets" color="info"
                                    icon="heroicon-o-arrow-down-tray">
                                    Export All
                                </x-filament::button>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="col-span-full mb-4">
                    <x-filament::input.checkbox wire:model.live="selectAll" id="select-all-cards"
                        label="Select All Assets" />
                    <label for="select-all-cards">Select All</label>
                </div>

                @forelse($assets as $asset)
                    <x-filament::section key="{{ $asset->id }}" :class="in_array((string) $asset->id, $selected) ? 'ring-2 ring-primary-500' : ''">
                        <div class="rounded-lg shadow overflow-hidden hover:shadow-md transition-shadow">
                            <div class="relative p-4">
                                <!-- Checkbox with improved visibility -->
                                <div class="absolute top-2 left-2">
                                    <x-filament::input.checkbox wire:model.live="selected"
                                        value="{{ $asset->id }}" wire:key="card-checkbox-{{ $asset->id }}"
                                        :class="in_array((string) $asset->id, $selected) ? 'bg-primary-500' : ''" />
                                    <label for="">Select</label>
                                </div>

                                <div class="flex justify-between mb-2 mt-6">
                                    <div>
                                        <x-filament::badge color="secondary">
                                            <h3 class="font-bold capitalize">{{ $asset->brand->name }}</h3>
                                        </x-filament::badge>
                                        <x-filament::badge color="info" class="mt-2">
                                            <span class="text-sm rounded py-1 capitalize">
                                                {{ $asset->category->name }}
                                            </span>
                                        </x-filament::badge>
                                    </div>
                                    <div>
                                        <x-filament::badge>
                                            <span class="text-sm rounded px-2 py-1 capitalize">
                                                {{ $asset->status }}
                                            </span>
                                        </x-filament::badge>
                                    </div>
                                </div>

                                <h4 class="text-lg font-semibold">{{ $asset->name }}</h4>

                                <div class="text-sm mt-2">
                                    <p><span class="font-medium">S/N:</span> {{ $asset->serial_number }}</p>

                                    @php
                                        $isSoftware = strtolower($asset->category->name) === 'software';
                                        $hasLicenseTag = $asset->assetTags->contains(function ($tag) {
                                            return strtolower($tag->name) === 'license';
                                        });
                                    @endphp

                                    @if ($asset->expiry_date && ($isSoftware || $hasLicenseTag))
                                        <p>
                                            <span class="font-medium">Expires:</span>
                                            {{ \Carbon\Carbon::parse($asset->expiry_date)->format('M d, Y') }}

                                            @php
                                                $daysUntilExpiry = \Carbon\Carbon::now()->diffInDays(
                                                    $asset->expiry_date,
                                                    false,
                                                );
                                            @endphp

                                            @if ($daysUntilExpiry < 0)
                                                <x-filament::badge color="danger">Expired</x-filament::badge>
                                            @elseif ($daysUntilExpiry < 30)
                                                <x-filament::badge color="warning">Expiring soon</x-filament::badge>
                                            @endif
                                        </p>
                                    @endif
                                </div>

                                <div class="mt-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium">Status:</span>
                                        @if ($asset->status === 'available')
                                            <x-filament::badge color="success">Available</x-filament::badge>
                                        @elseif($asset->status === 'deployed')
                                            <x-filament::badge color="primary">
                                                Deployed to:
                                                {{ $asset->assetGroups->first()->classroom->name ?? 'Unknown' }}
                                            </x-filament::badge>
                                        @endif
                                    </div>
                                </div>

                                @if ($asset->assetTags->count() > 0)
                                    <div class="mt-2 flex flex-wrap gap-1">
                                        @foreach ($asset->assetTags as $tag)
                                            <x-filament::badge>
                                                <span class="text-xs rounded px-2 py-1">
                                                    {{ $tag->name }}
                                                </span>
                                            </x-filament::badge>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="mt-3 flex justify-end gap-2">
                                    @if (count($selected) < 2)
                                        <x-filament::button
                                            @click="$dispatch('open-deploy-modal', { 
                                                assetId: {{ $asset->id }}, 
                                                assetName: '{{ $asset->name }}',
                                                assetCode: '{{ $asset->asset_code }}'
                                            })"
                                            class="text-sm hover:underline">
                                            Deploy Asset
                                        </x-filament::button>
                                        <x-filament::button
                                            href="{{ route('filament.app.resources.assets.edit', $asset->id) }}"
                                            tag="a" class="text-sm hover:underline">Edit Asset
                                        </x-filament::button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </x-filament::section>
                @empty
                    <div class="col-span-full py-10 text-center">
                        <p class="text-lg">No assets found matching your filter criteria.</p>
                        @if ($filterType != 'all')
                            <x-filament::button wire:click="$set('filterType', 'all')" class="mt-2 hover:underline">
                                Show all assets
                            </x-filament::button>
                        @endif
                    </div>
                @endforelse
            </div>
        @else
            <!-- Table View with Select All Header -->
            <div class="overflow-x-auto rounded-lg">
                <x-filament-tables::table class="min-w-full divide-y col-span-full">
                    <thead class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                        <x-filament-tables::row>
                            <x-filament-tables::header-cell scope="col" class="w-12 px-4 py-3">
                                <x-filament::input.checkbox wire:model.live="selectAll" @click="toggleAllCheckboxes()"
                                    id="select-all-table" class="rounded" />
                            </x-filament-tables::header-cell>

                            @if (count($selected) >= 2)
                                <x-filament-tables::header-cell scope="col" colspan="7" class="px-3 py-3">
                                    <div class="flex items-center gap-4">
                                        <span class="font-medium text-sm">{{ count($selected) }} selected</span>

                                        <x-filament::button wire:click="$set('selected', [])" size="sm"
                                            color="danger" outline>
                                            Clear
                                        </x-filament::button>

                                        <x-filament::input.wrapper>
                                            <x-filament::input.select wire:model.live="bulkAction"
                                                class="rounded border-gray-300 shadow-sm">
                                                <option value="">Bulk Actions</option>
                                                <option value="delete">Delete Selected</option>
                                                <option value="edit">Edit Selected</option>
                                                <option value="export">Export Selected</option>
                                            </x-filament::input.select>
                                        </x-filament::input.wrapper>

                                        <x-filament::button wire:click="executeBulkAction" :disabled="empty($bulkAction)">
                                            Apply
                                        </x-filament::button>

                                        @error('bulkAction')
                                            <span
                                                class="text-danger-500">{{ $message ?? 'Please select an action' }}</span>
                                        @enderror
                                    </div>
                                </x-filament-tables::header-cell>
                            @else
                                <x-filament-tables::header-cell scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                    Name</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                    Brand</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                    Category</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                    S/N</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                    Tags</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                    Action</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                    Deployment Status
                                </x-filament-tables::header-cell>
                            @endif
                        </x-filament-tables::row>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($assets as $asset)
                            <x-filament-tables::row :class="in_array((string) $asset->id, $selected) ? 'bg-primary-50' : ''">
                                <x-filament-tables::cell class="w-12 px-4 py-4 whitespace-nowrap">
                                    <x-filament::input.checkbox wire:model.live="selected"
                                        value="{{ $asset->id }}" wire:key="table-checkbox-{{ $asset->id }}"
                                        :class="in_array((string) $asset->id, $selected) ? 'bg-primary-500' : ''" />
                                </x-filament-tables::cell>
                                <x-filament-tables::cell class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $asset->name }}</div>
                                </x-filament-tables::cell>
                                <x-filament-tables::cell class="px-6 py-4 whitespace-nowrap">
                                    <x-filament::badge color="secondary">
                                        <span class="capitalize">{{ $asset->brand->name }}</span>
                                    </x-filament::badge>
                                </x-filament-tables::cell>
                                <x-filament-tables::cell class="px-6 py-4 whitespace-nowrap">
                                    <x-filament::badge color="info">
                                        <span class="capitalize">{{ $asset->category->name }}</span>
                                    </x-filament::badge>
                                </x-filament-tables::cell>
                                <x-filament-tables::cell class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $asset->serial_number }}</div>
                                </x-filament-tables::cell>
                                @if (count($selected) < 2)
                                    <x-filament-tables::cell class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($asset->assetTags as $tag)
                                                <x-filament::badge>
                                                    <span class="text-xs">{{ $tag->name }}</span>
                                                </x-filament::badge>
                                            @endforeach
                                        </div>
                                    </x-filament-tables::cell>
                                    <x-filament-tables::cell class="px-6 py-4 whitespace-nowrap">
                                        <div x-data="{ open: false }" class="relative">
                                            <!-- Action Menu Button -->
                                            <button @click="open = !open" class="text-gray-400 hover:text-gray-600">
                                                <x-heroicon-m-ellipsis-vertical class="w-5 h-5" />
                                            </button>

                                            <!-- Dropdown Menu -->
                                            <div x-show="open" @click.away="open = false"
                                                class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 z-50"
                                                x-transition:enter="transition ease-out duration-100"
                                                x-transition:enter-start="transform opacity-0 scale-95"
                                                x-transition:enter-end="transform opacity-100 scale-100"
                                                x-transition:leave="transition ease-in duration-75"
                                                x-transition:leave-start="transform opacity-100 scale-100"
                                                x-transition:leave-end="transform opacity-0 scale-95">
                                                <div class="py-1" role="menu">
                                                    <!-- Deploy Asset Button -->
                                                    <button
                                                        @click="
                                                        open = false;
                                                        $dispatch('open-deploy-modal', { 
                                                            assetId: {{ $asset->id }}, 
                                                            assetName: '{{ $asset->name }}',
                                                            assetCode: '{{ $asset->asset_code }}'
                                                        })"
                                                        class="w-full text-left px-4 py-2 text-sm " role="menuitem">
                                                        <div class="flex items-center">
                                                            <x-heroicon-m-arrow-up-tray class="w-4 h-4 mr-2" />
                                                            Deploy Asset
                                                        </div>
                                                    </button>

                                                    <!-- Edit Asset Button -->
                                                    <a href="{{ route('filament.app.resources.assets.edit', $asset->id) }}"
                                                        class="block px-4 py-2 text-sm " role="menuitem">
                                                        <div class="flex items-center">
                                                            <x-heroicon-m-pencil-square class="w-4 h-4 mr-2" />
                                                            Edit Asset
                                                        </div>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </x-filament-tables::cell>
                                    <x-filament-tables::cell class="px-6 py-4 whitespace-nowrap">
                                        @if ($asset->status === 'available')
                                            <x-filament::badge color="success">Available</x-filament::badge>
                                        @elseif($asset->status === 'deployed')
                                            <x-filament::badge color="primary">
                                                Deployed to:
                                                {{ $asset->assetGroups->first()->classroom->name ?? 'Unknown' }}
                                            </x-filament::badge>
                                        @endif
                                    </x-filament-tables::cell>
                                @endif
                            </x-filament-tables::row>
                        @empty
                            <tr>
                                <x-filament-tables::cell colspan="9" class="px-6 py-10 text-center">
                                    <p class="text-lg">No assets found matching your filter criteria.</p>
                                    @if ($filterType != 'all')
                                        <x-filament::button wire:click="$set('filterType', 'all')"
                                            class="mt-2 text-primary-600 hover:text-primary-900 hover:underline">
                                            Show all assets
                                        </x-filament::button>
                                    @endif
                                </x-filament-tables::cell>
                            </tr>
                        @endforelse
                    </tbody>
                </x-filament-tables::table>
            </div>
        @endif

    </x-filament::section>
    <!-- Pagination Controls -->
    <x-filament::section class="my-4">
        <div class="flex justify-between items-center">
            <div>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="perPage" class="rounded border-gray-300 shadow-sm">
                        <option value="12">12 per page</option>
                        <option value="24">24 per page</option>
                        <option value="36">36 per page</option>
                        <option value="48">48 per page</option>
                        <option value="100">100 per page</option>
                        <option value="200">200 per page</option>
                        <option value="500">500 per page</option>
                        <option value="1000">1000 per page</option>
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            <div>
                {{ $assets->links() }}
            </div>
        </div>
    </x-filament::section>

    <!-- Asset Deployment Modal -->
    <div x-data="{
        showDeployModal: false,
        assetId: null,
        assetName: '',
        assetCode: '',
        deploymentSuccess: false,
        selectedClassroom: '',
        statusActive: true,
        deploymentName: '',
        deploymentCode: ''
    }"
        @open-deploy-modal.window="
                showDeployModal = true;
                assetId = $event.detail.assetId;
                assetName = $event.detail.assetName;
                assetCode = $event.detail.assetCode;
                deploymentName = assetName;
                deploymentCode = assetCode;
             ">

        <!-- Deployment Success Message -->
        <div x-show="deploymentSuccess" x-transition
            class="fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50">
            Asset deployed successfully!
        </div>

        <!-- Deployment Modal -->
        <div x-show="showDeployModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
            style="display: none;">
            <div
                class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-2xl shadow-2xl p-6 sm:p-8 w-full max-w-md transition">
                <!-- Header -->
                <div class="flex justify-between items-center mb-4 sm:mb-6">
                    <h3 class="text-2xl font-bold">Deploy Asset to Classroom</h3>
                    <button @click="showDeployModal = false" class="text-gray-400 hover:text-gray-600">
                        <x-heroicon-o-x-mark class="w-6 h-6" />
                    </button>
                </div>

                <!-- Form -->
                <form wire:submit.prevent="deployAsset">
                    <div class="space-y-4">
                        <!-- Asset Info -->
                        <div>
                            <span class="text-sm font-medium">Asset:</span>
                            <p class="text-lg font-semibold" x-text="assetName"></p>
                            <input type="hidden" wire:model="deployAssetId" x-model="assetId">
                        </div>

                        <!-- Deployment Name -->
                        <div>
                            <label for="deploymentName" class="block text-sm font-medium">Deployment Name</label>
                            <x-filament::input.wrapper>
                                <x-filament::input type="text" id="deploymentName" wire:model="deploymentName"
                                    x-model="deploymentName"
                                    class="w-full mt-1 rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" />
                            </x-filament::input.wrapper>
                        </div>

                        <!-- Deployment Code -->
                        <div>
                            <label for="deploymentCode" class="block text-sm font-medium">Deployment Code</label>
                            <x-filament::input.wrapper>
                                <x-filament::input type="text" id="deploymentCode" wire:model="deploymentCode"
                                    x-model="deploymentCode"
                                    class="w-full mt-1 rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50" />
                            </x-filament::input.wrapper>
                        </div>

                        <!-- Classroom Selection -->
                        <div>
                            <label for="selectedClassroom">Select
                                Classroom</label>
                            <x-filament::input.wrapper>
                                <x-filament::input.select id="selectedClassroom" wire:model="selectedClassroom"
                                    x-model="selectedClassroom" placeholder="-- Select a classroom --">
                                    <option>Select Building</option>
                                    @foreach ($classrooms as $classroom)
                                        <option value="{{ $classroom->id }}">
                                            {{ $classroom->building->name }} - {{ $classroom->name }} (Floor
                                            {{ $classroom->floor }})
                                        </option>
                                    @endforeach
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>

                        <!-- Status -->
                        {{-- <div>

                            <x-filament::input.checkbox wire:model="statusActive" x-model="statusActive"
                                id="status-active">
                                <p class="ml-2 text-sm">Set as active</p>
                            </x-filament::input.checkbox>

                        </div> --}}

                        <!-- Action Buttons -->
                        <div class="flex justify-end mt-6 space-x-3 gap-4">
                            <x-filament::button type="button" color="gray" @click="showDeployModal = false">
                                Cancel
                            </x-filament::button>
                            <x-filament::button type="button"
                                @click="$wire.deployAsset(assetId, selectedClassroom, deploymentName, deploymentCode, statusActive)
                                .then(() => {
                                    showDeployModal = false;
                                    deploymentSuccess = true;
                                    setTimeout(() => {
                                        deploymentSuccess = false;
                                    }, 3000);
                                })">
                                Deploy Asset
                            </x-filament::button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
