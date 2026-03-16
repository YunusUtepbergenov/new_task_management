<?php

namespace Tests\Feature;

use App\Livewire\ViewModal;
use App\Models\Response;
use App\Models\Task;
use App\Models\TaskLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TaskLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_log_is_created_when_task_status_changes_on_click(): void
    {
        $this->seed();

        $creator = User::where('role_id', 2)->first();
        $assignee = User::where('role_id', 5)->first();

        $task = Task::create([
            'creator_id' => $creator->id,
            'user_id' => $assignee->id,
            'sector_id' => $assignee->sector_id,
            'name' => 'Test task for log',
            'description' => '',
            'deadline' => now()->addWeek()->format('Y-m-d'),
            'status' => 'Не прочитано',
            'overdue' => 0,
        ]);

        $this->actingAs($assignee);

        Livewire::test(ViewModal::class)
            ->call('taskClicked', $task->id);

        $this->assertDatabaseHas('task_logs', [
            'task_id' => $task->id,
            'user_id' => $assignee->id,
            'action' => 'status_changed',
        ]);
    }

    public function test_task_log_is_created_on_submit(): void
    {
        $this->seed();

        $creator = User::where('role_id', 2)->first();
        $assignee = User::where('role_id', 5)->first();

        $task = Task::create([
            'creator_id' => $creator->id,
            'user_id' => $assignee->id,
            'sector_id' => $assignee->sector_id,
            'name' => 'Test submit log',
            'description' => '',
            'deadline' => now()->addWeek()->format('Y-m-d'),
            'status' => 'Выполняется',
            'overdue' => 0,
        ]);

        $this->actingAs($assignee);

        Livewire::test(ViewModal::class)
            ->call('taskClicked', $task->id)
            ->set('description', 'Work completed')
            ->call('storeResponse');

        $this->assertDatabaseHas('task_logs', [
            'task_id' => $task->id,
            'user_id' => $assignee->id,
            'action' => 'submitted',
        ]);
    }

    public function test_task_log_is_created_on_reject(): void
    {
        $this->seed();

        $creator = User::where('role_id', 2)->first();
        $assignee = User::where('role_id', 5)->first();

        $task = Task::create([
            'creator_id' => $creator->id,
            'user_id' => $assignee->id,
            'sector_id' => $assignee->sector_id,
            'name' => 'Test reject log',
            'description' => '',
            'deadline' => now()->addWeek()->format('Y-m-d'),
            'status' => 'Ждет подтверждения',
            'overdue' => 0,
        ]);

        Response::create([
            'task_id' => $task->id,
            'user_id' => $assignee->id,
            'description' => 'Done',
        ]);

        $this->actingAs($creator);

        Livewire::test(ViewModal::class)
            ->call('taskClicked', $task->id)
            ->call('taskRejected', $task->id);

        $this->assertDatabaseHas('task_logs', [
            'task_id' => $task->id,
            'user_id' => $creator->id,
            'action' => 'rejected',
        ]);
    }

    public function test_task_logs_are_cascade_deleted_with_task(): void
    {
        $this->seed();

        $user = User::where('role_id', 2)->first();

        $task = Task::create([
            'creator_id' => $user->id,
            'user_id' => $user->id,
            'sector_id' => $user->sector_id,
            'name' => 'Cascade delete test',
            'description' => '',
            'deadline' => now()->addWeek()->format('Y-m-d'),
            'status' => 'Не прочитано',
            'overdue' => 0,
        ]);

        TaskLog::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'action' => 'created',
            'description' => 'Задача создана',
        ]);

        $this->assertDatabaseHas('task_logs', ['task_id' => $task->id]);

        $task->delete();

        $this->assertDatabaseMissing('task_logs', ['task_id' => $task->id]);
    }

    public function test_task_log_model_has_correct_relationships(): void
    {
        $this->seed();

        $user = User::first();

        $task = Task::create([
            'creator_id' => $user->id,
            'user_id' => $user->id,
            'sector_id' => $user->sector_id,
            'name' => 'Relationship test',
            'description' => '',
            'deadline' => now()->addWeek()->format('Y-m-d'),
            'status' => 'Не прочитано',
            'overdue' => 0,
        ]);

        $log = TaskLog::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'action' => 'created',
            'description' => 'Задача создана',
        ]);

        $this->assertEquals($task->id, $log->task->id);
        $this->assertEquals($user->id, $log->user->id);
        $this->assertTrue($task->logs->contains($log));
    }
}
