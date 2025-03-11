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
                                $query->where('slug', 'computer-case');
                            })
                                ->where('status', 'active')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        // Power Supply
                        \Filament\Forms\Components\Select::make('power_supply')
                            ->options(\App\Models\Asset::whereHas('assetTags', function ($query) {
                                $query->where('slug', 'power-supply');
                            })
                                ->where('status', 'active')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        // Motherboard
                        \Filament\Forms\Components\Select::make('motherboard')
                            ->options(\App\Models\Asset::whereHas('assetTags', function ($query) {
                                $query->where('slug', 'motherboard');
                            })
                                ->where('status', 'active')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        // Processor
                        \Filament\Forms\Components\Select::make('processor')
                            ->options(\App\Models\Asset::whereHas('assetTags', function ($query) {
                                $query->where('slug', 'processor');
                            })
                                ->where('status', 'active')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        // Drive's
                        \Filament\Forms\Components\Select::make('drive')
                            ->options(\App\Models\Asset::whereHas('assetTags', function ($query) {
                                $query->where('slug', 'drive');
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
                                $query->where('slug', 'ram');
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
                                $query->where('slug', 'graphics-card');
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
                                $query->where('slug', 'monitor');
                            })
                                ->where('status', 'active')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        // Keyboard
                        \Filament\Forms\Components\Select::make('keyboard')
                            ->options(\App\Models\Asset::whereHas('assetTags', function ($query) {
                                $query->where('slug', 'keyboard');
                            })
                                ->where('status', 'active')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        // Mouse
                        \Filament\Forms\Components\Select::make('mouse')
                            ->options(\App\Models\Asset::whereHas('assetTags', function ($query) {
                                $query->where('slug', 'mouse');
                            })
                                ->where('status', 'active')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        // Headphone
                        \Filament\Forms\Components\Select::make('headphone')
                            ->options(\App\Models\Asset::whereHas('assetTags', function ($query) {
                                $query->where('slug', 'headphone');
                            })
                                ->where('status', 'active')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        // Speaker
                        \Filament\Forms\Components\Select::make('speaker')
                            ->options(\App\Models\Asset::whereHas('assetTags', function ($query) {
                                $query->where('slug', 'speaker');
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
                                    ->required()
                                    ->default(function () {
                                        return 'T1'; // Default to T1 initially
                                    })
                                    ->reactive()
                                    ->afterStateHydrated(function ($state, \Filament\Forms\Set $set, \Filament\Forms\Get $get) {
                                        // Initially set to T1, will be updated when classroom is selected
                                        $set('name', 'T1');
                                    })
                                    ->disabled()
                                    ->dehydrated(),
                                // Classroom
                                \Filament\Forms\Components\Select::make('classroom')
                                    ->options(\App\Models\Classroom::where('is_active', true)->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, \Filament\Forms\Set $set, \Filament\Forms\Get $get) {
                                        if ($state) {
                                            $classroom = \App\Models\Classroom::find($state);

                                            if ($classroom) {
                                                // Get the highest terminal number for this specific classroom
                                                $lastTerminal = \App\Models\AssetGroup::where('classroom_id', $state)
                                                    ->where('name', 'LIKE', 'T%')
                                                    ->orderByRaw('CAST(SUBSTRING(name, 2) AS UNSIGNED) DESC')
                                                    ->first();

                                                $terminalNumber = 1; // Default to T1
                                                if ($lastTerminal) {
                                                    // Extract number part, increment it
                                                    $lastNumber = (int) substr($lastTerminal->name, 1);
                                                    $terminalNumber = $lastNumber + 1;
                                                }

                                                $terminalName = 'T' . $terminalNumber;
                                                $set('name', $terminalName);

                                                // Update the code as well
                                                $building = $classroom->building;
                                                $classroomSlug = \Illuminate\Support\Str::slug($classroom->name);
                                                $buildingPrefix = substr($building->name, 0, 3);

                                                $set('code', strtoupper("{$buildingPrefix}-{$classroomSlug}-{$terminalName}"));
                                            }
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
