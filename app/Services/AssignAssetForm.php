<?php

namespace App\Services;

use Filament\Forms;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class AssignAssetForm
{
    public static function schema($record = null): array
    {
        return [
            \Filament\Forms\Components\Grid::make(2)
                ->schema([
                    \Filament\Forms\Components\Select::make('classroom')
                        ->label('Classroom')
                        ->options(\App\Models\Classroom::where('is_active', true)->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, \Filament\Forms\Set $set) use ($record) {
                            $classroomId = $state;
                            if ($classroomId) {
                                // Pass the record to getNextTerminalNumber
                                $nextTerminal = self::getNextTerminalNumber($classroomId, $record);
                                $set('name', $nextTerminal);

                                // Generate unique code
                                $classroom = \App\Models\Classroom::find($classroomId);
                                if ($classroom) {
                                    $building = $classroom->building;
                                    $classroomSlug = Str::slug($classroom->name);
                                    $buildingPrefix = substr($building->name, 0, 3);
                                    $set('code', strtoupper("{$buildingPrefix}-{$classroomSlug}-{$nextTerminal}"));
                                }
                            }
                        }),

                    \Filament\Forms\Components\TextInput::make('name')
                        ->label('Asset Name')
                        ->required()
                        ->disabled()
                        ->dehydrated()
                        ->visible(fn(\Filament\Forms\Get $get): bool => (bool) $get('classroom')),
                ]),

            \Filament\Forms\Components\TextInput::make('code')
                ->label('Code')
                ->required()
                ->disabled()
                ->dehydrated()
                ->visible(fn(\Filament\Forms\Get $get): bool => (bool) $get('classroom'))
                ->extraAttributes([
                    'style' => 'text-transform:uppercase',
                    'class' => 'uppercase'
                ]),
        ];
    }

    public static function process(array $data, $assetId)
    {
        try {
            return DB::transaction(function () use ($data, $assetId) {
                Log::info("Starting asset assignment process", [
                    'asset_id' => $assetId,
                    'classroom_id' => $data['classroom'],
                    'terminal_number' => $data['name']
                ]);

                $exists = \App\Models\AssetGroup::query()
                    ->where('classroom_id', $data['classroom'])
                    ->where('name', $data['name'])
                    ->exists();

                if ($exists) {
                    throw new \Exception('Terminal number already exists. Please try again.');
                }

                $asset = \App\Models\Asset::find($assetId);
                if (!$asset) {
                    Log::error("Asset not found", ['asset_id' => $assetId]);
                    throw new \Exception("Asset with ID {$assetId} not found.");
                }

                // ✅ Update Asset Name Before Saving to Asset Group
                $asset->update([
                    'name' => $data['name'],
                    'status' => 'deploy'
                ]);

                Log::info("Asset name updated successfully", [
                    'asset_id' => $assetId,
                    'new_name' => $data['name']
                ]);

                // ✅ Create Asset Group with Updated Asset Name
                $assetGroup = \App\Models\AssetGroup::create([
                    'asset_id' => $assetId,
                    'classroom_id' => $data['classroom'],
                    'name' => $data['name'],
                    'code' => $data['code'],
                    'status' => 'active',
                ]);

                if (!$assetGroup) {
                    Log::error("Failed to create asset group", ['asset_id' => $assetId]);
                    throw new \Exception("Failed to create asset group for asset with ID {$assetId}.");
                }

                Log::info("Asset assigned successfully", [
                    'asset_id' => $assetId,
                    'asset_group_id' => $assetGroup->id
                ]);

                return true;
            }, 5);
        } catch (\Exception $e) {
            Log::error("Error in asset assignment", [
                'asset_id' => $assetId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected static function getNextTerminalNumber($classroomId, $assetRecord = null)
    {
        return DB::transaction(function () use ($classroomId, $assetRecord) {
            // Get the asset type directly from the passed record
            $originalAssetName = $assetRecord && isset($assetRecord->name) ? $assetRecord->name : 'PC';

            // Replace spaces with hyphens in the asset name
            $assetName = str_replace(' ', '-', $originalAssetName);

            $existingTerminals = \App\Models\AssetGroup::query()
                ->where('classroom_id', $classroomId)
                ->lockForUpdate()
                ->pluck('name')
                ->toArray();

            $highestNumber = 0;
            $pattern = '/^' . preg_quote($assetName, '/') . '(\d+)$/';

            foreach ($existingTerminals as $terminal) {
                if (preg_match($pattern, $terminal, $matches)) {
                    $highestNumber = max($highestNumber, (int)$matches[1]);
                }
            }

            $nextNumber = $highestNumber + 1;
            $nextTerminal = $assetName . $nextNumber;

            do {
                $exists = \App\Models\AssetGroup::query()
                    ->where('classroom_id', $classroomId)
                    ->where('name', $nextTerminal)
                    ->exists();

                if ($exists) {
                    $nextNumber++;
                    $nextTerminal = $assetName . $nextNumber;
                }
            } while ($exists);

            return $nextTerminal;
        }, 5);
    }
}
