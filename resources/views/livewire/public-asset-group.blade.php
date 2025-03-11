<div class="container mx-auto py-6 px-4">
    @if (!$classroom)
        <div class="bg-red-100 border border-red-400 text-red-700 dark:bg-red-900 dark:text-red-200 dark:border-red-700 px-4 py-3 rounded relative"
            role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">Classroom not found.</span>
        </div>
    @else
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Assets in {{ $classroom->name ?? 'Classroom' }}</h1>
        
            <!-- QR Code Button -->
            <button 
                onclick="generateQR('{{ url()->current() }}')"
                class="bg-gray-800 text-white px-4 py-2 rounded-lg shadow flex items-center space-x-2 no-print z-50"
                id="qr-button"
                wire:ignore
            >
                Show QR Code
            </button>
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
                                    <div class="mt-6 bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                                        <h3 class="text-xl font-semibold mb-4 text-gray-800 dark:text-white">Asset Details</h3>
                                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 rounded-lg overflow-hidden">
                                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                                @foreach ([
                                                    'Asset Name' => $assetGroup->classroomAsset->name,
                                                    'Serial Number' => $assetGroup->classroomAsset->serial_number,
                                                    'Asset Code' => $assetGroup->classroomAsset->asset_code,
                                                    'Brand' => $assetGroup->classroomAsset->brand->name ?? 'N/A',
                                                    'Category' => $assetGroup->classroomAsset->category->name ?? 'N/A',
                                                    'Status' => ucfirst($assetGroup->status)
                                                ] as $label => $value)
                                                    <tr class="odd:bg-gray-50 dark:odd:bg-gray-700">
                                                        <td class="py-3 px-4 font-medium text-gray-700 dark:text-gray-300">{{ $label }}:</td>
                                                        <td class="py-3 px-4 text-gray-800 dark:text-gray-200">
                                                            @if($label === 'Status')
                                                                <span class="{{ $assetGroup->status == 'active' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} font-medium">
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