<?php

namespace App\Services;

use App\Models\Classroom;
use Filament\Forms;

final class DeployComputerSet
{
    public static function schema(): array
    {
        return [
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Grid::make()
                        ->schema([
                            // First column
                            Forms\Components\Group::make()
                                ->schema([
                                    // For creating new Asset
                                    Forms\Components\TextInput::make('serial_number')
                                        ->label('Serial Number')
                                        ->required(),
                                        
                                    // Computer set name
                                    Forms\Components\TextInput::make('name')
                                        ->label('Computer Name')
                                        ->required(),
                                ])
                                ->columnSpan(1),
                                
                            // Second column
                            Forms\Components\Group::make()
                                ->schema([
                                    // Classroom ID
                                    Forms\Components\Select::make('classroom')
                                        ->label('Classroom')
                                        ->options(Classroom::pluck('name', 'id'))
                                        ->required(),
                                        
                                    // Terminal Number - Code (unique per classroom)
                                    Forms\Components\TextInput::make('code')
                                        ->label('Terminal Number')
                                        ->helperText('Must be unique within the classroom')
                                        ->required(),
                                ])
                                ->columnSpan(1),
                        ])
                        ->columns(2),
                ]),
        ];
    }
}