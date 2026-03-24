<?php

namespace Tests\Feature;

use App\Livewire\Reports\WeeklyTasksOverview;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WeeklyTasksDeletionTest extends TestCase
{
    use RefreshDatabase;

    private function createTask(User $creator, string $status = 'Ждет подтверждения'): Task
    {
        return Task::create([
            'creator_id' => $creator->id,
            'user_id' => $creator->id,
            'sector_id' => $creator->sector_id,
            'type_id' => 1,
            'priority_id' => 1,
            'score_id' => 1,
            'name' => 'Task pending confirmation',
            'description' => 'Test description',
            'deadline' => now()->startOfWeek()->addDays(2)->format('Y-m-d'),
            'status' => $status,
            'overdue' => 0,
        ]);
    }

    public function test_deputy_can_delete_task_with_pending_confirmation_status(): void
    {
        $this->seed();

        $deputyRoleId = \App\Models\Role::where('name', 'Заместитель директора')->first()->id;
        $deputy = User::factory()->create(['role_id' => $deputyRoleId]);
        $creator = User::where('role_id', 2)->first();

        $this->actingAs($deputy);

        $task = $this->createTask($creator);

        Livewire::test(WeeklyTasksOverview::class)
            ->call('deleteTask', $task->id)
            ->assertDispatched('toastr:success');

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_creator_can_delete_own_task_with_pending_confirmation_status(): void
    {
        $this->seed();

        $creator = User::where('role_id', 2)->first();

        $this->actingAs($creator);

        $task = $this->createTask($creator);

        Livewire::test(WeeklyTasksOverview::class)
            ->call('deleteTask', $task->id)
            ->assertDispatched('toastr:success');

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_non_deputy_non_creator_cannot_delete_task(): void
    {
        $this->seed();

        $otherUser = User::factory()->create(['role_id' => 2]);
        $creator = User::where('role_id', 4)->first();

        $this->actingAs($otherUser);

        $task = $this->createTask($creator);

        Livewire::test(WeeklyTasksOverview::class)
            ->call('deleteTask', $task->id)
            ->assertNotDispatched('toastr:success');

        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }
}
