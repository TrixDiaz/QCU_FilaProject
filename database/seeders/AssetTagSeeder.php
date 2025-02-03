<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tagIds = [1, 2, 3, 4,5,6,7,8,9,10,11,12];
        $assetTags = [];

        for ($i = 1; $i <= 20; $i++) {
            $randomTagIds = array_rand(array_flip($tagIds), 2);
            $assetTags[] = [
                'asset_id' => $i,
                'asset_tag_id' => $randomTagIds,
            ];
        }

        foreach ($assetTags as $assetTag) {
            foreach ($assetTag['asset_tag_id'] as $tagId) {
                DB::table('asset_tags')->insert([
                    'asset_id' => $assetTag['asset_id'],
                    'asset_tag_id' => $tagId,
                ]);
            }
        }
    }
}
