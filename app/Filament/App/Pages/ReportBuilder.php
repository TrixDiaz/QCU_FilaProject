<?php

namespace App\Filament\App\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ReportBuilder extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = "Reports";

    protected static string $view = 'filament.app.pages.report-builder';

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        // Show if user is super admin or has Inventory page permission
        return $user->hasRole('super_admin') || $user->can('page_ReportBuilder');
    }

    public function mount(): void
    {
        $user = Auth::user();

        if (!($user->hasRole('super_admin') || $user->can('page_ReportBuilder'))) {
            abort(Response::HTTP_NOT_FOUND);
        }
    }
}
