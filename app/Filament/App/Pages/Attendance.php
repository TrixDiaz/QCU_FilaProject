<?php

namespace App\Filament\App\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;


class Attendance extends Page implements HasForms, HasTable
{
    use HasPageShield;

    public static function getNavigationBadge(): ?string
    {
        return \App\Models\Attendance::count();
    }

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.app.pages.attendance';

    protected static ?string $navigationGroup = 'School';

    protected static ?string $navigationLabel = 'Attendances';

    use InteractsWithForms;
    use InteractsWithTable;
    use HasPageShield;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                function (Builder $query) {
                    // Show all attendance records for this professor's subjects
                    return \App\Models\Attendance::query()
                        ->whereHas('subject', function (Builder $subQuery) {
                            $subQuery->where('professor_id', auth()->id());
                        });
                }
            )
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('student_full_name'),
                \Filament\Tables\Columns\TextColumn::make('terminal_number')
                    ->formatStateUsing(fn($state) => $state === '0' ? 'Phone Attendance' : $state),
                \Filament\Tables\Columns\TextColumn::make('student_email')
                    ->formatStateUsing(fn($state) => $state ?: 'null')
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('student_number'),

                \Filament\Tables\Columns\TextColumn::make('subject.section.name'),
                \Filament\Tables\Columns\TextColumn::make('subject.name')
                    ->label('Subject'),
                \Filament\Tables\Columns\TextColumn::make('peripherals')
                    ->formatStateUsing(function ($state) {
                        if (empty($state)) {
                            return 'None';
                        }

                        // Convert JSON string to array if needed
                        if (is_string($state)) {
                            $state = json_decode($state, true);
                        }

                        // Handle objects/associative arrays with boolean values
                        if (is_array($state) && !empty($state)) {
                            $peripheralStatus = [];

                            foreach ($state as $peripheral => $isWorking) {
                                // Capitalize the first letter for better display
                                $peripheralName = ucfirst($peripheral);

                                // Show status as Working/Not Working
                                $status = $isWorking === true ? 'Working' : 'Not Working';
                                $peripheralStatus[] = "$peripheralName: $status";
                            }

                            if (empty($peripheralStatus)) {
                                return 'No peripherals data';
                            }

                            return implode(', ', $peripheralStatus);
                        }

                        return (string) $state;
                    })
                    ->wrap()
                    ->toggleable()
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('remarks')
                    ->formatStateUsing(fn($state) => $state ?? 'null')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                ExportBulkAction::make()->exports([
                    ExcelExport::make()
                        ->fromTable()
                        ->except(["id",])
                        ->withFilename(date('Y-m-d') . '-Buildings.xlsx'),
                    ExcelExport::make()
                        ->fromTable()
                        ->only([
                            'subject_id',
                            'terminal_number',
                            'student_full_name',
                            'student_number',
                            'student_email',
                            'periperals',
                            'remarks',
                            'created_at',
                            'updated_at',
                        ])
                        ->withFilename(date('Y-m-d') . '-Filtered-Assets.xlsx'),
                ])


            ])
            ->filters([
                SelectFilter::make('subject_id')
                    ->relationship(
                        'subject',
                        'name',
                        fn(Builder $query) => $query
                            ->where('professor_id', Auth::id())
                            ->select('id')
                            ->selectRaw("CONCAT(name, ' (', semester, ' - ', school_year, ')') as name")
                    )
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->label('Subject'),

                SelectFilter::make('school_year')
                    ->options(function () {
                        return \App\Models\Subject::where('professor_id', Auth::id())
                            ->distinct()
                            ->pluck('school_year', 'school_year')
                            ->toArray();
                    })
                    ->query(function (Builder $query, array $data) {
                        if (empty($data['value'])) {
                            return;
                        }

                        $query->whereHas('subject', function (Builder $query) use ($data) {
                            $query->where('school_year', $data['value']);
                        });
                    })
                    ->label('School Year'),

                SelectFilter::make('semester')
                    ->options(function () {
                        return \App\Models\Subject::where('professor_id', Auth::id())
                            ->distinct()
                            ->pluck('semester', 'semester')
                            ->toArray();
                    })
                    ->query(function (Builder $query, array $data) {
                        if (empty($data['value'])) {
                            return;
                        }

                        $query->whereHas('subject', function (Builder $query) use ($data) {
                            $query->where('semester', $data['value']);
                        });
                    })
                    ->label('Semester'),

                \Filament\Tables\Filters\Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label('From Date')
                            ->placeholder('From'),
                        \Filament\Forms\Components\DatePicker::make('until')
                            ->label('Until Date')
                            ->placeholder('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators['from'] = 'Created from ' . \Carbon\Carbon::parse($data['from'])->toFormattedDateString();
                        }

                        if ($data['until'] ?? null) {
                            $indicators['until'] = 'Created until ' . \Carbon\Carbon::parse($data['until'])->toFormattedDateString();
                        }

                        return $indicators;
                    })
                    ->label('Attendance Date'),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(2)
            ->emptyStateHeading('No attendance records found')
            ->emptyStateDescription('There are no attendance records for today. Use filters to view other dates.')
            ->emptyStateIcon('heroicon-o-calendar');
    }
}
