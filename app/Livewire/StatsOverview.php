<?php

namespace App\Livewire;

use Filament\Forms\Components\Placeholder;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', '100')
                ->description('Active ' . '50'),
            Stat::make('Total Users', '100')
                ->description('Active ' . '50'),
            Stat::make('Total Assets', '21%')
                ->description('3% decrease' . PHP_EOL . 'Compared to last quarter'),
            Stat::make('Tickets', '3:12')
                ->description('Average response time' . PHP_EOL . 'Based on last 100 tickets'),
        ];
    }
}
