<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\SectionResource\Pages;
use App\Filament\App\Resources\SectionResource\RelationManagers;
use App\Models\Section;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SectionResource extends Resource implements HasShieldPermissions
{
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'restore',
            'restore_any',
            'delete_any',
            'force_delete',
            'force_delete_any',
            'publish'
        ];
    }

    protected static ?string $model = Section::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?string $navigationGroup = 'School';

    protected static ?string $navigationParentItem = 'Buildings';

    protected static bool $shouldRegisterNavigation = false;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }

    protected static ?string $navigationBadgeTooltip = 'The number of active section';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema(\App\Services\DynamicForm::schema(\App\Models\Section::class))
                            ->columns(2),
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\Select::make('classroom_id')
                                    ->relationship(
                                        name: 'classroom',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: function (Builder $query, ?Forms\Get $get = null) {
                                            // Get the current building_id if we're in edit mode
                                            $currentClassroomId = $get ? $get('classroom_id') : null;

                                            return $query->where(function ($query) use ($currentClassroomId) {
                                                $query->where('is_active', true);

                                                // Only include the current building if it exists
                                                if ($currentClassroomId) {
                                                    $query->orWhere('id', $currentClassroomId);
                                                }
                                            });
                                        }
                                    )
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->editOptionForm(function () {
                                        return [
                                            // Include name and slug from DynamicForm
                                            ...\App\Services\DynamicForm::schema(\App\Models\Classroom::class),

                                            Forms\Components\Select::make('floor')
                                                ->required()
                                                ->options([
                                                    '1' => '1st Floor',
                                                    '2' => '2nd Floor',
                                                    '3' => '3rd Floor',
                                                    '4' => '4th Floor',
                                                    '5' => '5th Floor',
                                                    '6' => '6th Floor',
                                                    '7' => '7th Floor',
                                                    '8' => '8th Floor',
                                                ])
                                                ->native(false),
                                            // Add the building_id field
                                            Forms\Components\Select::make('building_id')
                                                ->relationship(
                                                    name: 'building',
                                                    titleAttribute: 'name',
                                                    modifyQueryUsing: fn(Builder $query) => $query->where('is_active', true)
                                                )
                                                ->required()
                                                ->searchable()
                                                ->preload()
                                                ->native(false)
                                        ];
                                    })
                                    ->createOptionForm(function () {
                                        return [
                                            // Include name and slug from DynamicForm
                                            ...\App\Services\DynamicForm::schema(\App\Models\Classroom::class),

                                            Forms\Components\Select::make('floor')
                                                ->required()
                                                ->options([
                                                    '1' => '1st Floor',
                                                    '2' => '2nd Floor',
                                                    '3' => '3rd Floor',
                                                    '4' => '4th Floor',
                                                    '5' => '5th Floor',
                                                    '6' => '6th Floor',
                                                    '7' => '7th Floor',
                                                    '8' => '8th Floor',
                                                ])
                                                ->native(false),

                                            // Add the building_id field
                                            Forms\Components\Select::make('building_id')
                                                ->relationship(
                                                    name: 'building',
                                                    titleAttribute: 'name',
                                                    modifyQueryUsing: fn(Builder $query) => $query->where('is_active', true)
                                                )
                                                ->required()
                                                ->searchable()
                                                ->preload()
                                                ->native(false)
                                        ];
                                    }),
                            ])->columnSpan(['lg' => fn(string $operation) => $operation === 'create' ? 3 : 2]),
                    ])->columnSpanFull(),
                Forms\Components\Grid::make(1)->schema([
                    Forms\Components\Section::make()->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Publish')
                            ->onIcon('heroicon-s-eye')
                            ->offIcon('heroicon-s-eye-slash')
                            ->default(true),
                    ])->hiddenOn(['create']),
                    Forms\Components\Section::make()->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Created at')
                            ->hiddenOn('create')
                            ->content(fn(\App\Models\Section $record): string => $record->created_at->toFormattedDateString()),
                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Last modified at')
                            ->content(fn(\App\Models\Section $record): string => $record->created_at->toFormattedDateString()),
                    ])->hiddenOn('create')
                ])->columnSpan(['lg' => fn(string $operation) => $operation === 'create' ? 0 : 1]),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('classroom.name')
                    ->description(fn($record) => $record->classroom?->building?->name ?? 'No Building')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(['name']),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Publish')
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
                        ->modalHeading('Archive Classroom'),
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
                ]),
            ]);
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
            'index' => Pages\ListSections::route('/'),
            'create' => Pages\CreateSection::route('/create'),
            'edit' => Pages\EditSection::route('/{record}/edit'),
        ];
    }
}
