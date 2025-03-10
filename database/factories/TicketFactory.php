<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;


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
            'ticket_number' => strtoupper(Str::random(10)),
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'ticket_type' => $this->faker->randomElement(['request', 'incident']),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high']),
//            'due_date' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
//            'date_finished' => $this->faker->optional()->dateTimeBetween('now', '+2 months'),
            // 'attachment' => $this->faker->optional()->imageUrl(),
            'status' => $this->faker->randomElement(['open','in-progress', 'completed', 'pending']),
        ];
    }
}
