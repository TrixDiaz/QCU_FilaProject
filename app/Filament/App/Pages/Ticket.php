<?php

namespace App\Filament\App\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Ticket extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Tickets';

    protected static string $view = 'filament.app.pages.ticket';

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        // Show if user is super admin or has Inventory page permission
        return $user->hasRole('super_admin') || $user->can('page_Ticket');
    }

    public function mount(): void
    {
        $user = Auth::user();

        if (!($user->hasRole('super_admin') || $user->can('page_Ticket'))) {
            abort(Response::HTTP_NOT_FOUND);
        }
    }
}
