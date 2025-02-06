<?php

namespace App\Filament\App\Pages;

use App\Models\AssetGroup;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class Terminal extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.app.pages.terminal';
    protected static ?string $navigationLabel = 'Terminal Asset';


    public function table(Table $table): Table
    {
        return $table
            ->query(
                AssetGroup::query()
                    ->select([
                        'terminal_assets_group.terminal_code',
                        'terminal_assets_group.name',
                        'terminal_assets_group.status',
                        'terminal_assets_group.classroom_id',
                        DB::raw('MIN(terminal_assets_group.id) as id'),
                        DB::raw('GROUP_CONCAT(DISTINCT assets.name) as asset_list') // Concatenates asset names instead of IDs
                    ])
                    ->leftJoin('assets', 'terminal_assets_group.asset_id', '=', 'assets.id') // Join with the Asset table
                    ->groupBy(
                        'terminal_assets_group.terminal_code',
                        'terminal_assets_group.name',
                        'terminal_assets_group.status',
                        'terminal_assets_group.classroom_id'
                    )
            )
            ->contentGrid([
                'md' => 2,
                'lg' => 3,
            ])
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('name')
                        ->label('Name')
                        ->searchable(['name', 'terminal_code'])
                        ->description(fn($record): string => $record->terminal_code),
                    Tables\Columns\TextColumn::make('classroom.building.name')
                        ->label('Building and Classroom')
                        ->description(fn($record): string => $record->classroom?->name)
                        ->extraAttributes(['class' => 'capitalize']),
                    Tables\Columns\TextColumn::make('asset_list')
                        ->label('Assets'),
                    Tables\Columns\TextColumn::make('status')
                        ->label('Status')
                        ->extraAttributes(['class' => 'capitalize'])
                        ->badge(),
                ])
            ])
            ->defaultSort('terminal_code')
            ->emptyStateHeading('No Computers yet');
    }





}
