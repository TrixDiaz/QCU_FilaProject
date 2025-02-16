<?php

namespace App\Livewire;

use App\Models\Asset;
use App\Models\Category;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Illuminate\Support\Facades\DB;

class AllocationStatus extends ApexChartWidget
{
    protected static ?string $chartId = 'allocationStatus';
    protected static ?int $contentHeight = 275;
    protected static ?string $heading = 'Asset Status';

    protected function getOptions(): array
    {
        // Get categories
        $categories = Category::where('is_active', true)
            ->select('id', 'name')
            ->get();

        // Initialize arrays for data
        $categoryNames = [];
        $inactiveData = [];
        $activeData = [];

        foreach ($categories as $category) {
            $categoryNames[] = $category->name;
            
            // Count inactive assets
            $inactive = Asset::where('category_id', $category->id)
                ->where('status', 'inactive')
                ->count();
            
            // Count active assets
            $active = Asset::where('category_id', $category->id)
                ->where('status', 'active')
                ->count();

            $inactiveData[] = $inactive;
            $activeData[] = $active;
        }

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
                'stacked' => true,
                'toolbar' => [
                    'show' => false,
                ],
            ],
            'series' => [
                [
                    'name' => 'Inactive',
                    'data' => $inactiveData,
                ],
                [
                    'name' => 'Active',
                    'data' => $activeData,
                ],
            ],
            'xaxis' => [
                'categories' => $categoryNames,
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
                'min' => 0,
                'tickAmount' => 4,
            ],
            'colors' => ['#f44336', '#00e396'], // Red for Inactive, Green for Active
            'plotOptions' => [
                'bar' => [
                    'horizontal' => false,
                    'columnWidth' => '70%',
                    'borderRadius' => 0,
                ],
            ],
            'grid' => [
                'show' => true,
                'borderColor' => '#e5e7eb',
                'strokeDashArray' => 1,
                'position' => 'back',
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'legend' => [
                'position' => 'right',  
                'horizontalAlign' => 'left', 
            ],
        ];
    }
}
