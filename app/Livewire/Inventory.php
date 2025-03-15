<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Asset;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Tag;
use Livewire\WithPagination;

class Inventory extends Component
{
    use WithPagination;

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
    public $perPage = 12;

    public function mount()
    {
        $this->brands = Brand::withCount('assets')->get();
        $this->categories = Category::withCount('assets')->get();
        $this->tags = Tag::withCount('assets')->get();
        $this->totalAssets = Asset::where('status', 'available')->count();
    }

    public function render()
    {
        $query = Asset::with(['brand', 'category', 'assetTags'])->where('status', 'available');

        if ($this->filterType === 'brand' && $this->filterValue) {
            $query->where('brand_id', $this->filterValue);
        } elseif ($this->filterType === 'category' && $this->filterValue) {
            $query->where('category_id', $this->filterValue);
        } elseif ($this->filterType === 'tag' && $this->filterValue) {
            $query->whereHas('assetTags', function ($q) {
                $q->where('tags.id', $this->filterValue);
            });
        } elseif ($this->filterType === 'brand-category') {
            if ($this->filterBrand) {
                $query->where('brand_id', $this->filterBrand);
            }
            if ($this->filterCategory) {
                $query->where('category_id', $this->filterCategory);
            }
        } elseif ($this->filterType === 'brand-tag') {
            if ($this->filterBrand) {
                $query->where('brand_id', $this->filterBrand);
            }
            if ($this->filterTag) {
                $query->whereHas('assetTags', function ($q) {
                    $q->where('tags.id', $this->filterTag);
                });
            }
        } elseif ($this->filterType === 'category-brand-tag') {
            if ($this->filterCategory) {
                $query->where('category_id', $this->filterCategory);
            }
            if ($this->filterBrand) {
                $query->where('brand_id', $this->filterBrand);
            }
            if ($this->filterTag) {
                $query->whereHas('assetTags', function ($q) {
                    $q->where('tags.id', $this->filterTag);
                });
            }
        }

        $assets = $query->paginate($this->perPage);
        $this->filteredCount = $assets->total();

        return view('livewire.inventory', [
            'assets' => $assets
        ]);
    }

    // Reset secondary filters when primary filter type changes
    public function updatedFilterType()
    {
        $this->filterValue = '';
        $this->filterBrand = '';
        $this->filterCategory = '';
        $this->filterTag = '';
        $this->resetPage();
    }

    // Reset pagination when filters change
    public function updatedFilterValue()
    {
        $this->resetPage();
    }

    public function updatedFilterBrand()
    {
        $this->resetPage();
    }

    public function updatedFilterCategory()
    {
        $this->resetPage();
    }

    public function updatedFilterTag()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }
}
