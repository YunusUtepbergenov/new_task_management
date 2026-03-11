<?php

namespace Tests\Feature;

use App\Livewire\Archive;
use App\Models\Scores;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class ArchiveTest extends TestCase
{
    use RefreshDatabase;

    private function createDeputy(): User
    {
        return User::factory()->create(['role_id' => 14, 'sector_id' => 1]);
    }

    public function test_archive_page_accessible_to_deputy(): void
    {
        $this->seed();

        $deputy = $this->createDeputy();
        $this->actingAs($deputy);

        $response = $this->get('/archive');
        $response->assertStatus(200);
    }

    public function test_archive_page_accessible_to_sector_head(): void
    {
        $this->seed();

        $head = User::where('role_id', 2)->first();
        $this->actingAs($head);

        $response = $this->get('/archive');
        $response->assertStatus(200);
    }

    public function test_archive_page_redirects_researcher(): void
    {
        $this->seed();

        $researcher = User::where('role_id', 3)->first();
        $this->actingAs($researcher);

        $response = $this->get('/archive');
        $response->assertRedirect(route('home'));
    }

    public function test_archive_shows_all_tasks_on_load_with_no_filters(): void
    {
        $this->seed();

        $deputy = $this->createDeputy();

        $component = Livewire::actingAs($deputy)->test(Archive::class);

        $this->assertEmpty($component->get('month'));
        $this->assertNull($component->get('score_id'));
        $this->assertNull($component->get('user_id'));
    }

    public function test_archive_shows_only_finished_tasks(): void
    {
        $this->seed();

        $deputy = $this->createDeputy();

        Livewire::actingAs($deputy)
            ->test(Archive::class)
            ->assertDontSee('Выполняется');
    }

    public function test_archive_clear_filters_resets_all_filters(): void
    {
        $this->seed();

        $deputy = $this->createDeputy();
        $worker = User::where('role_id', 3)->first();
        $scoreId = DB::table('scores')->insertGetId(['name' => 'Test Score', 'max_score' => 10, 'limit' => 10]);

        Livewire::actingAs($deputy)
            ->test(Archive::class)
            ->set('month', '2024-01')
            ->set('score_id', $scoreId)
            ->set('user_id', $worker->id)
            ->call('clearFilters')
            ->assertSet('month', '')
            ->assertSet('score_id', null)
            ->assertSet('user_id', null);
    }

    public function test_archive_month_filter_works(): void
    {
        $this->seed();

        $deputy = $this->createDeputy();
        $worker = User::where('role_id', 3)->first();
        $scoreId = DB::table('scores')->insertGetId(['name' => 'Test Score', 'max_score' => 10, 'limit' => 10]);
        $score = Scores::find($scoreId);

        Task::factory()->create([
            'user_id' => $worker->id,
            'sector_id' => $worker->sector_id,
            'score_id' => $score->id,
            'project_id' => null,
            'status' => 'Выполнено',
            'deadline' => '2024-01-15',
            'name' => 'January Task',
        ]);

        Task::factory()->create([
            'user_id' => $worker->id,
            'sector_id' => $worker->sector_id,
            'score_id' => $score->id,
            'project_id' => null,
            'status' => 'Выполнено',
            'deadline' => '2024-02-15',
            'name' => 'February Task',
        ]);

        Livewire::actingAs($deputy)
            ->test(Archive::class)
            ->set('month', '2024-01')
            ->assertSee('January Task')
            ->assertDontSee('February Task');
    }

    public function test_archive_score_filter_works(): void
    {
        $this->seed();

        $deputy = $this->createDeputy();
        $worker = User::where('role_id', 3)->first();
        $scoreId1 = DB::table('scores')->insertGetId(['name' => 'Score Type A', 'max_score' => 10, 'limit' => 10]);
        $scoreId2 = DB::table('scores')->insertGetId(['name' => 'Score Type B', 'max_score' => 10, 'limit' => 10]);
        $scores = Scores::whereIn('id', [$scoreId1, $scoreId2])->get();

        Task::factory()->create([
            'user_id' => $worker->id,
            'sector_id' => $worker->sector_id,
            'score_id' => $scores[0]->id,
            'project_id' => null,
            'status' => 'Выполнено',
            'deadline' => now()->format('Y-m-15'),
            'name' => 'Score One Task',
        ]);

        Task::factory()->create([
            'user_id' => $worker->id,
            'sector_id' => $worker->sector_id,
            'score_id' => $scores[1]->id,
            'project_id' => null,
            'status' => 'Выполнено',
            'deadline' => now()->format('Y-m-15'),
            'name' => 'Score Two Task',
        ]);

        Livewire::actingAs($deputy)
            ->test(Archive::class)
            ->set('score_id', $scores[0]->id)
            ->assertSee('Score One Task')
            ->assertDontSee('Score Two Task');
    }

    public function test_archive_responsible_filter_works(): void
    {
        $this->seed();

        $deputy = $this->createDeputy();
        $workers = User::where('role_id', 3)->take(2)->get();
        $scoreId = DB::table('scores')->insertGetId(['name' => 'Test Score', 'max_score' => 10, 'limit' => 10]);
        $score = Scores::find($scoreId);

        Task::factory()->create([
            'user_id' => $workers[0]->id,
            'sector_id' => $workers[0]->sector_id,
            'score_id' => $score->id,
            'project_id' => null,
            'status' => 'Выполнено',
            'deadline' => now()->format('Y-m-15'),
            'name' => 'Worker One Task',
        ]);

        Task::factory()->create([
            'user_id' => $workers[1]->id,
            'sector_id' => $workers[1]->sector_id,
            'score_id' => $score->id,
            'project_id' => null,
            'status' => 'Выполнено',
            'deadline' => now()->format('Y-m-15'),
            'name' => 'Worker Two Task',
        ]);

        Livewire::actingAs($deputy)
            ->test(Archive::class)
            ->set('user_id', $workers[0]->id)
            ->assertSee('Worker One Task')
            ->assertDontSee('Worker Two Task');
    }

    public function test_head_only_sees_their_sector_workers_in_filter(): void
    {
        $this->seed();

        $head = User::where('role_id', 2)->first();

        $component = Livewire::actingAs($head)->test(Archive::class);

        foreach ($component->get('workers') as $worker) {
            $this->assertEquals($head->sector_id, User::find($worker['id'])->sector_id);
        }
    }

    public function test_deputy_sees_all_workers_in_filter(): void
    {
        $this->seed();

        $deputy = $this->createDeputy();
        $totalWorkers = User::where('leave', 0)->count();

        $component = Livewire::actingAs($deputy)->test(Archive::class);

        $this->assertCount($totalWorkers, $component->get('workers'));
    }
}
