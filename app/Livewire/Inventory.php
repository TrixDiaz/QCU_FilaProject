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
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Inventory extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;
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
        $this->classrooms = Building::find($buildingId)->classrooms;
        $this->assets = [];
    }
    public function loadAssets($classroomId)
    {
        $this->selectedClassroomId = $classroomId;
        $this->assets = AssetGroup::query()
            ->select([
                'assets_group.code',
                'assets_group.name',
                'assets_group.status',
                'assets_group.classroom_id',
                DB::raw('MIN(assets_group.id) as id'),
                DB::raw('GROUP_CONCAT(DISTINCT assets.name) as asset_list')
            ])
            ->leftJoin('assets', 'assets_group.asset_id', '=', 'assets.id')
            ->where('assets_group.classroom_id', $classroomId)
            ->groupBy(
                'assets_group.code',
                'assets_group.name',
                'assets_group.status',
                'assets_group.classroom_id'
            )
            ->get();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(\App\Models\Asset::query())
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('color')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('ends_at')
                    ->dateTime()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'reviewing' => 'Reviewing',
                        'published' => 'Published',
                    ])
            ], layout: \Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->actions([
                \Filament\Tables\Actions\ViewAction::make(),
                \Filament\Tables\Actions\EditAction::make(),
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
