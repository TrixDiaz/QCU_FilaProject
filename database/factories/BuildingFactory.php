<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Building>
 */
class BuildingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'=> fake()->text(50),
            'slug'=> fake()->slug(),
            'is_active'=> fake()->boolean(),    
            'deleted_at'=> fake()->dateTime(),
            'created_at'=> fake()->dateTime(),
            'updated_at'=> fake()->dateTime(), 
        ];
    }
}
