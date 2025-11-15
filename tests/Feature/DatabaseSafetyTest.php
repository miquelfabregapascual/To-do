<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_sessions_table_exists_after_migration(): void
    {
        $this->assertTrue(Schema::hasTable('sessions'));
    }

    public function test_user_tasks_are_deleted_when_user_is_removed(): void
    {
        $user = User::factory()
            ->has(Task::factory()->count(2))
            ->create();

        $this->assertDatabaseCount('tasks', 2);

        $user->delete();

        $this->assertDatabaseCount('tasks', 0);
    }
}
