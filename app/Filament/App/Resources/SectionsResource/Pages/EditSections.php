<?php

namespace App\Filament\App\Resources\SectionsResource\Pages;

use App\Filament\App\Resources\SectionsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSections extends EditRecord
{
    protected static string $resource = SectionsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
