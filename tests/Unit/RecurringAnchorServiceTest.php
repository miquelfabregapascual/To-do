<?php

namespace Tests\Unit;

use App\Models\AnchorException;
use App\Models\RecurringAnchor;
use App\Models\Task;
use App\Models\User;
use App\Services\RecurringAnchorService;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecurringAnchorServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_materialize_week_creates_anchor_tasks(): void
    {
        $user = User::factory()->create();
        $anchor = RecurringAnchor::factory()->for($user)->create([
            'day_of_week' => CarbonInterface::MONDAY,
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
        ]);

        $service = app(RecurringAnchorService::class);

        $weekStart = CarbonImmutable::parse('2025-03-10'); // Monday
        $weekEnd = $weekStart->addDays(6);

        $materialized = $service->materializeWeek($user, CarbonPeriod::create($weekStart, '1 day', $weekEnd));

        $this->assertCount(1, $materialized);
        $task = $materialized->first();
        $this->assertInstanceOf(Task::class, $task);
        $this->assertTrue($task->is_anchor);
        $this->assertSame($anchor->id, $task->recurring_anchor_id);
        $this->assertSame('2025-03-10', $task->due_date->toDateString());
        $this->assertSame('09:00:00', $task->anchor_start_time?->format('H:i:s'));
        $this->assertSame('10:00:00', $task->anchor_end_time?->format('H:i:s'));
    }

    public function test_materialize_week_is_idempotent(): void
    {
        $user = User::factory()->create();
        RecurringAnchor::factory()->for($user)->create([
            'day_of_week' => CarbonInterface::TUESDAY,
        ]);

        $service = app(RecurringAnchorService::class);
        $weekStart = CarbonImmutable::parse('2025-03-10');
        $weekEnd = $weekStart->addDays(6);
        $period = CarbonPeriod::create($weekStart, '1 day', $weekEnd);

        $firstRun = $service->materializeWeek($user, $period);
        $this->assertCount(1, $firstRun);

        $secondRun = $service->materializeWeek($user, $period);
        $this->assertCount(1, $secondRun);
        $this->assertSame(1, Task::count());
    }

    public function test_materialize_week_respects_exceptions(): void
    {
        $user = User::factory()->create();
        $anchor = RecurringAnchor::factory()->for($user)->create([
            'day_of_week' => CarbonInterface::WEDNESDAY,
        ]);

        $weekStart = CarbonImmutable::parse('2025-03-10');
        $weekEnd = $weekStart->addDays(6);
        $occurrenceDate = $weekStart->addDays(2); // Wednesday

        AnchorException::create([
            'recurring_anchor_id' => $anchor->id,
            'anchor_date' => $occurrenceDate->toDateString(),
            'action' => AnchorException::ACTION_SKIP,
        ]);

        $service = app(RecurringAnchorService::class);
        $result = $service->materializeWeek($user, CarbonPeriod::create($weekStart, '1 day', $weekEnd));

        $this->assertCount(0, $result);
        $this->assertSame(0, Task::count());
    }

    public function test_materialize_week_removes_existing_tasks_for_new_exception(): void
    {
        $user = User::factory()->create();
        $anchor = RecurringAnchor::factory()->for($user)->create([
            'day_of_week' => CarbonInterface::THURSDAY,
        ]);

        $service = app(RecurringAnchorService::class);
        $weekStart = CarbonImmutable::parse('2025-03-10');
        $weekEnd = $weekStart->addDays(6);
        $period = CarbonPeriod::create($weekStart, '1 day', $weekEnd);

        $service->materializeWeek($user, $period);
        $this->assertSame(1, Task::count());

        $occurrenceDate = $weekStart->addDays(3); // Thursday
        AnchorException::create([
            'recurring_anchor_id' => $anchor->id,
            'anchor_date' => $occurrenceDate->toDateString(),
            'action' => AnchorException::ACTION_SKIP,
        ]);

        $result = $service->materializeWeek($user, $period);

        $this->assertCount(0, $result);
        $this->assertSame(0, Task::count());
    }
}
