<?php

namespace Tests\Feature;

use App\Http\Livewire\OrderedTable;
use App\Http\Livewire\TasksTable;
use App\Http\Livewire\ViewModal;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class TasksTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_tasks_page_is_working(){
        $this->seed();

        $response = $this->post('/login', [
            'email' => 'y.utepbergenov@cerr.uz',
            'password' => 'yu3667500',
        ]);

        $this->assertAuthenticated();

        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_creator_has_task_create_button(){
        $this->seed();

        $this->actingAs($user = User::first());
        $response = $this->get('/');

        $response->assertSee(' Добавить Задачу');
        $response->assertSee(' Добавить Проект');
    }

    public function test_ordinary_user_does_not_have_task_create_button(){
        $this->seed();

        $this->actingAs($user = User::where('role_id', 5)->first());
        $response = $this->get('/');

        $response->assertDontSee(' Добавить Задачу');
        $response->assertDontSee(' Добавить Проект');
    }

    public function test_ordered_page_is_working_for_heads(){
        $this->seed();

        $response = $this->post('/login', [
            'email' => 'o.khakimov@cerr.uz',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();

        $response = $this->get('/ordered');
        $response->assertStatus(200);
    }

    public function test_ordered_page_is_not_working_for_ordinary_users(){
        $this->seed();

        $response = $this->post('/login', [
            'email' => 'y.utepbergenov@cerr.uz',
            'password' => 'yu3667500',
        ]);

        $this->assertAuthenticated();

        $response = $this->get('/ordered');

        $response->assertStatus(500);
    }

    public function test_task_creation_is_working(){
        $this->seed();
        
        $this->actingAs($user = User::first());

        $task = Task::create([
            'creator_id' => $user->id,
            'user_id' => 1,
            'sector_id' => 1,
            'project_id' => Null,
            'type_id' => 1,
            'priority_id' => 1,
            'score_id' => 1,
            'name' => 'Test task',
            'description' => 'Test description',
            'deadline' => '2024-12-20',
            'status' => 'Не прочитано',
            'overdue' => 0,
        ]);

        Livewire::test(OrderedTable::class)
            ->assertViewHas('tasks', function($collection) use ($task){
                return $collection->contains($task);
        });
    }

    public function test_created_task_is_visible_for_user(){
        $this->seed();
        
        $this->actingAs($user = User::first());

        $task = Task::create([
            'creator_id' => $user->id,
            'user_id' => $user->id,
            'sector_id' => 1,
            'project_id' => Null,
            'type_id' => 1,
            'priority_id' => 1,
            'score_id' => 1,
            'name' => 'Test task',
            'description' => 'Test description',
            'deadline' => '2024-12-20',
            'status' => 'Не прочитано',
            'overdue' => 0,
        ]);

        Livewire::test(TasksTable::class)
            ->assertViewHas('tasks', function($collection) use ($task){
                return $collection->contains($task);
        });
    }

    public function test_view_task_modal_is_working(){
        $this->seed();
        
        $this->actingAs($user = User::first());
        $task = Task::create([
            'creator_id' => $user->id,
            'user_id' => $user->id,
            'sector_id' => 1,
            'project_id' => Null,
            'type_id' => 1,
            'priority_id' => 1,
            'score_id' => 1,
            'name' => 'Test task',
            'description' => 'Test description',
            'deadline' => '2024-12-20',
            'status' => 'Не прочитано',
            'overdue' => 0,
        ]);

        Livewire::test(ViewModal::class)
            ->call('taskClicked', $task->id)
            ->assertEmitted('show-modal');
    }
}
