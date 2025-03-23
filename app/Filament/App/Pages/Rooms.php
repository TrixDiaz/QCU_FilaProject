<?php

namespace App\Filament\App\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Rooms extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationGroup = 'Schedule';

    protected static string $view = 'filament.app.pages.rooms';

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        // Show if user is super admin or has Inventory page permission
        return $user->hasRole('super_admin') || $user->can('page_Rooms');
    }

    public function mount(): void
    {
        $user = Auth::user();

        if (!($user->hasRole('super_admin') || $user->can('page_Rooms'))) {
            abort(Response::HTTP_NOT_FOUND);
        }
    }
}
