<?php

namespace App\Livewire;

use App\Models\Building;
use App\Models\Classroom;
use App\Models\AssetGroup;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Enums\FiltersLayout;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use AymanAlhattami\FilamentDateScopesFilter\DateScopeFilter;
use Carbon\Carbon;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;


class Inventories extends Component implements HasTable, HasForms
{
    use InteractsWithTable, InteractsWithForms;

    public $buildings;
    public $classrooms = [];
    public $assets = [];
    public $selectedBuildingId;
    public $selectedClassroomId;

    public function mount()
    {
        $this->buildings = Building::all();
    }

    public function loadClassrooms($buildingId)
    {
        $this->selectedBuildingId = $buildingId;
        $this->classrooms = Building::find($buildingId)?->classrooms ?? [];
        $this->assets = [];
    }

    public function loadAssets($classroomId)
    {
        $this->selectedClassroomId = $classroomId;
        $this->assets = AssetGroup::query()
            ->select([
                'asset_groups.code',
                'asset_groups.name',
                'asset_groups.status',
                'asset_groups.classroom_id',
                DB::raw('MIN(asset_groups.id) as id'),
                DB::raw('GROUP_CONCAT(DISTINCT assets.name) as asset_list')
            ])
            ->leftJoin('assets', 'asset_groups.asset_id', '=', 'assets.id')
            ->where('asset_groups.classroom_id', $classroomId)
            ->groupBy(
                'asset_groups.code',
                'asset_groups.name',
                'asset_groups.status',
                'asset_groups.classroom_id'
            )
            ->get();
    }

                public $excelFile;
            public $skipHeader = true;

            public function importExcel()
            {
                $this->validate([
                    'excelFile' => 'required|file|mimes:xlsx,xls,csv|max:5120',
                ]);
                
                // Process the Excel file (you'll need a package like maatwebsite/excel for this)
                // ...
                
                // Reset the form
                $this->reset(['excelFile', 'skipHeader']);
                
                // Show success message
                session()->flash('message', 'Assets imported successfully!');
            }   

    public static function table(Table $table): Table
    {
        return $table
            ->query(\App\Models\AssetGroup::query())
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('classroom.name')
                    ->label('Classroom')
                    ->default(fn($record): string => $record->classroom->name ?? 'No classroom')
                    ->description(fn($record): string => $record->classroom->building->name ?? 'No building')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('code')->searchable()->sortable(),
                \Filament\Tables\Columns\TextColumn::make('assets.name')
                    ->label('Asset')
                    ->searchable()
                    ->description(fn($record): string => $record->assets->serial_number ?? 'No serial'),
                \Filament\Tables\Columns\TextColumn::make('status')->searchable()->badge()->extraAttributes(['class' => 'capitalize']),
                \Filament\Tables\Columns\TextColumn::make('created_at')->date()->sortable()->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('updated_at')->date()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->persistSortInSession()
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('building_id')
                    ->label('Building')
                    ->options(fn() => Building::pluck('name', 'id')->toArray())
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['value'],
                            function ($query, $buildingId) {
                                // Find all classrooms in this building
                                $classroomIds = Classroom::where('building_id', $buildingId)->pluck('id')->toArray();
                                // Filter asset groups by these classroom IDs
                                return $query->whereIn('classroom_id', $classroomIds);
                            }
                        );
                    }),
                \Filament\Tables\Filters\SelectFilter::make('classroom_id')
                    ->label('Classroom')
                    ->options(fn() => Classroom::pluck('name', 'id')->toArray())
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['value'],
                            function ($query, $classroomId) {
                                return $query->where('classroom_id', $classroomId);
                            }
                        );
                    }),
                // \Filament\Tables\Filters\SelectFilter::make('status')->options([
                //     'active' => 'Active',
                //     'inactive' => 'Inactive',
                // ]),
                \Filament\Tables\Filters\Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\Select::make('date_filter')
                            ->options([
                                'all' => 'All',
                                'today' => 'Today',
                                'yesterday' => 'Yesterday',
                                'last_week' => 'Last 7 Days',
                                'last_two_weeks' => 'Last 2 Weeks',
                                'last_month' => 'Last 30 Days',
                                'last_three_months' => 'Last 3 Months',
                                'last_year' => 'Last Year',
                                'custom' => 'Custom Range',
                            ])
                            ->default('all'), // Default to 'all'
                        \Filament\Forms\Components\DatePicker::make('custom_date_from')
                            ->label('From')
                            ->visible(fn($get) => $get('date_filter') === 'custom'),
                        \Filament\Forms\Components\DatePicker::make('custom_date_to')
                            ->label('To')
                            ->visible(fn($get) => $get('date_filter') === 'custom'),
                    ])
                    ->query(function ($query, array $data) {
                        $filter = $data['date_filter'] ?? 'all'; // Default to 'all' if not specified
                        $customFrom = $data['custom_date_from'] ?? null;
                        $customTo = $data['custom_date_to'] ?? null;

                        return match ($filter) {
                            'all' => $query, // Return unmodified query to show all assets
                            'today' => $query->whereDate('created_at', Carbon::today()),
                            'yesterday' => $query->whereDate('created_at', Carbon::yesterday()),
                            'last_week' => $query->whereDate('created_at', '>=', Carbon::now()->subDays(7)),
                            'last_two_weeks' => $query->whereDate('created_at', '>=', Carbon::now()->subDays(14)),
                            'last_month' => $query->whereDate('created_at', '>=', Carbon::now()->subDays(30)),
                            'last_three_months' => $query->whereDate('created_at', '>=', Carbon::now()->subMonths(3)),
                            'last_year' => $query->whereDate('created_at', '>=', Carbon::now()->subYear()),
                            'custom' => $query
                                ->when(
                                    $customFrom,
                                    fn($query) => $query->whereDate('created_at', '>=', $customFrom)
                                )
                                ->when(
                                    $customTo,
                                    fn($query) => $query->whereDate('created_at', '<=', $customTo)
                                ),
                            default => $query // Default to showing all assets
                        };
                    })
                    ->indicateUsing(function (array $data): ?string {
                        $filter = $data['date_filter'] ?? null;

                        if (!$filter || $filter === 'all') {
                            return null; // No indicator for 'all'
                        }

                        if ($filter === 'custom') {
                            $from = $data['custom_date_from'] ?? null;
                            $to = $data['custom_date_to'] ?? null;

                            if ($from && $to) {
                                return "Created from $from to $to";
                            }

                            if ($from) {
                                return "Created from $from";
                            }

                            if ($to) {
                                return "Created until $to";
                            }
                        }

                        $labels = [
                            'today' => 'Created today',
                            'yesterday' => 'Created yesterday',
                            'last_week' => 'Created in the last 7 days',
                            'last_two_weeks' => 'Created in the last 2 weeks',
                            'last_month' => 'Created in the last 30 days',
                            'last_three_months' => 'Created in the last 3 months',
                            'last_year' => 'Created in the last year',
                        ];

                        return $labels[$filter] ?? null;
                    }),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    \Filament\Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withFilename(date('Y-m-d') . '-AssetGroups.xlsx'),
                        ExcelExport::make()
                            ->fromTable()
                            ->only([
                                'code',
                                'name',
                                'asset.name',
                                'classroom.name',
                                'classroom.building.name',
                                'status',
                                'created_at',
                                'updated_at',
                            ])
                            ->withFilename(date('Y-m-d') . '-Filtered-AssetGroups.xlsx'),
                    ]),
                ]),
            ]);
    }

    public function render()
    {
        return view('livewire.inventories');
    }
}
