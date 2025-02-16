<?php

namespace App\Livewire;

use App\Models\Asset;
use Carbon\Carbon;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class InventoryTrends extends ApexChartWidget
{
    /**
     * Chart Id
     */
    protected static ?string $chartId = 'inventoryTrends';

    /**
     * Widget Title
     */
    protected static ?string $heading = 'Inventory Trends';

    /**
     * Widget content height
     */
    protected static ?int $contentHeight = 300;

    /**
     * Get Chart Options (Dynamically Fetch Data)
     */
    protected function getOptions(): array
    {
        // Get the last 12 months dynamically
        $months = collect(range(0, 11))->map(function ($i) {
            return Carbon::now()->subMonths($i)->format('M');
        })->reverse()->toArray();

        // Fetch total assets added per month dynamically
        $monthlyAssets = [];
        foreach ($months as $month) {
            $date = Carbon::parse($month . ' 1 ' . now()->year); // Convert to full date format

            $monthlyAssets[] = Asset::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
        }

        return [
            'chart' => [
                'type' => 'line',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'Total Assets Added',
                    'data' => $monthlyAssets, //  Use dynamic data from the database
                ],
            ],
            'xaxis' => [
                'categories' => $months, //  Use real month names dynamically
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'colors' => ['#f59e0b'], // Orange color for visibility
            'stroke' => [
                'curve' => 'smooth', // Make the trend line smooth
            ],
            'legend' => [
                'position' => 'bottom',
            ],
        ];
    }
}
