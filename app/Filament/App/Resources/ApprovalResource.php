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

class ApprovalResource extends Resource
{
    protected static ?string $model = Approval::class;

    protected static ?string $navigationGroup = 'Tickets';
    protected static ?string $navigationParentItem = 'Tickets';

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
                                ->color(fn (string $state): string => match ($state) {
                                    'approved' => 'success',
                                    'declined' => 'danger',
                                    'pending' => 'warning',
                                })
                                ->alignEnd(),
                                Tables\Columns\TextColumn::make('ticket.option')
                                ->badge()
                                ->getStateUsing(fn ($record) => $record->ticket->option ?? '')
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
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'declined' => 'Declined',
                    ])
                    ->default('pending'),
            ])

            ->actions([
                Tables\Actions\ViewAction::make()
                    ->button()
                    ->color('info')
                    ->icon('heroicon-o-eye')
                    ->tooltip('View details'),
                
                Tables\Actions\Action::make('approve')
                    ->button()
                    ->color('success')
                    ->icon('heroicon-o-check')
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
                        
                        // Update ticket status to 'resolved' if it exists
                        if ($record->ticket) {
                            $record->ticket->update(['status' => 'resolved']);
                        }
                        
                        if($option === 'asset')
                        {
                            \App\Models\AssetGroup::create([
                                'asset_id' => $record->asset_id,
                                'classroom_id' => $record->section->classroom_id ?? null,
                                'name' => $record->title,
                                'code' => $record->asset->asset_code ?? \App\Filament\App\Resources\AssetResource::generateUniqueCode(),
                                'status' => 'active',
                            ]);
                            
                            // Update asset status to 'deploy'
                            if ($record->asset) {
                                $record->asset->update(['status' => 'deploy']);
                            }
                        } else {
                            // For classroom/event option, retrieve dates from ticket if not present on approval
                            $startsAt = $record->starts_at;
                            $endsAt = $record->ends_at;
                            
                            // If the approval record doesn't have dates, check if they exist in the ticket
                            if ((!$startsAt || !$endsAt) && $record->ticket) {
                                $startsAt = $record->ticket->starts_at ?? $record->starts_at;
                                $endsAt = $record->ticket->ends_at ?? $record->ends_at;
                            }
                            
                            \App\Models\Event::create([
                                'professor_id' => $record->professor_id,
                                'section_id' => $record->section_id,
                                'subject_id' => $record->subject_id,
                                'title' => $record->title,
                                'color' => $record->color ?? '#ffffff',
                                'starts_at' => $startsAt,
                                'ends_at' => $endsAt,
                            ]);
                        }

                        // Update record status
                        $record->update(['status' => 'approved']);

                        Notification::make()
                            ->title('Approved successfully')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Approval $record) => $record->status === 'pending'),

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
                        
                        // Update record status
                        $record->update(['status' => 'declined']);

                        Notification::make()
                            ->title('Request declined')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Approval $record) => $record->status === 'pending')
                    ->modalWidth('md'),
                    
                Tables\Actions\DeleteAction::make()
                    ->tooltip('Remove this record')
                    ->visible(fn (Approval $record) => $record->status !== 'pending'),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s')
            ->modifyQueryUsing(function (Builder $query) {
                $statusFilter = request()->input('tableFilters.status');
            
                // Apply filter if a status is selected
                if ($statusFilter) {
                    return $query->where('status', $statusFilter);
                }
            
                // Default to showing all statuses if no filter is applied
                return $query;
            });
            
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