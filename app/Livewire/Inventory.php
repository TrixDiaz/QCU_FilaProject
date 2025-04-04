<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Asset;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Classroom;
use App\Models\AssetGroup;
use App\Models\User;
//use Filament\Notifications\Notification;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use League\Csv\Writer;
use League\Csv\Reader;
use SplTempFileObject;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AssetImport;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;


//use Filament\Notifications\Notification;



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

    public $totalAssetsAvailable = 0;
    public $filteredCount = 0;
    public $perPage = 12;
    public $viewType = 'table';
    public $search = '';

    // New properties for bulk actions
    public $selected = [];
    public $selectAll = false;
    public $bulkAction = '';
    public $confirmingBulkDelete = false;
    public $importFile;
    protected $rules = [
        'importFile' => 'required|file|mimes:csv,txt,xlsx,xls|max:1024',
    ];

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

    // Add this property
    public $statusFilter = '';

    // protected $listeners = ['refreshAssets' => '$refresh'];

    //  protected $listeners = [
    //'importFileSelected' => 'importFileSelected',
    //];

    //public function importFileSelected($file)
    //{
    //$this->importFile = $file;
    //}


    protected $listeners = ['refreshAssets' => '$refresh'];
    public $selectedAssets = [];

    public function mount()
    {
        $this->brands = Brand::withCount('assets')->orderByDesc('assets_count')->get();
        $this->categories = Category::withCount('assets')->orderByDesc('assets_count')->get();
        $this->tags = Tag::withCount('assets')->orderByDesc('assets_count')->get();
        $this->totalAssets = Asset::where('status', 'available')->count();
        $this->classrooms = Classroom::with(['building' => function ($query) {
            $query->orderBy('name');
        }])->orderBy('name')->get();
    }

    // Add this method to handle status filter
    public function filterByStatus($status)
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    public function render()
    {
        // Base query with relationships
        $query = Asset::with(['brand', 'category', 'assetTags', 'assetGroups.classroom.building'])
            ->orderBy('created_at', 'desc');

        // Apply filters based on filterType
        switch ($this->filterType) {
            case 'brand':
                if ($this->filterValue) {
                    $query->where('brand_id', $this->filterValue);
                }
                break;

            case 'category':
                if ($this->filterValue) {
                    $query->where('category_id', $this->filterValue);
                }
                break;

            case 'tag':
                if ($this->filterValue) {
                    $query->whereHas('assetTags', function ($q) {
                        $q->where('tag_id', $this->filterValue);
                    });
                }
                break;

            case 'brand-category':
                if ($this->filterBrand) {
                    $query->where('brand_id', $this->filterBrand);
                }
                if ($this->filterCategory) {
                    $query->where('category_id', $this->filterCategory);
                }
                break;

            case 'brand-tag':
                if ($this->filterBrand) {
                    $query->where('brand_id', $this->filterBrand);
                }
                if ($this->filterTag) {
                    $query->whereHas('assetTags', function ($q) {
                        $q->where('tag_id', $this->filterTag);
                    });
                }
                break;

            case 'category-brand-tag':
                if ($this->filterCategory) {
                    $query->where('category_id', $this->filterCategory);
                }
                if ($this->filterBrand) {
                    $query->where('brand_id', $this->filterBrand);
                }
                if ($this->filterTag) {
                    $query->whereHas('assetTags', function ($q) {
                        $q->where('tag_id', $this->filterTag);
                    });
                }
                break;
        }

        // Apply status filter if set
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        // Apply search if provided
        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                    ->orWhere('asset_code', 'like', $searchTerm)
                    ->orWhere('serial_number', 'like', $searchTerm)
                    ->orWhereHas('brand', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', $searchTerm);
                    })
                    ->orWhereHas('category', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', $searchTerm);
                    })
                    ->orWhereHas('assetTags', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', $searchTerm);
                    });
            });
        }

        // Get total counts - count ALL assets regardless of pagination
        $this->totalAssets = Asset::count();
        $this->totalAssetsAvailable = Asset::where('status', 'available')->count();
        $this->filteredCount = $query->count();

        // Get the paginated results
        $assets = $query->paginate($this->perPage);

        // Calculate deployment stats for all deployed assets
        $deploymentStats = [];
        foreach ($this->classrooms as $classroom) {
            $deployedCount = Asset::whereHas('assetGroups', function ($query) use ($classroom) {
                $query->where('classroom_id', $classroom->id);
            })->where('status', 'deployed')->count();

            if ($deployedCount > 0) {
                $deploymentStats[] = [
                    'classroom' => $classroom,
                    'count' => $deployedCount
                ];
            }
        }

        // Sort deploymentStats by count in descending order
        usort($deploymentStats, function ($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        // Total count of asset groups (not just the current page)
        $assetsGroups = AssetGroup::count();

        // Get category counts for available assets
        $categoryAvailableCounts = [];
        foreach ($this->categories as $category) {
            $categoryAvailableCounts[$category->id] = Asset::where('status', 'available')
                ->where('category_id', $category->id)
                ->count();
        }

        return view('livewire.inventory', [
            'assets' => $assets,
            'availableCount' => Asset::where('status', 'available')->count(),
            'deployedCount' => Asset::where('status', 'deployed')->count(),
            'deploymentStats' => $deploymentStats,
            'assetsGroups' => $assetsGroups,
            'categoryAvailableCounts' => $categoryAvailableCounts,
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

            $this->dispatch('notify', ['message' => 'Asset deployed successfully', 'type' => 'success']);

            // Reset deployment form
            $this->reset(['deployAssetId', 'selectedClassroom', 'deploymentName', 'deploymentCode']);
            $this->statusActive = true;

            // Get all users
            $users = \App\Models\User::all();

            // Optionally notify all users after giving them admin roles
            foreach ($users as $user) {
                $user->notify(
                    \Filament\Notifications\Notification::make()
                        ->title('Assets Deployed')
                        ->body('The selected assets have been successfully deployed.')
                        ->success()
                        ->icon('heroicon-m-computer-desktop')
                        ->toDatabase()
                );
                $user->notify(
                    \Filament\Notifications\Notification::make()
                        ->title('Assets Deployed')
                        ->body('The selected assets have been successfully deployed.')
                        ->success()
                        ->icon('heroicon-m-computer-desktop')
                        ->send()
                );
            }

            return true;
        } catch (\Exception $e) {
            DB::rollback();
            $this->dispatch('notify', ['message' => 'Error deploying asset: ' . $e->getMessage(), 'type' => 'error']);
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
            'statusFilter',
            'search'
        ]);
        $this->resetPage();
    }

    public function updating($name, $value)
    {
        if (in_array($name, ['search', 'filterType', 'filterValue', 'filterBrand', 'filterCategory', 'filterTag'])) {
            $this->resetPage();
        }
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

    public function resetImportFile()
    {
        // Reset the file input
        $this->importFile = null;
        // Optionally reset any other variables related to import (e.g., error messages, etc.)
        $this->resetErrorBag();
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
            // Store the file temporarily and get the path
            $path = $this->importFile->store('temp');
            $fullPath = Storage::path($path);

            // Check file extension
            $extension = $this->importFile->getClientOriginalExtension();

            if (in_array($extension, ['xlsx', 'xls'])) {
                // Handle Excel file
                $spreadsheet = IOFactory::load($fullPath);
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray();

                // Extract header and remove it from the rows
                $header = array_map('trim', $rows[0]);
                unset($rows[0]);

                foreach ($rows as $row) {
                    $assetData = array_combine($header, $row);
                    if (empty($assetData['serial_number'])) {
                        continue;
                    }

                    $asset = Asset::firstOrNew(['serial_number' => $assetData['serial_number']]);
                    $asset->name = $assetData['name'];
                    $asset->asset_code = $assetData['asset_code'] ?? null;
                    $asset->status = $assetData['status'] ?? 'available';
                    $asset->expiry_date = !empty($assetData['expiry_date'])
                        ? Carbon::parse($assetData['expiry_date'])
                        : null;

                    // Handle missing brand and category keys
                    $brandName = $assetData['brand'] ?? 'Unknown';
                    $categoryName = $assetData['category'] ?? 'Uncategorized';

                    $brand = Brand::firstOrCreate(
                        ['name' => $brandName],
                        ['slug' => Str::slug($brandName)]
                    );
                    $category = Category::firstOrCreate(
                        ['name' => $categoryName],
                        ['slug' => Str::slug($categoryName)]
                    );

                    $asset->brand_id = $brand->id;
                    $asset->category_id = $category->id;
                    $asset->save();
                }
            } else {
                // Handle CSV file
                if (($handle = fopen($fullPath, 'r')) !== false) {
                    $header = array_map('trim', fgetcsv($handle));

                    while (($row = fgetcsv($handle)) !== false) {
                        $assetData = array_combine($header, $row);

                        if (empty($assetData['serial_number'])) {
                            continue;
                        }

                        $asset = Asset::firstOrNew(['serial_number' => $assetData['serial_number']]);
                        $asset->name = $assetData['name'];
                        $asset->asset_code = $assetData['asset_code'] ?? null;
                        $asset->status = $assetData['status'] ?? 'available';
                        $asset->expiry_date = !empty($assetData['expiry_date'])
                            ? Carbon::parse($assetData['expiry_date'])
                            : null;

                        // Handle missing brand and category keys
                        $brandName = $assetData['brand'] ?? 'Unknown';
                        $categoryName = $assetData['category'] ?? 'Uncategorized';

                        $brand = Brand::firstOrCreate(
                            ['name' => $brandName],
                            ['slug' => Str::slug($brandName)]
                        );
                        $category = Category::firstOrCreate(
                            ['name' => $categoryName],
                            ['slug' => Str::slug($categoryName)]
                        );

                        $asset->brand_id = $brand->id;
                        $asset->category_id = $category->id;
                        $asset->save();
                    }
                    fclose($handle);
                }
            }

            // Clean up the uploaded file
            Storage::delete($path);

            // Reset file input and hide modal
            $this->resetImportFile();
            $this->showImportModal = false;
        } catch (\Exception $e) {
            $this->addError('importFile', 'Error importing file: ' . $e->getMessage());
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

    public function pullOutAsset($assetId)
    {
        try {
            DB::beginTransaction();

            // Find the asset
            $asset = Asset::findOrFail($assetId);

            // Delete all AssetGroup records for this asset
            AssetGroup::where('asset_id', $assetId)->delete();

            // Update asset status to available
            $asset->status = 'pull out';
            $asset->save();

            DB::commit();

            $this->dispatch('notify', ['message' => 'Asset pulled out successfully', 'type' => 'success']);

            // Get all users for notification
            $users = \App\Models\User::all();

            // Notify all users
            foreach ($users as $user) {
                $user->notify(
                    \Filament\Notifications\Notification::make()
                        ->title('Asset Pulled Out')
                        ->body('An asset has been pulled out and is now available.')
                        ->success()
                        ->icon('heroicon-m-computer-desktop')
                        ->toDatabase()
                );
            }

            return true;
        } catch (\Exception $e) {
            DB::rollback();
            $this->dispatch('notify', ['message' => 'Error pulling out asset: ' . $e->getMessage(), 'type' => 'error']);
            return false;
        }
    }
}
