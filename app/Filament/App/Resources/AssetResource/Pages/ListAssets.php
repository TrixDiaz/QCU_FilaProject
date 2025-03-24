<?php

namespace App\Filament\App\Resources\AssetResource\Pages;

use App\Filament\App\Resources\AssetResource;
use App\Models\Asset;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ListAssets extends ListRecords
{
    protected static string $resource = AssetResource::class;

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->orderByDesc('id');
    }

    public static function generateUniqueCode()
    {
        do {
            $code = Str::upper(Str::random(4));
        } while (DB::table('assets')->where('asset_code', $code)->exists());

        return $code;
    }

    protected function getNextTerminalNumber($classroomId)
    {
        // Use a transaction with table locking to prevent race conditions
        return DB::transaction(function () use ($classroomId) {
            // Lock the specific rows for this classroom to prevent concurrent access
            $existingTerminals = \App\Models\AssetGroup::query()
                ->where('classroom_id', $classroomId)
                ->lockForUpdate() // This locks the rows for the transaction duration
                ->pluck('name')
                ->toArray();

            // Find the highest number currently in use
            $highestNumber = 0;
            foreach ($existingTerminals as $terminal) {
                if (preg_match('/^T(\d+)$/', $terminal, $matches)) {
                    $highestNumber = max($highestNumber, (int)$matches[1]);
                }
            }

            // Generate the next terminal number
            $nextNumber = $highestNumber + 1;
            $nextTerminal = 'T' . $nextNumber;

            // Double-check that this terminal number doesn't exist already
            // This is a failsafe against any possible issues
            $exists = \App\Models\AssetGroup::query()
                ->where('classroom_id', $classroomId)
                ->where('name', $nextTerminal)
                ->exists();

            if ($exists) {
                // If somehow the terminal number exists, increment until we find a free one
                do {
                    $nextNumber++;
                    $nextTerminal = 'T' . $nextNumber;
                    $exists = \App\Models\AssetGroup::query()
                        ->where('classroom_id', $classroomId)
                        ->where('name', $nextTerminal)
                        ->exists();
                } while ($exists);
            }

            return $nextTerminal;
        }, 5); // 5 attempts at the transaction before giving up
    }

    protected function generateAssetCodeFromSerialNumber($serialNumber)
    {
        // Get the last 4 digits of the serial number
        $lastFourDigits = Str::substr($serialNumber, -4);

        // Make it uppercase and prefix with "AS"
        $baseCode = "AS" . Str::upper($lastFourDigits);

        // Add a unique code
        return $baseCode . self::generateUniqueCode();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('deploySet')
                ->label('New Computer Set')
                ->color('warning')
                ->form([
                    Forms\Components\Section::make()
                        ->columns(3)
                        ->schema([
                            Forms\Components\Select::make('brand_id')
                                ->label('Brand')
                                ->options(\App\Models\Brand::pluck('name', 'id'))
                                ->required()
                                ->columnSpan(1),

                            Forms\Components\TextInput::make('serial_number')
                                ->label('Serial Number')
                                ->required()
                                ->unique(table: Asset::class)
                                ->live(onBlur: true) // Only update on blur instead of on every keystroke
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    if ($state) {
                                        // Generate asset code based on serial number
                                        $set('asset_code', $this->generateAssetCodeFromSerialNumber($state));
                                    }
                                })
                                ->columnSpan(1),

                            Forms\Components\Select::make('classroom')
                                ->label('Classroom')
                                ->options(\App\Models\Classroom::where('is_active', true)->pluck('name', 'id'))
                                ->searchable()
                                ->preload()
                                ->reactive()
                                ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                    $classroomId = $state;
                                    if ($classroomId && $state) {
                                        // Auto-generate terminal number when classroom changes
                                        $nextTerminal = $this->getNextTerminalNumber($classroomId);
                                        $set('name', $nextTerminal);

                                        // Generate code based on the new terminal number
                                        $classroom = \App\Models\Classroom::find($classroomId);
                                        if ($classroom) {
                                            $building = $classroom->building;
                                            $classroomSlug = Str::slug($classroom->name);
                                            $buildingPrefix = substr($building->name, 0, 3);
                                            $set('code', strtoupper("{$buildingPrefix}-{$classroomSlug}-{$nextTerminal}"));
                                        }
                                    } else {
                                        // Set default values when no classroom is selected
                                        $set('name', 'Not Assigned');
                                        $set('code', '');
                                    }
                                })
                                ->columnSpan(1),

                            Forms\Components\TextInput::make('asset_code')
                                ->label('Asset Code')
                                ->unique(table: Asset::class)
                                ->disabled()
                                ->dehydrated()
                                ->columnSpan(1),



                            Forms\Components\TextInput::make('name')
                                ->label('Terminal Number')
                                ->required()
                                ->default('Not Assigned')
                                ->disabled() // Make it not editable
                                ->dehydrated() // Ensure the value is included when form submitted
                                ->columnSpan(1),

                            Forms\Components\TextInput::make('code')
                                ->label('Computer Name')
                                ->disabled()
                                ->dehydrated()
                                // ->visible(fn(Forms\Get $get): bool => (bool) $get('classroom'))
                                ->extraAttributes([
                                    'style' => 'text-transform:uppercase',
                                    'class' => 'uppercase'
                                ])
                                ->columnSpan(1),
                        ]),
                ])
                ->action(function (array $data) {
                    $user = auth()->user();

                    try {
                        DB::beginTransaction();

                        // Get a fresh terminal number immediately before inserting
                        // This helps prevent race conditions between form display and submission
                        if (isset($data['classroom'])) {
                            // Refresh the terminal number right before creating the record
                            $freshTerminal = $this->getNextTerminalNumber($data['classroom']);

                            // Only update if the terminal has changed
                            if ($freshTerminal !== $data['name']) {
                                $data['name'] = $freshTerminal;

                                // Update code based on the new name
                                $classroom = \App\Models\Classroom::find($data['classroom']);
                                if ($classroom) {
                                    $building = $classroom->building;
                                    $classroomSlug = Str::slug($classroom->name);
                                    $buildingPrefix = substr($building->name, 0, 3);
                                    $data['code'] = strtoupper("{$buildingPrefix}-{$classroomSlug}-{$freshTerminal}");
                                }
                            }

                            // Final verification to ensure no duplicates
                            $exists = \App\Models\AssetGroup::query()
                                ->where('classroom_id', $data['classroom'])
                                ->where('name', $data['name'])
                                ->exists();

                            if ($exists) {
                                throw new \Exception('Terminal number already exists. Please try again.');
                            }
                        } else {
                            // Ensure name is 'Not Assigned' when no classroom is selected
                            $data['name'] = 'Not Assigned';
                        }

                        // Get or create the category_id for "computer set"
                        $computerSetCategory = \App\Models\Category::firstOrCreate(
                            ['slug' => 'computer_set'],
                            ['name' => 'Computer Set', 'description' => 'Complete computer system']
                        );

                        // Set the status based on whether a classroom is assigned
                        $status = isset($data['classroom']) ? 'deploy' : 'active';

                        // Create a new asset for the computer set with automatically set category
                        $asset = \App\Models\Asset::create([
                            'category_id' => $computerSetCategory->id, // Automatically set category to "computer set"
                            'brand_id' => $data['brand_id'],
                            'name' => $data['name'], // Now always has a value
                            'serial_number' => $data['serial_number'],
                            'asset_code' => $data['asset_code'],
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

                        DB::commit();

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
                    } catch (\Exception $e) {
                        DB::rollBack();

                        \Filament\Notifications\Notification::make()
                            ->title('Error')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Actions\Action::make('deploy')
                ->label('Deploy Computer')
                ->color('secondary')
                ->form(\App\Services\DeployComputer::schema())
                ->action(function (array $data) {
                    $user = auth()->user();
                    $assetTypes = ['computer_case', 'power_supply', 'motherboard', 'processor', 'drive', 'ram', 'graphics_card', 'monitor', 'keyboard', 'mouse', 'headphone', 'speaker'];

                    try {
                        DB::beginTransaction();

                        // Ensure we get a fresh terminal number if one is provided
                        if (!isset($data['name']) && isset($data['classroom'])) {
                            $data['name'] = $this->getNextTerminalNumber($data['classroom']);

                            // Update code based on the new name if needed
                            if (isset($data['code'])) {
                                $classroom = \App\Models\Classroom::find($data['classroom']);
                                if ($classroom) {
                                    $building = $classroom->building;
                                    $classroomSlug = Str::slug($classroom->name);
                                    $buildingPrefix = substr($building->name, 0, 3);
                                    $data['code'] = strtoupper("{$buildingPrefix}-{$classroomSlug}-{$data['name']}");
                                }
                            }
                        } else if (!isset($data['classroom'])) {
                            // Set default name when no classroom is selected
                            $data['name'] = 'Not Assigned';
                        }

                        // Check if the terminal name already exists in this classroom
                        if (isset($data['classroom']) && isset($data['name'])) {
                            $exists = \App\Models\AssetGroup::query()
                                ->where('classroom_id', $data['classroom'])
                                ->where('name', $data['name'])
                                ->exists();

                            if ($exists) {
                                throw new \Exception('Terminal number already exists. Please choose a different one.');
                            }
                        }

                        foreach ($assetTypes as $assetType) {
                            if (isset($data[$assetType])) {
                                $assetIds = is_array($data[$assetType]) ? $data[$assetType] : [$data[$assetType]];
                                foreach ($assetIds as $assetId) {
                                    if (isset($data['classroom'])) {
                                        \App\Models\AssetGroup::create([
                                            'asset_id' => $assetId,
                                            'classroom_id' => $data['classroom'],
                                            'name' => $data['name'],
                                            'code' => $data['code'],
                                            'status' => 'active',
                                        ]);
                                    }

                                    // Update the status of the asset to 'deployed' if classroom is set, otherwise 'active'
                                    $status = isset($data['classroom']) ? 'deploy' : 'active';

                                    // Always ensure the name field has a value in the assets table
                                    \App\Models\Asset::where('id', $assetId)->update([
                                        'status' => $status,
                                        'name' => $data['name'] ?? 'Not Assigned'
                                    ]);

                                    // Send notification for each deployed asset
                                    if ($status === 'deploy') {
                                        $asset = \App\Models\Asset::find($assetId);
                                        $classroom = \App\Models\Classroom::find($data['classroom']);

                                        // Send notification to the user who performed the action
                                        \Filament\Notifications\Notification::make()
                                            ->title('Asset Deployed')
                                            ->body("Asset {$asset->asset_code} has been deployed to {$classroom->name} as {$data['name']}")
                                            ->success()
                                            ->icon('heroicon-m-computer-desktop')
                                            ->actions([
                                                \Filament\Notifications\Actions\Action::make('view')
                                                    ->url(AssetResource::getUrl('edit', ['record' => $assetId]))
                                            ])
                                            ->sendToDatabase(auth()->user());
                                    }
                                }
                            }
                        }

                        DB::commit();

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
                    } catch (\Exception $e) {
                        DB::rollBack();

                        \Filament\Notifications\Notification::make()
                            ->title('Error')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->whereIn('status', ['active', 'deploy']))
                ->badge(Asset::query()->whereIn('status', ['active', 'deploy'])->count()),
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

    public function getDefaultActiveTab(): string|int|null
    {
        return 'active';
    }
}
