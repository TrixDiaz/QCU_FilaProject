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
use Illuminate\Support\Facades\DB;

class Terminal extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.app.pages.terminal';
    protected static ?string $navigationLabel = 'Terminal Asset';


    public function table(Table $table): Table
    {
        return $table
            ->query(
                TerminalAsset::query()
                    ->select([
                        'terminal_code',
                        'name',
                        'slug',
                        'status',
                        'classroom_id',
                        DB::raw('MIN(id) as id'),
                        DB::raw('GROUP_CONCAT(DISTINCT asset_id) as asset_list')
                    ])
                    ->groupBy('terminal_code','slug', 'classroom_id')
            )
            ->columns([
                Tables\Columns\TextColumn::make('classroom.name')
                    ->label('Classroom')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('classroom.building.name')
                    ->label('Building')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('asset_list')
                    ->label('Assets')
                    ->formatStateUsing(function ($state) {
                        $assetIds = explode(',', $state);
                        $assets = DB::table('terminal_assets_group as t')
                            ->whereIn('t.asset_id', $assetIds)
                            ->join('assets', 'assets.id', '=', 't.asset_id')
                            ->pluck('assets.name')
                            ->unique()
                            ->filter()
                            ->toArray();

                        return view('components.asset-list', [
                            'assets' => $assets
                        ]);
                    })
                    ->html(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('terminal_code')
                    ->label('Terminal Code')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->sortable()
                    ->searchable(),
            ])
            ->defaultSort('terminal_code');
    }
}
