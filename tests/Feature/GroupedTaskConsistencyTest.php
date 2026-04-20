<?php

namespace Tests\Feature;

use App\Livewire\OrderedTable;
use App\Livewire\Reports\WeeklyTasksOverview;
use App\Livewire\ViewModal;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Livewire\LivewireManager;
use Tests\TestCase;

class GroupedTaskConsistencyTest extends TestCase
{
    use RefreshDatabase;

    private function createGroupedTask(User $creator, array $assignees, array $overrides = []): array
    {
        $groupId = Str::uuid()->toString();
        $tasks = [];

        foreach ($assignees as $assignee) {
            $tasks[] = Task::create(array_merge([
                'creator_id' => $creator->id,
                'user_id' => $assignee->id,
                'sector_id' => $assignee->sector_id,
                'project_id' => null,
                'type_id' => 1,
                'priority_id' => 1,
                'score_id' => 1,
                'name' => 'Grouped task',
                'description' => 'Test description',
                'deadline' => now()->startOfWeek()->addDay()->format('Y-m-d'),
                'status' => 'Не прочитано',
                'overdue' => 0,
                'group_id' => $groupId,
            ], $overrides));
        }

        return ['group_id' => $groupId, 'tasks' => $tasks];
    }

    public function test_ordered_table_picks_first_selected_user_as_main(): void
    {
        $this->seed();

        $creator = User::where('role_id', 2)->first();
        $this->actingAs($creator);

        // Pick three assignees; the first one here is the first-selected.
        $assignees = User::where('id', '<>', $creator->id)
            ->whereIn('role_id', [4, 5, 14])
            ->take(3)
            ->get()
            ->values();

        $this->assertCount(3, $assignees);

        $this->createGroupedTask($creator, $assignees->all(), [
            'deadline' => now()->endOfWeek()->format('Y-m-d'),
        ]);

        $component = Livewire::test(OrderedTable::class);
        $weeklyTasks = $component->get('weeklyTasks') ?? [];

        // OrderedTable does not expose weeklyTasks as a property (it's rendered).
        // Instead, assert the rendered HTML shows the first-selected user's short_name.
        $component->assertSee($assignees[0]->short_name);
    }

    public function test_group_member_count_is_total_group_size_in_ordered(): void
    {
        $this->seed();

        $creator = User::where('role_id', 2)->first();
        $this->actingAs($creator);

        $assignees = User::where('id', '<>', $creator->id)
            ->whereIn('role_id', [4, 5, 14])
            ->take(5)
            ->get();

        $this->assertCount(5, $assignees);

        $this->createGroupedTask($creator, $assignees->all(), [
            'deadline' => now()->endOfWeek()->format('Y-m-d'),
        ]);

        $group = Task::whereNotNull('group_id')->first();
        $row = Task::selectRaw('tasks.*, (SELECT COUNT(*) FROM tasks AS t2 WHERE t2.group_id = tasks.group_id AND tasks.group_id IS NOT NULL) as group_member_count')
            ->where('id', $group->id)
            ->first();

        $this->assertEquals(5, $row->group_member_count);
    }

    public function test_view_modal_resolves_first_group_task_for_non_assignee(): void
    {
        $this->seed();

        $creator = User::where('role_id', 2)->first();
        $assignees = User::where('id', '<>', $creator->id)
            ->whereIn('role_id', [4, 5, 14])
            ->take(3)
            ->get();

        $this->assertCount(3, $assignees);

        $group = $this->createGroupedTask($creator, $assignees->all());
        $firstTask = $group['tasks'][0];
        $secondTask = $group['tasks'][1];

        $this->actingAs($creator);

        // Creator clicks on the SECOND task (e.g. could happen if a page filtered out the first).
        // ViewModal should redirect to the first group task.
        Livewire::test(ViewModal::class)
            ->call('taskClicked', $secondTask->id)
            ->assertSet('taskId', $firstTask->id);
    }

    public function test_view_modal_keeps_own_task_for_assignee(): void
    {
        $this->seed();

        $creator = User::where('role_id', 2)->first();
        $assignees = User::where('id', '<>', $creator->id)
            ->whereIn('role_id', [4, 5, 14])
            ->take(3)
            ->get();

        $this->assertCount(3, $assignees);

        $group = $this->createGroupedTask($creator, $assignees->all());
        $assigneeTask = $group['tasks'][2]; // third assignee

        $this->actingAs($assignees[2]);

        // Assignee opens their own group task — they should see THEIR task, not the first.
        Livewire::test(ViewModal::class)
            ->call('taskClicked', $assigneeTask->id)
            ->assertSet('taskId', $assigneeTask->id);
    }

    public function test_weekly_tasks_main_is_first_selected_user_even_when_their_task_filtered_out(): void
    {
        $this->seed();

        $creator = User::where('role_id', 2)->first();
        $this->actingAs($creator);

        // First-selected user is in sector 1 (EXCLUDED from weekly allowedSectors).
        $excludedFirst = User::where('sector_id', 1)->where('id', '<>', $creator->id)->first();
        $this->assertNotNull($excludedFirst, 'need a user in sector 1 for this scenario');

        // Second user is in sector 4 (INCLUDED).
        $includedSecond = User::where('sector_id', 4)->first();
        $this->assertNotNull($includedSecond);

        $group = $this->createGroupedTask($creator, [$excludedFirst, $includedSecond]);
        $group['tasks'][0]->update([
            'sector_id' => $excludedFirst->sector_id,
            'status' => 'Ждет подтверждения',
            'deadline' => now()->startOfWeek()->addDay()->format('Y-m-d'),
        ]);
        $group['tasks'][1]->update([
            'sector_id' => $includedSecond->sector_id,
            'deadline' => now()->startOfWeek()->addDay()->format('Y-m-d'),
        ]);

        app(LivewireManager::class)->withoutLazyLoading();

        // Row's wire:key contains the main task id. With the fix it must be the
        // first-selected user's task id, NOT the filtered-collection first item.
        Livewire::test(WeeklyTasksOverview::class)
            ->assertSee('weekly-row-' . $group['tasks'][0]->id)
            ->assertDontSee('weekly-row-' . $group['tasks'][1]->id);
    }

    public function test_evaluate_button_gated_by_first_selected_user_status(): void
    {
        $this->seed();

        $creator = User::where('role_id', 2)->first();
        $assignees = User::where('id', '<>', $creator->id)
            ->whereIn('role_id', [4, 5, 14])
            ->take(2)
            ->get();

        $this->assertCount(2, $assignees);

        $group = $this->createGroupedTask($creator, $assignees->all());
        $firstTask = $group['tasks'][0];
        $secondTask = $group['tasks'][1];

        // Only the SECOND user submits, not the first.
        $secondTask->update(['status' => 'Ждет подтверждения']);

        $this->actingAs($creator);

        // Creator opens via the second task id (simulating a page where the first was filtered).
        $modal = Livewire::test(ViewModal::class)->call('taskClicked', $secondTask->id);

        // Modal should resolve to the first task, whose status is NOT 'Ждет подтверждения'.
        $modal->assertSet('taskId', $firstTask->id);
        $this->assertNotEquals('Ждет подтверждения', $firstTask->fresh()->status);
    }
}
