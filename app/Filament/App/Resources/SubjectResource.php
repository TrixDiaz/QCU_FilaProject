<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\SubjectResource\Pages;
use App\Filament\App\Resources\SubjectResource\RelationManagers;
use App\Models\Subject;
use App\Models\Section;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\FormsComponent;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubjectResource extends Resource implements HasShieldPermissions
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
            'force_delete',
            'force_delete_any',
            'publish'
        ];
    }

    protected static ?string $model = Subject::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'School';

    protected static ?string $navigationParentItem = 'Buildings';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'active')->count();
    }

    protected static ?string $navigationBadgeTooltip = 'The number of active Subject';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Subject Information
                Forms\Components\Section::make('Subject Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Subject Name')
                            ->required(),
                        Forms\Components\TextInput::make('subject_code')
                            ->required()
                            ->extraAlpineAttributes([
                                'style' => 'text-transform: uppercase;',
                                'class' => 'uppercase',
                                'x-model' => 'subject_code',
                                '@input' => "subject_code = subject_code.toUpperCase()"
                            ]),
                        Forms\Components\TextInput::make('subject_units')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(10)
                            ->step(1),
                    ])->columns(3),

                // Date and Time
                Forms\Components\Section::make('Date and Time')
                    ->schema([
                        Forms\Components\Select::make('day')
                            ->options([
                                'Monday' => 'Monday',
                                'Tuesday' => 'Tuesday',
                                'Wednesday' => 'Wednesday',
                                'Thursday' => 'Thursday',
                                'Friday' => 'Friday',
                                'Saturday' => 'Saturday',
                                'Sunday' => 'Sunday',
                            ])
                            ->label('Day')
                            ->required()
                            ->native(false),
                        Forms\Components\TimePicker::make('lab_time_starts_at')
                            ->required()
                            ->seconds(false)
                            ->native(false),
                        Forms\Components\TimePicker::make('lab_time_ends_at')
                            ->required()
                            ->seconds(false)
                            ->native(false),
                    ])->columns(3),

                // Section and Professor 
                Forms\Components\Section::make('Section and Professor')
                    ->schema([
                        Forms\Components\Select::make('section_id')
                            ->relationship('section', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->optionsLimit(5),
                        Forms\Components\Select::make('professor_id')
                            ->relationship(
                                'professor',
                                'name',
                                fn($query) => $query->whereHas('roles', fn($q) => $q->where('name', 'Professor'))
                            )
                            ->required()
                            ->searchable()
                            ->preload()
                            ->optionsLimit(5),
                        Forms\Components\Select::make('semester')
                            ->options([
                                '1st Semester' => '1st Semester',
                                '2nd Semester' => '2nd Semester',
                                'summer' => 'Summer',
                            ])
                            ->native(false)
                            ->preload()
                            ->required()
                            ->searchable()
                            ->default('1st Semester'),
                        Forms\Components\Select::make('school_year')
                            ->options([
                                '2021-2022' => '2021-2022',
                                '2022-2023' => '2022-2023',
                                '2023-2024' => '2023-2024',
                                '2024-2025' => '2024-2025',
                                '2025-2026' => '2025-2026',
                                '2026-2027' => '2026-2027',
                                '2027-2028' => '2027-2028',
                                '2028-2029' => '2028-2029',
                                '2029-2030' => '2029-2030',
                            ])
                            ->native(false)
                            ->preload()
                            ->searchable()
                    ])->columns(2),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Subject Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('subject_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('subject_units')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('section.name')
                    ->label('section')
                    ->sortable(),
                Tables\Columns\TextColumn::make('lab_time')
                    ->searchable(),
                Tables\Columns\TextColumn::make('lecture_time')
                    ->searchable(),
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
            'index' => Pages\ListSubjects::route('/'),
            'create' => Pages\CreateSubject::route('/create'),
            'edit' => Pages\EditSubject::route('/{record}/edit'),
        ];
    }
}
