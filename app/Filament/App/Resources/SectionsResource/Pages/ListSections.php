<?php

namespace App\Filament\App\Resources\SectionsResource\Pages;

use App\Filament\App\Resources\SectionsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSections extends ListRecords
{
    protected static string $resource = SectionsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
