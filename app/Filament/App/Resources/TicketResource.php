<?php

namespace App\Filament\app\Resources;

use App\Filament\App\Resources\TicketResource\Pages;
use App\Filament\App\Resources\TicketResource\RelationManagers;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Tables;
use Filament\Tables\Table;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Components\Wizard;
use Illuminate\Support\Str;
use Filament\Forms\Set;
use Filament\Tables\Columns\SelectColumn;
use Illuminate\Database\Eloquent\Model;

class TicketResource extends Resource implements HasShieldPermissions
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

    protected static ?string $model = Ticket::class;

    protected static ?string $navigationGroup = 'Tickets';
    protected static ?string $modelLabel = 'Tickets';
    protected static ?string $navigationLabel = 'Tickets';

    protected static ?string $navigationIcon = 'heroicon-o-ticket';


    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::all()->count();
    }

    protected static ?string $navigationBadgeTooltip = 'The number of active ticket';

    public static function form(Form $form): Form
{
    $isProfessor = auth()->user()->hasRole('professor');
    $isEditMode = $form->getOperation() === 'edit';

    return $form
        ->schema([
            Forms\Components\Grid::make()
                ->schema([
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\Placeholder::make('ticket_status')
                                ->label('Ticket Current Status')
                                ->content(fn($record): string => $record->ticket_status ?? 'New')
                                ->extraAttributes(['class' => 'capitalize']),
                        ])
                        ->columnSpan(1),

                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\Placeholder::make('ticket')
                                ->label('Ticket Number')
                                ->content(fn($get): string => $get('ticket_number') ?? 'Please Select Ticket Type to Generate'),
                        ])
                        ->columnSpan(1),
                ])
                ->columns(2),
            Forms\Components\Section::make()
                ->schema([
                    Wizard::make([
                        Wizard\Step::make('Ticket Information')
                            ->schema([
                                Forms\Components\Section::make()
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Select::make('ticket_type')
                                                    ->options([
                                                        'request' => 'Request',
                                                        'incident' => 'Incident',
                                                    ])
                                                    ->afterStateUpdated(fn($state, $set) => $set('ticket_number', ($state === 'request' ? 'REQ' : 'INC') . '-' . strtoupper(Str::random(8))))
                                                    ->required()
                                                    ->reactive()
                                                    ->live(onBlur: true)
                                                    ->native(false),
                                                Forms\Components\TextInput::make('title')
                                                    ->required(),
                                                Forms\Components\Select::make('option')
                                                    ->options([
                                                        'asset' => 'Asset',
                                                        'classroom' => 'Classroom',
                                                    ])
                                                    ->required()
                                                    ->visible(fn($get) => $get('ticket_type') === 'request')
                                                    ->live()
                                                    ->afterStateUpdated(function ($state, callable $set) {
                                                        if ($state === 'classroom') {
                                                            $set('asset_id', null);
                                                        }
                                                    }),

                                                Forms\Components\DateTimePicker::make('starts_at')
                                                    ->visible(fn($get) => $get('option') === 'classroom')
                                                    ->rules(['required_if:option,classroom'])
                                                    ->seconds(false)
                                                    ->minutesStep(15)
                                                    ->default(now()->startOfHour())
                                                    ->live()
                                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state) {
                                                    if (!$state) return;

                                                // Get the current selected end time duration
                                                    $currentEndHour = $get('ends_at');

                                                // If no end hour selected yet, default to starts_at + 1 hour
                                                    if (!$currentEndHour) {
                                                    $startHour = (int) Carbon::parse($state)->format('H');
                                                    $defaultEndHour = ($startHour + 1) % 24;
                                                    $set('ends_at', sprintf('%02d:00', $defaultEndHour));
                                                    }
                                                // If end hour is already selected, check if it's still valid
                                                    else {
                                                    $startHour = (int) Carbon::parse($state)->format('H');
                                                    $endHour = (int) explode(':', $currentEndHour)[0];

                                                // If end hour is not at least 1 hour after start hour
                                                    if ($endHour <= $startHour) {
                                                    $newEndHour = ($startHour + 1) % 24;
                                                    $set('ends_at', sprintf('%02d:00', $newEndHour));
                                                    }
                                                }
                
                                                // Check for time conflicts when date changes
                                                self::checkTimeConflict($get, $set);
                                                }),

                                                Forms\Components\Select::make('ends_at')
                                                    ->required()
                                                    ->options(function (Forms\Get $get) {
                                                    $startsAt = $get('starts_at');
                                                    if (!$startsAt) {
                                                        return [];
                                                    }

                                                    $startHour = (int) Carbon::parse($startsAt)->format('H');
                                                    $options = [];

                                                // Generate options for the next 8 hours after start hour
                                                    for ($i = 1; $i <= 8; $i++) {
                                                    $hour = ($startHour + $i) % 24;
                                                    $time = sprintf('%02d:00', $hour);
                                                    $formattedTime = Carbon::createFromFormat('H:i', $time)->format('g:i A');
                                                    $options[$time] = $formattedTime;
                                                    }

                                                    return $options;
                                                    })
                                                    ->live()
                                                    ->required()
                                                    ->disabled(fn(Forms\Get $get) => !$get('starts_at'))
                                                    ->visible(fn($get) => $get('option') === 'classroom')
                                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                                // Check for time conflicts when end time changes
                                                    self::checkTimeConflict($get, $set);
                                                    }),

                                                Forms\Components\Select::make('asset_id')
                                                    ->relationship('asset', 'name')
                                                    ->required(fn($get) => $get('option') === 'asset')
                                                    ->searchable()
                                                    ->preload()
                                                    ->optionsLimit(5)
                                                    ->visible(fn($get) => $get('option') !== 'classroom')
                                                    ->visible(
                                                        fn($get) =>
                                                        $get('ticket_type') === 'incident' ||
                                                            ($get('ticket_type') === 'request' && $get('option') === 'asset')
                                                    ),

                                                Forms\Components\Select::make('section_id')
                                                    ->relationship('section', 'name')
                                                    ->required()
                                                    ->searchable()
                                                    ->preload()
                                                    ->optionsLimit(5)
                                                    ->visible(fn($get) => $get('option') !== 'classroom')
                                                    ->visible(fn($get) => $get('option') !== 'asset')
                                                    ->afterStateUpdated(function ($state, callable $set) {
                                                        if ($state === 'asset') {
                                                            $set('subject_id', null);
                                                        }
                                                    }),

                                                Forms\Components\Select::make('subject_id')
                                                    ->options(Subject::all()->pluck('name', 'id'))
                                                    ->required(fn($get) => $get('option') === 'classroom')
                                                    ->searchable()
                                                    ->preload()
                                                    ->optionsLimit(5)
                                                    ->visible(fn($get) => $get('option') === 'classroom' || ($get('ticket_type') === 'request' && $get('option') === 'asset'))
                                                    ->live()
                                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, $state) {
                                                    if ($state === 'asset') {
                                                        $set('subject_id', null);
                                                    }
                                                    
                                                    // Check for time conflicts when subject changes
                                                    self::checkTimeConflict($get, $set);
                                                    }),
                                                
                                                Forms\Components\Section::make('Time Conflict Warning')
                                                    ->schema([
                                                        Forms\Components\Placeholder::make('conflict_warning')
                                                            ->content('This classroom is already reserved for the selected time period.')
                                                            ->extraAttributes(['class' => 'text-danger-500 font-medium']),
                                                            
                                                            Forms\Components\Radio::make('conflict_action')
                                                            ->label('What would you like to do?')
                                                            ->options([
                                                                'proceed' => 'Proceed with the conflicting time anyway',
                                                                'change' => 'Select a different time',
                                                            ])
                                                            ->required()
                                                            ->live()
                                                            ->afterStateUpdated(function (callable $set, $state) {
                                                                if ($state === 'proceed') {
                                                                    // User wants to proceed despite conflict
                                                                    $set('confirm_conflict', true);
                                                                    $set('first_step_validation', 'valid');
                                                                } else {
                                                                    // User wants to change the time, reset dates
                                                                    $set('confirm_conflict', false);
                                                                    $set('starts_at', null);
                                                                    $set('ends_at', null);
                                                                    $set('has_time_conflict', false);
                                                                    $set('first_step_validation', 'valid');
                                                                    
                                                                    // Use the correct Notification class
                                                                    Notification::make()
                                                                        ->title('Please select a new time')
                                                                        ->success()
                                                                        ->send();
                                                                }
                                                            }),
                                                        
                                                        // Keep this hidden field for tracking conflict state
                                                        Forms\Components\Hidden::make('confirm_conflict')
                                                            ->default(false)
                                                            ->dehydrated(true),
                                                            
                                                        Forms\Components\Hidden::make('has_time_conflict')
                                                            ->default(false)
                                                            ->dehydrated(true),
                                                    ])
                                                    ->extraAttributes(['class' => 'bg-danger-50 border border-danger-200 rounded-xl p-4'])
                                                    ->visible(fn(Forms\Get $get) => $get('has_time_conflict') === true)
                                                    ->columnSpanFull(),
                                                Forms\Components\Builder::make('description')
                                                    ->label('Remarks')
                                                    ->blocks([
                                                        Builder\Block::make('message')
                                                            ->schema([
                                                                Textarea::make('message')
                                                                    ->label('Message')
                                                                    ->placeholder('Type your message...')
                                                                    ->rows(3)
                                                                    ->required()
                                                                    ->live(onBlur: true),
                                                                Forms\Components\TextInput::make('sender_role')
                                                                    ->label('Sender')
                                                                    ->default(fn() => auth()->user()->name)
                                                                    ->readOnly()
                                                                    ->required()
                                                                    ->live(onBlur: true),
                                                            ])
                                                    ])
                                                    
                                                    ->collapsible()
                                                    ->columnSpanFull(),
                                            ]),
                                    ]),
                            ]),
                            
                        Wizard\Step::make('Other Information')
                            ->schema([
                                Forms\Components\TextInput::make('ticket_number')
                                    ->disabled()
                                    ->dehydrated()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique('tickets', ignoreRecord: true),
                                Forms\Components\Select::make('priority')
                                    ->required()
                                    ->default('low')
                                    ->options([
                                        'low' => 'Low',
                                        'medium' => 'Medium',
                                        'high' => 'High',
                                    ]),
                                Forms\Components\TextInput::make('created_by')
                                    ->required()
                                    ->default(fn() => auth()->user()->name)
                                    ->dehydrateStateUsing(fn() => auth()->id())
                                    ->readOnly(),
                                Forms\Components\Select::make('assigned_to')
                                    ->required()
                                    ->searchable()
                                    ->default(function () {
                                    // Find the first technician user
                                    $technician = User::whereHas('roles', function ($query) {
                                        $query->where('name', 'technician');
                                    })->first();
            
                                    // If no technician found, find the first user
                                    if (!$technician) {
                                        $technician = User::first();
                                    }
            
                                    // Throw an exception if no users exist
                                    if (!$technician) {
                                        throw new \Exception('No users available for ticket assignment');
                                    }
            
                                    return $technician->id;
                                    })
                                    ->disabled(fn() => auth()->user()->hasRole('professor'))
                                    ->dehydrated()
                                    ->options(function () {
                                    try {
                                        if (auth()->user()->hasRole('professor')) {
                                            // Direct database query instead of Eloquent for more reliable results
                                            $technicianIds = DB::table('model_has_roles')
                                                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                                                ->where('roles.name', 'technician')
                                                ->pluck('model_has_roles.model_id')
                                                ->toArray();
                                            
                                            return User::whereIn('id', $technicianIds)->pluck('name', 'id')->toArray();
                                        }
                                        
                                        return User::all()->pluck('name', 'id')->toArray();
                                    } catch (\Exception $e) {
                                        // Fallback if anything goes wrong
                                        return User::all()->pluck('name', 'id')->toArray();
                                    }
                                }),
                                Forms\Components\Hidden::make('ticket_status')
                                    ->default('open')
                                    ->dehydrated()
                                    ->required(),

                                Forms\Components\FileUpload::make('attachments')
                                    ->multiple()
                                    ->openable()
                                    ->image()
                                    ->downloadable()
                                    ->columnSpanFull(),
                            ])->columns(2),
                    ])->columnSpan(1), // Ensures the Wizard takes one column
                ]),
        ])


        ->disabled($isProfessor && $isEditMode); // Only disable form if professor AND in edit mode
}

