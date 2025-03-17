<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\SubjectResource\Pages;
use App\Filament\App\Resources\SubjectResource\RelationManagers;
use App\Models\Subject;
use App\Models\Section;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use Filament\Forms\FormsComponent;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Support\Facades\Log;
use Filament\Tables\Table;
use Carbon\Carbon;
use Filament\Forms\Get;
use Filament\Forms\Set;
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
            'restore',
            'restore_any',
            'force_delete',
            'force_delete_any',
            'publish'
        ];
    }

    protected static ?string $model = Subject::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'School';

    protected static ?string $navigationParentItem = 'Buildings';

    protected static bool $shouldRegisterNavigation = false;

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
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn(Get $get, Set $set) => self::checkForTimeConflicts($get, $set)),

                        Forms\Components\TimePicker::make('lab_time_starts_at')
                            ->required()
                            ->seconds(false)
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                if (!$state) return;

                                $endTime = $get('lab_time_ends_at');
                                if (!$endTime) return;

                                if (Carbon::parse($state)->gte(Carbon::parse($endTime))) {
                                    Notification::make('invalid_time_range')
                                        ->title('Invalid Time Range')
                                        ->body('Start time must be before end time.')
                                        ->warning()
                                        ->send();
                                } else {
                                    // Only check for conflicts if time range is valid
                                    self::checkForTimeConflicts($get, $set);
                                }
                            }),

                        Forms\Components\TimePicker::make('lab_time_ends_at')
                            ->required()
                            ->seconds(false)
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                if (!$state) return;

                                $startTime = $get('lab_time_starts_at');
                                if (!$startTime) return;

                                if (Carbon::parse($state)->lte(Carbon::parse($startTime))) {
                                    Notification::make('invalid_time_range')
                                        ->title('Invalid Time Range')
                                        ->body('End time must be after start time.')
                                        ->warning()
                                        ->send();
                                } else {
                                    // Only check for conflicts if time range is valid
                                    self::checkForTimeConflicts($get, $set);
                                }
                            }),

                        Forms\Components\Hidden::make('lab_confirm_conflict')
                            ->default(false)
                            ->dehydrated(true),

                        Forms\Components\Hidden::make('has_lab_time_conflict')
                            ->default(false)
                            ->dehydrated(true),

                    ])->columns(3),

                // Time Conflict Warning Section
                Forms\Components\Section::make('Time Conflict Warning')
                    ->schema([
                        Forms\Components\Placeholder::make('lab_conflict_warning')
                            ->content('This time slot conflicts with another subject scheduled on the same day.')
                            ->extraAttributes(['class' => 'text-danger-500 font-medium']),

                        Forms\Components\Radio::make('lab_conflict_action')
                            ->label('What would you like to do?')
                            ->options([
                                'proceed' => 'Proceed with the conflicting time anyway',
                                'change' => 'Select a different time',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                if ($state === 'proceed') {
                                    // User wants to proceed despite conflict
                                    $set('lab_confirm_conflict', true);
                                } else {
                                    // User wants to change the time, reset times
                                    $set('lab_confirm_conflict', false);
                                    $set('lab_time_starts_at', null);
                                    $set('lab_time_ends_at', null);
                                    $set('has_lab_time_conflict', false);

                                    Notification::make()
                                        ->title('Please select a new time')
                                        ->success()
                                        ->send();
                                }
                            }),
                    ])
                    ->extraAttributes(['class' => 'bg-danger-50 border border-danger-200 rounded-xl p-4'])
                    ->visible(fn(Get $get) => $get('has_lab_time_conflict') === true)
                    ->columnSpanFull(),

                // Section and Professor 
                Forms\Components\Section::make('Section and Professor')
                    ->schema([
                        Forms\Components\Select::make('section_id')
                            ->relationship('section', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->optionsLimit(5)
                            ->live()
                            ->afterStateUpdated(fn(Get $get, Set $set) => self::checkForTimeConflicts($get, $set)),
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
                            ->default('1st Semester')
                            ->live()
                            ->afterStateUpdated(fn(Get $get, Set $set) => self::checkForTimeConflicts($get, $set)),
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
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn(Get $get, Set $set) => self::checkForTimeConflicts($get, $set))
                    ])->columns(2),
            ]);
    }

    // Validation rules
    protected function rules()
    {
        $baseRules = parent::rules();

        return array_merge($baseRules, [
            'lab_confirm_conflict' => [
                function ($attribute, $value, $fail) {
                    // Check if there's a conflict that hasn't been confirmed
                    if (($this->has_lab_time_conflict ?? false) && !$value) {
                        $fail('Please resolve the time conflict before submitting.');
                    }
                },
            ],
        ]);
    }

    /**
     * Check for time conflicts in the schedule
     */
    protected static function checkForTimeConflicts(Get $get, Set $set): void
    {
        // Get all required values
        $startsAt = $get('lab_time_starts_at');
        $endsAt = $get('lab_time_ends_at');
        $day = $get('day');
        $section_id = $get('section_id');
        $schoolYear = $get('school_year');
        $semester = $get('semester');

        // Skip check if any required fields are missing
        if (!$startsAt || !$endsAt || !$day || !$section_id || !$schoolYear || !$semester) {
            return;
        }

        // Format the times consistently
        try {
            $startTime = Carbon::parse($startsAt)->format('H:i:s');
            $endTime = Carbon::parse($endsAt)->format('H:i:s');
        } catch (\Exception $e) {
            // Log error and return if there's an issue with time format
            Log::error('Time parsing error', [
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'error' => $e->getMessage()
            ]);
            return;
        }

        // Get current record ID if editing
        $currentId = request()->route('record');

        // Build query to check for conflicts
        $query = Subject::query()
            ->where('day', $day)
            ->where('section_id', $section_id)
            ->where('school_year', $schoolYear)
            ->where('semester', $semester);

        // Exclude the current record if we're editing
        if ($currentId) {
            $query->where('id', '!=', $currentId);
        }

        // Check for time conflicts with raw SQL for accurate time comparison
        $conflicts = $query->where(function ($q) use ($startTime, $endTime) {
            // New time starts during existing slot
            $q->orWhereRaw('TIME(?) >= TIME(lab_time_starts_at) AND TIME(?) < TIME(lab_time_ends_at)', [$startTime, $startTime]);

            // New time ends during existing slot
            $q->orWhereRaw('TIME(?) > TIME(lab_time_starts_at) AND TIME(?) <= TIME(lab_time_ends_at)', [$endTime, $endTime]);

            // New time contains existing slot
            $q->orWhereRaw('TIME(?) <= TIME(lab_time_starts_at) AND TIME(?) >= TIME(lab_time_ends_at)', [$startTime, $endTime]);

            // Existing slot contains new time
            $q->orWhereRaw('TIME(lab_time_starts_at) <= TIME(?) AND TIME(lab_time_ends_at) >= TIME(?)', [$startTime, $endTime]);
        })->get();

        $hasConflict = $conflicts->isNotEmpty();

        // Log for debugging
        Log::info('Time conflict check', [
            'day' => $day,
            'section_id' => $section_id,
            'school_year' => $schoolYear,
            'semester' => $semester,
            'starts_at' => $startTime,
            'ends_at' => $endTime,
            'has_conflict' => $hasConflict,
            'current_id' => $currentId,
            'conflict_count' => $conflicts->count(),
            'conflicts' => $conflicts->pluck('name', 'id')->toArray()
        ]);

        // Update form state
        $set('has_lab_time_conflict', $hasConflict);

        // Clear confirmation if no conflict exists
        if (!$hasConflict) {
            $set('lab_confirm_conflict', false);
        }
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
                Tables\Columns\TextColumn::make('lab_time_starts_at')
                    ->searchable(),
                Tables\Columns\TextColumn::make('lab_time_ends_at')
                    ->searchable(),
                Tables\Columns\TextColumn::make('day')
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
