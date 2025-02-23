<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\ApprovalResource\Pages;
use App\Filament\App\Resources\ApprovalResource\RelationManagers;
use App\Models\Approval;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;

class ApprovalResource extends Resource
{
    protected static ?string $model = Approval::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                ->headerActions([
                    Tables\Actions\Action::make('approve')
                        ->button()
                        ->color('success')
                        ->icon('heroicon-o-check')
                        ->action(function (Approval $record) {
                            $record->update(['status' => 'approved']);

                            Notification::make()
                                ->title('Approved successfully')
                                ->success()
                                ->send();
                        })
                        ->visible(fn ($record) => $record?->status === 'pending'),

                    Tables\Actions\Action::make('decline')
                        ->button()
                        ->color('danger')
                        ->icon('heroicon-o-x-mark')
                        ->action(function (Approval $record) {
                            $record->update(['status' => 'declined']);

                            Notification::make()
                                ->title('Declined successfully')
                                ->success()
                                ->send();
                        })
                        ->visible(fn ($record) => $record?->status === 'pending'),
                ])
                ->actions([
                    Tables\Actions\EditAction::make()
                        ->modalWidth('md'),
                ])
                ->bulkActions([
                    Tables\Actions\BulkActionGroup::make([
                        Tables\Actions\DeleteBulkAction::make(),
                    ]),
                ])
                ->defaultSort('created_at', 'desc');
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
            'create' => Pages\CreateApproval::route('/create'),
            'edit' => Pages\EditApproval::route('/{record}/edit'),
        ];
    }
}
