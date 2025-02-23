<?php

namespace App\Livewire;

use App\Models\Asset;
use App\Models\Category;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Illuminate\Support\Facades\DB;

class AllocationStatus extends ApexChartWidget
{
    // protected static ?string $chartId = 'allocationStatus';
    // protected static ?int $contentHeight = 275;
    // protected static ?string $heading = 'Asset Status';

    // protected function getOptions(): array
    // {
    //     // Get categories
    //     $categories = Category::where('is_active', true)
    //         ->select('id', 'name')
    //         ->get();

    //     // Initialize arrays for data
    //     $categoryNames = [];
    //     $inactiveData = [];
    //     $activeData = [];
    //     $deployedData = [];

    //     foreach ($categories as $category) {
    //         $categoryNames[] = $category->name;
            
    //         // Count inactive assets - ensure exact string match
    //         $inactive = Asset::where('category_id', $category->id)
    //             ->whereRaw('LOWER(status) = ?', ['inactive'])
    //             ->count();
            
    //         // Count active assets - ensure exact string match
    //         $active = Asset::where('category_id', $category->id)
    //             ->whereRaw('LOWER(status) = ?', ['active'])
    //             ->count();

    //         // Count deployed assets - ensure exact string match
    //         $deployed = Asset::where('category_id', $category->id)
    //             ->whereRaw('LOWER(status) = ?', ['deployed'])
    //             ->count();

    //         $inactiveData[] = $inactive;
    //         $activeData[] = $active;
    //         $deployedData[] = $deployed;

    //         // Add debu
    //         // g logging to check the counts
    //         \Log::info("Category: {$category->name}", [
    //             'inactive' => $inactive,
    //             'active' => $active,
    //             'deployed' => $deployed
    //         ]);
    //     }

    //     return [
    //         'chart' => [
    //             'type' => 'bar',
    //             'height' => 300,
    //             'stacked' => true,
    //             'toolbar' => [
    //                 'show' => false,
    //             ],
    //         ],
    //         'series' => [
    //             [
    //                 'name' => 'Inactive',
    //                 'data' => $inactiveData,
    //             ],
    //             [
    //                 'name' => 'Active',
    //                 'data' => $activeData,
    //             ],
    //             [
    //                 'name' => 'Deployed',
    //                 'data' => $deployedData,
    //             ],
    //         ],
    //         'xaxis' => [
    //             'categories' => $categoryNames,
    //             'labels' => [
    //                 'style' => [
    //                     'fontFamily' => 'inherit',
    //                 ],
    //             ],
    //         ],
    //         'yaxis' => [
    //             'labels' => [
    //                 'style' => [
    //                     'fontFamily' => 'inherit',
    //                 ],
    //             ],
    //             'min' => 0,
    //             'tickAmount' => 4,
    //         ],
    //         'colors' => ['#f44336', '#00e396', '#3366ff'],
    //         'plotOptions' => [
    //             'bar' => [
    //                 'horizontal' => false,
    //                 'columnWidth' => '70%',
    //                 'borderRadius' => 4,
    //             ],
    //         ],
    //         'grid' => [
    //             'show' => true,
    //             'borderColor' => '#e5e7eb',
    //             'strokeDashArray' => 1,
    //             'position' => 'back',
    //         ],
    //         'dataLabels' => [
    //             'enabled' => false,
    //         ],
    //         'legend' => [
    //             'position' => 'right',
    //             'horizontalAlign' => 'left',
    //             'offsetY' => 40,
    //         ],
    //     ];
    // }
}