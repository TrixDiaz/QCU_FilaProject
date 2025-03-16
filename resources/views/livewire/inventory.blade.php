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
                                <span class="text-gray-500">Search by: Asset Name, Brand, Category, Serial Number,
                                    Asset Code</span>
                            </x-slot>

                            <x-filament::input type="text" class="w-full" wire:model.live.debounce.500ms='search'
                                @keydown.escape.window="isSearchOpen = false" />

                            <x-slot name="suffix">
                                <button type="button" @click="isSearchOpen = false"
                                    class="text-gray-400 hover:text-gray-600">
                                    <x-heroicon-m-x-mark class="w-5 h-5" />
                                </button>
                            </x-slot>
                        </x-filament::input.wrapper>

                        <x-filament::button href="{{ route('filament.app.resources.assets.create') }}" tag="a"
                            tooltip="Create New Asset" icon="heroicon-m-plus">
                            New
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>

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
                        {{ $categories->firstWhere('id', $filterValue)->name ?? 'Category' }}
                        Assets</h3>
                @elseif($filterType == 'tag' && $filterValue)
                    <h3 class="text-lg font-medium mb-4">Assets tagged with
                        "{{ $tags->firstWhere('id', $filterValue)->name ?? 'Tag' }}"</h3>
                @elseif($filterType == 'brand-category' && $filterBrand && $filterCategory)
                    <h3 class="text-lg font-medium mb-4">
                        {{ $brands->firstWhere('id', $filterBrand)->name ?? 'Brand' }}
                        {{ $categories->firstWhere('id', $filterCategory)->name ?? 'Category' }} Assets</h3>
                @elseif($filterType == 'brand-tag' && $filterBrand && $filterTag)
                    <h3 class="text-lg font-medium mb-4">
                        {{ $brands->firstWhere('id', $filterBrand)->name ?? 'Brand' }}
                        Assets
                        with tag "{{ $tags->firstWhere('id', $filterTag)->name ?? 'Tag' }}"</h3>
                @elseif($filterType == 'category-brand-tag' && $filterCategory && $filterBrand && $filterTag)
                    <h3 class="text-lg font-medium mb-4">
                        {{ $categories->firstWhere('id', $filterCategory)->name ?? 'Category' }} Assets
                        from {{ $brands->firstWhere('id', $filterBrand)->name ?? 'Brand' }}
                        with tag "{{ $tags->firstWhere('id', $filterTag)->name ?? 'Tag' }}"</h3>
                @else
                    <h3 class="text-lg font-medium mb-4">All Assets</h3>
                @endif
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="perPage" class="rounded border-gray-300 shadow-sm">
                        <option value="12">12 per page</option>
                        <option value="24">24 per page</option>
                        <option value="36">36 per page</option>
                        <option value="48">48 per page</option>
                        <option value="100">100 per page</option>
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            <div>
                {{ $assets->links() }}
            </div>
        </div>

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
                                    <span class="text-danger-500">{{ $message }}</span>
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
                    <label for="">Select All</label>
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
                                    <p><span class="font-medium">Asset Code:</span> {{ $asset->asset_code }}</p>

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
                                    <x-filament::button class="text-sm hover:underline">Deploy
                                        Asset</x-filament::button>
                                    <x-filament::button
                                        href="{{ route('filament.app.resources.assets.edit', $asset->id) }}"
                                        tag="a" class="text-sm hover:underline">Edit
                                        Asset</x-filament::button>
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
                                            <span class="text-danger-500">{{ $message }}</span>
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
                                    Asset Code</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                    Status</x-filament-tables::header-cell>
                            @endif

                            <x-filament-tables::header-cell scope="col"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                Tags</x-filament-tables::header-cell>
                            <x-filament-tables::header-cell scope="col"
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                Action</x-filament-tables::header-cell>
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
                                <x-filament-tables::cell class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $asset->asset_code }}</div>
                                </x-filament-tables::cell>
                                <x-filament-tables::cell class="px-6 py-4 whitespace-nowrap">
                                    <x-filament::badge>
                                        <span class="capitalize">{{ $asset->status }}</span>
                                    </x-filament::badge>
                                </x-filament-tables::cell>
                                <x-filament-tables::cell class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($asset->assetTags as $tag)
                                            <x-filament::badge>
                                                <span class="text-xs">{{ $tag->name }}</span>
                                            </x-filament::badge>
                                        @endforeach
                                    </div>
                                </x-filament-tables::cell>
                                <x-filament-tables::cell
                                    class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <x-filament::button class="text-sm hover:underline">Deploy
                                        Asset</x-filament::button>
                                    <x-filament::button
                                        href="{{ route('filament.app.resources.assets.edit', $asset->id) }}"
                                        tag="a" class="text-sm hover:underline">Edit
                                        Asset</x-filament::button>
                                </x-filament-tables::cell>
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

        </x-filament::sectionsection>
        <!-- Pagination Controls -->
        <x-filament::section class="mt-6">
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

                <div class="fi-ta-pagination px-3 py-3 sm:px-6">
                    {{ $assets->links() }}
                </div>
            </div>
        </x-filament::section>
</div>
