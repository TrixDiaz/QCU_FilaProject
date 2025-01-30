<?php

namespace App\Filament\App\Resources\ClassroomResource\Pages;

use App\Filament\App\Resources\ClassroomResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClassroom extends EditRecord
{
    protected static string $resource = ClassroomResource::class;

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
            ->title('Classroom Resource Modified')
            ->body("Classroom {$record->name} Modified by {$auth->name}!")
            ->actions([\Filament\Notifications\Actions\Action::make('View')->url(ClassroomResource::getUrl('edit', ['record' => $record]))])
            ->sendToDatabase([$auth]);

        return $notification;
    }
}
