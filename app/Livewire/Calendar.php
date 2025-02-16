<?php

namespace App\Livewire;

use App\Services\CalendarForm;
use App\Models\Event;
use App\Models\Section;
use App\Models\Subject;
use App\Models\User;
use App\Models\Building;
use App\Models\Classroom;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
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
        return [
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
                        ->helperText('Excel file should contain: Title, Professor, Section, Subject, Color, Start Date/Time, End Date/Time and should be .xls, .xlsx, or .csv'),
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
                            'color' => array_search('color', $headers),
                            'starts_at' => array_search('starts_at', $headers),
                            'ends_at' => array_search('ends_at', $headers),
                        ];

                        if (in_array(false, $columnIndexes, true)) {
                            throw new \Exception('One or more required columns are missing in the uploaded file.');
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
                                $color = trim($row[$columnIndexes['color']]);
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
                                        'is_active' => true
                                    ]
                                );

                                // Find or create a default classroom if it doesn't exist
                                $classroom = Classroom::firstOrCreate(
                                    ['name' => 'Default Classroom'],
                                    [
                                        'building_id' => $building->id,
                                        'name' => 'Default Classroom',
                                        'slug' => str('Default Classroom')->slug(),
                                        'is_active' => true
                                    ]
                                );

                                // Find or create section with slug and classroom_id
                                $section = Section::firstOrCreate(
                                    ['name' => $sectionName],
                                    [
                                        'classroom_id' => $classroom->id,
                                        'slug' => str($sectionName)->slug(),
                                        'is_active' => true
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
                                    $parsedStartsAt = Carbon::createFromFormat('d/m/Y H:i', $startsAt);
                                    $parsedEndsAt = Carbon::createFromFormat('d/m/Y H:i', $endsAt);
                                } catch (\Exception $e) {
                                    throw new \Exception("Invalid date format. Expected dd/mm/yyyy HH:mm for dates in row " . ($index + 2));
                                }

                                // Validate that end date is after start date
                                if ($parsedEndsAt->lte($parsedStartsAt)) {
                                    throw new \Exception("End date must be after start date in row " . ($index + 2));
                                }

                                Event::create([
                                    'title' => $title,
                                    'professor_id' => $professor->id,
                                    'section_id' => $section->id,
                                    'subject_id' => $subject->id,
                                    'color' => $color,
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
