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

        $this->assetGroups = AssetGroup::where('classroom_id', $this->classroomId)
            ->with(['classroomAsset', 'classroomAsset.brand', 'classroomAsset.category'])
            ->get();
    }

    public function render()
    {
        return view('livewire.public-asset-group')
            ->layout('layouts.app');
    }
}
