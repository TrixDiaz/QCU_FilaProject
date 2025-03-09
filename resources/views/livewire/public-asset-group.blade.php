<div class="container mx-auto py-6 px-4">
    @if (!$classroom)
        <div class="bg-red-100 border border-red-400 text-red-700 dark:bg-red-900 dark:text-red-200 dark:border-red-700 px-4 py-3 rounded relative"
            role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">Classroom not found.</span>
        </div>
    @else
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold dark:text-white">Assets in {{ $classroom->name ?? 'Classroom' }}</h1>

            <!-- QR Code Button -->
            <button @click="generateQR(window.location.href)"
                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg shadow flex items-center space-x-2 no-print">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
                <span>Show QR Code</span>
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
                                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <h3 class="text-lg font-medium mb-2 dark:text-white">Asset Details</h3>
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <tbody
                                            class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            <tr>
                                                <td
                                                    class="py-2 pr-2 w-1/3 font-medium text-gray-700 dark:text-gray-300">
                                                    Asset Name:</td>
                                                <td class="py-2 text-gray-800 dark:text-gray-200">
                                                    {{ $assetGroup->classroomAsset->name }}</td>
                                            </tr>
                                            <tr>
                                                <td
                                                    class="py-2 pr-2 w-1/3 font-medium text-gray-700 dark:text-gray-300">
                                                    Serial Number:</td>
                                                <td class="py-2 text-gray-800 dark:text-gray-200">
                                                    {{ $assetGroup->classroomAsset->serial_number }}</td>
                                            </tr>
                                            <tr>
                                                <td
                                                    class="py-2 pr-2 w-1/3 font-medium text-gray-700 dark:text-gray-300">
                                                    Asset Code:</td>
                                                <td class="py-2 text-gray-800 dark:text-gray-200">
                                                    {{ $assetGroup->classroomAsset->asset_code }}</td>
                                            </tr>
                                            <tr>
                                                <td
                                                    class="py-2 pr-2 w-1/3 font-medium text-gray-700 dark:text-gray-300">
                                                    Brand:</td>
                                                <td class="py-2 text-gray-800 dark:text-gray-200">
                                                    {{ $assetGroup->classroomAsset->brand->name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td
                                                    class="py-2 pr-2 w-1/3 font-medium text-gray-700 dark:text-gray-300">
                                                    Category:</td>
                                                <td class="py-2 text-gray-800 dark:text-gray-200">
                                                    {{ $assetGroup->classroomAsset->category->name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td
                                                    class="py-2 pr-2 w-1/3 font-medium text-gray-700 dark:text-gray-300">
                                                    Status:</td>
                                                <td class="py-2">
                                                    <span
                                                        class="{{ $assetGroup->status == 'active' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} font-medium">
                                                        {{ ucfirst($assetGroup->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <button @click="generateQR('{{ url()->current() }}?asset={{ $assetGroup->id }}')"
                                        class="mt-4 bg-indigo-500 hover:bg-indigo-600 text-white px-3 py-1 rounded text-sm flex items-center space-x-1 no-print">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                        </svg>
                                        <span>Asset QR</span>
                                    </button>
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
