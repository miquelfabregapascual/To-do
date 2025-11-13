<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BacklogTriageTest extends TestCase
{
    use RefreshDatabase;

    public function test_backlog_view_lists_only_backlog_tasks(): void
    {
        $user = User::factory()->create();
        $backlogTask = Task::factory()->for($user)->create([
            'title' => 'Revisar research',
            'stage' => Task::STAGE_BACKLOG,
            'completed' => false,
            'due_date' => null,
        ]);

        Task::factory()->for($user)->inbox()->create([
            'title' => 'Preparar demo',
            'due_date' => null,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('backlog'));

        $response->assertOk()
            ->assertViewIs('tasks.backlog')
            ->assertViewHas('tasks', function ($tasks) use ($backlogTask) {
                return $tasks->count() === 1 && $tasks->first()->is($backlogTask);
            })
            ->assertSee('Revisar research')
            ->assertDontSee('Preparar demo');
    }

    public function test_triage_update_allows_priority_labels_and_stage_changes(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create([
            'stage' => Task::STAGE_BACKLOG,
            'priority' => null,
            'labels' => null,
        ]);

        $this->actingAs($user);

        $response = $this->patchJson(route('tasks.triage', $task), [
            'stage' => Task::STAGE_INBOX,
            'priority' => 2,
            'labels' => ['product', 'focus'],
        ]);

        $response->assertOk()
            ->assertJsonPath('task.stage', Task::STAGE_INBOX)
            ->assertJsonPath('task.priority', 2)
            ->assertJsonPath('task.labels', ['product', 'focus']);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'stage' => Task::STAGE_INBOX,
            'priority' => 2,
        ]);
    }

    public function test_scheduling_from_backlog_moves_task_into_inbox(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-03-24 09:00:00'));

        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create([
            'stage' => Task::STAGE_BACKLOG,
            'due_date' => null,
            'completed' => false,
        ]);

        $this->actingAs($user);

        $response = $this->postJson(route('planner.schedule'), [
            'task_id' => $task->id,
            'due_date' => '2025-03-26',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('task.stage', Task::STAGE_INBOX)
            ->assertJsonPath('task.due_date', '2025-03-26');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'stage' => Task::STAGE_INBOX,
            'due_date' => '2025-03-26',
        ]);

        Carbon::setTestNow();
    }
}
