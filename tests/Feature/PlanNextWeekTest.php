<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use App\Models\WeeklyGoal;
use App\Services\RecurringAnchorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Mockery;
use Tests\TestCase;

class PlanNextWeekTest extends TestCase
{
    use RefreshDatabase;

    public function test_wizard_overview_summarizes_last_week(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-03-17 09:00:00'));

        $user = User::factory()->create();
        $lastWeekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->subWeek();

        $plannedCompleted = Task::factory()
            ->for($user)
            ->completed()
            ->create([
                'due_date' => $lastWeekStart->copy()->addDays(1)->toDateString(),
            ]);

        $plannedCarryOver = Task::factory()
            ->for($user)
            ->incomplete()
            ->create([
                'due_date' => $lastWeekStart->copy()->addDays(3)->toDateString(),
            ]);

        Task::factory()
            ->for($user)
            ->incomplete()
            ->create([
                'due_date' => $lastWeekStart->copy()->subDays(2)->toDateString(),
            ]);

        WeeklyGoal::factory()->for($user)->create([
            'week_start_date' => $lastWeekStart->copy()->addWeek()->toDateString(),
            'position' => 1,
            'title' => 'Seguir creciendo el proyecto',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('plan-next-week'));

        $response->assertOk()
            ->assertViewIs('tasks.plan-next-week')
            ->assertViewHas('lastWeekPlanned', function ($tasks) use ($plannedCompleted, $plannedCarryOver) {
                return $tasks->contains($plannedCompleted) && $tasks->contains($plannedCarryOver);
            })
            ->assertViewHas('lastWeekCarryOver', function ($tasks) use ($plannedCarryOver) {
                return $tasks->count() === 1 && $tasks->first()->is($plannedCarryOver);
            })
            ->assertViewHas('goals', function ($goals) {
                return $goals->count() === 1;
            });

        Carbon::setTestNow();
    }

    public function test_user_can_store_goals_for_next_week(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-03-17 09:00:00'));

        $user = User::factory()->create();
        $this->actingAs($user);

        $payload = [
            'goals' => [
                'Cerrar iteración del roadmap',
                'Plan de lanzamiento beta',
                null,
            ],
        ];

        $response = $this->post(route('plan-next-week.goals'), $payload);

        $response->assertRedirect(route('plan-next-week', ['step' => 'anchors']))
            ->assertSessionHas('success');

        $nextWeekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->addWeek()->toDateString();

        $this->assertDatabaseHas('weekly_goals', [
            'user_id' => $user->id,
            'week_start_date' => $nextWeekStart,
            'position' => 1,
            'title' => 'Cerrar iteración del roadmap',
        ]);

        $this->assertDatabaseHas('weekly_goals', [
            'user_id' => $user->id,
            'week_start_date' => $nextWeekStart,
            'position' => 2,
            'title' => 'Plan de lanzamiento beta',
        ]);

        $this->assertDatabaseCount('weekly_goals', 2);

        Carbon::setTestNow();
    }

    public function test_user_can_schedule_backlog_tasks(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-03-17 09:00:00'));

        $user = User::factory()->create();
        $tasks = Task::factory()->count(2)->for($user)->create([
            'due_date' => null,
            'completed' => false,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('plan-next-week.schedule'), [
            'assignments' => [
                ['task_id' => $tasks[0]->id, 'day' => 0],
                ['task_id' => $tasks[1]->id, 'day' => 2],
            ],
        ]);

        $response->assertRedirect(route('plan-next-week', ['step' => 'summary']))
            ->assertSessionHas('success');

        $nextWeekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->addWeek();

        $this->assertDatabaseHas('tasks', [
            'id' => $tasks[0]->id,
            'due_date' => $nextWeekStart->copy()->addDays(0)->toDateString(),
        ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $tasks[1]->id,
            'due_date' => $nextWeekStart->copy()->addDays(2)->toDateString(),
        ]);

        Carbon::setTestNow();
    }

    public function test_finalize_materializes_next_week(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-03-17 09:00:00'));

        $user = User::factory()->create();

        $mock = Mockery::mock(RecurringAnchorService::class);
        $mock->shouldReceive('materializeWeek')
            ->once()
            ->withArgs(function ($passedUser, $period) use ($user) {
                $expectedStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->addWeek();
                $expectedEnd = $expectedStart->copy()->endOfWeek(Carbon::SUNDAY);

                return $passedUser->is($user)
                    && $period instanceof \Carbon\CarbonPeriod
                    && $period->getStartDate()->isSameDay($expectedStart)
                    && $period->getEndDate()->isSameDay($expectedEnd);
            })
            ->andReturn(collect());

        app()->instance(RecurringAnchorService::class, $mock);

        $this->actingAs($user);

        $response = $this->post(route('plan-next-week.finalize'));

        $response->assertRedirect(route('dashboard', ['week' => 1]))
            ->assertSessionHas('success');

        Carbon::setTestNow();
    }
}
