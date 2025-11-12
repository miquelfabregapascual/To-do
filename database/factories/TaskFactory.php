<?php

namespace Database\\Factories;

use App\\Models\\Task;
use App\\Models\\User;
use Illuminate\\Database\\Eloquent\\Factories\\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->optional()->paragraph(),
            'due_date' => $this->faker->dateTimeBetween('now', '+2 weeks')->format('Y-m-d'),
            'completed' => $this->faker->boolean(20),
            'is_anchor' => false,
            'recurring_anchor_id' => null,
            'anchor_start_time' => null,
            'anchor_end_time' => null,
        ];
    }

    public function incomplete(): self
    {
        return $this->state(fn () => [
            'completed' => false,
        ]);
    }

    public function completed(): self
    {
        return $this->state(fn () => [
            'completed' => true,
        ]);
    }
}
