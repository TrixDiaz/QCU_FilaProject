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
                                $query->where('slug', 'computer-case')
                                    ->where('is_active', true); // Ensure the tag is active
                            })
                                ->where('status', 'active') // Ensure the asset status is active
                                ->pluck('name', 'id')) // Adjust this if you need a different field for the select
                            ->searchable()
                            ->preload()
                            ->required(),
                        // Power Supply
                        \Filament\Forms\Components\Select::make('power_supply')
                            ->options(\App\Models\Asset::whereHas('assetTags', function ($query) {
                                $query->where('slug', 'power-supply')
                                    ->where('is_active', true); // Ensure the tag is active
                            })
                                ->where('status', 'active') // Ensure the asset status is active
                                ->pluck('name', 'id')) // Adjust this if you need a different field for the select
                            ->searchable()
                            ->preload()
                            ->required(),
                        // Motherboard
                        \Filament\Forms\Components\Select::make('motherboard')
                            ->options(\App\Models\Asset::whereHas('assetTags', function ($query) {
                                $query->where('slug', 'motherboard')
                                    ->where('is_active', true); // Ensure the tag is active
                            })
                                ->where('status', 'active') // Ensure the asset status is active
                                ->pluck('name', 'id')) // Adjust this if you need a different field for the select
                            ->searchable()
                            ->preload()
                            ->required(),
                        // Processor
                        \Filament\Forms\Components\Select::make('processor')
                            ->options(\App\Models\Asset::whereHas('assetTags', function ($query) {
                                $query->where('slug', 'processor')
                                    ->where('is_active', true); // Ensure the tag is active
                            })
                                ->where('status', 'active') // Ensure the asset status is active
                                ->pluck('name', 'id')) // Adjust this if you need a different field for the select
                            ->searchable()
                            ->preload()
                            ->required(),
                        // Drive's
                        \Filament\Forms\Components\Select::make('drive')
                            ->options(\App\Models\Asset::whereHas('assetTags', function ($query) {
                                $query->where('slug', 'drive')
                                    ->where('is_active', true); // Ensure the tag is active
                            })
                                ->where('status', 'active') // Ensure the asset status is active
                                ->pluck('name', 'id')) // Adjust this if you need a different field for the select
                            ->searchable()
                            ->preload()
                            ->multiple()
                            ->required(),
                        // Ram's
                        \Filament\Forms\Components\Select::make('ram')
                            ->options(\App\Models\Asset::whereHas('assetTags', function ($query) {
                                $query->where('slug', 'ram')
                                    ->where('is_active', true); // Ensure the tag is active
                            })
                                ->where('status', 'active') // Ensure the asset status is active
                                ->pluck('name', 'id')) // Adjust this if you need a different field for the select
                            ->searchable()
                            ->preload()
                            ->multiple()
                            ->required(),
                        // Graphic's Card
                        \Filament\Forms\Components\Select::make('graphics_card')
                            ->options(\App\Models\Asset::whereHas('assetTags', function ($query) {
                                $query->where('slug', 'graphics-card')
                                    ->where('is_active', true); // Ensure the tag is active
                            })
                                ->where('status', 'active') // Ensure the asset status is active
                                ->pluck('name', 'id')) // Adjust this if you need a different field for the select
                            ->searchable()
                            ->preload(),
                    ])->columns(2),
                \Filament\Forms\Components\Wizard\Step::make('Peripheral Components')
                    ->schema([
                        // Monitor
                        \Filament\Forms\Components\Select::make('monitor')
                            ->options(\App\Models\Asset::whereHas('assetTags', function ($query) {
                                $query->where('slug', 'monitor')
                                    ->where('is_active', true); // Ensure the tag is active
                            })
                                ->where('status', 'active') // Ensure the asset status is active
                                ->pluck('name', 'id')) // Adjust this if you need a different field for the select
                            ->searchable()
                            ->preload()
                            ->required(),
                        // Keyboard
                        \Filament\Forms\Components\Select::make('keyboard')
                            ->options(\App\Models\Asset::whereHas('assetTags', function ($query) {
                                $query->where('slug', 'keyboard')
                                    ->where('is_active', true); // Ensure the tag is active
                            })
                                ->where('status', 'active') // Ensure the asset status is active
                                ->pluck('name', 'id')) // Adjust this if you need a different field for the select
                            ->searchable()
                            ->preload()
                            ->required(),
                        // Mouse
                        \Filament\Forms\Components\Select::make('mouse')
                            ->options(\App\Models\Asset::whereHas('assetTags', function ($query) {
                                $query->where('slug', 'mouse')
                                    ->where('is_active', true); // Ensure the tag is active
                            })
                                ->where('status', 'active') // Ensure the asset status is active
                                ->pluck('name', 'id')) // Adjust this if you need a different field for the select
                            ->searchable()
                            ->preload()
                            ->required(),
                        // Headphone
                        \Filament\Forms\Components\Select::make('headphone')
                            ->options(\App\Models\Asset::whereHas('assetTags', function ($query) {
                                $query->where('slug', 'headphone')
                                    ->where('is_active', true); // Ensure the tag is active
                            })
                                ->where('status', 'active') // Ensure the asset status is active
                                ->pluck('name', 'id')) // Adjust this if you need a different field for the select
                            ->searchable()
                            ->preload()
                            ->required(),
                        // Speaker
                        \Filament\Forms\Components\Select::make('speaker')
                            ->options(\App\Models\Asset::whereHas('assetTags', function ($query) {
                                $query->where('slug', 'speaker')
                                    ->where('is_active', true); // Ensure the tag is active
                            })
                                ->where('status', 'active') // Ensure the asset status is active
                                ->pluck('name', 'id')) // Adjust this if you need a different field for the select
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
                                            $slug = \Illuminate\Support\Str::slug($classroom->name);
                                            $enteredSlug = $get('name'); // Assuming 'name' is the field where the new entered slug is stored

                                            $buildingSlugFirstLetter = substr($building->slug, 0, 1);
                                            $buildingSlugLastLetter = substr($building->slug, -1);
                                            $classroomSlugFirstLetter = substr($slug, 0, 1);
                                            $classroomSlugLastLetter = substr($slug, -1);
                                            $enteredSlugFirstLetter = substr($enteredSlug, 0, 1);
                                            $enteredSlugLastLetter = substr($enteredSlug, -1);

                                            $set('terminal_code', strtoupper("{$buildingSlugFirstLetter}{$buildingSlugLastLetter}-{$classroomSlugFirstLetter}{$classroomSlugLastLetter}-{$enteredSlugFirstLetter}{$enteredSlugLastLetter}"));
                                        }
                                    }),
                            ]),
                        // Terminal Code
                        \Filament\Forms\Components\TextInput::make('terminal_code')
                            ->label('Terminal Code')
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
