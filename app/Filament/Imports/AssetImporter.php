<?php

namespace App\Filament\Imports;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Brand;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Str;

class AssetImporter extends Importer
{
    protected static ?string $model = Asset::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('category')
                ->requiredMapping()
                ->relationship()
                ->rules(['required']),

            ImportColumn::make('brand')
                ->requiredMapping()
                ->relationship()
                ->rules(['required']),

            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),

            ImportColumn::make('serial_number')
                ->requiredMapping()
                ->rules(['required', 'max:255', 'unique:assets,serial_number']),

            ImportColumn::make('asset_code')
                ->requiredMapping()
                ->rules(['required', 'max:255', 'unique:assets,asset_code']),

            ImportColumn::make('expiry_date')
                ->rules(['nullable', 'date']),

            ImportColumn::make('status')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
        ];
    }

    public function resolveRecord(): ?Asset
    {
        $category = Category::firstOrCreate(['name' => $this->data['category']]);
        $brand = Brand::firstOrCreate(['name' => $this->data['brand']]);

        return Asset::firstOrNew([
            'serial_number' => $this->data['serial_number'],
        ])->fill([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => $this->data['name'],
            'slug' => Str::slug($this->data['name']),
            'serial_number' => $this->data['serial_number'],
            'asset_code' => $this->data['asset_code'],
            'expiry_date' => $this->data['expiry_date'] ?? null,
            'status' => $this->data['status'],
        ]);
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
