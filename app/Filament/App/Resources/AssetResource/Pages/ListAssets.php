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

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->orderByDesc('id');
    }
    protected function getHeaderActions(): array
    {
        return [
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
                                    'status' => 'active'
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
