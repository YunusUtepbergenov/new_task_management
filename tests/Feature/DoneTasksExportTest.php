<?php

namespace Tests\Feature;

use App\Exports\DoneTasksExport;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class DoneTasksExportTest extends TestCase
{
    use RefreshDatabase;

    private function createDeputy(): User
    {
        return User::factory()->create(['role_id' => 14, 'sector_id' => 1]);
    }

    public function test_export_includes_done_and_awaiting_confirmation_tasks_within_range(): void
    {
        $this->seed();

        $worker = User::where('role_id', 3)->first();

        $done = Task::factory()->create([
            'user_id' => $worker->id,
            'sector_id' => $worker->sector_id,
            'project_id' => null,
            'score_id' => null,
            'status' => 'Выполнено',
            'deadline' => '2026-03-15',
            'name' => 'Done task',
        ]);

        $awaiting = Task::factory()->create([
            'user_id' => $worker->id,
            'sector_id' => $worker->sector_id,
            'project_id' => null,
            'score_id' => null,
            'status' => 'Ждет подтверждения',
            'deadline' => '2026-03-15',
            'name' => 'Awaiting task',
        ]);

        // Excluded: in range but a status we do not report on.
        Task::factory()->create([
            'user_id' => $worker->id,
            'sector_id' => $worker->sector_id,
            'project_id' => null,
            'score_id' => null,
            'status' => 'Выполняется',
            'deadline' => '2026-03-15',
            'name' => 'In progress task',
        ]);

        // Excluded: reported status but outside the range.
        Task::factory()->create([
            'user_id' => $worker->id,
            'sector_id' => $worker->sector_id,
            'project_id' => null,
            'score_id' => null,
            'status' => 'Выполнено',
            'deadline' => '2025-06-15',
            'name' => 'Out of range task',
        ]);

        $tasks = (new DoneTasksExport('2026-01-15', '2026-07-15'))
            ->view()
            ->getData()['tasks'];

        $this->assertCount(2, $tasks);
        $this->assertTrue($tasks->contains('id', $done->id));
        $this->assertTrue($tasks->contains('id', $awaiting->id));
    }

    public function test_grouped_tasks_are_collapsed_into_a_single_row(): void
    {
        $this->seed();

        $workers = User::where('role_id', 3)->take(3)->get();
        $groupId = (string) Str::uuid();

        foreach ($workers as $worker) {
            Task::factory()->create([
                'user_id' => $worker->id,
                'sector_id' => $workers[0]->sector_id,
                'project_id' => null,
                'score_id' => null,
                'status' => 'Выполнено',
                'deadline' => '2026-03-15',
                'name' => 'Grouped task',
                'group_id' => $groupId,
            ]);
        }

        $tasks = (new DoneTasksExport('2026-01-15', '2026-07-15'))
            ->view()
            ->getData()['tasks'];

        $this->assertCount(1, $tasks);

        $row = $tasks->first();
        foreach ($workers as $worker) {
            $this->assertStringContainsString($worker->name, $row->merged_responsibles);
        }
    }

    public function test_download_route_returns_the_export_file(): void
    {
        $this->seed();

        Excel::fake();

        $this->actingAs($this->createDeputy())
            ->get(route('download.done_tasks', ['2026-01-15', '2026-07-15']))
            ->assertOk();

        Excel::assertDownloaded(
            'done_tasks_2026-01-15_2026-07-15.xlsx',
            fn (DoneTasksExport $export): bool => true
        );
    }

    public function test_any_authenticated_employee_can_download_the_export(): void
    {
        $this->seed();

        Excel::fake();

        $this->actingAs(User::factory()->create(['role_id' => 3, 'sector_id' => 1]))
            ->get(route('download.done_tasks', ['2026-01-15', '2026-07-15']))
            ->assertOk();

        Excel::assertDownloaded('done_tasks_2026-01-15_2026-07-15.xlsx');
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('download.done_tasks', ['2026-01-15', '2026-07-15']))
            ->assertRedirect(route('login'));
    }
}
