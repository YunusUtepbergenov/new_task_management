<?php

namespace Tests\Feature;

use App\Livewire\Reports\WeeklyTasksOverview;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Livewire\LivewireManager;
use Tests\TestCase;

class WeeklyTasksOverviewTest extends TestCase
{
    use RefreshDatabase;

    private function createTask(User $creator, array $overrides = []): Task
    {
        return Task::create(array_merge([
            'creator_id' => $creator->id,
            'user_id' => $creator->id,
            'sector_id' => $creator->sector_id,
            'type_id' => 1,
            'priority_id' => 1,
            'score_id' => 1,
            'name' => 'Test task',
            'description' => 'Test description',
            'deadline' => now()->format('Y-m-d'),
            'status' => 'Не прочитано',
            'overdue' => 0,
        ], $overrides));
    }

    public function test_shows_tasks_for_current_week(): void
    {
        $this->seed();
        app(LivewireManager::class)->withoutLazyLoading();

        $user = User::where('role_id', 2)->first();
        $this->actingAs($user);

        $task = $this->createTask($user, [
            'name' => 'Current week task',
            'deadline' => now()->startOfWeek()->addDay()->format('Y-m-d'),
            'status' => 'Выполняется',
        ]);

        Livewire::test(WeeklyTasksOverview::class)
            ->assertSee('Current week task');
    }

    public function test_shows_previously_incomplete_tasks_in_current_week(): void
    {
        $this->seed();
        app(LivewireManager::class)->withoutLazyLoading();

        $user = User::where('role_id', 2)->first();
        $this->actingAs($user);

        $unread = $this->createTask($user, [
            'name' => 'Old unread task',
            'deadline' => now()->subWeeks(2)->format('Y-m-d'),
            'status' => 'Не прочитано',
        ]);

        $inProgress = $this->createTask($user, [
            'name' => 'Old in-progress task',
            'deadline' => now()->subWeeks(3)->format('Y-m-d'),
            'status' => 'Выполняется',
        ]);

        $revision = $this->createTask($user, [
            'name' => 'Old revision task',
            'deadline' => now()->subWeek()->format('Y-m-d'),
            'status' => 'Дорабатывается',
        ]);

        Livewire::test(WeeklyTasksOverview::class)
            ->assertSee('Old unread task')
            ->assertSee('Old in-progress task')
            ->assertSee('Old revision task');
    }

    public function test_does_not_show_previously_completed_tasks(): void
    {
        $this->seed();
        app(LivewireManager::class)->withoutLazyLoading();

        $user = User::where('role_id', 2)->first();
        $this->actingAs($user);

        $completed = $this->createTask($user, [
            'name' => 'Old completed task',
            'deadline' => now()->subWeeks(2)->format('Y-m-d'),
            'status' => 'Выполнено',
        ]);

        $waiting = $this->createTask($user, [
            'name' => 'Old waiting task',
            'deadline' => now()->subWeeks(2)->format('Y-m-d'),
            'status' => 'Ждет подтверждения',
        ]);

        Livewire::test(WeeklyTasksOverview::class)
            ->assertDontSee('Old completed task')
            ->assertDontSee('Old waiting task');
    }
}
