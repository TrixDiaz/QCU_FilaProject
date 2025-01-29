<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\AssetResource\Pages;
use App\Models\Asset;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AssetResource extends Resource
{
    protected static ?string $model = Asset::class;

    protected static ?string $navigationGroup = 'Assets';

    protected static ?string $navigationLabel = 'Asset';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $uuidRandom = rand(10000, 99999);
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name'),
                        Forms\Components\TextInput::make('brand'),
                        Forms\Components\TextInput::make('serial_number')->default($uuidRandom),
                        Forms\Components\DatePicker::make('expiry_date')
                            ->native(false),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'Inactive' => 'Inactive',
                            ])
                            ->native(false)
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->description(fn(Asset $record): string => $record->serial_number),
                Tables\Columns\TextColumn::make('brand')
                    ->searchable()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssets::route('/'),
            'create' => Pages\CreateAsset::route('/create'),
            'edit' => Pages\EditAsset::route('/{record}/edit'),
        ];
    }
}
