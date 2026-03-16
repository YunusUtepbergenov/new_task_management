<?php

namespace Tests\Feature;

use App\Livewire\OrderedTable;
use App\Livewire\TasksTable;
use App\Livewire\ViewModal;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TasksTest extends TestCase
{
    use RefreshDatabase;

    public function test_tasks_page_is_working(): void
    {
        $this->seed();

        $response = $this->post('/login', [
            'email' => 'y.utepbergenov@cerr.uz',
            'password' => 'yu3667500',
        ]);

        $this->assertAuthenticated();

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('layout.css');
    }

    public function test_researcher_has_task_create_button(): void
    {
        $this->seed();

        $this->actingAs(User::where('role_id', 4)->first());
        $response = $this->get('/');

        $response->assertSee('Добавить Задачу');
    }

    public function test_ordinary_user_does_not_have_task_create_button(): void
    {
        $this->seed();

        $this->actingAs($user = User::where('role_id', 5)->first());
        $response = $this->get('/');

        $response->assertDontSee('Добавить Задачу');
    }

    public function test_ordered_page_is_working_for_heads(): void
    {
        $this->seed();

        $response = $this->post('/login', [
            'email' => 'o.khakimov@cerr.uz',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();

        $response = $this->get('/ordered');
        $response->assertStatus(200);
        $response->assertSee('layout.css');
    }

    public function test_ordered_page_redirects_ordinary_users(): void
    {
        $this->seed();

        $response = $this->post('/login', [
            'email' => 'y.utepbergenov@cerr.uz',
            'password' => 'yu3667500',
        ]);

        $this->assertAuthenticated();

        $response = $this->get('/ordered');

        $response->assertRedirect('/');
    }

    public function test_task_creation_is_working(): void
    {
        $this->seed();

        $this->actingAs($user = User::first());

        $task = Task::create([
            'creator_id' => $user->id,
            'user_id' => $user->id,
            'sector_id' => $user->sector_id,
            'project_id' => null,
            'type_id' => 1,
            'priority_id' => 1,
            'score_id' => 1,
            'name' => 'Test task',
            'description' => 'Test description',
            'deadline' => now()->addWeek()->format('Y-m-d'),
            'status' => 'Не прочитано',
            'overdue' => 0,
        ]);

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'name' => 'Test task']);
    }

    public function test_created_task_is_visible_for_user(): void
    {
        $this->seed();

        $this->actingAs($user = User::first());

        $task = Task::create([
            'creator_id' => $user->id,
            'user_id' => $user->id,
            'sector_id' => $user->sector_id,
            'project_id' => null,
            'type_id' => 1,
            'priority_id' => 1,
            'score_id' => 1,
            'name' => 'Test task visible',
            'description' => 'Test description',
            'deadline' => now()->addWeek()->format('Y-m-d'),
            'status' => 'Не прочитано',
            'overdue' => 0,
        ]);

        Livewire::test(TasksTable::class)
            ->assertSee('Test task visible');
    }

    public function test_view_task_modal_is_working(): void
    {
        $this->seed();

        $this->actingAs($user = User::first());
        $task = Task::create([
            'creator_id' => $user->id,
            'user_id' => $user->id,
            'sector_id' => $user->sector_id,
            'project_id' => null,
            'type_id' => 1,
            'priority_id' => 1,
            'score_id' => 1,
            'name' => 'Test task modal',
            'description' => 'Test description',
            'deadline' => now()->addWeek()->format('Y-m-d'),
            'status' => 'Не прочитано',
            'overdue' => 0,
        ]);

        Livewire::test(ViewModal::class)
            ->call('taskClicked', $task->id)
            ->assertDispatched('show-modal');
    }
}
