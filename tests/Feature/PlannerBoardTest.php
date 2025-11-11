<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PlannerBoardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_week_grid_and_backlog(): void
    {
        $user = User::factory()->create();

        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $inWeekTask = Task::factory()->for($user)->incomplete()->create([
            'due_date' => $startOfWeek->copy()->addDays(2)->toDateString(),
        ]);
        $backlogTask = Task::factory()->for($user)->incomplete()->create([
            'due_date' => null,
        ]);
        Task::factory()->for($user)->incomplete()->create([
            'due_date' => $startOfWeek->copy()->subDays(3)->toDateString(),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertViewIs('dashboard')
            ->assertViewHas('backlog', fn ($tasks) => $tasks->contains($backlogTask))
            ->assertSee($inWeekTask->title)
            ->assertSee($backlogTask->title);
    }

    public function test_backlog_task_can_be_scheduled_from_board(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->incomplete()->create([
            'due_date' => null,
        ]);

        $this->actingAs($user);

        $targetDate = Carbon::today()->addDay()->toDateString();

        $response = $this->postJson(route('planner.schedule'), [
            'task_id' => $task->id,
            'due_date' => $targetDate,
        ]);

        $response->assertOk();
        $this->assertSame($targetDate, $task->fresh()->due_date->toDateString());
    }

    public function test_scheduled_task_can_return_to_backlog(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->incomplete()->create([
            'due_date' => Carbon::today()->addDay()->toDateString(),
        ]);

        $this->actingAs($user);

        $response = $this->postJson(route('planner.schedule'), [
            'task_id' => $task->id,
            'due_date' => null,
        ]);

        $response->assertOk();
        $this->assertNull($task->fresh()->due_date);
    }
}
