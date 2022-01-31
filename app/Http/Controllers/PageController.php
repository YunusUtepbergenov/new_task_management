<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Role;
use App\Models\Sector;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function dashboard(){
        $projects = Project::all();
        $tasks = Task::where('project_id', NULL)->get();
        $users = User::all();
        $sectors = Sector::all();
        return view('page.index', [
            'projects' => $projects,
            'users' => $users,
            'tasks' => $tasks,
            'sectors' => $sectors
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
}
