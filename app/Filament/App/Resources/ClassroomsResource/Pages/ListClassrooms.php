<?php

namespace App\Filament\App\Resources\ClassroomsResource\Pages;

use App\Filament\App\Resources\ClassroomsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClassrooms extends ListRecords
{
    protected static string $resource = ClassroomsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
