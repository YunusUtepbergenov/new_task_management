<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Sector;
use App\Models\Journal;
use App\Models\Project;
use App\Exports\TasksExport;
use App\Models\Vacation;
use Illuminate\Http\Request;
use App\Services\TaskService;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Task;
use ZipArchive;


class PageController extends Controller
{
    public function dashboard(){
        $projects = (new TaskService())->projectList();
        $sectors = (new TaskService())->sectorList();
        $scores = (new TaskService())->scoresList();
        $hrScores = (new TaskService())->hrList();
        $accountantScores = (new TaskService())->accountantList();
        $lawyerScores = (new TaskService())->lawyerList();
        $maintainerScores = (new TaskService())->maintainerList();
        $ictScores = (new TaskService())->ictList();

        return view('page.index', [
            'projects' => $projects,
            'sectors' => $sectors,
            'scores' => $scores,
            'hrScores' => $hrScores,
            'accountantScores' => $accountantScores,
            'lawyerScores' => $lawyerScores,
            'maintainerScores' => $maintainerScores,
            'ictScores' => $ictScores,
        ]);
    }

    public function ordered(){
        $sectors = (new TaskService())->sectorList();
        $projects = (new TaskService())->projectList();
        $scores = (new TaskService())->scoresList();
        $hrScores = (new TaskService())->hrList();
        $accountantScores = (new TaskService())->accountantList();
        $lawyerScores = (new TaskService())->lawyerList();
        $maintainerScores = (new TaskService())->maintainerList();
        $ictScores = (new TaskService())->ictList();

        return view('page.ordered', [
            'projects' => $projects,
            'sectors' => $sectors,
            'scores' => $scores,
            'hrScores' => $hrScores,
            'accountantScores' => $accountantScores,
            'lawyerScores' => $lawyerScores,
            'maintainerScores' => $maintainerScores,
            'ictScores' => $ictScores,
        ]);
    }

    public function helping(){
        $projects = Project::where('user_id', Auth::user()->id)->get();
        $sectors = Sector::with('users:id,name,sector_id,role_id')->get();
        $scores = (new TaskService())->scoresList();
        $hrScores = (new TaskService())->hrList();
        $accountantScores = (new TaskService())->accountantList();
        $lawyerScores = (new TaskService())->lawyerList();
        $maintainerScores = (new TaskService())->maintainerList();
        $ictScores = (new TaskService())->ictList();

        return view('page.helping', [
            'projects' => $projects,
            'sectors' => $sectors,
            'scores' => $scores,
            'hrScores' => $hrScores,
            'accountantScores' => $accountantScores,
            'lawyerScores' => $lawyerScores,
            'maintainerScores' => $maintainerScores,
            'ictScores' => $ictScores,
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

    public function testReport(){
        $user = Auth::user();
        if($user->isDirector() || $user->isDeputy() || $user->isHead() || $user->isMailer() || $user->isHR()){
            $sectors = Sector::all();
            return view('page.reports.test_report', [
                'sectors' => $sectors
            ]);
        }

        abort(404);
    }

    public function kpiReport(){
        $user = Auth::user();
        if($user->isDirector() || $user->isDeputy() || $user->isHead() || $user->isMailer() || $user->isHR()){
            return view('page.reports.kpi');
        }

        abort(404);
    }

    public function userReport($id, $start, $end){
        $user = User::where('id', $id)->first();

        return view('page.reports.user', [
            'user_id' => $user->id,
            'start' => $start,
            'end' => $end
        ]);
    }

    public function downloadReport($param1, $param2){
        return Excel::download(new TasksExport($param1, $param2), 'report.xlsx');
    }

    public function surveys(){
        return view('page.research.survey');
    }

    public function employees(){
        $sectors = Sector::with(['users' => function($query){
            $query->with('role')->where('leave', 0)->orderBy('role_id', 'ASC');
        }])->get();
        $roles = Role::all();

        return view('page.employees', ['sectors' => $sectors, 'roles' => $roles]);
    }
    
    public function vacations(){
        $sectors = Sector::with(['users' => function($query){
            $query->with('role')->where('leave', 0)->orderBy('role_id', 'ASC');
        }])->get();

        $roles = Role::all();

        $vacations = Vacation::with('user')->select('month')
                            ->groupBy('month')
                            ->get()
                            ->map(function ($vacation) {
                                $vacation->users = Vacation::where('month', $vacation->month)
                                    ->with('user')
                                    ->get()
                                    ->pluck('user');
                                return $vacation;
                            });
        return view('page.vacation', ['vacations' => $vacations, 'sectors' => $sectors, 'roles' => $roles]);
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

    public function settings(){
        return view('page.settings');
    }

    public function read($id, Request $request){
        Auth::user()->unreadNotifications->where('id', $id)->markAsRead();
        return redirect()->back();
    }

    public function readNoti(){
        Auth::user()->unreadNotifications->markAsRead();
        return back();
    }
    
    public function getDocuments(){
        $tasks = Task::where('score_id', 5)->where('deadline', '>', '2025-01-01')->where('status', 'Выполнено')->get();

        // Create a ZIP file to download all files at once
        $zipFileName = 'files_2025.zip';
        $zip = new ZipArchive;
        $zipPath = storage_path('app/' . $zipFileName);

        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            foreach ($tasks as $task) {
                $filePath = storage_path('app/files/responses/' . $task->response->filename);
                if (file_exists($filePath)) {
                    $zip->addFile($filePath, basename($filePath));
                }
            }
            $zip->close();
        }

        // return file download
        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}
