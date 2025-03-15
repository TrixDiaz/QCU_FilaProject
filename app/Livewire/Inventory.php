<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Asset;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Tag;

class Inventory extends Component
{
    public $filterType = 'all';
    public $filterValue = '';
    public $filterBrand = '';
    public $filterCategory = '';
    public $filterTag = '';
    public $brands = [];
    public $categories = [];
    public $tags = [];
    public $totalAssets = 0;
    public $filteredCount = 0;

    public function mount()
    {
        $this->brands = Brand::withCount('assets')->get();
        $this->categories = Category::withCount('assets')->get();
        $this->tags = Tag::withCount('assets')->get();
        $this->totalAssets = Asset::count();
    }

    public function render()
    {
        $assets = Asset::with(['brand', 'category', 'assetTags']);

        if ($this->filterType === 'brand' && $this->filterValue) {
            $assets->where('brand_id', $this->filterValue);
        } elseif ($this->filterType === 'category' && $this->filterValue) {
            $assets->where('category_id', $this->filterValue);
        } elseif ($this->filterType === 'tag' && $this->filterValue) {
            $assets->whereHas('assetTags', function ($query) {
                $query->where('tags.id', $this->filterValue);
            });
        } elseif ($this->filterType === 'brand-category') {
            if ($this->filterBrand) {
                $assets->where('brand_id', $this->filterBrand);
            }
            if ($this->filterCategory) {
                $assets->where('category_id', $this->filterCategory);
            }
        } elseif ($this->filterType === 'brand-tag') {
            if ($this->filterBrand) {
                $assets->where('brand_id', $this->filterBrand);
            }
            if ($this->filterTag) {
                $assets->whereHas('assetTags', function ($query) {
                    $query->where('tags.id', $this->filterTag);
                });
            }
        } elseif ($this->filterType === 'category-brand-tag') {
            if ($this->filterBrand) {
                $assets->where('brand_id', $this->filterBrand);
            }
            if ($this->filterCategory) {
                $assets->where('category_id', $this->filterCategory);
            }
            if ($this->filterTag) {
                $assets->whereHas('assetTags', function ($query) {
                    $query->where('tags.id', $this->filterTag);
                });
            }
        }

        $assets = $assets->get();
        $this->filteredCount = $assets->count();

        return view('livewire.inventory', [
            'assets' => $assets
        ]);
    }

    // Reset all filter values when the filter type changes
    public function updatedFilterType()
    {
        $this->filterValue = '';
        $this->filterBrand = '';
        $this->filterCategory = '';
        $this->filterTag = '';
    }
}
