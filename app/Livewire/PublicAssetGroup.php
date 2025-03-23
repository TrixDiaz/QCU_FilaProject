<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\AssetGroup;
use App\Models\Classroom;

class PublicAssetGroup extends Component
{
    public $classroomId;
    public $classroom;
    public $assetGroups;
    public $search = '';

    public function mount($classroomId)
    {
        $this->classroomId = $classroomId;
        $this->loadAssets();
    }

    public function loadAssets()
    {
        $this->classroom = Classroom::find($this->classroomId);

        if (!$this->classroom) {
            return;
        }

        $query = AssetGroup::where('classroom_id', $this->classroomId);

        // Apply search filter if search term exists
        if (!empty(trim($this->search))) {
            $searchTerm = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                    ->orWhere('code', 'like', $searchTerm)
                    ->orWhereHas('classroomAsset', function ($assetQuery) use ($searchTerm) {
                        $assetQuery->where('name', 'like', $searchTerm)
                            ->orWhere('asset_code', 'like', $searchTerm)
                            ->orWhere('serial_number', 'like', $searchTerm)
                            ->orWhereHas('brand', function ($brandQuery) use ($searchTerm) {
                                $brandQuery->where('name', 'like', $searchTerm);
                            })
                            ->orWhereHas('category', function ($categoryQuery) use ($searchTerm) {
                                $categoryQuery->where('name', 'like', $searchTerm);
                            });
                    });
            });
        }

        // Apply the eager loading AFTER the where conditions
        $query->with(['classroomAsset' => function ($query) {
            $query->with(['brand', 'category']);
        }]);

        $this->assetGroups = $query->get();
    }

    // This is the key method for handling search updates
    public function updatedSearch()
    {
        $this->loadAssets();
    }

    public function render()
    {
        // These queries should run AFTER loadAssets so they reflect current search state
        $assetGroupsCount = $this->assetGroups ? $this->assetGroups->count() : 0;

        // These counts should not be affected by search
        $activeAssetsCount = AssetGroup::where('classroom_id', $this->classroomId)
            ->where('status', 'active')
            ->count();

        $maintenanceAssetsCount = AssetGroup::where('classroom_id', $this->classroomId)
            ->where('status', 'maintenance')
            ->count();

        $brokenAssetsCount = AssetGroup::where('classroom_id', $this->classroomId)
            ->where('status', 'broken')
            ->count();

        $inactiveAssetsCount = AssetGroup::where('classroom_id', $this->classroomId)
            ->where('status', 'inactive')
            ->count();

        return view('livewire.public-asset-group', [
            'assetGroupsCount' => $assetGroupsCount,
            'activeAssetsCount' => $activeAssetsCount,
            'maintenanceAssetsCount' => $maintenanceAssetsCount,
            'brokenAssetsCount' => $brokenAssetsCount,
            'inactiveAssetsCount' => $inactiveAssetsCount,
        ])->layout('layouts.app');
    }
}
