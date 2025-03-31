<?php

namespace App\Filament\App\Resources\AssetResource\Pages;

use App\Filament\App\Resources\AssetResource;
use App\Models\Category;
use Filament\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateAsset extends CreateRecord
{
    protected static string $resource = AssetResource::class;

    protected function getRedirectUrl(): string
    {
        return route('filament.app.pages.inventory');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            // Actions\Action::make('computerSet')
            //     ->label('Computer Set')
            //     ->color('success')
            //     ->icon('heroicon-o-computer-desktop')
            //     ->action(function (): void {
            //         // Check if Computer Set category exists, if not create it
            //         $category = Category::firstOrCreate(
            //             ['name' => 'Computer Set'],
            //             ['description' => 'Computer and related equipment', 'status' => 'active']
            //         );

            //         // Set the category in the form
            //         $this->form->fill([
            //             'category_id' => $category->id,
            //         ]);

            //         Notification::make()
            //             ->title('Category set to Computer Set')
            //             ->success()
            //             ->send();
            //     }),
        ];
    }
}
