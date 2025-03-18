<div class="p-6 bg-white shadow-md rounded-lg">
    <h2 class="text-xl font-semibold mb-4">Report Builder</h2>

    <!-- Flash Messages -->
    @if (session()->has('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <!-- Report Title -->
    <div class="mb-4">
        <label class="block font-medium text-gray-700">Report Title:</label>
        <input type="text" wire:model="reportTitle" class="w-full p-2 border rounded-md" placeholder="Enter report title">
    </div>

    <!-- Module Selection as Dropdown -->
    <div class="mb-4">
        <label class="block font-medium text-gray-700">Select Module:</label>
        <select wire:model.live="selectedModule" class="w-full p-2 border rounded-md">
            <option value="inventory">Inventory - Asset and inventory reports</option>
            <option value="users">Users - User management reports</option>
            <option value="maintenance">Maintenance - Maintenance and repairs reports</option>
        </select>
    </div>

    <!-- Filters -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div>
            <label class="block font-medium text-gray-700">Date Range:</label>
            <select wire:model.live="filters.date_range" class="w-full p-2 border rounded-md">
                <option value="last_90_days">Last 90 days</option>
                <option value="last_30_days">Last 30 days</option>
                <option value="this_year">This Year</option>
            </select>
        </div>

        <!-- Show Categories filter only for Inventory module -->
        @if($selectedModule === 'inventory')
        <div>
            <label class="block font-medium text-gray-700">Categories:</label>
            <div class="border rounded-md p-2 h-32 overflow-y-auto">
                @foreach($categories as $category)
                    <label class="flex items-center mb-1">
                        <input type="checkbox" wire:model.live="filters.categories" value="{{ $category->id }}" class="mr-2">
                        {{ $category->name }}
                    </label>
                @endforeach
            </div>
        </div>
        
        <div>
            <label class="block font-medium text-gray-700">Brands:</label>
            <div class="border rounded-md p-2 h-32 overflow-y-auto">
                @foreach($brands as $brand)
                    <label class="flex items-center mb-1">
                        <input type="checkbox" wire:model.live="filters.brands" value="{{ $brand->id }}" class="mr-2">
                        {{ $brand->name }}
                    </label>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Fields Selection -->
    <div class="mb-4 border p-4 rounded-lg">
        <label class="block font-medium text-gray-700">Fields to Display:</label>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mt-2">
            @foreach($availableFields as $field)
                <label class="flex items-center">
                    <input type="checkbox" wire:model.live="selectedFields" value="{{ $field }}" class="mr-2">
                    {{ ucfirst(str_replace('_', ' ', $field)) }}
                </label>
            @endforeach
        </div>
    </div>


<!-- Right-aligned buttons with more spacing -->
<div style="display: flex; justify-content: flex-end; padding: 16px; border-top: 2px solid #e5e7eb;">
    <button wire:click="runReport" 
            style="background-color: #3b82f6; color: white; padding: 10px 20px; border-radius: 8px; font-weight: bold; border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-right: 16px;"
            wire:loading.attr="disabled">
        Run Report
    </button>

    <button wire:click="printReport"
            style="background-color: #10b981; color: white; padding: 10px 20px; border-radius: 8px; font-weight: bold; border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        Print Report
    </button>
</div>


    <!-- Report Results -->
    <div class="mt-6">
        <h3 class="text-lg font-semibold">{{ $reportTitle ? $reportTitle : 'Report Results' }}</h3>
        @if($reportGenerated)
            @if ($data->isNotEmpty())
                <div class="overflow-x-auto mt-2">
                    <table class="w-full border">
                        <thead class="bg-gray-200">
                            <tr>
                                @foreach($selectedFields as $field)
                                    <th class="p-2 text-left">{{ ucfirst(str_replace('_', ' ', $field)) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $item)
                                <tr class="border-t hover:bg-gray-50">
                                    @foreach($selectedFields as $field)
                                        <td class="p-2">
                                            @if($selectedModule === 'inventory')
                                                @if($field == 'expiry_date' && $item->$field)
                                                    {{ \Carbon\Carbon::parse($item->$field)->format('Y-m-d') }}
                                                @elseif($field == 'category')
                                                    {{ $item->category ? $item->category->name : 'N/A' }}
                                                @elseif($field == 'brand')
                                                    {{ $item->brand ? $item->brand->name : 'N/A' }}
                                                @else
                                                    {{ $item->$field ?? 'N/A' }}
                                                @endif
                                            @elseif($selectedModule === 'users')
                                                @if($field == 'created_at' && $item->$field)
                                                    {{ \Carbon\Carbon::parse($item->$field)->format('Y-m-d') }}
                                                @elseif($field == 'updated_at' && $item->$field)
                                                    {{ \Carbon\Carbon::parse($item->$field)->format('Y-m-d') }}
                                                @else
                                                    {{ $item->$field ?? 'N/A' }}
                                                @endif
                                            @elseif($selectedModule === 'maintenance')
                                                @if($field == 'date_performed' && $item->$field)
                                                    {{ \Carbon\Carbon::parse($item->$field)->format('Y-m-d') }}
                                                @elseif($field == 'asset_id')
                                                    {{ $item->asset ? $item->asset->name : 'N/A' }}
                                                @else
                                                    {{ $item->$field ?? 'N/A' }}
                                                @endif
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $data->links() }}
                </div>
                
                <!-- Export Options -->
                <div class="mt-4 flex justify-end space-x-2">
                    <button class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        Export to PDF
                    </button>
                    <button class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                        Export to Excel
                    </button>
                </div>
            @else
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mt-2">
                    <p class="text-yellow-700">No data found for selected filters.</p>
                </div>
            @endif
        @else
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mt-2">
                <p class="text-blue-700">Click "Run Report" to generate results based on your selections.</p>
            </div>
        @endif
    </div>
    
    <!-- Floating Action Button for Mobile -->
    <div class="md:hidden fixed bottom-4 right-4 z-50">
        <button wire:click="runReport" 
                class="bg-green-600 text-white p-4 rounded-full shadow-lg hover:bg-green-700 flex items-center justify-center"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-75">
            <span wire:loading.remove wire:target="runReport">Run</span>
        </button>
    </div>
    
    @if($reportGenerated && $selectedModule === 'inventory')
    <div class="mt-6">
        <h3 class="text-lg font-semibold">Category & Brand Counts</h3>

        <!-- Combined Category & Brand Counts Table -->
        <div class="overflow-x-auto mt-2">
            <table class="w-full border">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="p-2 text-left">Category</th>
                        <th class="p-2 text-left">Total Count</th>
                        <th class="p-2 text-left">Brand</th>
                        <th class="p-2 text-left">Total Count</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $maxRows = max($categoryCounts->count(), $brandCounts->count());
                    @endphp

                    @for ($i = 0; $i < $maxRows; $i++)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="p-2">
                                {{ $categoryCounts[$i]->category->name ?? '' }}
                            </td>
                            <td class="p-2">
                                {{ $categoryCounts[$i]->total ?? '' }}
                            </td>
                            <td class="p-2">
                                {{ $brandCounts[$i]->brand->name ?? '' }}
                            </td>
                            <td class="p-2">
                                {{ $brandCounts[$i]->total ?? '' }}
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
        Livewire.on('openPrintPreview', function () {
            setTimeout(() => {
                window.print();
            }, 500); // Small delay to ensure UI updates
        });
    });
</script>


</div>