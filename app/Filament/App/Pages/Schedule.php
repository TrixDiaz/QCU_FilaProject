<?php

namespace App\Filament\App\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;

class Schedule extends Page
{
    use HasPageShield;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'School';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.app.pages.schedule';
}
