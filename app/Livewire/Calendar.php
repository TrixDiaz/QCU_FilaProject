<?php

namespace App\Livewire;


use App\Services\CalendarForm;
use App\Models\Event;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Actions;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class Calendar extends FullCalendarWidget
{
    public Model|string|null $model = Event::class;

    public function fetchEvents(array $fetchInfo): array
    {
        return Event::query()
            ->where('starts_at', '>=', $fetchInfo['start'])
            ->where('ends_at', '<=', $fetchInfo['end'])
            ->get()
            ->map(
                fn(Event $event) => [
                    'id' => $event->id,
                    'title' => $event->title,
                    'professor_id' => $event->professor_id,
                    'section_id' => $event->section_id,
                    'subject_id' => $event->subject_id,
                    'color' => $event->color,
                    'start' => $event->starts_at,
                    'end' => $event->ends_at,
                    'openModalAction' => true,
                ]
            )
            ->all();
    }

    protected function getHeaderActions(): array
    {
        return [
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->color("primary"),
           Actions\CreateAction::make(),
        ];
    }

    protected function modalActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }



    public function getFormSchema(): array
    {
        return [
            \Filament\Forms\Components\Grid::make()
                ->schema(CalendarForm::schema()),
        ];
    }

}
