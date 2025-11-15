<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TaskManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_task(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('tasks.store'), [
            'title' => 'Prepare interview notes',
            'description' => 'Summarize topics for the upcoming call',
            'due_date' => Carbon::tomorrow()->toDateString(),
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'title' => 'Prepare interview notes',
            'completed' => false,
        ]);
    }

    public function test_user_can_toggle_completion_state_for_own_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->incomplete()->create();

        $this->actingAs($user);

        $response = $this->patch(route('tasks.toggle', $task));

        $response->assertRedirect();

        $this->assertTrue($task->fresh()->completed);
    }

    public function test_user_cannot_toggle_task_they_do_not_own(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $task = Task::factory()->for($owner)->incomplete()->create();

        $this->actingAs($otherUser);

        $response = $this->patch(route('tasks.toggle', $task));

        $response->assertForbidden();
        $this->assertFalse($task->fresh()->completed);
    }

    public function test_user_can_delete_their_own_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $this->actingAs($user);

        $response = $this->delete(route('tasks.destroy', $task));

        $response->assertRedirect();
        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }

    public function test_user_cannot_delete_someone_elses_task(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $task = Task::factory()->for($owner)->create();

        $this->actingAs($otherUser);

        $response = $this->delete(route('tasks.destroy', $task));

        $response->assertForbidden();
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
        ]);
    }
}
