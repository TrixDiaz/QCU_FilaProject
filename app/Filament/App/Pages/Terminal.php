<?php

namespace App\Filament\App\Pages;

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
                Tables\Columns\TextColumn::make('classroom.id')
                    ->label('Classroom'),
                Tables\Columns\TextColumn::make('asset.id')
                    ->label('Asset')
            ]);
    }
}
