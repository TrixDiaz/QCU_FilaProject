<?php

namespace App\Filament\App\Resources\BrandsResource\Pages;

use App\Filament\App\Resources\BrandsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBrands extends EditRecord
{
    protected static string $resource = BrandsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
