<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\BuildingResource\Pages;
use App\Filament\App\Resources\BuildingResource\RelationManagers;
use App\Models\Building;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;



class BuildingResource extends Resource implements HasShieldPermissions
{
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'restore',
            'restore_any',
            'force_delete',
            'force_delete_any',
            'publish'
        ];
    }

    protected static ?string $model = Building::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationGroup = 'School';

    protected static ?string $modelLabel = 'Buildings';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }

    protected static ?string $navigationBadgeTooltip = 'The number of active building';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()->schema([
                    Forms\Components\Grid::make(1)
                        ->schema(\App\Services\DynamicForm::schema(\App\Models\Building::class))->columns(2),
                ])->columnSpan(['lg' => fn(string $operation) => $operation === 'create' ? 3 : 2]),

                Forms\Components\Grid::make(1)->schema([
                    Forms\Components\Section::make()->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Publish')
                            ->onIcon('heroicon-s-eye')
                            ->offIcon('heroicon-s-eye-slash')
                            ->default(true),
                    ])->hiddenOn(['create']),
                    Forms\Components\Section::make()->schema([
                        Forms\Components\Placeholder::make('created')
                            ->content(fn(Building $record): string => $record->created_at->toFormattedDateString()),
                        Forms\Components\Placeholder::make('updated')
                            ->content(fn(Building $record): string => $record->created_at->toFormattedDateString()),
                    ])->hiddenOn('create')
                ])->columnSpan(['lg' => fn(string $operation) => $operation === 'create' ? 0 : 1]),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Publish')
                    ->getStateUsing(fn($record) => $record->is_active ? 'Yes' : 'No')
                    ->onIcon('heroicon-m-bolt')
                    ->offIcon('heroicon-m-bolt-slash'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
                    ->native(false),
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Status')
                    ->label('Status')
                    ->options([
                        true => 'Active',
                        false => 'Inactive'
                    ])
                    ->native(false)
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->tooltip('View'),
                    Tables\Actions\EditAction::make()
                        ->tooltip('Edit')
                        ->color('warning'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Archive')
                        ->tooltip('Archive')
                        ->modalHeading('Archive Building'),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make()
                        ->color('secondary'),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->tooltip('Actions')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    ExportBulkAction::make()->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->except(["id", "slug",])
                            ->withFilename(date('Y-m-d') . '-Buildings.xlsx'),
                        ExcelExport::make()
                            ->fromTable()
                            ->only([
                                'name',
                                'serial_number',
                                'assetTags.name',
                                'expiry_date',
                                'status',
                                'created_at',
                                'updated_at',
                            ])
                            ->withFilename(date('Y-m-d') . '-Filtered-Assets.xlsx'),
                    ])

                ]),
            ])->poll('30s');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBuildings::route('/'),
            'create' => Pages\CreateBuilding::route('/create'),
            'edit' => Pages\EditBuilding::route('/{record}/edit'),
        ];
    }
}
