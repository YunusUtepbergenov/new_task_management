<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\File;
use App\Models\Journal;
use App\Models\Project;
use App\Models\Role;
use App\Models\Sector;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PageController extends Controller
{
    public function dashboard(){
        $projects = (new TaskService())->projectList();
        $sectors = (new TaskService())->sectorList();

        return view('page.index', [
            'projects' => $projects,
            'sectors' => $sectors,
        ]);
    }

    public function ordered(){
        $sectors = (new TaskService())->sectorList();
        $projects = (new TaskService())->projectList();

        return view('page.ordered', [
            'projects' => $projects,
            'sectors' => $sectors
        ]);
    }

    public function helping(){
        $projects = Project::where('user_id', Auth::user()->id)->get();
        $sectors = Sector::with('users:id,name,sector_id,role_id')->get();

        return view('page.helping', [
            'projects' => $projects,
            'sectors' => $sectors,
        ]);
    }

    public function reports(){
        return view('page.reports');
    }

    public function reportTable(){
        $sectors = Sector::all();
        return view('page.reports.new_report', [
            'sectors' => $sectors
        ]);
    }

    public function userReport($id){
        $user = User::where('id', $id)->first();

        return view('page.reports.user', [
            'user_id' => $user->id
        ]);
    }

    public function employees(){
        $sectors = Sector::with('users.role')->get();
        $roles = Role::all();

        return view('page.employees', ['sectors' => $sectors, 'roles' => $roles]);
    }

    public function journalRu($year)
    {
        $journals = Journal::where('lang', 'ru')->where('year', $year)->orderBy('number', 'DESC')->get();
        $years = Journal::select('year')->distinct()->where('lang', 'ru')->orderBy('year', 'DESC')->get();

        return view('page.documents.ru_journal', [
            'journals' => $journals,
            'years' => $years
        ]);
    }

    public function journalUz($year)
    {
        $journals = Journal::where('lang', 'uz')->where('year', $year)->orderBy('number', 'DESC')->get();
        $years = Journal::select('year')->distinct()->where('lang', 'uz')->orderBy('year', 'DESC')->get();

        return view('page.documents.uz_journal', [
            'journals' => $journals,
            'years' => $years
        ]);
    }

    public function journal($id){
        $journal = Journal::where('id', $id)->first();
        $years = Journal::select('year')->distinct()->orderBy('year', 'DESC')->get();

        return view('page.documents.journal', [
            'journal' => $journal,
            'years' => $years
        ]);
    }

    public function register(Request $request){
        $request->validate([
            'user_name' => ['required', 'string', 'max:128'],
            'email' => ['required', 'string', 'email', 'max:128', 'unique:users'],
            'sector_id' => 'required',
            'role_id' => 'required',
            'password' => 'required|min:6|max:15'
        ]);

        User::create([
            'name' => $request->user_name,
            'email' => $request->email,
            'sector_id' => $request->sector_id,
            'role_id' => $request->role_id,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        return back();
    }

    public function getTaskInfo($id){
        $task = Task::with(['executers', 'repeat'])->where('id', $id)->first();
        // $creator = $task->username($task->creator_id);
        return response()->json(['task' => $task]);
    }

    public function getArticleInfo($id){
        $article = Article::with(['user'])->where('id', $id)->first();
        return response()->json(['article' => $article]);
    }

    public function download($id){
        $file = File::where('id', $id)->first();
        return response()->download(storage_path('app/files/'.$file->name));
    }

    public function responseDownload($filename){
        return response()->download(storage_path('app/files/responses/'.$filename));
    }

    public function articleDownload($filename){
        return response()->download(storage_path('app/files/articles/'.$filename));
    }

    public function read($id, Request $request){
        Auth::user()->unreadNotifications->where('id', $id)->markAsRead();
        return redirect()->back();
    }

    public function readNoti(){
        Auth::user()->unreadNotifications->markAsRead();
        return back();
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
            return back()->withMessage('Пароль успешно изменен');
        }else{
            return back()->withError("Неправильный пароль");
        }
    }
}
