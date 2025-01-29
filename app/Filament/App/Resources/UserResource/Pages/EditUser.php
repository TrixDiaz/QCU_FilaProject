<?php

namespace App\Filament\App\Resources\UserResource\Pages;

use App\Filament\App\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('verifyEmail')
                ->label('Verify Email')
                ->icon('heroicon-m-envelope')
                ->color('success')
                ->visible(fn(\App\Models\User $record) => $record->email_verified_at === null)
                ->action(function (\App\Models\User $record) {
                    $record->email_verified_at = now();
                    $record->save();

                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('Email Verified')
                        ->body('User email has been verified successfully.')
                        ->send();
                }),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getSavedNotification(): ?\Filament\Notifications\Notification
    {
        $user = $this->record;

        $auth = auth()->user();

        $notification = \Filament\Notifications\Notification::make()
            ->info()
            ->icon('heroicon-o-finger-print')
            ->title('Account Resource Modified')
            ->body("Account {$user->name} Modified by {$auth->name}!")
            ->actions([\Filament\Notifications\Actions\Action::make('View')->url(UserResource::getUrl('edit', ['record' => $user]))])
            ->sendToDatabase([$user,$auth]);

        return $notification;
    }
}
