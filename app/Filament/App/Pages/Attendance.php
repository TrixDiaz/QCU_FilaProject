<?php

namespace App\Filament\App\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class Attendance extends Page implements HasForms, HasTable
{
    use HasPageShield;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.app.pages.attendance';

    protected static ?string $navigationGroup = 'School';

    protected static ?string $navigationLabel = 'Attendances';

    use InteractsWithForms;
    use InteractsWithTable;
    use HasPageShield;

    public function table(Table $table): Table
    {
        return $table
            ->query(\App\Models\Attendance::query())
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('section_id'),
            ])->emptyStateHeading('No Attendance yet');
    }
}
