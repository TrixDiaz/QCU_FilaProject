<?php

namespace App\Filament\App\Pages;

use App\Models\Building;
use App\Models\Classroom;
use App\Models\Section;
use Filament\Pages\Page;

class Inventory extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static string $view = 'filament.app.pages.inventory';


}
