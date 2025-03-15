<div>
    <section class="flex flex-row justify-between items-center mb-6">
        <div>
            <h2 class="text-xl font-bold">Inventory Assets</h2>
            <div class="text-sm text-gray-500 capitalize">
                @if ($filterType == 'all')
                    Showing all {{ $filteredCount }} assets
                @elseif($filterType == 'brand' && $filterValue)
                    Showing {{ $filteredCount }} assets from {{ $brands->firstWhere('id', $filterValue)->name ?? '' }}
                @elseif($filterType == 'category' && $filterValue)
                    Showing {{ $filteredCount }} assets in {{ $categories->firstWhere('id', $filterValue)->name ?? '' }}
                @elseif($filterType == 'tag' && $filterValue)
                    Showing {{ $filteredCount }} assets with tag
                    "{{ $tags->firstWhere('id', $filterValue)->name ?? '' }}"
                @elseif($filterType == 'brand-category' && $filterBrand && $filterCategory)
                    Showing {{ $filteredCount }} assets from {{ $brands->firstWhere('id', $filterBrand)->name ?? '' }}
                    in {{ $categories->firstWhere('id', $filterCategory)->name ?? '' }}
                @elseif($filterType == 'brand-tag' && $filterBrand && $filterTag)
                    Showing {{ $filteredCount }} assets from {{ $brands->firstWhere('id', $filterBrand)->name ?? '' }}
                    with tag "{{ $tags->firstWhere('id', $filterTag)->name ?? '' }}"
                @elseif($filterType == 'category-brand-tag' && $filterCategory && $filterBrand && $filterTag)
                    Showing {{ $filteredCount }} {{ $categories->firstWhere('id', $filterCategory)->name ?? '' }}
                    assets
                    from {{ $brands->firstWhere('id', $filterBrand)->name ?? '' }}
                    with tag "{{ $tags->firstWhere('id', $filterTag)->name ?? '' }}"
                @endif
            </div>
        </div>

        <div class="flex space-x-4 gap-4">
            <x-filament::input.wrapper>
                <x-filament::input.select wire:model.live="filterType" class="rounded border-gray-300 shadow-sm">
                    <option value="all">All Assets ({{ $totalAssets }})</option>
                    <option value="brand">Filter by Brand</option>
                    <option value="category">Filter by Category</option>
                    <option value="tag">Filter by Tag</option>
                    <option value="brand-category">Filter by Brand & Category</option>
                    <option value="brand-tag">Filter by Brand & Tag</option>
                    <option value="category-brand-tag">Filter by Category, Brand & Tag</option>
                </x-filament::input.select>
            </x-filament::input.wrapper>

            @if ($filterType == 'brand')
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="filterValue" class="rounded border-gray-300 shadow-sm">
                        <option value="">Select Brand</option>
                        @foreach ($brands as $brand)
                            <option class="capitalize" value="{{ $brand->id }}">{{ $brand->name }}
                            </option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            @elseif($filterType == 'category')
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="filterValue" class="rounded border-gray-300 shadow-sm">
                        <option value="">Select Category</option>
                        @foreach ($categories as $category)
                            <option class="capitalize" value="{{ $category->id }}">{{ $category->name }}
                            </option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            @elseif($filterType == 'tag')
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="filterValue" class="rounded border-gray-300 shadow-sm">
                        <option value="">Select Tag</option>
                        @foreach ($tags as $tag)
                            <option class="capitalize" value="{{ $tag->id }}">{{ $tag->name }}
                            </option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            @elseif($filterType == 'brand-category')
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="filterBrand" class="rounded border-gray-300 shadow-sm">
                        <option value="">Select Brand</option>
                        @foreach ($brands as $brand)
                            <option class="capitalize" value="{{ $brand->id }}">{{ $brand->name }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="filterCategory"
                        class="rounded border-gray-300 shadow-sm">
                        <option value="">Select Category</option>
                        @foreach ($categories as $category)
                            <option class="capitalize" value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            @elseif($filterType == 'brand-tag')
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="filterBrand" class="rounded border-gray-300 shadow-sm">
                        <option value="">Select Brand</option>
                        @foreach ($brands as $brand)
                            <option class="capitalize" value="{{ $brand->id }}">{{ $brand->name }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="filterTag" class="rounded border-gray-300 shadow-sm">
                        <option value="">Select Tag</option>
                        @foreach ($tags as $tag)
                            <option class="capitalize" value="{{ $tag->id }}">{{ $tag->name }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            @elseif($filterType == 'category-brand-tag')
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="filterCategory"
                        class="rounded border-gray-300 shadow-sm">
                        <option value="">Select Category</option>
                        @foreach ($categories as $category)
                            <option class="capitalize" value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="filterBrand" class="rounded border-gray-300 shadow-sm">
                        <option value="">Select Brand</option>
                        @foreach ($brands as $brand)
                            <option class="capitalize" value="{{ $brand->id }}">{{ $brand->name }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="filterTag" class="rounded border-gray-300 shadow-sm">
                        <option value="">Select Tag</option>
                        @foreach ($tags as $tag)
                            <option class="capitalize" value="{{ $tag->id }}">{{ $tag->name }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            @endif
        </div>
    </section>

    <section>
        @if ($filterType == 'brand' && $filterValue)
            <h3 class="text-lg font-medium mb-4 capitalize">
                {{ $brands->firstWhere('id', $filterValue)->name ?? 'Brand' }} Assets
            </h3>
        @elseif($filterType == 'category' && $filterValue)
            <h3 class="text-lg font-medium mb-4">{{ $categories->firstWhere('id', $filterValue)->name ?? 'Category' }}
                Assets</h3>
        @elseif($filterType == 'tag' && $filterValue)
            <h3 class="text-lg font-medium mb-4">Assets tagged with
                "{{ $tags->firstWhere('id', $filterValue)->name ?? 'Tag' }}"</h3>
        @elseif($filterType == 'brand-category' && $filterBrand && $filterCategory)
            <h3 class="text-lg font-medium mb-4">{{ $brands->firstWhere('id', $filterBrand)->name ?? 'Brand' }}
                {{ $categories->firstWhere('id', $filterCategory)->name ?? 'Category' }} Assets</h3>
        @elseif($filterType == 'brand-tag' && $filterBrand && $filterTag)
            <h3 class="text-lg font-medium mb-4">{{ $brands->firstWhere('id', $filterBrand)->name ?? 'Brand' }} Assets
                with tag "{{ $tags->firstWhere('id', $filterTag)->name ?? 'Tag' }}"</h3>
        @elseif($filterType == 'category-brand-tag' && $filterCategory && $filterBrand && $filterTag)
            <h3 class="text-lg font-medium mb-4">
                {{ $categories->firstWhere('id', $filterCategory)->name ?? 'Category' }} Assets
                from {{ $brands->firstWhere('id', $filterBrand)->name ?? 'Brand' }}
                with tag "{{ $tags->firstWhere('id', $filterTag)->name ?? 'Tag' }}"</h3>
        @else
            <h3 class="text-lg font-medium mb-4">All Assets</h3>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($assets as $asset)
                <x-filament::section>
                    <div class="rounded-lg shadow overflow-hidden hover:shadow-md transition-shadow">
                        <div class="p-4">
                            <div class="flex justify-between mb-2">
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

                            <div class="mt-3 flex justify-end">
                                <button class="text-sm hover:underline">View Details</button>
                            </div>
                        </div>
                    </div>
                </x-filament::section>
            @empty
                <div class="col-span-full py-10 text-center">
                    <p class="text-lg">No assets found matching your filter criteria.</p>
                    @if ($filterType != 'all')
                        <button wire:click="$set('filterType', 'all')" class="mt-2 hover:underline">
                            Show all assets
                        </button>
                    @endif
                </div>
            @endforelse
        </div>

        <!-- Pagination Controls -->
        <div class="mt-6">
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
        </div>
    </section>
</div>
