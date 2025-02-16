<?php

namespace App\Livewire;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Str;

class InventoryStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Get categories and their asset counts
        $categories = Category::where('is_active', true)
            ->withCount('assets')
            ->get();

        // Function to format descriptions with aligned numbers at the end
        $formatDescription = function ($categoryName, $count) {
            return sprintf("%-15s %15d", $categoryName, $count);
        };

        // Build the description string for total assets
        $totalAssetsDescription = "<pre>" . $categories->map(function ($category) use ($formatDescription) {
            return $formatDescription($category->name, $category->assets_count);
        })->join("\n") . "</pre>";

        // Get allocated/deployed assets
        $allocatedAssets = Asset::where('status', 'deploy')->count();

        // Build the description string for allocated assets
        $allocatedDescription = "<pre>" . $categories->map(function ($category) use ($formatDescription) {
            $activeCount = Asset::where('category_id', $category->id)
                ->where('status', 'deploy')
                ->count();
            return $formatDescription($category->name, $activeCount);
        })->join("\n") . "</pre>";

        // Build maintenance stats
        $maintenanceCount = Ticket::where('status', 'open')
            ->where('ticket_type', 'incident')
            ->count();

        $maintenanceDescription = "<pre>" . $categories->map(function ($category) use ($formatDescription) {
            $maintenanceCount = Ticket::whereHas('asset', function ($query) use ($category) {
                $query->where('category_id', $category->id);
            })
                ->where('status', 'open')
                ->where('ticket_type', 'incident')
                ->count();

            return $formatDescription($category->name, $maintenanceCount);
        })->join("\n") . "</pre>";

        return [
            Stat::make('Total Assets', Asset::count())
                ->icon('heroicon-o-computer-desktop')
                ->color('info')
                ->description(Str::of($totalAssetsDescription)->toHtmlString()),

            Stat::make('Allocated', $allocatedAssets)
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->description(Str::of($allocatedDescription)->toHtmlString()),

            Stat::make('In Maintenance', $maintenanceCount)
                ->icon('heroicon-o-exclamation-triangle')
                ->color('warning')
                ->description(Str::of($maintenanceDescription)->toHtmlString()),
        ];
    }
}
