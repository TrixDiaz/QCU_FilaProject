<?php

namespace App\Filament\App\Resources\SubjectResource\Pages;

use App\Filament\App\Resources\SubjectResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSubject extends CreateRecord
{
    protected static string $resource = SubjectResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('classroom')
                ->label('Create New Classroom')
                ->button()
                ->url(route('filament.app.resources.classrooms.create')),
            Actions\Action::make('classroom')
                ->label('Create New Sections')
                ->button()
                ->url(route('filament.app.resources.sections.create')),
        ];
    }
}
