<?php

namespace App\Filament\App\Pages;

use App\Models\Building;
use App\Models\Classroom;
use App\Models\Section;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Inventory extends Page
{
    use HasPageShield;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Assets';

    protected static ?string $navigationLabel = 'Assets & Inventory';

    protected static string $view = 'filament.app.pages.inventory';

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        // Show if user is super admin or has Inventory page permission
        return $user->hasRole('super_admin') || $user->can('page_Inventory');
    }

    public function mount(): void
    {
        $user = Auth::user();

        if (!($user->hasRole('super_admin') || $user->can('page_Inventory'))) {
            abort(Response::HTTP_NOT_FOUND);
        }
    }
}
