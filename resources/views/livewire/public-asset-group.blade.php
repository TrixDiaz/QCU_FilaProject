<div class="container mx-auto pb-6 px-4">
    @if (!$classroom)
        <div class="bg-red-100 border border-red-400 text-red-700 dark:bg-red-900 dark:text-red-200 dark:border-red-700 px-4 py-3 rounded relative"
            role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">Classroom not found.</span>
        </div>
    @else
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Assets in {{ $classroom->name ?? 'Classroom' }}
            </h1>

            <!-- QR Code Button -->
            <button onclick="generateQR('{{ url()->current() }}')"
                class="bg-gray-800 text-white px-4 py-2 rounded-lg shadow flex items-center space-x-2 no-print z-50"
                id="qr-button" wire:ignore>
                Show QR Code
            </button>
        </div>

        <!-- Search Bar -->
        <div class="mb-6">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                    </svg>
                </div>
                <input type="text" wire:model.live="search"
                    class="block w-full p-3 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                    placeholder="Search assets by name or code..." />
            </div>
        </div>

        <!-- Asset Summary Section -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <!-- Total Assets -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">Total Assets</h3>
                <p class="text-4xl font-bold text-gray-700 dark:text-gray-300">{{ $assetGroupsCount }}</p>
            </div>

            <!-- Active Assets -->
            <div
                class="bg-green-100 dark:bg-green-900 p-6 rounded-lg shadow-md border border-green-300 dark:border-green-700">
                <h3 class="text-lg font-semibold text-green-700 dark:text-green-300 mb-2">Active</h3>
                <p class="text-4xl font-bold text-green-800 dark:text-green-100">{{ $activeAssetsCount }}</p>
            </div>

            <!-- Maintenance Assets -->
            <div
                class="bg-yellow-100 dark:bg-yellow-900 p-6 rounded-lg shadow-md border border-yellow-300 dark:border-yellow-700">
                <h3 class="text-lg font-semibold text-yellow-700 dark:text-yellow-300 mb-2">Maintenance</h3>
                <p class="text-4xl font-bold text-yellow-800 dark:text-yellow-100">{{ $maintenanceAssetsCount }}</p>
            </div>

            <!-- Broken Assets -->
            <div
                class="bg-orange-100 dark:bg-orange-900 p-6 rounded-lg shadow-md border border-orange-300 dark:border-orange-700">
                <h3 class="text-lg font-semibold text-orange-700 dark:text-orange-300 mb-2">Broken</h3>
                <p class="text-4xl font-bold text-orange-800 dark:text-orange-100">{{ $brokenAssetsCount }}</p>
            </div>

            <!-- Inactive Assets -->
            <div class="bg-red-100 dark:bg-red-900 p-6 rounded-lg shadow-md border border-red-300 dark:border-red-700">
                <h3 class="text-lg font-semibold text-red-700 dark:text-red-300 mb-2">Inactive</h3>
                <p class="text-4xl font-bold text-red-800 dark:text-red-100">{{ $inactiveAssetsCount }}</p>
            </div>
        </div>

        @if ($assetGroups && $assetGroups->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($assetGroups as $assetGroup)
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden transition-all duration-200 hover:shadow-lg border border-gray-200 dark:border-gray-700">
                        <div class="p-6">
                            <h2 class="text-xl font-semibold mb-2 dark:text-white">{{ $assetGroup->name }}</h2>
                            <p class="text-gray-700 dark:text-gray-300 mb-1"><span class="font-medium">Code:</span>
                                {{ $assetGroup->code }}</p>

                            @if ($assetGroup->classroomAsset)
                                <div
                                    class="mt-6 bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                                    <h3 class="text-xl font-semibold mb-4 text-gray-800 dark:text-white">Asset Details
                                    </h3>
                                    <table
                                        class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 rounded-lg overflow-hidden">
                                        <tbody
                                            class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach ([
        'Asset Name' => $assetGroup->classroomAsset->name,
        'Serial Number' => $assetGroup->classroomAsset->serial_number,
        'Asset Code' => $assetGroup->classroomAsset->asset_code,
        'Brand' => $assetGroup->classroomAsset->brand->name ?? 'N/A',
        'Category' => $assetGroup->classroomAsset->category->name ?? 'N/A',
        'Status' => ucfirst($assetGroup->status),
    ] as $label => $value)
                                                <tr class="odd:bg-gray-50 dark:odd:bg-gray-700">
                                                    <td class="py-3 px-4 font-medium text-gray-700 dark:text-gray-300">
                                                        {{ $label }}:</td>
                                                    <td class="py-3 px-4 text-gray-800 dark:text-gray-200">
                                                        @if ($label === 'Status')
                                                            <span
                                                                class="{{ $assetGroup->status == 'active' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} font-medium">
                                                                {{ $value }}
                                                            </span>
                                                        @else
                                                            {{ $value }}
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-red-500 dark:text-red-400 mt-2">No asset information available</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div
                class="bg-yellow-100 border border-yellow-400 text-yellow-700 dark:bg-yellow-800 dark:border-yellow-600 dark:text-yellow-200 px-4 py-3 rounded">
                No assets found for this classroom.
            </div>
        @endif
    @endif
</div>
