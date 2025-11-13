<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WeeklyGoal;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<WeeklyGoal>
 */
class WeeklyGoalFactory extends Factory
{
    protected $model = WeeklyGoal::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'week_start_date' => Carbon::now()->startOfWeek(Carbon::MONDAY),
            'position' => $this->faker->numberBetween(1, 5),
            'title' => $this->faker->sentence(4),
        ];
    }
}
