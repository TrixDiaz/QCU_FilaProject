<?php

namespace App\Livewire;

use App\Models\Building;
use App\Models\Classroom;
use App\Models\AssetGroup;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Inventory extends Component
{
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

    public function render()
    {
        return view('livewire.inventory');
    }
}
