<?php

namespace App\Filament\App\Pages;

use Filament\Pages\Page;

class Tickets extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Tickets';

    protected static string $view = 'filament.app.pages.tickets';
}
