<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TaskSectionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_inbox_shows_only_unscheduled_incomplete_tasks(): void
    {
        $user = User::factory()->create();
        $inboxTask = Task::factory()->for($user)->incomplete()->create([
            'due_date' => null,
        ]);
        Task::factory()->for($user)->incomplete()->create([
            'due_date' => Carbon::today()->toDateString(),
        ]);
        Task::factory()->for($user)->completed()->create([
            'due_date' => null,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('inbox'));

        $response->assertOk()
            ->assertViewIs('tasks.inbox')
            ->assertViewHas('unscheduled', fn ($tasks) => $tasks->contains($inboxTask) && $tasks->every(fn ($task) => $task->due_date === null && ! $task->completed))
            ->assertSee($inboxTask->title)
            ->assertDontSee('Completadas recientemente');
    }

    public function test_today_view_includes_overdue_and_today_tasks_only(): void
    {
        $user = User::factory()->create();
        $overdueTask = Task::factory()->for($user)->incomplete()->create([
            'due_date' => Carbon::yesterday()->toDateString(),
        ]);
        $todayTask = Task::factory()->for($user)->incomplete()->create([
            'due_date' => Carbon::today()->toDateString(),
        ]);
        Task::factory()->for($user)->incomplete()->create([
            'due_date' => Carbon::tomorrow()->toDateString(),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('today'));

        $response->assertOk()
            ->assertViewIs('tasks.today')
            ->assertViewHas('overdue', fn ($tasks) => $tasks->contains($overdueTask) && $tasks->every(fn ($task) => $task->due_date->isBefore(Carbon::today())))
            ->assertViewHas('today', fn ($tasks) => $tasks->contains($todayTask) && $tasks->every(fn ($task) => $task->due_date->isSameDay(Carbon::today())))
            ->assertSee($overdueTask->title)
            ->assertSee($todayTask->title)
            ->assertDontSee('No hay nada planificado para hoy.', false);
    }

    public function test_completed_groups_tasks_by_week(): void
    {
        $user = User::factory()->create();
        $thisWeek = Carbon::now()->startOfWeek();
        $lastWeek = (clone $thisWeek)->subWeek();

        $recentTask = Task::factory()->for($user)->completed()->create([
            'updated_at' => $thisWeek->copy()->addDay(),
        ]);
        $olderTask = Task::factory()->for($user)->completed()->create([
            'updated_at' => $lastWeek->copy()->addDay(),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('completed'));

        $response->assertOk()
            ->assertViewIs('tasks.completed')
            ->assertViewHas('completedGroups', function ($groups) use ($recentTask, $olderTask) {
                return $groups->flatten()->contains($recentTask) && $groups->flatten()->contains($olderTask);
            })
            ->assertSee('Semana del', false);
    }

    public function test_overview_includes_all_key_sections(): void
    {
        $user = User::factory()->create();
        $overdueTask = Task::factory()->for($user)->incomplete()->create([
            'due_date' => Carbon::yesterday()->toDateString(),
        ]);
        $upcomingTask = Task::factory()->for($user)->incomplete()->create([
            'due_date' => Carbon::today()->addDays(3)->toDateString(),
        ]);
        $unscheduledTask = Task::factory()->for($user)->incomplete()->create([
            'due_date' => null,
        ]);
        $completedTask = Task::factory()->for($user)->completed()->create();

        $this->actingAs($user);

        $response = $this->get(route('all'));

        $response->assertOk()
            ->assertViewIs('tasks.overview')
            ->assertViewHasAll([
                'overdue',
                'upcoming',
                'unscheduled',
                'recentlyCompleted',
            ])
            ->assertSee($overdueTask->title)
            ->assertSee($upcomingTask->title)
            ->assertSee($unscheduledTask->title)
            ->assertSee($completedTask->title);
    }
}
