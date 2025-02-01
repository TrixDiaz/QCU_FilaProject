<?php

namespace Database\Factories;

use App\Filament\App\Resources\CategoryResource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Asset>
 */
class AssetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => \App\Models\Category::factory(),
            'brand_id' => \App\Models\Brand::factory(),
            'name' => fake()->text(20),
            'slug' => fake()->slug(),
            'serial_number' => fake()->uuid(),
            'asset_code' => fake()->text(6),
            'expiry_date' => now(),
            'status' => 'active'
        ];
    }
}
