<?php

namespace App\Livewire;

use App\Models\Asset;
use App\Models\Category;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class AssetAvailability extends ApexChartWidget
{
    protected static ?string $chartId = 'assetAvailability';
    protected static ?string $heading = 'Asset Availability';
    protected static ?int $contentHeight = 300;

    protected function getOptions(): array
    {
        $categories = Category::pluck('name')->toArray();

        $active = [];
        $inactive = [];
        $deploy = [];

        foreach ($categories as $category) {
            $active[] = Asset::whereHas('category', function ($query) use ($category) {
                $query->where('name', $category);
            })->where('status', 'available')->count();

            $inactive[] = Asset::whereHas('category', function ($query) use ($category) {
                $query->where('name', $category);
            })->where('status', 'inactive')->count();

            $deploy[] = Asset::whereHas('category', function ($query) use ($category) {
                $query->where('name', $category);
            })->where('status', 'deployed')->count();

            $maintenance[] = Asset::whereHas('category', function ($query) use ($category) {
                $query->where('name', $category);
            })->where('status', 'maintenance')->count();
        }

        return [
            'chart' => [
                'type' => 'bar',
                'stacked' => true,
                'height' => 300,
            ],
            'series' => [
                ['name' => 'Active', 'data' => $active],
                ['name' => 'Inactive', 'data' => $inactive],
                ['name' => 'Deployed', 'data' => $deploy],
                ['name' => 'Maintenance', 'data' => $maintenance],
            ],
            'xaxis' => [
                'categories' => $categories,
                'labels' => ['style' => ['fontFamily' => 'inherit']],
            ],
            'yaxis' => ['labels' => ['style' => ['fontFamily' => 'inherit']]],
            'colors' => ['#22C55E', '#EF4444', '#FACC15', '#3B82F6'],
            'plotOptions' => ['bar' => ['borderRadius' => 4, 'horizontal' => false]],
            'legend' => ['position' => 'bottom'],
        ];
    }
}
