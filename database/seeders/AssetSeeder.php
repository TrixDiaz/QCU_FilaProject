<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $assets = [
            [
                'category_id' => 1,
                'brand_id' => 1,
                'name' => 'Asset 1',
                'slug' => 'asset-1',
                'serial_number' => 'SN001',
                'asset_code' => 'AC001',
                'expiry_date' => now(),
                'status' => 'active'
            ],
            [
                'category_id' => 2,
                'brand_id' => 2,
                'name' => 'Asset 2',
                'slug' => 'asset-2',
                'serial_number' => 'SN002',
                'asset_code' => 'AC002',
                'expiry_date' => now(),
                'status' => 'active'
            ],
            [
                'category_id' => 3,
                'brand_id' => 3,
                'name' => 'Asset 3',
                'slug' => 'asset-3',
                'serial_number' => 'SN003',
                'asset_code' => 'AC003',
                'expiry_date' => now(),
                'status' => 'active'
            ],
            [
                'category_id' => 4,
                'brand_id' => 4,
                'name' => 'Asset 4',
                'slug' => 'asset-4',
                'serial_number' => 'SN004',
                'asset_code' => 'AC004',
                'expiry_date' => now(),
                'status' => 'active'
            ],
            [
                'category_id' => 1,
                'brand_id' => 1,
                'name' => 'Asset 5',
                'slug' => 'asset-5',
                'serial_number' => 'SN005',
                'asset_code' => 'AC005',
                'expiry_date' => now(),
                'status' => 'active'
            ],
            [
                'category_id' => 2,
                'brand_id' => 2,
                'name' => 'Asset 6',
                'slug' => 'asset-6',
                'serial_number' => 'SN006',
                'asset_code' => 'AC006',
                'expiry_date' => now(),
                'status' => 'active'
            ],
            [
                'category_id' => 3,
                'brand_id' => 3,
                'name' => 'Asset 7',
                'slug' => 'asset-7',
                'serial_number' => 'SN007',
                'asset_code' => 'AC007',
                'expiry_date' => now(),
                'status' => 'active'
            ],
            [
                'category_id' => 4,
                'brand_id' => 4,
                'name' => 'Asset 8',
                'slug' => 'asset-8',
                'serial_number' => 'SN008',
                'asset_code' => 'AC008',
                'expiry_date' => now(),
                'status' => 'active'
            ],
            [
                'category_id' => 1,
                'brand_id' => 1,
                'name' => 'Asset 9',
                'slug' => 'asset-9',
                'serial_number' => 'SN009',
                'asset_code' => 'AC009',
                'expiry_date' => now(),
                'status' => 'active'
            ],
            [
                'category_id' => 2,
                'brand_id' => 2,
                'name' => 'Asset 10',
                'slug' => 'asset-10',
                'serial_number' => 'SN010',
                'asset_code' => 'AC010',
                'expiry_date' => now(),
                'status' => 'active'
            ],
            [
                'category_id' => 3,
                'brand_id' => 3,
                'name' => 'Asset 11',
                'slug' => 'asset-11',
                'serial_number' => 'SN011',
                'asset_code' => 'AC011',
                'expiry_date' => now(),
                'status' => 'active'
            ],
            [
                'category_id' => 4,
                'brand_id' => 4,
                'name' => 'Asset 12',
                'slug' => 'asset-12',
                'serial_number' => 'SN012',
                'asset_code' => 'AC012',
                'expiry_date' => now(),
                'status' => 'active'
            ],
            [
                'category_id' => 1,
                'brand_id' => 1,
                'name' => 'Asset 13',
                'slug' => 'asset-13',
                'serial_number' => 'SN013',
                'asset_code' => 'AC013',
                'expiry_date' => now(),
                'status' => 'active'
            ],
            [
                'category_id' => 2,
                'brand_id' => 2,
                'name' => 'Asset 14',
                'slug' => 'asset-14',
                'serial_number' => 'SN014',
                'asset_code' => 'AC014',
                'expiry_date' => now(),
                'status' => 'active'
            ],
            [
                'category_id' => 3,
                'brand_id' => 3,
                'name' => 'Asset 15',
                'slug' => 'asset-15',
                'serial_number' => 'SN015',
                'asset_code' => 'AC015',
                'expiry_date' => now(),
                'status' => 'active'
            ],
            [
                'category_id' => 4,
                'brand_id' => 4,
                'name' => 'Asset 16',
                'slug' => 'asset-16',
                'serial_number' => 'SN016',
                'asset_code' => 'AC016',
                'expiry_date' => now(),
                'status' => 'active'
            ],
            [
                'category_id' => 1,
                'brand_id' => 1,
                'name' => 'Asset 17',
                'slug' => 'asset-17',
                'serial_number' => 'SN017',
                'asset_code' => 'AC017',
                'expiry_date' => now(),
                'status' => 'active'
            ],
            [
                'category_id' => 2,
                'brand_id' => 2,
                'name' => 'Asset 18',
                'slug' => 'asset-18',
                'serial_number' => 'SN018',
                'asset_code' => 'AC018',
                'expiry_date' => now(),
                'status' => 'active'
            ],
            [
                'category_id' => 3,
                'brand_id' => 3,
                'name' => 'Asset 19',
                'slug' => 'asset-19',
                'serial_number' => 'SN019',
                'asset_code' => 'AC019',
                'expiry_date' => now(),
                'status' => 'active'
            ],
            [
                'category_id' => 4,
                'brand_id' => 4,
                'name' => 'Asset 20',
                'slug' => 'asset-20',
                'serial_number' => 'SN020',
                'asset_code' => 'AC020',
                'expiry_date' => now(),
                'status' => 'active'
            ],
        ];

        DB::table('assets')->insert($assets);
    }
}
