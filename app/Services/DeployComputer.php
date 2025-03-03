<?php

namespace App\Services;

use Filament\Forms;

final class DeployComputer
{
    public static function schema(): array
    {
        return [
            \Filament\Forms\Components\Wizard::make([
                \Filament\Forms\Components\Wizard\Step::make('Internal Hardware')
                    ->schema([
                        // Computer Case
                        \Filament\Forms\Components\Select::make('computer_case')
                            ->options(\App\Models\Asset::whereHas('assetTags', function ($query) {
                                $query->where('name', 'computer-case')
                                    ->where('is_active', true);
                            })
                                ->where('status', 'active')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        // Power Supply
                        \Filament\Forms\Components\Select::make('power_supply')
                            ->options(\App\Models\Asset::whereHas('assetTags', function ($query) {
                                $query->where('name', 'power-supply')
                                    ->where('is_active', true);
                            })
                                ->where('status', 'active')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        // Motherboard
                        \Filament\Forms\Components\Select::make('motherboard')
                            ->options(\App\Models\Asset::whereHas('assetTags', function ($query) {
                                $query->where('name', 'motherboard')
                                    ->where('is_active', true);
                            })
                                ->where('status', 'active')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        // Processor
                        \Filament\Forms\Components\Select::make('processor')
                            ->options(\App\Models\Asset::whereHas('assetTags', function ($query) {
                                $query->where('name', 'processor')
                                    ->where('is_active', true);
                            })
                                ->where('status', 'active')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        // Drive's
                        \Filament\Forms\Components\Select::make('drive')
                            ->options(\App\Models\Asset::whereHas('assetTags', function ($query) {
                                $query->where('name', 'drive')
                                    ->where('is_active', true);
                            })
                                ->where('status', 'active')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->multiple()
                            ->required(),
                        // Ram's
                        \Filament\Forms\Components\Select::make('ram')
                            ->options(\App\Models\Asset::whereHas('assetTags', function ($query) {
                                $query->where('name', 'ram')
                                    ->where('is_active', true);
                            })
                                ->where('status', 'active')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->multiple()
                            ->required(),
                        // Graphic's Card
                        \Filament\Forms\Components\Select::make('graphics_card')
                            ->options(\App\Models\Asset::whereHas('assetTags', function ($query) {
                                $query->where('name', 'graphics-card')
                                    ->where('is_active', true);
                            })
                                ->where('status', 'active')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                    ])->columns(2),
                \Filament\Forms\Components\Wizard\Step::make('Peripheral Components')
                    ->schema([
                        // Monitor
                        \Filament\Forms\Components\Select::make('monitor')
                            ->options(\App\Models\Asset::whereHas('assetTags', function ($query) {
                                $query->where('name', 'monitor')
                                    ->where('is_active', true);
                            })
                                ->where('status', 'active')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        // Keyboard
                        \Filament\Forms\Components\Select::make('keyboard')
                            ->options(\App\Models\Asset::whereHas('assetTags', function ($query) {
                                $query->where('name', 'keyboard')
                                    ->where('is_active', true);
                            })
                                ->where('status', 'active')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        // Mouse
                        \Filament\Forms\Components\Select::make('mouse')
                            ->options(\App\Models\Asset::whereHas('assetTags', function ($query) {
                                $query->where('name', 'mouse')
                                    ->where('is_active', true);
                            })
                                ->where('status', 'active')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        // Headphone
                        \Filament\Forms\Components\Select::make('headphone')
                            ->options(\App\Models\Asset::whereHas('assetTags', function ($query) {
                                $query->where('name', 'headphone')
                                    ->where('is_active', true);
                            })
                                ->where('status', 'active')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        // Speaker
                        \Filament\Forms\Components\Select::make('speaker')
                            ->options(\App\Models\Asset::whereHas('assetTags', function ($query) {
                                $query->where('name', 'speaker')
                                    ->where('is_active', true);
                            })
                                ->where('status', 'active')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])->columns(2),
                \Filament\Forms\Components\Wizard\Step::make('Location')
                    ->schema([
                        \Filament\Forms\Components\Grid::make(2)
                            ->schema([
                                // Name
                                \Filament\Forms\Components\TextInput::make('name')
                                    ->required(),
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
                                            $name = \Illuminate\Support\Str::name($classroom->name);
                                            $enteredname = $get('name'); // Assuming 'name' is the field where the new entered name is stored

                                            $buildingnameFirstLetter = substr($building->name, 0, 1);
                                            $buildingnameLastLetter = substr($building->name, -1);
                                            $classroomnameFirstLetter = substr($name, 0, 1);
                                            $classroomnameLastLetter = substr($name, -1);
                                            $enterednameFirstLetter = substr($enteredname, 0, 1);
                                            $enterednameLastLetter = substr($enteredname, -1);

                                            $set('code', strtoupper("{$buildingnameFirstLetter}{$buildingnameLastLetter}-{$classroomnameFirstLetter}{$classroomnameLastLetter}-{$enterednameFirstLetter}{$enterednameLastLetter}"));
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
                    ]),
            ]),
        ];
    }
}
