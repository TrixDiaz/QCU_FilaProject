<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subject>
 */
class SubjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'subject_code' => fake()->unique()->numerify('SUB-###'),
            'subject_units' => fake()->numberBetween(1, 10),
            'lab_hours' => fake()->numberBetween(1, 10),
            'lecture_hours' => fake()->numberBetween(1, 10),
            'status' => 'active, inactive'
        ];
    }
}
