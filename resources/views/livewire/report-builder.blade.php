<div class="p-6 shadow-md rounded-lg bg-white dark:bg-gray-800">
    <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Report Builder</h2>

    <!-- Flash Messages -->
    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 dark:bg-red-900 dark:border-red-700 dark:text-red-200"
            role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Form -->
    <form wire:submit="runReport" class="screen-only">
        {{ $this->form }}
    </form>

    <!-- Buttons -->
    <div class="my-4 flex justify-end space-x-2 screen-only gap-4">
        <x-filament::button wire:click="runReport">
            Run Report
        </x-filament::button>

        @if ($reportGenerated)
            <x-filament::button wire:click="printReport">
                Print Report
            </x-filament::button>
        @endif
    </div>

    <!-- Results section -->
    @if ($reportGenerated && $displayData->isNotEmpty())
        <!-- Screen display version -->
        <div class="mt-6 screen-only">
            <x-filament::section>
                <x-slot name="heading">
                    {{ $reportTitle }}
                </x-slot>

                <div id="report-content" class="overflow-x-auto">
                    <table class="fi-ta-table w-full border-collapse">
                        <thead>
                            <tr class="fi-ta-header-row border-b-2 border-gray-200 dark:border-gray-700">
                                <th class="fi-ta-cell p-3 text-left bg-gray-50 dark:bg-gray-800">
                                    #
                                </th>
                                @foreach ($selectedFields as $field)
                                    <th class="fi-ta-cell p-3 text-left bg-gray-50 dark:bg-gray-800">
                                        {{ ucfirst(str_replace('_', ' ', $field)) }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($displayData as $index => $item)
                                <tr
                                    class="fi-ta-row border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 {{ $index % 2 === 0 ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-900' }}">
                                    <td class="fi-ta-cell p-3">
                                        {{ $index + 1 }}
                                    </td>

                                    @foreach ($selectedFields as $field)
                                        <td class="fi-ta-cell p-3">
                                            @if ($field == 'classroom_id' && isset($item->classroom))
                                                {{ $item->classroom->name ?? 'N/A' }}
                                            @elseif($field == 'category_id' && isset($item->category))
                                                {{ $item->category->name ?? 'N/A' }}
                                            @elseif($field == 'brand_id' && isset($item->brand))
                                                {{ $item->brand->name ?? 'N/A' }}
                                            @elseif($field == 'status' && isset($item->status))
                                                {{ ucfirst($item->status ?? 'N/A') }}
                                            @elseif($field == 'approval_status' && isset($item->approval_status))
                                                {{ $item->approval_status == 1 ? 'Approved' : 'Pending' }}
                                            @elseif(is_object($item))
                                                @if (property_exists($item, $field) || isset($item->$field))
                                                    @if ($field == 'created_at' || $field == 'updated_at' || $field == 'expiry_date')
                                                        {{ $item->$field ? \Carbon\Carbon::parse($item->$field)->format('M d, Y H:i') : 'N/A' }}
                                                    @else
                                                        {{ $item->$field ?? 'N/A' }}
                                                    @endif
                                                @else
                                                    N/A
                                                @endif
                                            @elseif(is_array($item))
                                                {{ $item[$field] ?? 'N/A' }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        </div>

        <!-- Print-only version -->
        <div id="printable-report" class="print-only">
            <div class="print-header mb-4">
                <h2 class="text-xl font-bold">{{ $reportTitle }}</h2>
                <p class="text-sm text-gray-600">Generated by: {{ auth()->user()->name }}</p>
                <p class="text-sm text-gray-600">Date: {{ now()->format('F d, Y h:i A') }}</p>
                <hr class="my-2">
            </div>

            <table border="1" cellspacing="0" cellpadding="8"
                style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <thead>
                    <tr>
                        <th style="border: 1px solid #000; background-color: #f2f2f2; text-align: left; padding: 8px;">#
                        </th>
                        @foreach ($selectedFields as $field)
                            <th
                                style="border: 1px solid #000; background-color: #f2f2f2; text-align: left; padding: 8px;">
                                {{ ucfirst(str_replace('_', ' ', $field)) }}
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @foreach ($displayData as $index => $item)
                        <tr
                            style="{{ $index % 2 === 0 ? 'background-color: #ffffff;' : 'background-color: #f9f9f9;' }}">
                            <td style="border: 1px solid #000; padding: 8px; text-align: left;">{{ $index + 1 }}
                            </td>

                            @foreach ($selectedFields as $field)
                                <td style="border: 1px solid #000; padding: 8px; text-align: left;">
                                    @if ($field == 'classroom_id' && isset($item->classroom))
                                        {{ $item->classroom->name ?? 'N/A' }}
                                    @elseif($field == 'category_id' && isset($item->category))
                                        {{ $item->category->name ?? 'N/A' }}
                                    @elseif($field == 'brand_id' && isset($item->brand))
                                        {{ $item->brand->name ?? 'N/A' }}
                                    @elseif($field == 'status' && isset($item->status))
                                        {{ ucfirst($item->status ?? 'N/A') }}
                                    @elseif($field == 'approval_status' && isset($item->approval_status))
                                        {{ $item->approval_status == 1 ? 'Approved' : 'Pending' }}
                                    @elseif(is_object($item))
                                        @if (property_exists($item, $field) || isset($item->$field))
                                            @if ($field == 'created_at' || $field == 'updated_at' || $field == 'expiry_date')
                                                {{ $item->$field ? \Carbon\Carbon::parse($item->$field)->format('M d, Y H:i') : 'N/A' }}
                                            @else
                                                {{ $item->$field ?? 'N/A' }}
                                            @endif
                                        @else
                                            N/A
                                        @endif
                                    @elseif(is_array($item))
                                        {{ $item[$field] ?? 'N/A' }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @elseif($reportGenerated)
        <div class="mt-4 p-4 bg-yellow-50 text-yellow-700 rounded screen-only">
            No data found matching your criteria.
        </div>
    @endif

    <style>
        @media screen {
            .print-only {
                display: none;
            }
        }

        @media print {
            body * {
                visibility: hidden;
            }

            .screen-only {
                display: none !important;
            }

            #printable-report,
            #printable-report * {
                visibility: visible !important;
            }

            #printable-report {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                padding: 20px;
            }

            #printable-report table {
                border-collapse: collapse;
                width: 100%;
                margin-top: 20px;
                page-break-inside: auto;
            }

            #printable-report th,
            #printable-report td {
                border: none !important;
                padding: 8px;
                text-align: left;
            }

            #printable-report th {
                background-color: #f2f2f2;
            }

            #printable-report tr:nth-child(even) {
                background-color: #f9f9f9;
            }

            #printable-report tr {
                page-break-inside: avoid;
            }

            @page {
                size: portrait;
                margin: 1cm;
            }
        }
    </style>

    <script>
        document.addEventListener('livewire:initialized', function() {
            @this.on('reportGenerated', function() {
                // Scroll to the report section
                document.querySelector('.fi-section-header-heading')?.scrollIntoView({
                    behavior: 'smooth'
                });
            });

            @this.on('openPrintPreview', function() {
                window.print();
            });
        });
    </script>
</div>
