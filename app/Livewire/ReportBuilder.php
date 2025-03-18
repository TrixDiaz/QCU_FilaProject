<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Asset;
use App\Models\User;
use App\Models\Maintenance;
use Livewire\WithPagination;
use App\Models\Category;
use App\Models\Brand;

class ReportBuilder extends Component
{
    use WithPagination;

    public $selectedModule = 'inventory'; // Default module
    public $selectedFields = [];
    public $filters = [
        'date_range' => 'last_90_days',
        'categories' => [],
        'brands' => [],
    ];
    public $moduleFields = [
        'inventory' => [
            'category', 'brand', 'asset_tag', 'name', 'asset_code',
            'serial_number', 'expiry_date', 'status'
        ],
        'users' => [
            'name', 'email', 'status', 'created_at', 'updated_at'
        ],
        'maintenance' => [
            'asset_id', 'maintenance_type', 'description', 'cost', 'date_performed', 'performed_by', 'status'
        ]
    ];
    public $availableFields = [];
    public $reportTitle = '';
    public $isGenerating = false;
    public $reportGenerated = false;

    public function mount()
    {
        $this->availableFields = $this->moduleFields[$this->selectedModule];
        $this->selectedFields = ['asset_tag', 'name', 'category', 'status', 'brand'];
    }

    public function updatedSelectedModule()
    {
        $this->availableFields = $this->moduleFields[$this->selectedModule];
        $this->selectedFields = [];
        $this->reportGenerated = false;
    }

    public function updatedFilters()
    {
        $this->resetPage();
        $this->reportGenerated = false;
    }

    public function updatedSelectedFields()
    {
        $this->reportGenerated = false;
    }

    public function runReport()
    {
        if (empty($this->selectedFields)) {
            session()->flash('error', 'Please select at least one field to display in the report.');
            return;
        }

        $this->isGenerating = true;
        $this->reportGenerated = true;
        $this->dispatch('reportGenerated');
        $this->isGenerating = false;
    }

    public function getCategories()
    {
        return Category::orderBy('name')->get();
    }

    public function getBrands()
    {
        return Brand::orderBy('name')->get();
    }

    public function getCategoryCounts()
    {
        return Asset::selectRaw('category_id, COUNT(*) as total')
                    ->groupBy('category_id')
                    ->with('category')
                    ->get();
    }

    public function getBrandCounts()
    {
        return Asset::selectRaw('brand_id, COUNT(*) as total')
                    ->groupBy('brand_id')
                    ->with('brand')
                    ->get();
    }

    public function printReport()
    {
        if (!$this->reportGenerated) {
            session()->flash('error', 'Please generate a report before printing.');
            return;
        }
        
        $this->dispatch('openPrintPreview');
    }


    public function render()
{
    if (!$this->reportGenerated) {
        return view('livewire.report-builder', [
            'data' => collect([]),
            'categories' => $this->getCategories(),
            'brands' => $this->getBrands(),
            'categoryCounts' => collect([]),
            'brandCounts' => collect([]),
        ]);
    }

    $data = collect([]); // Default empty collection

    if ($this->selectedModule === 'inventory') {
        $query = Asset::query();
        
        if (in_array('category', $this->selectedFields)) {
            $query->with('category');
        }
        
        if (in_array('brand', $this->selectedFields)) {
            $query->with('brand');
        }

        if ($this->filters['date_range'] == 'last_90_days') {
            $query->where('expiry_date', '>=', now()->subDays(90));
        } elseif ($this->filters['date_range'] == 'last_30_days') {
            $query->where('expiry_date', '>=', now()->subDays(30));
        } elseif ($this->filters['date_range'] == 'this_year') {
            $query->whereYear('expiry_date', now()->year);
        }

        if (!empty($this->filters['categories'])) {
            $query->whereIn('category_id', $this->filters['categories']);
        }
        
        if (!empty($this->filters['brands'])) {
            $query->whereIn('brand_id', $this->filters['brands']);
        }

        $data = $query->paginate(10);
    } elseif ($this->selectedModule === 'users') {
        // ✅ Add query for Users module
        $query = User::query();
        $data = $query->paginate(10);
    } elseif ($this->selectedModule === 'maintenance') {
        // ✅ Add query for Maintenance module
        $query = Maintenance::query();
        $data = $query->paginate(10);
    }

    return view('livewire.report-builder', [
        'data' => $data,
        'categories' => $this->getCategories(),
        'brands' => $this->getBrands(),
        'categoryCounts' => $this->getCategoryCounts(),
        'brandCounts' => $this->getBrandCounts(),
    ]);
    }
}
