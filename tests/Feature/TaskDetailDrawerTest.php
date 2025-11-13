<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskDetailDrawerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_task_detail_payload(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create([
            'title' => 'Preparar informe',
            'description' => 'Notas iniciales',
            'priority' => Task::PRIORITY_P2,
            'labels' => ['work', 'report'],
            'estimate_minutes' => 90,
            'due_date' => now()->addDay()->toDateString(),
            'subtasks' => [
                ['title' => 'Borrador', 'completed' => false],
                ['title' => 'Revisión', 'completed' => true],
            ],
        ]);

        $response = $this->actingAs($user)->getJson(route('tasks.detail', $task));

        $response->assertOk()
            ->assertJsonPath('task.title', 'Preparar informe')
            ->assertJsonPath('task.priority', Task::PRIORITY_P2)
            ->assertJsonPath('task.labels', ['work', 'report'])
            ->assertJsonPath('task.estimate_minutes', 90)
            ->assertJsonPath('task.subtasks.0.title', 'Borrador');
    }

    public function test_it_updates_task_detail_and_schedules(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create([
            'title' => 'Escribir artículo',
            'description' => 'Borrador inicial',
        ]);

        $payload = [
            'title' => 'Escribir artículo largo',
            'description' => '## Nueva estructura',
            'priority' => Task::PRIORITY_P1,
            'labels' => ['writing', 'focus'],
            'estimate_minutes' => 120,
            'due_date' => now()->addDays(2)->toDateString(),
            'subtasks' => [
                ['title' => 'Outline', 'completed' => true],
                ['title' => 'Redacción', 'completed' => false],
            ],
        ];

        $response = $this->actingAs($user)->patchJson(route('tasks.detail.update', $task), $payload);

        $response->assertOk()
            ->assertJsonPath('task.title', 'Escribir artículo largo')
            ->assertJsonPath('task.priority', Task::PRIORITY_P1)
            ->assertJsonPath('task.due_date', $payload['due_date']);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Escribir artículo largo',
            'priority' => Task::PRIORITY_P1,
            'estimate_minutes' => 120,
            'stage' => Task::STAGE_INBOX,
        ]);
    }
}
