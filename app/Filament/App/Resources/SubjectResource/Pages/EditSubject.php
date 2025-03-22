<?php

namespace App\Filament\App\Resources\SubjectResource\Pages;

use App\Filament\App\Resources\SubjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubject extends EditRecord
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
                ->label('View All Classroom')
                ->button()
                ->url(route('filament.app.resources.classrooms.index')),
            Actions\Action::make('sections')
                ->label('View All Sections')
                ->button()
                ->url(route('filament.app.resources.sections.index')),
            Actions\DeleteAction::make(),
        ];
    }
}
