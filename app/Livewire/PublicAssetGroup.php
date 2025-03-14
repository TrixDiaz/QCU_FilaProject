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
    public $search = ''; // Add search property

    public function mount($classroomId)
    {
        $this->classroomId = $classroomId;
        $this->loadAssets();
    }

    public function loadAssets()
    {
        $this->classroom = Classroom::find($this->classroomId);

        if (!$this->classroom) {
            return redirect()->route('welcome')->with('error', 'Classroom not found');
        }

        $query = AssetGroup::where('classroom_id', $this->classroomId);

        // Apply search filter if search term exists
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('code', 'like', '%' . $this->search . '%')
                    ->orWhereHas('assets', function ($assetQuery) {
                        $assetQuery->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('code', 'like', '%' . $this->search . '%');
                    });
            });
        }

        $this->assetGroups = $query->with(['assets' => function ($query) {
            $query->whereIn('status', ['active', 'inactive']);
        }, 'assets.brand', 'assets.category'])
            ->get();
    }

    // Add method to update search when typing
    public function updatedSearch()
    {
        $this->loadAssets();
    }

    public function render()
    {
        $assetGroupsCount = $this->assetGroups->count();
        $activeAssetsCount = \App\Models\Asset::where('status', 'active')->count();
        $inactiveAssetsCount = \App\Models\Asset::where('status', 'inactive')->count();

        return view('livewire.public-asset-group', [
            'assetGroupsCount' => $assetGroupsCount,
            'activeAssetsCount' => $activeAssetsCount,
            'inactiveAssetsCount' => $inactiveAssetsCount,
        ])->layout('layouts.app');
    }
}
