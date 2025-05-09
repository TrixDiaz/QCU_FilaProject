<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\StudentReportResource\Pages;
use App\Filament\App\Resources\StudentReportResource\RelationManagers;
use App\Models\StudentReport;
use App\Models\Subject; 
use App\Models\Section;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class StudentReportResource extends Resource implements HasShieldPermissions
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

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        // Show if user is super admin or has Inventory page permission
        return $user->hasRole('professor');
    }

    protected static ?string $navigationGroup = 'Tickets';
    protected static ?string $model = StudentReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // Add this method to filter records based on logged-in professor
    public static function getEloquentQuery(): Builder 
    {
        $query = parent::getEloquentQuery();
        
        // Only filter if the user is a professor (not admin)
        if (Auth::user()->hasRole('professor') && !Auth::user()->hasRole('super_admin')) {
            return $query->whereHas('attendance.subject.professor', function (Builder $query) {
                $query->where('id', Auth::id());
            });
        }
        
        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\TextInput::make('attendance_id')
                //     ->required()
                //     ->numeric(),
                // Forms\Components\Toggle::make('is_reported')
                //     ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('attendance.student_full_name')
                    ->label('Student Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('attendance.subject.professor.name')
                    ->label('Professor Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_reported')
                    ->label('Reported')
                    ->boolean(),
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
                                                // Modified: Use query approach instead of relationship
                SelectFilter::make('subject')
                ->label('Subject')
                ->options(function () {
                    // Get all subjects related to the current professor
                    if (Auth::user()->hasRole('professor') && !Auth::user()->hasRole('super_admin')) {
                        return Subject::whereHas('professor', function (Builder $query) {
                            $query->where('id', Auth::id());
                        })->pluck('name', 'id');
                    }
                    return Subject::pluck('name', 'id');
                })
                ->query(function (Builder $query, array $data) {
                    if (!$data['value']) {
                        return $query;
                    }
                    
                    return $query->whereHas('attendance', function (Builder $subQuery) use ($data) {
                        $subQuery->where('subject_id', $data['value']);
                    });
                }),
            
            // Modified: Fix the section filter to use the correct column name
            SelectFilter::make('section')
    ->label('Section')
    ->options(function () {
        return Section::pluck('name', 'id');
    })
    ->query(function (Builder $query, array $data) {
        if (!$data['value']) {
            return $query;
        }
        
        // Try to find the correct relationship between attendance and section
        // Option 1: If section is related to subject
        return $query->whereHas('attendance.subject', function (Builder $subQuery) use ($data) {
            $subQuery->where('section_id', $data['value']);
                    });
                }),
        ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('report')
                    ->label('Report to Technician')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Report to Technician')
                    ->modalDescription('Are you sure you want to report this issue to the technician?')
                    ->modalSubmitActionLabel('Yes, Report It')
                    ->action(function (StudentReport $record) {
                        // Create new ticket based on the attendance data
                        $ticket = new \App\Models\Ticket();
                        $ticket->ticket_number = 'INC-' . now()->format('YmdHis') . '-' . rand(100, 999);
                        $ticket->created_by = auth()->id();
                        $ticket->title = 'Computer Issue Report from ' . $record->attendance->student_full_name;

                        // Add the terminal number to the ticket
                        $ticket->terminal_number = $record->attendance->terminal_number;

                        // Add formatted description with sender role
                        $userRole = auth()->user()->roles->first()->name ?? 'User';
                        $ticket->description = json_encode([
                            [
                                "type" => "message",
                                "data" => [
                                    "message" => "Computer issue reported by student",
                                    "sender_role" => $userRole
                                ]
                            ]
                        ]);

                        $ticket->ticket_type = 'incident';
                        $ticket->option = 'asset';
                        $ticket->priority = 'medium';
                        $ticket->ticket_status = 'open';
                        $ticket->subject_id = $record->attendance->subject_id;

                        // Find a random user with the role of technician
                        $technician = \App\Models\User::role('technician')->inRandomOrder()->first();
                        if ($technician) {
                            $ticket->assigned_to = $technician->id;
                        }

                        // Save the ticket
                        $ticket->save();

                        // Update the student report as reported
                        $record->is_reported = true;
                        $record->save();

                        // Show success notification
                        \Filament\Notifications\Notification::make()
                            ->title('Ticket Created')
                            ->body('The issue has been successfully reported to the technician.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn(StudentReport $record) => !$record->is_reported),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('report')
                        ->label('Report to Technician')
                        ->requiresConfirmation()
                        ->modalHeading('Report to Technician')
                        ->modalDescription('Are you sure you want to report these issues to the technician?')
                        ->modalSubmitActionLabel('Yes, Report Them')
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                // Create new ticket based on the attendance data
                                $ticket = new \App\Models\Ticket();
                                $ticket->ticket_number = 'INC-' . now()->format('YmdHis') . '-' . rand(100, 999);
                                $ticket->created_by = auth()->id();
                                $ticket->title = 'Computer Issue Report from ' . $record->attendance->student_full_name;

                                // Add the terminal number to the ticket
                                $ticket->terminal_number = $record->attendance->terminal_number;

                                // Add formatted description with sender role
                                $userRole = auth()->user()->roles->first()->name ?? 'User';
                                $ticket->description = json_encode([
                                    [
                                        "type" => "message",
                                        "data" => [
                                            "message" => "Computer issue reported by student",
                                            "sender_role" => $userRole
                                        ]
                                    ]
                                ]);

                                $ticket->ticket_type = 'incident';
                                $ticket->option = 'asset';
                                $ticket->priority = 'medium';
                                $ticket->ticket_status = 'open';
                                $ticket->subject_id = $record->attendance->subject_id;

                                // Find a random user with the role of technician
                                $technician = \App\Models\User::role('technician')->inRandomOrder()->first();
                                if ($technician) {
                                    $ticket->assigned_to = $technician->id;
                                }

                                // Save the ticket
                                $ticket->save();

                                // Update the student report as reported
                                $record->is_reported = true;
                                $record->save();
                            }

                            // Show success notification
                            \Filament\Notifications\Notification::make()
                                ->title('Tickets Created')
                                ->body('The issues have been successfully reported to the technician.')
                                ->success()
                                ->send();
                        }),
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
            'index' => Pages\ListStudentReports::route('/'),
            // 'create' => Pages\CreateStudentReport::route('/create'),
            // 'edit' => Pages\EditStudentReport::route('/{record}/edit'),
        ];
    }
}
