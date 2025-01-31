<?php

namespace App\Filament\App\Resources\BrandsResource\Pages;

use App\Filament\App\Resources\BrandsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBrands extends EditRecord
{
    protected static string $resource = BrandsResource::class;

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
            ->icon('heroicon-o-rectangle-group')
            ->title('Brand Resource Modified')
            ->body("Brand {$record->name} Modified by {$auth->name}!")
            ->actions([\Filament\Notifications\Actions\Action::make('View')->url(BrandsResource::getUrl('edit', ['record' => $record]))])
            ->sendToDatabase([$auth]);

        return $notification;
    }
}
