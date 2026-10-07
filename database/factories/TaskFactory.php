<?php

namespace Database\Factories;

use App\Models\task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(2, false),
            'description' => fake()->sentence(10),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high']),
            'status' => $this->faker->randomElement(['todo', 'in_progress', 'done']),
            'due_date' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
