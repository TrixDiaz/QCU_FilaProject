<?php

namespace App\Filament\App\Resources\CategoryResource\Pages;

use App\Filament\App\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

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
            ->title('Category Resource Modified')
            ->body("Category {$record->name} Modified by {$auth->name}!")
            ->actions([\Filament\Notifications\Actions\Action::make('View')->url(CategoryResource::getUrl('edit', ['record' => $record]))])
            ->sendToDatabase([$auth]);

        return $notification;
    }
}
