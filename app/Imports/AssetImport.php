<?php

namespace App\Imports;

use App\Models\Asset;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\SkipsFirstRow;

class AssetImport implements ToModel
{
    public function model(array $row)
    {
        // Check if the brand name is empty
        $brandName = trim($row[1]);
        if (empty($brandName)) {
            // Log or skip row if brand name is empty
            \Log::warning('Skipping row due to empty brand name: ' . json_encode($row));
            return null; // Skip this row
        }

        // Check if the category name is empty
        $categoryName = trim($row[0]);
        if (empty($categoryName)) {
            // Log or skip row if category name is empty
            \Log::warning('Skipping row due to empty category name: ' . json_encode($row));
            return null; // Skip this row
        }

        // Create or get the Brand
        $brand = Brand::firstOrCreate(
            ['name' => $brandName],
            ['slug' => Str::slug($brandName)]
        );

        // Create or get the Category
        $category = Category::firstOrCreate(
            ['name' => $categoryName],
            ['slug' => Str::slug($categoryName)]
        );

        // Create the Asset
        return new Asset([
            'category_id'   => $category->id,
            'brand_id'      => $brand->id,
            'name'          => $row[2],
            'slug'          => Str::slug($row[2]), // Auto-generate slug for Asset
            'asset_code'    => $row[4],
            'serial_number' => $row[5],
            'expiry_date'   => $row[6],
            'status'        => $row[7] ?? 'available',
        ]);
    }
}
