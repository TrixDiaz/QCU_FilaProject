<?php

namespace App\Filament\App\Resources\AssetResource\Pages;

use App\Filament\App\Resources\AssetResource;
use App\Models\Asset;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAssets extends ListRecords
{
    protected static string $resource = AssetResource::class;

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->orderByDesc('id');
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('deploySet')
                ->label('New Computer Set')
                ->color('warning')
                ->form([
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\Select::make('brand_id')
                                ->label('Brand')
                                ->options(\App\Models\Brand::pluck('name', 'id'))
                                ->required(),
                                
                            Forms\Components\TextInput::make('asset_code')
                                ->label('Asset Code')
                                ->unique(table: Asset::class)
                                ->helperText('Must be unique'),
                                
                            Forms\Components\TextInput::make('serial_number')
                                ->label('Serial Number')
                                ->required()
                                ->unique(table: Asset::class)
                                ->helperText('Must be unique'),
                                
                            Forms\Components\Select::make('classroom')
                                ->label('Classroom')
                                ->options(\App\Models\Classroom::where('is_active', true)->pluck('name', 'id'))
                                ->searchable()
                                ->preload()
                                ->helperText('If available')
                                ->reactive(),
                                
                            Forms\Components\TextInput::make('name')
                                ->label('Terminal Number')
                                ->required()
                                ->reactive()
                                // Validate uniqueness based on classroom
                                ->rules([
                                    function (Forms\Get $get) {
                                        return function ($attribute, $value, $fail) use ($get) {
                                            $classroomId = $get('classroom');
                                            if (!$classroomId) {
                                                return;
                                            }
                                            
                                            // Check if this terminal name already exists in this classroom
                                            $exists = \App\Models\AssetGroup::query()
                                                ->where('classroom_id', $classroomId)
                                                ->where('name', $value)
                                                ->exists();
                                                
                                            if ($exists) {
                                                $fail("This terminal number is already used in this classroom.");
                                            }
                                        };
                                    }
                                ])
                                ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                    $classroomId = $get('classroom');
                                    if ($classroomId && $state) {
                                        $classroom = \App\Models\Classroom::find($classroomId);
                                        if ($classroom) {
                                            $building = $classroom->building;
                                            $name = \Illuminate\Support\Str::slug($classroom->name);
                                            $enteredname = $state;

                                            $buildingnameFirstLetter = substr($building->name, 0, 1);
                                            $buildingnameLastLetter = substr($building->name, -1);
                                            $classroomnameFirstLetter = substr($name, 0, 1);
                                            $classroomnameLastLetter = substr($name, -1);
                                            $enterednameFirstLetter = substr($enteredname, 0, 1);
                                            $enterednameLastLetter = substr($enteredname, -1);

                                            $set('code', strtoupper("{$buildingnameFirstLetter}{$buildingnameLastLetter}-{$classroomnameFirstLetter}{$classroomnameLastLetter}-{$enterednameFirstLetter}{$enterednameLastLetter}"));
                                        }
                                    }
                                }),
                                
                            Forms\Components\TextInput::make('code')
                                ->label('Code')
                                ->disabled()
                                ->dehydrated()
                                ->visible(fn (Forms\Get $get): bool => (bool) $get('classroom'))
                                ->extraAttributes([
                                    'style' => 'text-transform:uppercase',
                                    'class' => 'uppercase'
                                ])
                                // Validate uniqueness based on classroom
                                ->rules([
                                    function (Forms\Get $get) {
                                        return function ($attribute, $value, $fail) use ($get) {
                                            $classroomId = $get('classroom');
                                            if (!$classroomId || !$value) {
                                                return;
                                            }
                                            
                                            // Check if this code already exists in this classroom
                                            $exists = \App\Models\AssetGroup::query()
                                                ->where('classroom_id', $classroomId)
                                                ->where('code', $value)
                                                ->exists();
                                                
                                            if ($exists) {
                                                $fail("This code is already used in this classroom.");
                                            }
                                        };
                                    }
                                ]),
                        ]),
                ])
                ->action(function (array $data) {
                    $user = auth()->user();
                    
                    // Get the category_id for "computer set"
                    $computerSetCategory = \App\Models\Category::where('slug', 'computer_set')->first();
                    
                    // Set the status based on whether a classroom is assigned
                    $status = isset($data['classroom']) ? 'deploy' : 'active';
                    
                    // Create a new asset for the computer set with automatically set category
                    $asset = \App\Models\Asset::create([
                        'category_id' => $computerSetCategory->id, // Automatically set category to "computer set"
                        'brand_id' => $data['brand_id'],
                        'name' => $data['name'],
                        'serial_number' => $data['serial_number'],
                        'asset_code' => $data['asset_code'] ?? null,
                        'status' => $status,
                    ]);
                    
                    // Create the asset group entry for this computer set if classroom is selected
                    if (isset($data['classroom'])) {
                        \App\Models\AssetGroup::create([
                            'asset_id' => $asset->id,
                            'classroom_id' => $data['classroom'],
                            'name' => $data['name'],
                            'code' => $data['code'],
                            'status' => 'active',
                        ]);
                    }
                    
                    // Send a Filament notification to the authenticated user
                    \Filament\Notifications\Notification::make()
                        ->title('Computer Set Added')
                        ->body('The new computer set has been successfully added' . (isset($data['classroom']) ? ' to the classroom' : '') . '.')
                        ->success()
                        ->icon('heroicon-m-computer-desktop')
                        ->sendToDatabase($user);
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Computer Set Added')
                        ->body('The new computer set has been successfully added' . (isset($data['classroom']) ? ' to the classroom' : '') . '.')
                        ->success()
                        ->icon('heroicon-m-computer-desktop')
                        ->send();
                }),
            Actions\Action::make('deploy')
                ->label('Deploy Computer')
                ->color('secondary')
                ->form(\App\Services\DeployComputer::schema())
                ->action(function (array $data) {
                    $user = auth()->user();
                    $assetTypes = ['computer_case', 'power_supply', 'motherboard', 'processor', 'drive', 'ram', 'graphics_card', 'monitor', 'keyboard', 'mouse', 'headphone', 'speaker'];

                    foreach ($assetTypes as $assetType) {
                        if (isset($data[$assetType])) {
                            $assetIds = is_array($data[$assetType]) ? $data[$assetType] : [$data[$assetType]];
                            foreach ($assetIds as $assetId) {
                                \App\Models\AssetGroup::create([
                                    'asset_id' => $assetId,
                                    'classroom_id' => $data['classroom'], // Assuming classroom is the asset_tag_id
                                    'name' => $data['name'],
                                    'code' => $data['code'],
                                    'status' => 'active',
                                ]);

                                // Update the status of the asset to 'deployed'
                                \App\Models\Asset::where('id', $assetId)->update(['status' => 'deploy']);
                            }
                        }
                    }

                    // Send a Filament notification to the authenticated user
                    \Filament\Notifications\Notification::make()
                        ->title('Assets Deployed')
                        ->body('The selected assets have been successfully deployed.')
                        ->success()
                        ->icon('heroicon-m-computer-desktop')
                        ->sendToDatabase($user);

                    \Filament\Notifications\Notification::make()
                        ->title('Assets Deployed')
                        ->body('The selected assets have been successfully deployed.')
                        ->success()
                        ->icon('heroicon-m-computer-desktop')
                        ->send();
                }),
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(),
            'active' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'active'))
                ->badge(Asset::query()->where('status', 'active')->count())
                ->badgeColor('primary'),
            'deploy' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'deploy'))
                ->badge(Asset::query()->where('status', 'deploy')->count())
                ->badgeColor('secondary'),
            'inactive' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'inactive'))
                ->badge(Asset::query()->where('status', 'inactive')->count())
                ->badgeColor('danger'),
        ];
    }
}