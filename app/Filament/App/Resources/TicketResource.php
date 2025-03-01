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
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Textarea;
use Illuminate\Database\Eloquent\Builder;
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
        return $form
            ->schema([
                Forms\Components\Grid::make(2) // Creating a 2-column grid
                ->schema([
                    Forms\Components\Grid::make() // Right column for Placeholder
                    ->schema([
                        //.....
                    ])->columnSpan(1),
                    Forms\Components\Section::make() // Right column for Placeholder
                    ->schema([
                        Forms\Components\Placeholder::make('ticket')
                            ->label('Ticket Number')
                            ->content(fn($get): string => $get('ticket_number') ?? 'Please Select Ticket Type to Generate'),
                    ])->columnSpan(1),
                    Forms\Components\Section::make()
                        ->schema([
                            Wizard::make([
                                Wizard\Step::make('Ticket Information')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('title')
                                                    ->required(),
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
                                                Forms\Components\Select::make('option')
                                                    ->options([
                                                        'asset' => 'Asset',
                                                        'classroom' => 'Classroom',
                                                    ])
                                                    ->visible(fn($get) => $get('ticket_type') === 'request')
                                                    ->live()
                                                    ->afterStateUpdated(function ($state, callable $set) {
                                                        if ($state === 'classroom') {
                                                            $set('asset_id', null);
                                                        }
                                                    }),

                                                Forms\Components\DateTimePicker::make('starts_at')
                                                    ->visible(fn($get) => $get('option') === 'classroom'),

                                                Forms\Components\DateTimePicker::make('ends_at')
                                                    ->visible(fn($get) => $get('option') === 'classroom'),

                                                Forms\Components\Select::make('asset_id')
                                                    ->relationship('asset', 'name')
                                                    ->required(fn($get) => $get('option') === 'asset')
                                                    ->searchable()
                                                    ->preload()
                                                    ->optionsLimit(5)
                                                    ->visible(fn($get) => $get('option') !== 'classroom')
                                                    ->visible(fn($get) => $get('option') === 'asset'),

                                                Forms\Components\Select::make('section_id')
                                                    ->relationship('section', 'name')
                                                    ->required()
                                                    ->searchable()
                                                    ->preload()
                                                    ->optionsLimit(5)
                                                    ->visible(fn($get) => $get('option') !== 'classroom')
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
                                                    ->visible(fn($get) => $get('option') === 'classroom')
                                                    ->afterStateUpdated(function ($state, callable $set) {
                                                        if ($state === 'asset') {
                                                            $set('subject_id', null);
                                                        }
                                                    }),
                                                Textarea::make('description')
                                                    ->required()
                                                    ->columnSpanFull(),
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
                                        Forms\Components\TextInput::make('created_by')
                                            ->required()
                                            ->default(fn() => auth()->user()->name)
                                            ->dehydrateStateUsing(fn() => auth()->id()),
                                        Forms\Components\Select::make('assigned_to')
                                            ->required()
                                            ->options(User::all()->pluck('name', 'id'))
                                            ->searchable()
                                            ->visible(fn() => auth()->user()->role !== 'professor'),
                                        Forms\Components\Select::make('priority')
                                            ->required()
                                            ->default('low')
                                            ->options([
                                                'low' => 'Low',
                                                'medium' => 'Medium',
                                                'high' => 'High',
                                            ]),
                                        Forms\Components\FileUpload::make('attachments')
                                            ->multiple()
                                            ->openable()
                                            ->image()
                                            ->downloadable(),
                                        //    Forms\Components\DateTimePicker::make('starts_at'),
                                        //    Forms\Components\DateTimePicker::make('ends_at'),
                                    ]),
                            ])->columnSpan(1), // Ensures the Wizard takes one column
                        ]),

                ])
                    ->columnSpanFull(), // Full width grid with two equal columns
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->query(
                \App\Models\Ticket::query()->where(function ($query) {
                    $query->where('created_by', auth()->id())
                        ->orWhere('assigned_to', auth()->id());
                })
            )
            ->columns([
                Tables\Columns\TextColumn::make('Ticket Information')
                    ->searchable()
                    ->default(fn($record): string => $record->ticket_number)
                    ->description(fn($record): string => $record->creator?->name),
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
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('section.name')
                    ->label('section')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Tables\Filters\SelectFilter::make('is_active')
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
        return [];
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
