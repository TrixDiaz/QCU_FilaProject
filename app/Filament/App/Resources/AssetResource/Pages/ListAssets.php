<?php

namespace App\Filament\App\Resources\AssetResource\Pages;

use App\Filament\App\Resources\AssetResource;
use App\Models\Asset;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAssets extends ListRecords
{
    protected static string $resource = AssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('deploy')
                ->label('Deploy Computer')
                ->color('secondary')
                ->form(\App\Services\DeployComputer::schema())
                ->action(function (array $data) {
                    // Loop through each selected asset and create a new AssetTag record
                    foreach (['computer_case', 'power_supply', 'motherboard', 'processor', 'drive', 'ram', 'graphics_card', 'monitor', 'keyboard', 'mouse', 'headphone', 'speaker'] as $assetType) {
                        if (isset($data[$assetType])) {
                            $assetIds = is_array($data[$assetType]) ? $data[$assetType] : [$data[$assetType]];
                            foreach ($assetIds as $assetId) {
                                \App\Models\TerminalAsset::create([
                                    'asset_id' => $assetId,
                                    'classroom_id' => $data['classroom'], // Assuming classroom is the asset_tag_id
                                ]);
                            }
                        }
                    }
                }),
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(),
            'active' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'active'))
                ->badge(Asset::query()->where('status', 'active')->count())
                ->badgeColor('primary'),
            'inactive' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'inactive'))
                ->badge(Asset::query()->where('status', 'active')->count())
                ->badgeColor('danger'),
        ];
    }
}
