<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'stage' => Task::STAGE_BACKLOG,
            'priority' => $this->faker->optional()->numberBetween(Task::PRIORITY_P1, Task::PRIORITY_P4),
            'labels' => $this->faker->optional()->words(2),
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

    public function inbox(): self
    {
        return $this->state(fn () => [
            'stage' => Task::STAGE_INBOX,
        ]);
    }

    public function archived(): self
    {
        return $this->state(fn () => [
            'stage' => Task::STAGE_ARCHIVED,
        ]);
    }
}
