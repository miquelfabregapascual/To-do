<?php

namespace Database\Factories;

use App\Models\RecurringAnchor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecurringAnchor>
 */
class RecurringAnchorFactory extends Factory
{
    protected $model = RecurringAnchor::class;

    public function definition(): array
    {
        $start = Carbon::createFromTime($this->faker->numberBetween(6, 10), 0);
        $end = (clone $start)->addMinutes($this->faker->numberBetween(30, 120));

        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->optional()->paragraph(),
            'day_of_week' => $this->faker->numberBetween(0, 6),
            'start_time' => $start->format('H:i:s'),
            'end_time' => $end->format('H:i:s'),
            'timezone' => 'UTC',
            'is_active' => true,
        ];
    }
}
