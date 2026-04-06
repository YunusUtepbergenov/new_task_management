<?php

namespace Tests\Feature;

use App\Livewire\EditTaskModal;
use App\Livewire\TasksTable;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HomepageTaskActionsTest extends TestCase
{
    use RefreshDatabase;

    private function createTask(User $creator, User $assignee, string $status = 'Выполняется'): Task
    {
        return Task::create([
            'creator_id' => $creator->id,
            'user_id' => $assignee->id,
            'sector_id' => $assignee->sector_id,
            'type_id' => 1,
            'priority_id' => 1,
            'score_id' => 1,
            'name' => 'Test task',
            'description' => 'Test description',
            'deadline' => now()->addDays(3)->format('Y-m-d'),
            'status' => $status,
            'overdue' => 0,
        ]);
    }

    public function test_homepage_includes_edit_task_modal(): void
    {
        $this->seed();

        $user = User::where('role_id', 2)->first();
        $this->actingAs($user);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSeeLivewire('edit-task-modal');
    }

    public function test_homepage_task_deletion_works(): void
    {
        $this->seed();

        $creator = User::where('role_id', 2)->first();
        $this->actingAs($creator);

        $task = $this->createTask($creator, $creator);

        Livewire::test(TasksTable::class)
            ->call('deleteTask', $task->id)
            ->assertDispatched('toastr:success');

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_homepage_task_edit_dispatches_event(): void
    {
        $this->seed();

        $creator = User::where('role_id', 2)->first();
        $this->actingAs($creator);

        $task = $this->createTask($creator, $creator);

        Livewire::test(EditTaskModal::class)
            ->call('loadTask', $task->id)
            ->assertDispatched('show-edit-modal')
            ->assertSet('taskId', $task->id)
            ->assertSet('name', 'Test task');
    }

    public function test_non_creator_cannot_delete_task_on_homepage(): void
    {
        $this->seed();

        $creator = User::where('role_id', 2)->first();
        $otherUser = User::factory()->create(['role_id' => 3]);
        $this->actingAs($otherUser);

        $task = $this->createTask($creator, $otherUser);

        Livewire::test(TasksTable::class)
            ->call('deleteTask', $task->id)
            ->assertNotDispatched('toastr:success');

        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    public function test_deputy_can_delete_task_on_homepage(): void
    {
        $this->seed();

        $deputyRoleId = \App\Models\Role::where('name', 'Заместитель директора')->first()->id;
        $deputy = User::factory()->create(['role_id' => $deputyRoleId]);
        $creator = User::where('role_id', 2)->first();
        $this->actingAs($deputy);

        $task = $this->createTask($creator, $creator);

        Livewire::test(TasksTable::class)
            ->call('deleteTask', $task->id)
            ->assertDispatched('toastr:success');

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}
