<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Asset;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Classroom;
use App\Models\AssetGroup;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use League\Csv\Writer;
use League\Csv\Reader;
use SplTempFileObject;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;


class Inventory extends Component
{
    use WithPagination;
    use WithFileUploads;

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
    public $viewType = 'table';
    public $search = '';

    // New properties for bulk actions
    public $selected = [];
    public $selectAll = false;
    public $bulkAction = '';
    public $confirmingBulkDelete = false;
    public $importFile = null;
    public $showImportModal = false;
    public $showBulkEditModal = false;
    public $bulkEditData = [
        'status' => '',
        'deployClassroom' => null,
    
    ];

    // Deployment properties
    public $classrooms = [];
    public $deployAssetId;
    public $selectedClassroom;
    public $deploymentName;
    public $deploymentCode;
    public $statusActive = true;

    protected $listeners = ['refreshAssets' => '$refresh'];
    public $selectedAssets = [];

    public function mount()
    {
        $this->brands = Brand::withCount('assets')->get();
        $this->categories = Category::withCount('assets')->get();
        $this->tags = Tag::withCount('assets')->get();
        $this->totalAssets = Asset::where('status', 'available')->count();
        $this->classrooms = Classroom::with('building')->get();
    }

    public function render()


    {
        
        
        $query = Asset::with(['brand', 'category', 'assetTags'])->where('status', 'available');

        // Apply search if provided
        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                    ->orWhere('serial_number', 'like', $searchTerm)
                    ->orWhere('asset_code', 'like', $searchTerm)
                    ->orWhereHas('brand', function ($brandQuery) use ($searchTerm) {
                        $brandQuery->where('name', 'like', $searchTerm);
                    })
                    ->orWhereHas('category', function ($categoryQuery) use ($searchTerm) {
                        $categoryQuery->where('name', 'like', $searchTerm);
                    })
                    ->orWhereHas('assetTags', function ($tagQuery) use ($searchTerm) {
                        $tagQuery->where('name', 'like', $searchTerm);
                    });
            });
        }

        // Apply filters
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

        // Fix ordering by making it more explicit
        // Order first by updated_at, then by created_at (both descending)
        // This shows most recently modified assets first, then newest created assets
        $query->orderByDesc('updated_at')
            ->orderByDesc('created_at');

        $assets = $query->paginate($this->perPage);
        $this->filteredCount = $assets->total();

        // Handle "Select All" checkboxes
        if ($this->selectAll) {
            $this->selected = $assets->pluck('id')->map(fn($id) => (string) $id)->toArray();
        }

        return view('livewire.inventory', [
            'assets' => $assets
        ]);
    }

    // Deploy asset method
    public function deployAsset($assetId, $classroomId, $name, $code, $isActive)
    {
        $this->validate([
            'selectedClassroom' => 'required',
        ]);

        try {
            DB::beginTransaction();

            // Create the asset group record
            AssetGroup::create([
                'asset_id' => $assetId,
                'classroom_id' => $classroomId,
                'name' => $name,
                'code' => $code,
                'status' => $isActive ? 'active' : 'inactive',
            ]);

            // Update asset status to deployed
            $asset = Asset::find($assetId);
            $asset->status = 'deployed';
            $asset->save();

            DB::commit();

            // Use Filament Notification instead of dispatching a custom event
            Notification::make()
                ->title('Asset Deployed')
                ->body('Asset has been successfully deployed to the classroom.')
                ->success()
                ->send();

            // Reset deployment form
            $this->reset(['deployAssetId', 'selectedClassroom', 'deploymentName', 'deploymentCode']);
            $this->statusActive = true;

            return true;
        } catch (\Exception $e) {
            DB::rollback();

            Notification::make()
                ->title('Deployment Failed')
                ->body('Error deploying asset: ' . $e->getMessage())
                ->danger()
                ->send();

            return false;
        }
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

    public function updatedSelectAll($value)
    {
        if (!$value) {
            $this->selected = [];
        }
    }

    // Reset pagination when search changes
    public function updatedSearch()
    {
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

    // Add a new method to toggle view type
    public function setViewType($type)
    {
        $this->viewType = $type;
    }

    // Reset filters
    public function resetFilters()
    {
        $this->reset([
            'filterType',
            'filterValue',
            'filterBrand',
            'filterCategory',
            'filterTag',
            'search'
        ]);
        $this->resetPage();
    }

    // Bulk action methods
    public function confirmBulkDelete()
    {
        if (empty($this->selected)) {
            $this->addError('bulkAction', 'Please select at least one asset');
            return;
        }

        $this->confirmingBulkDelete = true;
    }

    public function doBulkDelete()
    {
        // Ensure selected assets are not empty
        if (empty($this->selected)) {
            $this->addError('bulkAction', 'Please select at least one asset');
            return;
        }
    
        // Perform the archiving by updating the status
        Asset::whereIn('id', $this->selected)->update(['status' => 'archived']);
    
        // Reset the state
        $this->confirmingBulkDelete = false;
        $this->selected = [];
        $this->selectAll = false;
    
        // Notify the user
        $this->dispatch('notify', ['message' => count($this->selected) . ' assets archived successfully', 'type' => 'success']);
    }

    public function openBulkEditModal()
    {
        // Fetch the selected assets
        $this->selectedAssets = Asset::whereIn('id', $this->selected)->get();
    
        // Initialize the bulk edit data for each asset
        foreach ($this->selectedAssets as $asset) {
            $this->bulkEditData[$asset->id] = [
                'category_id' => $asset->category_id,
                'brand_id' => $asset->brand_id,
                'status' => $asset->status,
                'asset_tag_id' => $asset->assetTags->pluck('id')->toArray(),
            ];
        }
    
        // Show the bulk edit modal
        $this->showBulkEditModal = true;
    }
    
    public function saveBulkEdit()
    {
        // Validate the bulk edit data
        $this->validate([
            'bulkEditData.status' => 'nullable|in:broken,maintenance',
            'bulkEditData.deployClassroom' => 'nullable|exists:classrooms,id',
        ]);

        // Update selected assets
        foreach ($this->selected as $assetId) {
            $asset = Asset::find($assetId);

            // Update status if provided
            if ($this->bulkEditData['status']) {
                $asset->status = $this->bulkEditData['status'];
                $asset->save();
            }

            // Deploy to classroom if provided
            if ($this->bulkEditData['deployClassroom']) {
                // Auto-generate deployment code based on the asset's serial number
                $deploymentCode = $this->generateAssetCodeFromSerialNumber($asset->serial_number);

                // Update the asset's status to "deployed"
                $asset->status = 'deployed';
                $asset->save();

                // Create or update the AssetGroup record
                AssetGroup::updateOrCreate(
                    ['asset_id' => $assetId], // Find by asset_id
                    [
                        'classroom_id' => $this->bulkEditData['deployClassroom'],
                        'name' => $asset->name, // Use the asset's name as the deployment name
                        'code' => $deploymentCode, // Auto-generated deployment code
                        'status' => 'active', // Set status to "active" in AssetGroup
                    ]
                );
            }
        }

        // Reset bulk edit data and close modal
        $this->reset('bulkEditData');
        $this->showBulkEditModal = false;

        // Notify user
        $this->dispatch('notify', message: 'Bulk edit applied successfully!');
    }

    protected function generateAssetCodeFromSerialNumber($serialNumber)
    {
        // Get the last 3 digits of the serial number
        $lastThreeDigits = Str::substr($serialNumber, -3);

        // Combine base code "AC" with the last 3 digits in uppercase
        return "AC" . Str::upper($lastThreeDigits);
    }
  
    
    

    public function openImportModal()
    {
        $this->showImportModal = true;
    }

    public function importAssets()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:csv,txt,xlsx,xls|max:1024',
        ]);

        if (!$this->importFile) {
            $this->addError('importFile', 'No file selected.');
            return;
        }

        try {
            // Store temporarily and get the path
            $path = $this->importFile->store('temp');  // Saves in storage/app/temp
            $fullPath = Storage::path($path);  // Gets the absolute path

            $csv = Reader::createFromPath($fullPath, 'r');
            $csv->setHeaderOffset(0);

            $records = $csv->getRecords();
            $imported = 0;

            DB::beginTransaction();

            foreach ($records as $record) {
                $brand = Brand::firstOrCreate(['name' => $record['brand']]);
                $category = Category::firstOrCreate(['name' => $record['category']]);

                Asset::create([
                    'name' => $record['name'],
                    'serial_number' => $record['serial_number'],
                    'asset_code' => $record['asset_code'] ?? '',
                    'status' => $record['status'] ?? 'available',
                    'brand_id' => $brand->id,
                    'category_id' => $category->id,
                ]);

                $imported++;
            }

            DB::commit();
            $this->dispatch('notify', ['message' => $imported . ' assets imported successfully', 'type' => 'success']);
            $this->importFile = null;
            $this->showImportModal = false;

            // Clean up the file after importing
            Storage::delete($path);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('importFile', 'Error importing file: ' . $e->getMessage());
        }
    }

    

    public function exportAssets()
    {
        try {
            $csv = Writer::createFromFileObject(new SplTempFileObject());

            // Add headers
            $csv->insertOne(['Name', 'Serial Number', 'Asset Code', 'Status', 'Brand', 'Category']);

            // Get selected assets
            $assets = Asset::with(['brand', 'category'])
                ->whereIn('id', $this->selected)
                ->get();

            foreach ($assets as $asset) {
                $csv->insertOne([
                    $asset->name,
                    $asset->serial_number,
                    $asset->asset_code,
                    $asset->status,
                    $asset->brand->name,
                    $asset->category->name,
                ]);
            }

            $this->dispatch('notify', ['message' => 'Assets exported successfully', 'type' => 'success']);

            return response()->streamDownload(
                function () use ($csv) {
                    echo $csv->getContent();
                },
                'assets-export-' . now()->format('Y-m-d') . '.csv'
            );
        } catch (\Exception $e) {
            $this->dispatch('notify', ['message' => 'Error exporting assets: ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    public function executeBulkAction()
    {
        if (empty($this->selected)) {
            $this->addError('bulkAction', 'Please select at least one asset');
            return;
        }

        switch ($this->bulkAction) {
            case 'delete':
                $this->confirmBulkDelete();
                break;
            case 'edit':
                $this->openBulkEditModal();
                break;
            case 'export':
                return $this->exportAssets();
                break;
            default:
                $this->addError('bulkAction', 'Please select a valid action');
        }
    }

}
