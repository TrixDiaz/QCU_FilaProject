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
                \App\Models\Attendance::query()
                    ->whereHas('subject', function (Builder $query) {
                        $query->where('professor_id', auth()->id());
                    })
            )
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('student_full_name'),
                \Filament\Tables\Columns\TextColumn::make('terminal_number')
                    ->formatStateUsing(fn($state) => $state === '0' ? 'Phone Attendance' : $state),
                \Filament\Tables\Columns\TextColumn::make('student_email')
                    ->formatStateUsing(fn($state) => $state ?: 'null')
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('student_number'),
                \Filament\Tables\Columns\TextColumn::make('remarks')
                    ->formatStateUsing(fn($state) => $state ?? 'null')
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('subject.section.name'),
                \Filament\Tables\Columns\TextColumn::make('subject.name')
                    ->label('Subject'),
            ])
            ->headerActions([
                ExportBulkAction::make()->label('Export'),
            ])
            ->filters([
                SelectFilter::make('subject_id')
                    ->relationship(
                        'subject',
                        'name',
                        fn(Builder $query) => $query
                            ->where('professor_id', Auth::id())
                            ->select('id') // Add this line to select the id
                            ->selectRaw("CONCAT(name, ' (', semester, ' - ', school_year, ')') as name")
                    )
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->label('Subject'),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(1)
            ->emptyStateHeading('No Attendance yet');
    }
}
