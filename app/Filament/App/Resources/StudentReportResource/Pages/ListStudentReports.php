<?php

namespace App\Filament\App\Resources\StudentReportResource\Pages;

use App\Filament\App\Resources\StudentReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStudentReports extends ListRecords
{
    protected static string $resource = StudentReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
