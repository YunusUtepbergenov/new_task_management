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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PageController extends Controller
{
    public function dashboard(){
        $user = Auth::user();

        if($user->isDirector() || $user->isMailer()){
            $projects = Project::where('user_id', $user->id)->get();
            $tasks = Task::with(['creator', 'user'])->where('user_id', $user->id)->where('project_id', null)->orderBy('deadline', 'ASC')->get();

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

            $sectors = Sector::with('users:id,name,sector_id,role_id')->get();

        }elseif ($user->isHead()) {
            $projects = Project::where('user_id', $user->id)->get();
            $tasks = Task::with(['creator', 'user'])->where('user_id', $user->id)->where('project_id', null)->orderBy('deadline', 'ASC')->get();
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

            $sectors = NULL;
        }
        else{
            $projects = NULL;
            $tasks = Task::with(['creator', 'user'])->where('user_id', $user->id)->where('project_id', null)->orderBy('deadline', 'ASC')->get();
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

            $sectors = NULL;
        }
        return view('page.index', [
            'projects' => $projects,
            'tasks' => $tasks,
            'sectors' => $sectors,
            'user_projects' => $user_projects
        ]);
    }

    public function ordered(){
        $user = Auth::user();

        if($user->isDirector() || $user->isMailer()){
            $projects = Project::where('user_id', $user->id)->get();
            $sectors = Sector::with('users:id,name,sector_id,role_id')->get();
        }elseif ($user->isHead()) {
            $projects = Project::where('user_id', $user->id)->get();
            $sectors = NULL;
        }else{
            abort(404);
        }

        return view('page.ordered', ['projects' => $projects,'sectors' => $sectors]);
    }

    public function helping(){
        $projects = Project::where('user_id', Auth::user()->id)->get();

        $sectors = Sector::with('users:id,name')->get();

        return view('page.helping', [
            'projects' => $projects,
            'sectors' => $sectors,
        ]);
    }

    public function employees(){
        $sectors = Sector::with('users.role')->get();
        $roles = Role::all();
        return view('page.employees', ['sectors' => $sectors, 'roles' => $roles]);
    }

    public function register(Request $request){
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'sector_id' => 'required',
            'role_id' => 'required',
            'password' => 'required|min:6|max:15'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'sector_id' => $request->sector_id,
            'role_id' => $request->role_id,
            'password' => Hash::make($request->password),
        ]);

        return back();
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

    public function responseDownload($filename){
        return response()->download(storage_path('app/files/responses/'.$filename));
    }


    public function read($id, Request $request){
        Auth::user()->unreadNotifications->where('id', $id)->markAsRead();
        return redirect()->back();
    }

    public function settings(){
        return view('page.settings');
    }

    public function updatePassword(Request $request){
        $request->validate([
            'old_password' => 'required|min:6|max:20',
            'new_password' => 'required|min:6|max:20',
            'confirm_password' => 'required|same:new_password'
        ]);

        $user = Auth::user();
        if(Hash::check($request->old_password, $user->password)){
            $user->update([
                'password' => bcrypt($request->new_password)
            ]);
            return redirect()->back();
        }

    }


}
