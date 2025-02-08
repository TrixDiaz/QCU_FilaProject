<?php

namespace App\Filament\App\Widgets;

use App\Filament\App\Resources\EventResource;
use App\Models\Event;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Actions\DeleteAction;
use Saade\FilamentFullCalendar\Actions\EditAction;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CalendarWidget extends FullCalendarWidget
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
                    'color' => $event->color,
                    'start' => $event->starts_at,
                    'end' => $event->ends_at,
                    'url' => EventResource::getUrl(name: 'view', parameters: ['record' => $event]),
                    'shouldOpenUrlInNewTab' => true
                ]
            )
            ->all();
    }

    public function getFormSchema(): array
    {
        return [
            \Filament\Forms\Components\Grid::make()
                ->schema([
                    \Filament\Forms\Components\Section::make()
                        ->schema([
                            \Filament\Forms\Components\TextInput::make('title'),
                            \Filament\Forms\Components\ColorPicker::make('color'),
                            \Filament\Forms\Components\DateTimePicker::make('starts_at')->native(false),
                            \Filament\Forms\Components\DateTimePicker::make('ends_at')->native(false),
                        ])->columns(2),
                ]),
        ];
    }

    protected function modalActions(): array
    {
        return [
            EditAction::make()
                ->mountUsing(
                    function (Event $record, \Filament\Forms\Form $form, array $arguments) {
                        $form->fill([
                            'title' => $record->title,
                            'color' => $record->color,
                            'starts_at' => $arguments['event']['start'] ?? $record->starts_at,
                            'ends_at' => $arguments['event']['end'] ?? $record->ends_at
                        ]);
                    }
                ),
            DeleteAction::make(),
        ];
    }
}
