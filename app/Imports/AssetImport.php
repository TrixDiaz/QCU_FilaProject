<?php

namespace App\Imports;

use App\Models\Asset;
use App\Models\Brand;
use App\Models\Category;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Str;

class AssetImport extends Importer
{
    protected static ?string $model = Asset::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('category')
                ->label(__('Category'))
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('Electronics'),
            ImportColumn::make('brand')
                ->label(__('Brand'))
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('Samsung'),
            ImportColumn::make('name')
                ->label(__('Asset Name'))
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('TV'),
            ImportColumn::make('asset_code')
                ->label(__('Asset Code'))
                ->rules(['nullable', 'max:255'])
                ->example('AC123'),
            ImportColumn::make('serial_number')
                ->label(__('Serial Number'))
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('SN001'),
            ImportColumn::make('expiry_date')
                ->label(__('Expiry Date'))
                ->rules(['nullable', 'date'])
                ->example('2025-12-31'),
            ImportColumn::make('status')
                ->label(__('Status'))
                ->rules(['nullable', 'in:available,unavailable,deployed'])
                ->example('available'),
        ];
    }

    public function resolveRecord(): ?Asset
    {
        // Check if an asset with the same serial number already exists
        return Asset::firstOrNew([
            'serial_number' => $this->data['serial_number'],
        ]);
    }

    public function mutateRecord(Asset $asset): Asset
    {
        // Resolve or create related Brand and Category
        $brand = Brand::firstOrCreate(
            ['name' => $this->data['brand']],
            ['slug' => Str::slug($this->data['brand'])]
        );

        $category = Category::firstOrCreate(
            ['name' => $this->data['category']],
            ['slug' => Str::slug($this->data['category'])]
        );

        // Set asset attributes
        $asset->category_id = $category->id;
        $asset->brand_id = $brand->id;
        $asset->name = $this->data['name'];
        $asset->asset_code = $this->data['asset_code'] ?? null;
        $asset->serial_number = $this->data['serial_number'];
        $asset->expiry_date = $this->data['expiry_date'] ?? null;
        $asset->status = $this->data['status'] ?? 'available';

        // Save the asset
        $asset->save();

        return $asset;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your asset import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}