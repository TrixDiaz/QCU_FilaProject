<div class="container mx-auto py-6 px-4">
    @if (!$classroom)
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">Classroom not found.</span>
        </div>
    @else
        <h1 class="text-2xl font-bold mb-6">Assets in {{ $classroom->name ?? 'Classroom' }}</h1>

        @if ($assetGroups && $assetGroups->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($assetGroups as $assetGroup)
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="p-6">
                            <h2 class="text-xl font-semibold mb-2">{{ $assetGroup->name }}</h2>
                            <p class="text-gray-700 mb-1"><span class="font-medium">Code:</span> {{ $assetGroup->code }}
                            </p>

                            @if ($assetGroup->classroomAsset)
                                <div class="mt-4 pt-4 border-t">
                                    <h3 class="text-lg font-medium mb-2">Asset Details</h3>
                                    <p class="text-gray-700 mb-1">
                                        <span class="font-medium">Asset Name:</span>
                                        {{ $assetGroup->classroomAsset->name }}
                                    </p>
                                    <p class="text-gray-700 mb-1">
                                        <span class="font-medium">Serial Number:</span>
                                        {{ $assetGroup->classroomAsset->serial_number }}
                                    </p>
                                    <p class="text-gray-700 mb-1">
                                        <span class="font-medium">Asset Code:</span>
                                        {{ $assetGroup->classroomAsset->asset_code }}
                                    </p>
                                    <p class="text-gray-700 mb-1">
                                        <span class="font-medium">Brand:</span>
                                        {{ $assetGroup->classroomAsset->brand->name ?? 'N/A' }}
                                    </p>
                                    <p class="text-gray-700 mb-1">
                                        <span class="font-medium">Category:</span>
                                        {{ $assetGroup->classroomAsset->category->name ?? 'N/A' }}
                                    </p>
                                    <p class="text-gray-700 mb-1">
                                        <span class="font-medium">Status:</span>
                                        <span
                                            class="{{ $assetGroup->status == 'active' ? 'text-green-600' : 'text-red-600' }}">
                                            {{ ucfirst($assetGroup->status) }}
                                        </span>
                                    </p>
                                </div>
                            @else
                                <p class="text-red-500 mt-2">No asset information available</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                No assets found for this classroom.
            </div>
        @endif
    @endif
</div>
