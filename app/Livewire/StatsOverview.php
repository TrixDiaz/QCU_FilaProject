<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Asset;
use App\Models\Ticket;
use App\Models\Building;
use App\Models\AssetGroup;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '30s'; // Refresh every 30 seconds

    protected function getStats(): array
    {
        //  Users Stats
        $totalUsers = User::count();
        $activeUsers = User::where('updated_at', '>=', now()->subDays(30))->count();

        //  Assets Stats
        $totalAssets = Asset::count();
        $activeAssets = Asset::where('status', 'Active')->count();
        $inactiveAssets =Asset::where('status','Inactive')->count();
        $deploy = Asset::where('status', 'Deploy')->count();
        $maintenanceAssets = AssetGroup::where('status', 'Maintenance')->count();
        

        //  Tickets Stats
        $totalTickets = Ticket::count();
        $openTickets = Ticket::where('status', 'open')->count();
        $inProgressTickets = Ticket::where('status', 'in-progress')->count();
        $unassignedTickets = Ticket::whereNull('assigned_to')->count();
        $resolvedTickets = Ticket::where('status', 'resolved')->count();

        //  Buildings Stats
        $totalBuildings = Building::count();

        return [
            Stat::make('Total Users', $totalUsers)
                ->description("Active: $activeUsers")
                ->icon('heroicon-o-users')
                ->color('success'),
                

                Stat::make('Total Assets', $totalAssets)
                ->description(
                    "Active: $activeAssets " .
                    "Inactive: $inactiveAssets " .
                    "Deploy: $deploy " .
                    "Maintenance: $maintenanceAssets"
                )
                ->icon('heroicon-o-computer-desktop')
                ->color('primary'),
            

            Stat::make('Total Tickets', $totalTickets)
                ->description(
                    "Open: $openTickets " .
                    "In Progress: $inProgressTickets " .
                    "Unassigned: $unassignedTickets" .
                    " Resolved: $resolvedTickets"
                )
                ->icon('heroicon-o-ticket')
                ->color('warning'),

            Stat::make('Total Buildings', $totalBuildings)
                ->description('Total Registered Buildings')
                ->icon('heroicon-o-building-office')
                ->color('info'),
        ];
    }
}
