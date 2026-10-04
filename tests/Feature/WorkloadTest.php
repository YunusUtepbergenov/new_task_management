<?php

namespace Tests\Feature;

use App\Models\MailDeadline;
use App\Models\MailItem;
use App\Models\Task;
use App\Models\User;
use App\Services\WorkloadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function taskFor(User $user, array $overrides = []): Task
    {
        return Task::create(array_merge([
            'creator_id' => $user->id,
            'user_id' => $user->id,
            'sector_id' => $user->sector_id,
            'type_id' => 1,
            'priority_id' => 1,
            'score_id' => 1,
            'name' => 'Workload task',
            'description' => 'Description',
            'deadline' => today()->addDays(10)->toDateString(),
            'status' => 'Выполняется',
            'overdue' => 0,
        ], $overrides));
    }

    /**
     * @param  list<array<string, mixed>>  $deadlines
     */
    private function mailItemFor(User $user, bool $isMain, array $deadlines): MailItem
    {
        $item = MailItem::factory()->create(['content' => 'Edo item']);
        $item->executors()->attach($user->id, ['is_main' => $isMain, 'sector_id' => $user->sector_id]);

        foreach ($deadlines as $deadline) {
            MailDeadline::factory()->for($item, 'item')->create($deadline);
        }

        return $item;
    }

    /**
     * @return array<string, mixed>
     */
    private function employeeRow(User $user): array
    {
        $overview = app(WorkloadService::class)->overview();

        return collect($overview['sectors'])->flatMap(fn (array $sector): array => $sector['employees'])->firstWhere('id', $user->id);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('workload'))->assertRedirect(route('login'));
    }

    public function test_every_prototype_renders_and_unknown_variants_fall_back_to_the_table(): void
    {
        $director = User::factory()->director()->create();
        $employee = User::factory()->create(['sector_id' => 2, 'name' => 'Каримов Жасур']);
        $this->taskFor($employee, ['name' => 'Макроиқтисодий шарҳ']);
        $this->mailItemFor($employee, true, [['deadline' => today()->addDays(3)->toDateString()]]);

        foreach (['a', 'b', 'c'] as $variant) {
            $this->actingAs($director)
                ->get(route('workload', ['v' => $variant]))
                ->assertOk()
                ->assertViewHas('variant', $variant)
                ->assertSee('Каримов Жасур')
                ->assertSee('Макроиқтисодий шарҳ')
                ->assertSee('Edo item');
        }

        $this->actingAs($director)->get(route('workload', ['v' => 'zzz']))->assertViewHas('variant', 'a');
    }

    public function test_tasks_are_split_into_active_overdue_and_awaiting_confirmation(): void
    {
        $employee = User::factory()->create(['sector_id' => 2]);
        $this->taskFor($employee, ['deadline' => today()->subDays(4)->toDateString()]);
        $this->taskFor($employee, ['deadline' => today()->addDays(2)->toDateString(), 'status' => 'Не прочитано']);
        $this->taskFor($employee, ['status' => 'Ждет подтверждения']);
        $this->taskFor($employee, ['status' => 'Выполнено']);
        $this->taskFor($employee, [
            'deadline' => today()->subDays(20)->toDateString(),
            'extended_deadline' => today()->addDays(20)->toDateTimeString(),
            'status' => 'Дорабатывается',
        ]);

        $row = $this->employeeRow($employee);

        $this->assertSame(3, $row['counts']['tasks']);
        $this->assertSame(1, $row['counts']['tasks_overdue']);
        $this->assertSame(1, $row['counts']['tasks_review']);
        $this->assertSame(1, $row['counts']['due_week']);
        $this->assertSame(3, $row['counts']['load']);
        $this->assertSame(4, $row['worst_overdue']);
        $this->assertSame('medium', $row['level']);
        $this->assertSame(['tasks' => 1, 'mails' => 0], $row['buckets']['overdue']);
        $this->assertSame(['tasks' => 1, 'mails' => 0], $row['buckets']['month']);
        $this->assertCount(4, $row['tasks']);
    }

    public function test_edo_items_use_the_nearest_open_deadline_and_keep_the_executor_role(): void
    {
        $employee = User::factory()->create(['sector_id' => 2]);

        $this->mailItemFor($employee, true, [
            ['deadline' => today()->subDays(30)->toDateString(), 'status' => MailDeadline::STATUS_DONE],
            ['deadline' => today()->subDays(5)->toDateString()],
            ['deadline' => today()->addMonth()->toDateString()],
        ]);
        $this->mailItemFor($employee, false, [['deadline' => today()->addDays(10)->toDateString()]]);
        $this->mailItemFor($employee, false, [['deadline' => today()->addDays(1)->toDateString(), 'status' => MailDeadline::STATUS_IN_REVIEW]]);
        $this->mailItemFor($employee, true, [['deadline' => today()->subDay()->toDateString(), 'status' => MailDeadline::STATUS_DONE]]);

        $row = $this->employeeRow($employee);

        $this->assertSame(2, $row['counts']['mails']);
        $this->assertSame(1, $row['counts']['mails_overdue']);
        $this->assertSame(1, $row['counts']['mails_review']);
        $this->assertSame(1, $row['counts']['mails_main']);
        $this->assertCount(3, $row['mails']);

        $overdueItem = $row['mails'][0];
        $this->assertSame(-5, $overdueItem['days_left']);
        $this->assertSame(2, $overdueItem['open_deadlines']);
        $this->assertTrue($overdueItem['main']);
        $this->assertSame('overdue', $overdueItem['bucket']);
    }

    public function test_level_combines_internal_tasks_and_edo_items(): void
    {
        $expectedLevels = [
            'idle' => [0, 0],
            'low' => [1, 1],
            'medium' => [3, 2],
            'high' => [4, 2],
        ];

        foreach ($expectedLevels as $level => [$taskCount, $mailCount]) {
            $employee = User::factory()->create(['sector_id' => 2]);

            for ($index = 0; $index < $taskCount; $index++) {
                $this->taskFor($employee);
            }
            for ($index = 0; $index < $mailCount; $index++) {
                $this->mailItemFor($employee, true, [['deadline' => today()->addWeek()->toDateString()]]);
            }

            $row = $this->employeeRow($employee);

            $this->assertSame($taskCount + $mailCount, $row['counts']['load']);
            $this->assertSame($level, $row['level'], "{$taskCount} tasks + {$mailCount} edo items");
        }
    }

    public function test_heavy_load_is_flagged_and_counted_in_totals(): void
    {
        $busy = User::factory()->create(['sector_id' => 3]);
        foreach (range(1, WorkloadService::HIGH_LOAD) as $index) {
            $this->taskFor($busy);
        }

        $overview = app(WorkloadService::class)->overview();
        $sector = collect($overview['sectors'])->firstWhere('id', 3);

        $this->assertSame('high', $this->employeeRow($busy)['level']);
        $this->assertGreaterThanOrEqual(1, $overview['totals']['high']);
        $this->assertSame(WorkloadService::HIGH_LOAD, $sector['totals']['tasks']);
    }

    public function test_employees_who_left_and_other_departments_are_not_listed(): void
    {
        $left = User::factory()->create(['sector_id' => 2, 'leave' => 1]);
        $management = User::factory()->create(['sector_id' => 1]);

        $ids = collect(app(WorkloadService::class)->overview()['sectors'])
            ->flatMap(fn (array $sector): array => $sector['employees'])
            ->pluck('id');

        $this->assertNotContains($left->id, $ids);
        $this->assertNotContains($management->id, $ids);
    }
}
