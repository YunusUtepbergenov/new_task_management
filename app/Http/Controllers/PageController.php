<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Project;
use App\Models\Role;
use App\Models\Sector;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PageController extends Controller
{
    public function dashboard(){
        $projects = Project::with('tasks')->get();
        $tasks = Task::where('user_id', Auth::user()->id)->where('project_id', null)->get();
        $users = User::select(['id', 'name'])->get();
        $project_tasks = Task::with('project')->where('user_id', Auth::user()->id)->where('project_id', '<>', null)->get();
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
        $tasks = Task::where('creator_id', Auth::user()->id)->where('project_id', null)->get();
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
        return view('page.index', [
            'projects' => $projects,
            'users' => $users,
            'tasks' => $tasks,
            'sectors' => $sectors,
            'user_projects' => $user_projects
        ]);
    }

    public function employees(){
        $employees = User::all();
        $sectors = Sector::all();
        $roles = Role::all();
        return view('page.employees', ['employees' => $employees, 'sectors' => $sectors, 'roles' => $roles]);
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
