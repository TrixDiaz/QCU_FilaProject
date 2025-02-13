<?php

namespace App\Services;

use App\Models\Section;
use App\Models\Subject;
use App\Models\User;
use Filament\Forms;

final class CalendarForm
{
    public static function schema(): array
    {
        return [
            \Filament\Forms\Components\Grid::make()
                ->schema([
                    \Filament\Forms\Components\Section::make()
                        ->schema([
                            \Filament\Forms\Components\TextInput::make('title'),
                            \Filament\Forms\Components\ColorPicker::make('color'),
                            \Filament\Forms\Components\Select::make('professor_id')
                                ->label('Professor')
                                ->options(User::role('professor')->pluck('name', 'id'))
                                ->preload()
                                ->native(false),
                            \Filament\Forms\Components\Select::make('section_id')
                                ->label('Section')
                                ->options(Section::all()->pluck('name', 'id'))
                                ->preload()
                                ->native(false),
                            \Filament\Forms\Components\Select::make('subject_id')
                                ->label('Subject')
                                ->options(Subject::all()->pluck('name', 'id'))
                                ->preload()
                                ->native(false),
                            \Filament\Forms\Components\DateTimePicker::make('starts_at')->native(false),
                            \Filament\Forms\Components\DateTimePicker::make('ends_at')->native(false),
                        ])->columns(2),
                ]),
        ];
    }
}
