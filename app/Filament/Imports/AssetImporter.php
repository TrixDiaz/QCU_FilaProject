<?php

namespace App\Filament\Imports;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Brand;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AssetImporter extends Importer
{
    protected static ?string $model = Asset::class;

    // Function to generate unique asset code, similar to the one in AssetResource
    private function generateUniqueCode()
    {
        do {
            $code = Str::upper(Str::random(10));
        } while (Asset::where('asset_code', $code)->exists());

        return $code;
    }

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('category')
                ->requiredMapping()
                ->rules(['required']),

            ImportColumn::make('brand')
                ->requiredMapping()
                ->rules(['required']),

            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),

                ImportColumn::make('slug')
                ->requiredMapping()
                ->rules(['required', 'max:255']),

                ImportColumn::make('asset_code')
                ->requiredMapping()
                ->rules(['required', 'max:255']),

                ImportColumn::make('serial_number')
                ->requiredMapping()
                ->rules(['required', 'max:255', 'unique:assets,serial_number']),            

            ImportColumn::make('expiry_date')
                ->rules(['nullable', 'date']),

            ImportColumn::make('status')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
        ];
    }

    public function map($row): array
{
    // Retrieve category and brand IDs
    $category = Category::where('name', $row['category'] ?? '')->first();
    $brand = Brand::where('name', $row['brand'] ?? '')->first();

    // Generate a formatted asset code
    $assetSlug = Str::slug($row['name'] ?? '');
    $uniqueCode = $this->generateUniqueCode();
    $assetCode = $assetSlug . '-' . $uniqueCode;

    return [
        'name' => $row['name'] ?? null,
        'slug' => $row['slug'] ?? null,
        'brand_id' => $brand ? $brand->id : null, // Assign brand ID
        'category_id' => $category ? $category->id : null, // Assign category ID
        'serial_number' => $row['serial_number'] ?? null,
        'asset_code' => $assetCode, // Generate the asset code here
        'expiry_date' => isset($row['expiry_date']) && !empty($row['expiry_date']) 
                        ? \Carbon\Carbon::parse($row['expiry_date'])->format('Y-m-d') 
                        : null,
        'status' => $row['status'] ?? 'active',
    ];
}


public function resolveRecord(): ?Asset
{
    Log::info('Importing row:', $this->data);

    try {
        $category = Category::firstOrCreate(['name' => $this->data['category']], ['is_active' => true]);
        $brand = Brand::firstOrCreate(['name' => $this->data['brand']], ['is_active' => true]);

        $slug = Str::slug($this->data['name']);

        // Find asset by serial number; if exists, update it
        $asset = Asset::updateOrCreate(
            ['serial_number' => $this->data['serial_number']],
            [
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'name' => $this->data['name'],
                'slug' => $slug,
                'asset_code' => $this->data['asset_code'],
                'expiry_date' => $this->data['expiry_date'] ?? null,
                'status' => $this->data['status'],
            ]
        );

        return $asset;
    } catch (\Exception $e) {
        Log::error('Error resolving asset record: ' . $e->getMessage());
        throw $e;
    }
}


    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your asset import has completed. ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}