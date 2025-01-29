<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Classroom;   

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Section>
 */
class SectionFactory extends Factory
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
            'classroom_id'=> Classroom::factory(),  
        ];
    }
}
