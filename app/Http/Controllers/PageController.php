<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Project;
use App\Models\Role;
use App\Models\Sector;
use App\Models\Task;
use App\Models\TaskUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PageController extends Controller
{
    public function dashboard(){
        $user = Auth::user();
        $projects = Project::with('tasks')->get();
        $tasks = Task::where('user_id', $user->id)->where('project_id', null)->orderBy('deadline', 'ASC')->get();
        $project_tasks = Task::with('project')->where('user_id', $user->id)->where('project_id', '<>', null)->get();
        $projects_arr = array();

        $user_projects = collect([]);

        foreach($project_tasks as $task){
            array_push($projects_arr, $task->project->name);
        }

        $unique_projects = array_unique($projects_arr);
        foreach($unique_projects as $project){
            $project_collection = Project::where('name', $project)->first();
            $user_projects = $user_projects->merge([$project_collection]);
        }

        $sectors = Sector::with('users')->get();
        $users = User::select(['id', 'name'])->get(5);

        return view('page.index', [
            'projects' => $projects,
            'users' => $users,
            'tasks' => $tasks,
            'sectors' => $sectors,
            'user_projects' => $user_projects
        ]);
    }

    public function ordered(){
        $projects = Project::with('tasks')->get();
        $tasks = Task::where('creator_id', Auth::user()->id)->where('project_id', null)->orderBy('deadline', 'ASC')->get();
        $users = User::select(['id', 'name'])->get();
        $project_tasks = Task::with('project')->where('creator_id', Auth::user()->id)->where('project_id', '<>', null)->get();
        $projects_arr = array();

        $user_projects = collect([]);

        foreach($project_tasks as $task){
            array_push($projects_arr, $task->project->name);
        }

        $unique_projects = array_unique($projects_arr);
        foreach($unique_projects as $project){
            $project_collection = Project::where('name', $project)->first();
            $user_projects = $user_projects->merge([$project_collection]);
        }
        $sectors = Sector::with('users')->get();
        return view('page.ordered', [
            'projects' => $projects,
            'users' => $users,
            'tasks' => $tasks,
            'sectors' => $sectors,
            'user_projects' => $user_projects
        ]);
    }

    public function helping(){
        $projects = Project::with('tasks')->get();
        $tasks = TaskUser::where('user_id', Auth::user()->id)->orderBy('created_at', 'ASC')->get();
        $users = User::select(['id', 'name'])->get();

        $projects_arr = array();
        $tasks_id = array();

        $helping_projects = collect([]);
        $tasks_without_project = collect([]);

        foreach($tasks as $task){
            $helping_task = Task::with('project')->where('id', $task->task_id)->first();
            if($helping_task->project_id == NULL){
                $tasks_without_project = $tasks_without_project->merge([$helping_task]);
            }else{
                array_push($projects_arr, $helping_task->project->name);
                array_push($tasks_id, $helping_task->id);
            }
        }

        $unique_projects = array_unique($projects_arr);
        foreach($unique_projects as $project){
            $project_collection = Project::where('name', $project)->first();
            $helping_projects = $helping_projects->merge([$project_collection]);
        }
        $sectors = Sector::with('users')->get();

        return view('page.helping', [
            'projects' => $projects,
            'users' => $users,
            'helping_projects' => $helping_projects,
            'tasks_without_project' => $tasks_without_project,
            'sectors' => $sectors,
            'tasks_id' => $tasks_id
        ]);

    }

    public function employees(){
        $sectors = Sector::with('users.role')->get();
        $roles = Role::all();
        return view('page.employees', ['sectors' => $sectors, 'roles' => $roles]);
    }

    public function getTaskInfo($id){
        $task = Task::where('id', $id)->first();
        $creator = $task->username($task->creator_id);
        return response()->json(['task' => $task, 'creator' => $creator]);
    }

    public function download($id){
        $file = File::where('id', $id)->first();
        return response()->download(storage_path('app/files/'.$file->name));
    }
}
