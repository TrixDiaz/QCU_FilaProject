<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'asset_id' => \App\Models\Asset::factory(),
            'user_id' => \App\Models\User::factory(),
            'section_id' => \App\Models\Section::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'ticket_type' => fake()->randomElement(['maintenance', 'repair', 'replacement']),
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
        ];
    }
}
