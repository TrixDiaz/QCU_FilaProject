<?php

namespace App\Filament\App\Pages;

use App\Models\TerminalAsset;
use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables;
use Filament\Tables\Table;
class Terminal extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.app.pages.terminal';

    protected static ?string $navigationLabel = 'Terminal Asset';

    public function table(Table $table): Table
    {
        return $table
           ->query(\App\Models\TerminalAsset::query())
            ->columns([
                Tables\Columns\TextColumn::make('classroom.name'),
                Tables\Columns\TextColumn::make('asset.name'),
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('slug'),
                Tables\Columns\TextColumn::make('terminal_code'),
                Tables\Columns\TextColumn::make('status'),

            ]);
    }
}
