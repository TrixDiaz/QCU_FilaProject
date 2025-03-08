<?php

namespace App\Livewire;

use App\Services\CalendarForm;
use App\Models\Event;
use App\Models\Section;
use App\Models\Subject;
use App\Models\User;
use App\Models\Building;
use App\Models\Classroom;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
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

    protected function headerActions(): array
    {
        if (auth()->user()->hasRole(['super_admin', 'admin'])) {
            return [
                \Filament\Actions\Action::make('Download Template')
                    ->label('Download Template')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        return response()->download(
                            public_path('excel-templates/Schedule-Template.xlsx'),
                            'Schedule-Template.xlsx'
                        );
                    }),
                \Filament\Actions\Action::make('import')
                    ->label('Import Excel')
                    ->color('secondary')
                    ->form([
                        FileUpload::make('excel_file')
                            ->label('Excel File')
                            ->acceptedFileTypes([
                                'application/vnd.ms-excel', // .xls
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
                                'text/csv', // CSV
                            ])
                            ->required()
                            ->helperText('Excel file should contain: Title, Professor, Section, Subject, Start Date/Time, End Date/Time and should be .xls, .xlsx, or .csv'),
                    ])
                    ->action(function (array $data): void {
                        $file = storage_path('app/public/' . $data['excel_file']);

                        try {
                            $spreadsheet = IOFactory::load($file);
                            $worksheet = $spreadsheet->getActiveSheet();
                            $rows = $worksheet->toArray();

                            if (count($rows) < 2) {
                                throw new \Exception('The uploaded file does not contain enough data.');
                            }

                            // Identify column indexes dynamically
                            $headers = array_map('strtolower', array_map('trim', $rows[0]));

                            $columnIndexes = [
                                'title' => array_search('title', $headers),
                                'professor' => array_search('professor', $headers),
                                'section' => array_search('section', $headers),
                                'subject' => array_search('subject', $headers),
                                'starts_at' => array_search('starts at', $headers) !== false ? array_search('starts at', $headers) : array_search('starts_at', $headers),
                                'ends_at' => array_search('ends at', $headers) !== false ? array_search('ends at', $headers) : array_search('ends_at', $headers),
                            ];

                            // Check if required columns are present
                            $missingColumns = [];
                            foreach ($columnIndexes as $key => $index) {
                                if ($index === false) {
                                    $missingColumns[] = $key;
                                }
                            }

                            if (!empty($missingColumns)) {
                                throw new \Exception('Required columns are missing in the uploaded file: ' . implode(', ', $missingColumns));
                            }

                            // Remove header row
                            array_shift($rows);

                            $successful = 0;
                            $failed = 0;
                            $errors = [];

                            foreach ($rows as $index => $row) {
                                try {
                                    $title = trim($row[$columnIndexes['title']]);
                                    $professorName = trim($row[$columnIndexes['professor']]);
                                    $sectionName = trim($row[$columnIndexes['section']]);
                                    $subjectName = trim($row[$columnIndexes['subject']]);
                                    $startsAt = trim($row[$columnIndexes['starts_at']]);
                                    $endsAt = trim($row[$columnIndexes['ends_at']]);

                                    // Validate required fields first
                                    if (!$title || !$professorName || !$sectionName || !$subjectName || !$startsAt || !$endsAt) {
                                        throw new \Exception("Missing required fields in row " . ($index + 2));
                                    }

                                    // Find or create professor
                                    $professor = User::firstOrCreate(
                                        ['email' => strtolower(str_replace(' ', '.', $professorName)) . '@example.com'],
                                        [
                                            'name' => $professorName,
                                            'password' => bcrypt('password123'),
                                            'email_verified_at' => now()
                                        ]
                                    );

                                    if (!$professor->hasRole('professor')) {
                                        $professor->assignRole('professor');
                                    }

                                    // Find or create a default building if it doesn't exist
                                    $building = Building::firstOrCreate(
                                        ['name' => 'Main Building'],
                                        [
                                            'name' => 'Main Building',
                                            'slug' => str('Main Building')->slug(),
                                        ]
                                    );

                                    // Find or create a default classroom if it doesn't exist
                                    $classroom = Classroom::firstOrCreate(
                                        ['name' => 'Default Classroom'],
                                        [
                                            'building_id' => $building->id,
                                            'name' => 'Default Classroom',
                                            'slug' => str('Default Classroom')->slug(),
                                        ]
                                    );

                                    // Find or create section with slug and classroom_id
                                    $section = Section::firstOrCreate(
                                        ['name' => $sectionName],
                                        [
                                            'classroom_id' => $classroom->id,
                                            'slug' => str($sectionName)->slug(),
                                        ]
                                    );

                                    // Find or create subject with required fields
                                    $subject = Subject::firstOrCreate(
                                        ['name' => $subjectName],
                                        [
                                            'name' => $subjectName,
                                            'subject_code' => strtoupper(substr(str($subjectName)->slug(), 0, 6)),
                                            'subject_units' => 3, // default value
                                            'lab_time' => '00:00:00',
                                            'lecture_time' => '00:00:00'
                                        ]
                                    );

                                    // Parse dates with error handling
                                    try {
                                        $parsedStartsAt = Carbon::createFromFormat('m/d/Y H:i', $startsAt);
                                        $parsedEndsAt = Carbon::createFromFormat('m/d/Y H:i', $endsAt);
                                    } catch (\Exception $e) {
                                        throw new \Exception("Invalid date format. Expected dd/mm/yyyy HH:mm for dates in row " . ($index + 2));
                                    }

                                    // Validate that end date is after start date
                                    if ($parsedEndsAt->lte($parsedStartsAt)) {
                                        throw new \Exception("End date must be after start date in row " . ($index + 2));
                                    }

                                    // Generate a random color since the color column was deleted
                                    $randomColor = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);

                                    Event::create([
                                        'title' => $title,
                                        'professor_id' => $professor->id,
                                        'section_id' => $section->id,
                                        'subject_id' => $subject->id,
                                        'color' => $randomColor,
                                        'starts_at' => $parsedStartsAt,
                                        'ends_at' => $parsedEndsAt,
                                    ]);

                                    $successful++;
                                } catch (\Exception $e) {
                                    $failed++;
                                    $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                                }
                            }

                            // Show import results
                            Notification::make()
                                ->title('Import completed')
                                ->body("Successfully imported {$successful} events. Failed: {$failed}")
                                ->success()
                                ->send();

                            if (count($errors) > 0) {
                                Notification::make()
                                    ->title('Import errors')
                                    ->body(implode("\n", array_slice($errors, 0, 5)) .
                                        (count($errors) > 5 ? "\n...and " . (count($errors) - 5) . " more errors" : ""))
                                    ->danger()
                                    ->send();
                            }

                            // Refresh the calendar
                            $this->dispatch('filament-full-calendar::refresh');
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Import failed')
                                ->body('Error: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }

                        // Clean up uploaded file
                        unlink($file);
                    }),
                \Saade\FilamentFullCalendar\Actions\CreateAction::make()
                    ->label('Create Schedule')
                    ->mountUsing(
                        function (\Filament\Forms\Form $form, array $arguments) {
                            $form->fill([
                                'starts_at' => $arguments['start'] ?? null,
                                'ends_at' => $arguments['end'] ?? null,
                                'color' => '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT)
                            ]);
                        }
                    )
                    ->action(function (array $data): void {
                        // Parse dates for comparison
                        $newStart = Carbon::parse($data['starts_at']);
                        $newEnd = Carbon::parse($data['ends_at']);

                        // First check for exact time matches
                        $exactMatch = Event::query()
                            ->where('section_id', $data['section_id'])
                            ->where('starts_at', $newStart)
                            ->where('ends_at', $newEnd)
                            ->first();

                        if ($exactMatch) {
                            $conflictStart = $newStart->format('M d, Y g:i A');
                            $conflictEnd = $newEnd->format('g:i A');

                            Notification::make()
                                ->danger()
                                ->title('Exact Time Conflict')
                                ->body("An event already exists at exactly the same time: {$exactMatch->title} ({$conflictStart} - {$conflictEnd})")
                                ->persistent()
                                ->send();

                            return; // Stop here - don't check for other conflicts
                        }

                        // Then check for overlapping times
                        $conflicts = Event::query()
                            ->where('section_id', $data['section_id'])
                            ->where(function ($query) use ($newStart, $newEnd) {
                                $query->where(function ($q) use ($newStart, $newEnd) {
                                    $q->where('starts_at', '<=', $newStart)
                                        ->where('ends_at', '>', $newStart);
                                })->orWhere(function ($q) use ($newStart, $newEnd) {
                                    $q->where('starts_at', '<', $newEnd)
                                        ->where('ends_at', '>=', $newEnd);
                                })->orWhere(function ($q) use ($newStart, $newEnd) {
                                    $q->where('starts_at', '>=', $newStart)
                                        ->where('ends_at', '<=', $newEnd);
                                });
                            })
                            ->first();

                        if ($conflicts) {
                            $conflictStart = Carbon::parse($conflicts->starts_at)->format('M d, Y g:i A');
                            $conflictEnd = Carbon::parse($conflicts->ends_at)->format('g:i A');

                            Notification::make()
                                ->warning()
                                ->title('Schedule Conflict Detected')
                                ->body("This time slot conflicts with an existing schedule: {$conflicts->title} ({$conflictStart} - {$conflictEnd})")
                                ->persistent()
                                ->send();

                            return; // Don't proceed with creation
                        }

                        // Only create if there are no conflicts
                        Event::create($data);
                        $this->dispatch('filament-full-calendar::refresh');

                        Notification::make()
                            ->success()
                            ->title('Schedule Created')
                            ->body('New schedule has been successfully created.')
                            ->send();
                    }),
            ];
        } else {
            return [];
        }
    }
    protected function modalActions(): array
    {
        return [
            Actions\EditAction::make()
                ->mountUsing(
                    function (Event $record, \Filament\Forms\Form $form, array $arguments) {
                        if (isset($arguments['event']['start'])) {
                            $newStart = Carbon::parse($arguments['event']['start']);
                            $newEnd = Carbon::parse($arguments['event']['end']);

                            // Check for exact time matches first
                            $exactMatch = Event::query()
                                ->where('section_id', $record->section_id)
                                ->where('id', '!=', $record->id)
                                ->where('starts_at', $newStart)
                                ->where('ends_at', $newEnd)
                                ->first();

                            if ($exactMatch) {
                                $conflictStart = $newStart->format('M d, Y g:i A');
                                $conflictEnd = $newEnd->format('g:i A');

                                Notification::make()
                                    ->danger()
                                    ->title('Exact Time Conflict')
                                    ->body("An event already exists at exactly the same time: {$exactMatch->title} ({$conflictStart} - {$conflictEnd})")
                                    ->persistent()
                                    ->send();

                                // Reset to original values
                                $form->fill([
                                    'starts_at' => $record->starts_at,
                                    'ends_at' => $record->ends_at,
                                ]);
                                return;
                            }

                            // Then check for overlapping times
                            $conflicts = Event::query()
                                ->where('section_id', $record->section_id)
                                ->where('id', '!=', $record->id)
                                ->where(function ($query) use ($newStart, $newEnd) {
                                    $query->where(function ($q) use ($newStart, $newEnd) {
                                        $q->where('starts_at', '<=', $newStart)
                                            ->where('ends_at', '>', $newStart);
                                    })->orWhere(function ($q) use ($newStart, $newEnd) {
                                        $q->where('starts_at', '<', $newEnd)
                                            ->where('ends_at', '>=', $newEnd);
                                    })->orWhere(function ($q) use ($newStart, $newEnd) {
                                        $q->where('starts_at', '>=', $newStart)
                                            ->where('ends_at', '<=', $newEnd);
                                    });
                                })
                                ->first();

                            if ($conflicts) {
                                $conflictStart = Carbon::parse($conflicts->starts_at)->format('M d, Y g:i A');
                                $conflictEnd = Carbon::parse($conflicts->ends_at)->format('g:i A');

                                Notification::make()
                                    ->warning()
                                    ->title('Schedule Conflict Detected')
                                    ->body("This time slot conflicts with an existing schedule: {$conflicts->title} ({$conflictStart} - {$conflictEnd})")
                                    ->persistent()
                                    ->send();

                                // Reset to original values
                                $form->fill([
                                    'starts_at' => $record->starts_at,
                                    'ends_at' => $record->ends_at,
                                ]);
                                return;
                            }
                        }

                        // Fill the form with new values
                        $form->fill([
                            'title' => $record->title,
                            'starts_at' => $arguments['event']['start'] ?? $record->starts_at,
                            'ends_at' => $arguments['event']['end'] ?? $record->ends_at,
                            'professor_id' => $record->professor_id,
                            'section_id' => $record->section_id,
                            'subject_id' => $record->subject_id,
                            'color' => $record->color,
                        ]);
                    }
                )
                ->action(function (Event $record, array $data): void {
                    $newStart = Carbon::parse($data['starts_at']);
                    $newEnd = Carbon::parse($data['ends_at']);

                    // Check for exact time matches first
                    $exactMatch = Event::query()
                        ->where('section_id', $data['section_id'])
                        ->where('id', '!=', $record->id)
                        ->where('starts_at', $newStart)
                        ->where('ends_at', $newEnd)
                        ->first();

                    if ($exactMatch) {
                        $conflictStart = $newStart->format('M d, Y g:i A');
                        $conflictEnd = $newEnd->format('g:i A');

                        Notification::make()
                            ->danger()
                            ->title('Exact Time Conflict')
                            ->body("An event already exists at exactly the same time: {$exactMatch->title} ({$conflictStart} - {$conflictEnd})")
                            ->persistent()
                            ->send();

                        return; // Don't proceed with update
                    }

                    // Then check for overlapping times
                    $conflicts = Event::query()
                        ->where('section_id', $data['section_id'])
                        ->where('id', '!=', $record->id)
                        ->where(function ($query) use ($newStart, $newEnd) {
                            $query->where(function ($q) use ($newStart, $newEnd) {
                                $q->where('starts_at', '<=', $newStart)
                                    ->where('ends_at', '>', $newStart);
                            })->orWhere(function ($q) use ($newStart, $newEnd) {
                                $q->where('starts_at', '<', $newEnd)
                                    ->where('ends_at', '>=', $newEnd);
                            })->orWhere(function ($q) use ($newStart, $newEnd) {
                                $q->where('starts_at', '>=', $newStart)
                                    ->where('ends_at', '<=', $newEnd);
                            });
                        })
                        ->first();

                    if ($conflicts) {
                        $conflictStart = Carbon::parse($conflicts->starts_at)->format('M d, Y g:i A');
                        $conflictEnd = Carbon::parse($conflicts->ends_at)->format('g:i A');

                        Notification::make()
                            ->warning()
                            ->title('Schedule Conflict Detected')
                            ->body("This time slot conflicts with an existing schedule: {$conflicts->title} ({$conflictStart} - {$conflictEnd})")
                            ->persistent()
                            ->send();

                        return; // Don't proceed with update
                    }

                    // Only update if there are no conflicts
                    $record->update($data);
                    $this->dispatch('filament-full-calendar::refresh');

                    Notification::make()
                        ->success()
                        ->title('Schedule Updated')
                        ->body('Schedule has been successfully updated.')
                        ->send();
                }),
            Actions\DeleteAction::make(),
        ];
    }

    public function getFormSchema(): array
    {
        return [
            \Filament\Forms\Components\Grid::make()
                ->schema(CalendarForm::schema())
        ];
    }

    public function eventDidMount(): string
    {
        return <<<JS
        function({ event, timeText, isStart, isEnd, isMirror, isPast, isFuture, isToday, el, view }){
            el.setAttribute("x-tooltip", "tooltip");
            el.setAttribute("x-data", "{ tooltip: '"+event.title+"' }");
        }
    JS;
    }
}
