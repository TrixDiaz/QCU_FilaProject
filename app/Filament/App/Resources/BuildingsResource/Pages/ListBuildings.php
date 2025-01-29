<?php

namespace App\Filament\App\Resources\BuildingsResource\Pages;

use App\Filament\App\Resources\BuildingsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBuildings extends ListRecords
{
    protected static string $resource = BuildingsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
