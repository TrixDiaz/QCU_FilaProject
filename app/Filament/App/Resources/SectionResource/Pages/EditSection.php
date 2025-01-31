<?php

namespace App\Filament\App\Resources\SectionResource\Pages;

use App\Filament\App\Resources\SectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSection extends EditRecord
{
    protected static string $resource = SectionResource::class;

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
            ->icon('heroicon-o-finger-print')
            ->title('Section Resource Modified')
            ->body("Section {$record->name} Modified by {$auth->name}!")
            ->actions([\Filament\Notifications\Actions\Action::make('View')->url(SectionResource::getUrl('edit', ['record' => $record]))])
            ->sendToDatabase([$auth]);

        return $notification;
    }
}
