<?php

namespace App\Filament\App\Pages;

use App\Models\Building;
use App\Models\Classroom;
use App\Models\Section;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;

class Inventory extends Page
{
    use HasPageShield;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Assets';

    protected static ?int $navigationSort = 8;
    protected static string $view = 'filament.app.pages.inventory';


}
