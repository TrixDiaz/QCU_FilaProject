<?php

namespace App\Filament\App\Pages;

use Filament\Pages\Page;

class Ticket extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Tickets';
    protected static string $view = 'filament.app.pages.ticket';
}
