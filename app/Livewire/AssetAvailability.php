<?php

namespace App\Livewire;

use App\Models\Asset;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class AssetAvailability extends ApexChartWidget
{
    /**
     * Chart Id
     */
    protected static ?string $chartId = 'assetAvailability';

    /**
     * Widget Title
     */
    protected static ?string $heading = 'Asset Availability';

    /**
     * Widget content height
     */
    protected static ?int $contentHeight = 275;

    /**
     * Get Chart Options
     */
    protected function getOptions(): array
    {
        $assets = ['Asset 1', 'Asset 2', 'Asset 3', 'Asset 4', 'Asset 5'];

        //  Fetch ACTIVE and INACTIVE counts
        $active = [];
        $inactive = [];
        $deploy =[];

        foreach ($assets as $assetName) {
            $active[] = Asset::where('name', $assetName)->where('status', 'ACTIVE')->count();
            $inactive[] = Asset::where('name', $assetName)->where('status', 'INACTIVE')->count();
            $deploy[] = Asset::where('name', $assetName)->where('status', 'DEPLOY')->count();
        }

        return [
            'chart' => [
                'type' => 'bar',
                'stacked' => true, //  Enable stacked bars
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'Active',
                    'data' => $active, //  Use ACTIVE status data
                ],
                [
                    'name' => 'Inactive',
                    'data' => $inactive, //  Use INACTIVE status data
                ],
                [
                    'name' => 'Deployed',
                    'data' => $deploy, //  Use DEPLOYED status data
                ],
            ],
            'xaxis' => [
                'categories' => $assets, //  Asset names on X-axis
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
            'colors' => ['#22C55E', '#EF4444', '#FACC15'], // Green (Active), Red (Inactive), Yellow (Deployed)
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 4,
                    'horizontal' => false,
                ],
            ],
            'legend' => [
                'position' => 'bottom',
            ],
        ];
    }
}
