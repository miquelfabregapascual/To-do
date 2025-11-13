<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class WeeklyReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_weekly_review_summarizes_previous_week_metrics(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-03-17 10:00:00'));

        $user = User::factory()->create();
        $lastWeekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->subWeek();

        $plannedCompleted = Task::factory()
            ->for($user)
            ->completed()
            ->create([
                'due_date' => $lastWeekStart->copy()->addDays(2)->toDateString(),
                'created_at' => $lastWeekStart->copy()->addDay(),
                'updated_at' => $lastWeekStart->copy()->addDays(3),
            ]);

        $plannedIncomplete = Task::factory()
            ->for($user)
            ->incomplete()
            ->create([
                'due_date' => $lastWeekStart->copy()->addDays(4)->toDateString(),
                'created_at' => $lastWeekStart->copy()->addDays(2),
                'updated_at' => $lastWeekStart->copy()->addDays(4),
            ]);

        Task::factory()
            ->for($user)
            ->incomplete()
            ->create([
                'due_date' => $lastWeekStart->copy()->subDay()->toDateString(),
                'created_at' => $lastWeekStart->copy()->subDays(2),
            ]);

        $completedDuringWeek = Task::factory()
            ->for($user)
            ->completed()
            ->create([
                'due_date' => $lastWeekStart->copy()->subWeek()->addDay()->toDateString(),
                'created_at' => $lastWeekStart->copy()->subWeek()->addDay(),
                'updated_at' => $lastWeekStart->copy()->addDays(5),
            ]);

        $this->actingAs($user);

        $response = $this->get(route('weekly-review'));

        $response->assertOk()
            ->assertViewIs('tasks.weekly-review')
            ->assertViewHas('plannedCount', 2)
            ->assertViewHas('completedCount', 1)
            ->assertViewHas('carryOverCount', 1)
            ->assertViewHas('carryOverTasks', function ($tasks) use ($plannedIncomplete) {
                return $tasks->contains($plannedIncomplete) && $tasks->count() === 1;
            })
            ->assertViewHas('completedDuringWeek', function ($tasks) use ($completedDuringWeek) {
                return $tasks->contains($completedDuringWeek);
            })
            ->assertViewHas('dailyStats', function ($stats) use ($lastWeekStart) {
                return $stats->count() === 7
                    && $stats->first()['date']->isSameDay($lastWeekStart)
                    && collect($stats)->every(fn ($day) => isset($day['planned'], $day['completed'], $day['carryOver']));
            });

        Carbon::setTestNow();
    }
}
