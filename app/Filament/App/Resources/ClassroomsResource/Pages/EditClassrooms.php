<?php

namespace App\Filament\App\Resources\ClassroomsResource\Pages;

use App\Filament\App\Resources\ClassroomsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClassrooms extends EditRecord
{
    protected static string $resource = ClassroomsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
