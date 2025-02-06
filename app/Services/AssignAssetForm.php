<?php

namespace App\Services;

use Filament\Forms;

final class AssignAssetForm
{
    public static function schema($record = null): array
    {
        return [
            \Filament\Forms\Components\Grid::make(2)
                ->schema([
                    // Name
                    \Filament\Forms\Components\TextInput::make('name')
                        ->required()
                        ->default($record ? $record->name : ''),
                    // Classroom
                    \Filament\Forms\Components\Select::make('classroom')
                        ->options(\App\Models\Classroom::where('is_active', true)->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->reactive()
                        ->disabled(fn(\Filament\Forms\Get $get) => !$get('name'))
                        ->afterStateUpdated(function ($state, \Filament\Forms\Set $set, \Filament\Forms\Get $get) {
                            $classroom = \App\Models\Classroom::find($state);
                            if ($classroom) {
                                $building = $classroom->building;
                                $slug = \Illuminate\Support\Str::slug($classroom->name);
                                $enteredSlug = $get('name'); // Assuming 'name' is the field where the new entered slug is stored

                                $buildingSlugFirstLetter = substr($building->slug, 0, 1);
                                $buildingSlugLastLetter = substr($building->slug, -1);
                                $classroomSlugFirstLetter = substr($slug, 0, 1);
                                $classroomSlugLastLetter = substr($slug, -1);
                                $enteredSlugFirstLetter = substr($enteredSlug, 0, 1);
                                $enteredSlugLastLetter = substr($enteredSlug, -1);

                                $set('code', strtoupper("{$buildingSlugFirstLetter}{$buildingSlugLastLetter}-{$classroomSlugFirstLetter}{$classroomSlugLastLetter}-{$enteredSlugFirstLetter}{$enteredSlugLastLetter}"));
                            }
                        }),
                ]),
            // Terminal Code
            \Filament\Forms\Components\TextInput::make('code')
                ->label('Code')
                ->required()
                ->disabled()
                ->dehydrated()
                ->extraAttributes([
                    'style' => 'text-transform:uppercase',
                    'class' => 'uppercase'
                ]),
        ];
    }
}
