<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Asset;
use App\Models\Ticket;
use App\Models\Building;
use App\Models\AssetGroup;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Str;

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
        $inactiveAssets = Asset::where('status', 'Inactive')->count();
        $deploy = Asset::where('status', 'Deploy')->count();
        $maintenanceAssets = AssetGroup::where('status', 'Maintenance')->count();

        // Format Assets description in two-column style with right-aligned numbers
        $assetsDescription = "<div style='display: grid; grid-template-columns: auto 90px; color: #3b82f6;'>
            <span>Active</span><span style='text-align: right;'>$activeAssets</span>
            <span>Inactive</span><span style='text-align: right;'>$inactiveAssets</span>
            <span>Deploy</span><span style='text-align: right;'>$deploy</span>
            <span>Maintenance</span><span style='text-align: right;'>$maintenanceAssets</span>
        </div>";

        //  Tickets Stats
        $totalTickets = Ticket::count();
        $openTickets = Ticket::where('ticket_status', 'open')->count();
        $inProgressTickets = Ticket::where('ticket_status', 'in-progress')->count();
        $unassignedTickets = Ticket::whereNull('assigned_to')->count();
        $resolvedTickets = Ticket::where('ticket_status', 'resolved')->count();

        // Format Tickets description in two-column style with right-aligned numbers
        $ticketsDescription = "<div style='display: grid; grid-template-columns: auto 90px; color: #f59e0b;'>
            <span>Open</span><span style='text-align: right;'>$openTickets</span>
            <span>In Progress</span><span style='text-align: right;'>$inProgressTickets</span>
            <span>Unassigned</span><span style='text-align: right;'>$unassignedTickets</span>
            <span>Resolved</span><span style='text-align: right;'>$resolvedTickets</span>
        </div>";

        //  Buildings Stats
        $totalBuildings = Building::count();

        return [
            Stat::make('Total Users', $totalUsers)
                ->description("Active: $activeUsers")
                ->icon('heroicon-o-users')
                ->color('success'),

            Stat::make('Total Assets', $totalAssets)
                ->description(Str::of($assetsDescription)->toHtmlString())
                ->icon('heroicon-o-computer-desktop')
                ->color('primary'),

            Stat::make('Total Tickets', $totalTickets)
                ->description(Str::of($ticketsDescription)->toHtmlString())
                ->icon('heroicon-o-ticket')
                ->color('warning'),

            Stat::make('Total Buildings', $totalBuildings)
                ->description('Total Registered Buildings')
                ->icon('heroicon-o-building-office')
                ->color('info'),
        ];
    }
}