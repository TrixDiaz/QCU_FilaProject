<?php

namespace App\Filament\App\Resources\BuildingResource\Pages;

use App\Filament\App\Resources\BuildingResource;
use App\Models\Building;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBuilding extends EditRecord
{
    protected static string $resource = BuildingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getSavedNotification(): ?\Filament\Notifications\Notification
    {
        $record = $this->record;

        $auth = auth()->user();

        $notification = \Filament\Notifications\Notification::make()
            ->info()
            ->icon('heroicon-o-building-office')
            ->title('Building Resource Modified')
            ->body("Building {$record->name} Modified by {$auth->name}!")
            ->actions([\Filament\Notifications\Actions\Action::make('View')->url(BuildingResource::getUrl('edit', ['record' => $record]))])
            ->sendToDatabase([$auth]);

        return $notification;
    }
}
