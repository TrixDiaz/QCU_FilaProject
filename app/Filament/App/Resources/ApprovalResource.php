<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\ApprovalResource\Pages;
use App\Filament\App\Resources\ApprovalResource\RelationManagers;
use App\Models\Approval;
use App\Models\Ticket;  // Added import for Ticket model
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Subject;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Components\Wizard;
use Illuminate\Support\Str;
use Filament\Forms\Set;
use Filament\Tables\Columns\SelectColumn;
use Illuminate\Database\Eloquent\Model;

class ApprovalResource extends Resource implements HasShieldPermissions
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

    protected static ?string $model = Approval::class;

    protected static ?string $navigationGroup = 'Ticketss';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationBadge(): ?string
    {
        // Only count pending approvals for the badge
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('asset_id')
                    ->relationship('asset', 'name')
                    ->required(),
                Forms\Components\Select::make('professor_id')
                    ->relationship('professor', 'name')
                    ->required(),
                Forms\Components\Select::make('section_id')
                    ->relationship('section', 'name')
                    ->required(),
                Forms\Components\Select::make('subject_id')
                    ->relationship('subject', 'name')
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->required(),
                Forms\Components\TextInput::make('color')
                    ->required(),
                Forms\Components\DateTimePicker::make('starts_at')
                    ->required(),
                Forms\Components\DateTimePicker::make('ends_at')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'md' => 1,
                'lg' => 1,
            ])
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\Layout\Grid::make([
                        'default' => 3,
                    ])
                        ->columnSpan(1)
                        ->schema([
                            Tables\Columns\TextColumn::make('title')
                                ->size(Tables\Columns\TextColumn\TextColumnSize::Large)
                                ->searchable()
                                ->weight('bold')
                                ->columnSpan(2),
                            Tables\Columns\TextColumn::make('status')
                                ->badge()
                                ->color(fn(string $state): string => match ($state) {
                                    'approved' => 'success',
                                    'declined' => 'danger',
                                    'pending' => 'warning',
                                })
                                ->alignEnd(),
                            Tables\Columns\TextColumn::make('ticket.option')
                                ->badge()
                                ->getStateUsing(fn($record) => $record->ticket->option ?? '')
                                ->extraAttributes([
                                    'class' => 'capitalize'
                                ]),
                        ]),
                    Tables\Columns\Layout\Grid::make([
                        'default' => 2,
                    ])
                        ->schema([
                            Tables\Columns\TextColumn::make('asset.name')
                                ->label('Asset')
                                ->sortable(),
                            Tables\Columns\TextColumn::make('professor.name')
                                ->label('Professor')
                                ->sortable(),
                            Tables\Columns\TextColumn::make('section.name')
                                ->label('Section')
                                ->sortable(),
                            Tables\Columns\TextColumn::make('subject.name')
                                ->label('Subject')
                                ->sortable(),
                        ]),
                    Tables\Columns\Layout\Grid::make([
                        'default' => 2,
                    ])
                        ->schema([
                            Tables\Columns\TextColumn::make('starts_at')
                                ->dateTime()
                                ->sortable(),
                            Tables\Columns\TextColumn::make('ends_at')
                                ->dateTime()
                                ->sortable(),
                        ]),
                ]),
            ])
            ->filters([
                //
            ])

            ->actions([
                Tables\Actions\Action::make('approve')
                    ->button()
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->action(function (Approval $record) {
                        // Check if already processed
                        if ($record->status !== 'pending') {
                            Notification::make()
                                ->title('Request already processed')
                                ->warning()
                                ->send();
                            return;
                        }

                        // Get the necessary data from the record
                        $option = $record->ticket->option ?? 'asset';
                        $ticketType = $record->ticket->type ?? null;

                        // Update ticket status to 'resolved' if it exists
                        if ($record->ticket) {
                            $record->ticket->update(['status' => 'resolved']);
                        }

                        // Handle asset option
                        if ($option === 'asset') {
                            // Get a valid classroom ID using various relationships
                            $classroomId = null;

                            // First try to get classroom from the subject (preferred method since you mentioned this relationship)
                            if ($record->subject_id && $record->subject && $record->subject->classroom_id) {
                                $classroomId = $record->subject->classroom_id;
                            }
                            // If not available, try to get from section
                            else if ($record->section && $record->section->classroom_id) {
                                $classroomId = $record->section->classroom_id;
                            }
                            // Last resort - fetch the first classroom as fallback
                            else {
                                $firstClassroom = \App\Models\Classroom::first();
                                $classroomId = $firstClassroom ? $firstClassroom->id : 1;
                            }

                            // Debug output to understand what we're working with
                            \Illuminate\Support\Facades\Log::info('Asset Group Creation', [
                                'subject_id' => $record->subject_id,
                                'subject_classroom' => $record->subject->classroom_id ?? 'null',
                                'section_classroom' => $record->section->classroom_id ?? 'null',
                                'chosen_classroom_id' => $classroomId
                            ]);

                            // Create new asset group with valid classroom_id
                            \App\Models\AssetGroup::create([
                                'asset_id' => $record->asset_id,
                                'classroom_id' => $classroomId, // Using the valid classroom ID
                                'name' => $record->title,
                                'code' => $record->asset->asset_code ?? \App\Filament\App\Resources\AssetResource::generateUniqueCode(),
                                'status' => 'deploy',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);

                            // Update asset status to 'deploy'
                            if ($record->asset) {
                                $record->asset->update(['status' => 'deploy']);

                                // Also update all related asset groups to 'deploy'
                                \App\Models\AssetGroup::where('asset_id', $record->asset_id)
                                    ->update(['status' => 'deploy']);
                            }
                        }
                        // Handle classroom option
                        else {
                            // For classroom/event option, retrieve dates from ticket if not present on approval
                            $startsAt = $record->starts_at;
                            $endsAt = $record->ends_at;

                            // If the approval record doesn't have dates, check if they exist in the ticket
                            if ((!$startsAt || !$endsAt) && $record->ticket) {
                                $startsAt = $record->ticket->starts_at ?? $record->starts_at;
                                $endsAt = $record->ticket->ends_at ?? $record->ends_at;
                            }

                            // Create event record for classroom option
                            if ($ticketType === 'request' && $option === 'classroom') {
                                $event = \App\Models\Event::create([
                                    'professor_id' => $record->professor_id,
                                    'section_id' => $record->section_id,
                                    'subject_id' => $record->subject_id,
                                    'title' => $record->title,
                                    'color' => $record->color ?? '#a855f7',
                                    'starts_at' => $startsAt,
                                    'ends_at' => $endsAt,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                    'is_visible' => true,  // Ensure the event is visible
                                    'calendar_id' => 1,    // Associate with default calendar
                                ]);

                                // If classroom_id is available, associate it with the event
                                $classroomId = null;
                                if ($record->subject && $record->subject->classroom_id) {
                                    $classroomId = $record->subject->classroom_id;
                                } else if ($record->ticket && $record->ticket->classroom_id) {
                                    $classroomId = $record->ticket->classroom_id;
                                } else if ($record->section && $record->section->classroom_id) {
                                    $classroomId = $record->section->classroom_id;
                                }

                                if ($classroomId) {
                                    // Update the event with classroom information
                                    $event->update(['classroom_id' => $classroomId]);

                                    // Sync with calendar system if needed
                                    try {
                                        // Log successful calendar registration
                                        \Illuminate\Support\Facades\Log::info('Calendar event created', [
                                            'event_id' => $event->id,
                                            'classroom_id' => $classroomId,
                                            'title' => $record->title
                                        ]);
                                    } catch (\Exception $e) {
                                        \Illuminate\Support\Facades\Log::error('Failed to sync event with calendar', [
                                            'event_id' => $event->id,
                                            'error' => $e->getMessage()
                                        ]);
                                    }
                                }
                            }
                        }

                        // Update record status
                        $record->update(['status' => 'approved']);

                        // Send email notification to ticket creator
                        if ($record->ticket && $record->ticket->created_by) {
                            $user = \App\Models\User::find($record->ticket->created_by);

                            if ($user && $user->email) {
                                \Illuminate\Support\Facades\Mail::to($user->email)
                                    ->send(new \App\Mail\TicketApproved([
                                        'ticketTitle' => $record->title,
                                        'ticketType' => $ticketType,
                                        'ticketOption' => $option,
                                        'userName' => $user->name,
                                    ]));
                            }
                        }

                        Notification::make()
                            ->title('Approved successfully')
                            ->success()
                            ->send();

                        $record->delete();
                    })
                //                    ->visible(fn (Approval $record) => $record->status === 'pending')
                ,

                Tables\Actions\Action::make('decline')
                    ->button()
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->requiresConfirmation()
                    ->modalHeading('Decline Request')
                    ->modalDescription('Are you sure you want to decline this request? This will update the ticket status to closed.')
                    ->modalSubmitActionLabel('Yes, decline it')
                    ->action(function (Approval $record) {
                        // Check if already processed
                        if ($record->status !== 'pending') {
                            Notification::make()
                                ->title('Request already processed')
                                ->warning()
                                ->send();
                            return;
                        }

                        // Update ticket status to 'closed' if it exists
                        if ($record->ticket) {
                            $record->ticket->update(['status' => 'closed']);
                        }

                        // Delete record
                        $record->delete();

                        Notification::make()
                            ->title('Request declined')
                            ->success()
                            ->send();
                    })
                    //                    ->visible(fn (Approval $record) => $record->status === 'pending')
                    ->modalWidth('md'),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
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
            'index' => Pages\ListApprovals::route('/'),

            //            'create' => Pages\CreateApproval::route('/create'),
            //            'edit' => Pages\EditApproval::route('/{record}/edit'),
        ];
    }
}
