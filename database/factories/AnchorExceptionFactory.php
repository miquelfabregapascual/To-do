<?php

namespace Database\Factories;

use App\Models\AnchorException;
use App\Models\RecurringAnchor;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnchorException>
 */
class AnchorExceptionFactory extends Factory
{
    protected $model = AnchorException::class;

    public function definition(): array
    {
        return [
            'recurring_anchor_id' => RecurringAnchor::factory(),
            'anchor_date' => CarbonImmutable::now()->addDays($this->faker->numberBetween(0, 14))->format('Y-m-d'),
            'action' => AnchorException::ACTION_SKIP,
            'metadata' => null,
        ];
    }
}
