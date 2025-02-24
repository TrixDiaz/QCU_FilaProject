<?php

namespace App\Livewire;

use App\Models\Asset;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class AssetCategories extends ApexChartWidget
{
    protected static ?string $chartId = 'monthlyAssetTrends';
    protected static ?int $contentHeight = 275;
    protected static ?string $heading = 'Asset Distribution';

    protected function getOptions(): array
    {
        $data = $this->getChartData();

        return [
            'chart' => [
                'type' => 'line',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'New Assets',
                    'data' => $data['counts'],
                ],
            ],
            'xaxis' => [
                'categories' => $data['months'],
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
            'colors' => ['#f59e0b'],
            'stroke' => [
                'curve' => 'smooth',
            ],
            'tooltip' => [
                'y' => [
                    'formatter' => 'function (val) { return val + " assets" }'
                ]
            ]
        ];
    }

    protected function getChartData(): array
    {
        // Count new assets created per month for the last 12 months
        $results = Asset::select(
            DB::raw('COUNT(*) as count'),
            DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month") // Use DATE_FORMAT() instead of strftime()
        )
            ->where('created_at', '>=', now()->subMonths(11))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $months = collect([]);
        $counts = collect([]);

        // Ensure all months are represented, even those with no new assets
        for ($i = 11; $i >= 0; $i--) {
            $monthDate = now()->subMonths($i);
            $monthKey = $monthDate->format('Y-m');

            $monthData = $results->firstWhere('month', $monthKey);

            $months->push($monthDate->format('M'));
            $counts->push($monthData ? $monthData->count : 0);
        }

        return [
            'months' => $months->toArray(),
            'counts' => $counts->toArray(),
        ];
    }
}
