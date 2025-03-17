<?php

namespace App\Filament\App\Pages;

use Filament\Pages\Page;

class Rooms extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationGroup = 'Schedule';

    protected static string $view = 'filament.app.pages.rooms';
}
