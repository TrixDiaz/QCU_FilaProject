<?php

namespace App\Livewire;

use App\Filament\App\Resources\EventResource;
use App\Models\Event;
use App\Models\Section;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Actions\DeleteAction;
use Saade\FilamentFullCalendar\Actions\EditAction;
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
                            \Filament\Forms\Components\Select::make('professor_id')
                                ->label('Professor')
                                ->options(User::role('professor')->pluck('name', 'id'))
                                ->preload()
                                ->native(false),
                            \Filament\Forms\Components\Select::make('section_id')
                                ->label('Section')
                                ->options(Section::all()->pluck('name', 'id'))
                                ->preload()
                                ->native(false),
                            \Filament\Forms\Components\Select::make('subject_id')
                                ->label('Subject')
                                ->options(Subject::all()->pluck('name', 'id'))
                                ->preload()
                                ->native(false),
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
                            'professor_id' => $record->professor_id,
                            'section_id' => $record->section_id,
                            'subject_id' => $record->subject_id,
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
