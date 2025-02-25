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

class Inventory extends Component implements HasTable, HasForms
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

    public static function table(Table $table): Table
    {
        return $table
            ->query(\App\Models\Asset::query())
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                \Filament\Tables\Columns\TextColumn::make('category.name')->searchable()->sortable(),
                \Filament\Tables\Columns\TextColumn::make('brand.name')->searchable()->sortable(),
                \Filament\Tables\Columns\TextColumn::make('serial_number')->searchable()->sortable(),
                \Filament\Tables\Columns\TextColumn::make('asset_code')->searchable()->sortable(),
                \Filament\Tables\Columns\TextColumn::make('expiry_date')
                    ->date()
                    ->placeholder('Not Available') // Show "Not Available" for null values
                    ->sortable(), 
                \Filament\Tables\Columns\TextColumn::make('status')->searchable()->sortable(),
                \Filament\Tables\Columns\TextColumn::make('created_at')->date()->sortable(),
                \Filament\Tables\Columns\TextColumn::make('updated_at')->date()->sortable(),
            ])
            ->defaultSort('created_at', 'desc') // Sort by created_at in descending order
            ->persistSortInSession() // Persist sorting between requests
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('building_id')
                    ->label('Building')
                    ->options(fn () => Building::pluck('name', 'id')->toArray())
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['value'],
                            function ($query, $buildingId) {
                                // Find all classrooms in this building
                                $classroomIds = Classroom::where('building_id', $buildingId)->pluck('id')->toArray();
                                
                                // Find all asset groups in these classrooms
                                $assetIds = AssetGroup::whereIn('classroom_id', $classroomIds)
                                    ->pluck('asset_id')
                                    ->toArray();
                                
                                // Filter assets by these IDs
                                return $query->whereIn('id', $assetIds);
                            }
                        );
                    }),
                \Filament\Tables\Filters\SelectFilter::make('classroom_id')
                    ->label('Classroom')
                    ->options(fn () => Classroom::pluck('name', 'id')->toArray())
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['value'],
                            function ($query, $classroomId) {
                                // Find all asset groups in this classroom
                                $assetIds = AssetGroup::where('classroom_id', $classroomId)
                                    ->pluck('asset_id')
                                    ->toArray();
                                
                                // Filter assets by these IDs
                                return $query->whereIn('id', $assetIds);
                            }
                        );
                    }),
                \Filament\Tables\Filters\SelectFilter::make('status')->options([
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                ]),
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
                            ->visible(fn ($get) => $get('date_filter') === 'custom'),
                        \Filament\Forms\Components\DatePicker::make('custom_date_to')
                            ->label('To')
                            ->visible(fn ($get) => $get('date_filter') === 'custom'),
                    ])
                    ->query(function ($query, array $data) {
                        $filter = $data['date_filter'] ?? 'all'; // Default to 'all' if not specified
                        $customFrom = $data['custom_date_from'] ?? null;
                        $customTo = $data['custom_date_to'] ?? null;
                        
                        return match($filter) {
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
                                    fn ($query) => $query->whereDate('created_at', '>=', $customFrom)
                                )
                                ->when(
                                    $customTo,
                                    fn ($query) => $query->whereDate('created_at', '<=', $customTo)
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
            ->filtersLayout(FiltersLayout::AboveContent) // Using the enum for above content
            ->actions([
                \Filament\Tables\Actions\ActionGroup::make([
                    \Filament\Tables\Actions\ViewAction::make(),
                    \Filament\Tables\Actions\EditAction::make(),
                    \Filament\Tables\Actions\Action::make('archive')
                        ->label('Archive')
                        ->icon('heroicon-o-archive-box')
                        ->color('danger')
                        ->action(fn (\App\Models\Asset $record) => $record->update(['status' => 'archived']))
                        ->requiresConfirmation(),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->label('')
                ->tooltip('Actions')
                ->size('sm')
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    \Filament\Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function render()
    {
        return view('livewire.inventory');
    }
}