protected function rules()
{
    return [
        'data.confirm_conflict' => [
            function ($attribute, $value, $fail) {
                if ($this->data['has_time_conflict'] && !$value) {
                    $fail('You must address the time conflict before submitting.');
                }
            },
        ],
    ];
}

protected static function checkTimeConflict(Forms\Get $get, Forms\Set $set): void
{
    // Only check for conflicts if this is a classroom request
    if ($get('option') !== 'classroom') {
        $set('has_time_conflict', false);
        return;
    }
    
    $subjectId = $get('subject_id');
    $startsAt = $get('starts_at');
    $endsAtTime = $get('ends_at');
    
    // If any required fields are missing, we can't check for conflicts yet
    if (!$subjectId || !$startsAt || !$endsAtTime) {
        return;
    }
    
    // Convert times to Carbon instances for comparison
    $startsAtDate = Carbon::parse($startsAt);
    list($hours, $minutes) = explode(':', $endsAtTime);
    $endsAtDate = Carbon::parse($startsAt)->setTime((int)$hours, (int)$minutes);
    
    // Get current record ID if we're editing
    $currentId = null;
    if (request()->route('record')) {
        $currentId = request()->route('record');
    }
    
    // Check for overlapping bookings
    $conflictingBookings = Ticket::query()
        ->where('subject_id', $subjectId)
        ->where('option', 'classroom')
        ->where('ticket_status', '!=', 'rejected')
        ->where(function ($query) use ($startsAtDate, $endsAtDate) {
            $query->where(function ($q) use ($startsAtDate, $endsAtDate) {
                // New booking starts during an existing booking
                $q->where('starts_at', '<=', $startsAtDate)
                  ->where('ends_at', '>', $startsAtDate);
            })->orWhere(function ($q) use ($startsAtDate, $endsAtDate) {
                // New booking ends during an existing booking
                $q->where('starts_at', '<', $endsAtDate)
                  ->where('ends_at', '>=', $endsAtDate);
            })->orWhere(function ($q) use ($startsAtDate, $endsAtDate) {
                // New booking completely encapsulates an existing booking
                $q->where('starts_at', '>=', $startsAtDate)
                  ->where('ends_at', '<=', $endsAtDate);
            });
        });
        
    // Exclude the current ticket if we're editing
    if ($currentId) {
        $conflictingBookings->where('id', '!=', $currentId);
    }
    
    $hasConflict = $conflictingBookings->exists();
    
    // Set the conflict flag to control warning display
    $set('has_time_conflict', $hasConflict);
}


    public static function table(Table $table): Table
    {
        return $table
            ->query(
                \App\Models\Ticket::query()
                    ->when(!auth()->user()->hasRole(['super_admin', 'admin', 'technician']), function ($query) {
                        $query->where(function ($query) {
                            $query->where('created_by', auth()->id())
                                ->orWhere('assigned_to', auth()->id());
                        });
                    })
            )
            ->columns([
                Tables\Columns\TextColumn::make('Ticket Information')
                    ->searchable()
                    ->default(fn($record): string => $record->ticket_number)
                    ->description(fn($record): string => 'Created By ' . $record->creator?->name),
                Tables\Columns\TextColumn::make('asset.name')
                    ->label('Asset')
                    ->description(fn($record): string => $record->ticket_type)
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('ticket_type')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'request' => 'Request',
                        'incident' => 'Incident',
                        default => $state,
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('priority')
                    ->searchable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'low' => 'gray',
                        'medium' => 'warning',
                        'high' => 'danger',
                    })->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('ticket_status')
                    ->badge()
                    ->description(fn($record) => null) // Hide the default description
                    ->formatStateUsing(fn($record) => strtoupper($record->ticket_status . ' - Priority ' . $record->priority))
                    ->color(fn($record) => match (strtolower($record->priority)) {
                        'high' => 'danger',
                        'medium' => 'warning',
                        'low' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('assignedUser.name')
                    ->label('Assigned To')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('section.name')
                    ->label('section')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('priority')
                    ->label('Priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                    ])
                    ->native(false)
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->tooltip('View'),
                    Tables\Actions\EditAction::make()
                        ->tooltip('Edit')
                        ->color('warning')
                        ->disabled(fn($record) => $record->status === 'closed')
                        ->visible(fn() => !auth()->user()->hasRole('professor')),
                    Tables\Actions\DeleteAction::make()
                        ->label('Archive')
                        ->tooltip('Archive')
                        ->modalHeading('Archive Ticket')
                        ->visible(fn() => !auth()->user()->hasRole('professor')),
                    Tables\Actions\ForceDeleteAction::make()
                        ->visible(fn() => !auth()->user()->hasRole('professor')),
                    Tables\Actions\RestoreAction::make()
                        ->color('secondary')
                        ->visible(fn() => !auth()->user()->hasRole('professor')),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->tooltip('Actions')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->poll('30s')
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    // Method to get default technician
    public static function getDefaultTechnician(): ?int
    {
        $technician = User::whereHas('roles', function ($query) {
            $query->where('name', 'technician');
        })->first();

        return $technician ? $technician->id : null;
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }
}
