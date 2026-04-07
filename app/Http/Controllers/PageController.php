<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Sector;
use App\Models\Journal;
use App\Exports\OffDaysWorkExport;
use App\Exports\TasksExport;
use App\Models\Vacation;
use Illuminate\Http\Request;
use App\Services\TaskService;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TopUsersExport;
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

        $scoresGrouped = [];

        if (Auth::user()->isDirector() || Auth::user()->isMailer() || Auth::user()->isDeputy()) {
            $scoresGrouped = [
                'Научные сотрудники' => $scores,
                'Специалиста по работе с персоналом' => $hrScores,
                'Главный бухгалтер' => $accountantScores,
                'Юристконсульт' => $lawyerScores,
                'Заведующий хозяйством' => $maintainerScores,
                'Специалист ИКТ' => $ictScores,
            ];
        } else {
            $scoresGrouped = ['Категории' => $scores];
        }

        return view('page.index', [
            'projects' => $projects,
            'sectors' => $sectors,
            'scoresGrouped' => $scoresGrouped
        ]);
    }

    public function ordered(){
        if (Auth::user()->isResearcher()){
            return redirect()->route('home');
        }

        $sectors = (new TaskService())->sectorList();
        $scores = (new TaskService())->scoresList();
        $hrScores = (new TaskService())->hrList();
        $accountantScores = (new TaskService())->accountantList();
        $lawyerScores = (new TaskService())->lawyerList();
        $maintainerScores = (new TaskService())->maintainerList();
        $ictScores = (new TaskService())->ictList();

        $scoresGrouped = [];

        if (Auth::user()->isDirector() || Auth::user()->isMailer() || Auth::user()->isDeputy()) {
            $scoresGrouped = [
                'Научные сотрудники' => $scores,
                'Специалиста по работе с персоналом' => $hrScores,
                'Главный бухгалтер' => $accountantScores,
                'Юристконсульт' => $lawyerScores,
                'Заведующий хозяйством' => $maintainerScores,
                'Специалист ИКТ' => $ictScores,
            ];
        } else {
            $scoresGrouped = ['Категории' => $scores];
        }

        return view('page.ordered', [
            'sectors' => $sectors,
            'scoresGrouped' => $scoresGrouped
        ]);
    }

    public function finished_tasks(){
        return view('page.finished_tasks');
    }

    public function archive(){
        if (!Auth::user()->isDeputy() && !Auth::user()->isHead()) {
            return redirect()->route('home');
        }

        return view('page.archive');
    }

    public function reports(){
        return view('page.reports');
    }

    public function weeklyTasks(){
        if (Auth::user()->isResearcher()){
            return redirect()->route('home');
        }

        $sectors = (new TaskService())->sectorList();
        $scores = (new TaskService())->scoresList();
        $hrScores = (new TaskService())->hrList();
        $accountantScores = (new TaskService())->accountantList();
        $lawyerScores = (new TaskService())->lawyerList();
        $maintainerScores = (new TaskService())->maintainerList();
        $ictScores = (new TaskService())->ictList();

        $scoresGrouped = [];

        if (Auth::user()->isDirector() || Auth::user()->isMailer() || Auth::user()->isDeputy()) {
            $scoresGrouped = [
                'Научные сотрудники' => $scores,
                'Специалиста по работе с персоналом' => $hrScores,
                'Главный бухгалтер' => $accountantScores,
                'Юристконсульт' => $lawyerScores,
                'Заведующий хозяйством' => $maintainerScores,
                'Специалист ИКТ' => $ictScores,
            ];
        } else {
            $scoresGrouped = ['Категории' => $scores];
        }
        return view('page.reports.weekly', [
            'sectors' => $sectors,
            'scoresGrouped' => $scoresGrouped
        ]);
    }

    public function protocolTasks(){
        if (Auth::user()->isResearcher() || Auth::user()->isHead()){
            return redirect()->route('home');
        }

        return view('page.reports.protocol');
    }

    public function reportTable(){
        return view('page.reports.new_report', [
            'sectors' => TaskService::cachedSectors()
        ]);

    }

    public function kpiReport(){
        return view('page.reports.kpi');
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
        return view('page.employees');
    }

     public function offDaysWorkExport(){
        $file = 'off_days_work_' . now()->format('Y_m_d_His') . '.xlsx';
        return Excel::download(new OffDaysWorkExport, $file);
    }
    
    public function vacations(){
        $sectors = TaskService::cachedSectorsWithUsers();
        $roles = TaskService::cachedRoles();

        $vacations = Vacation::with('user')->select('month')
                            ->groupBy('month')
                            ->get()
                            ->map(function ($vacation) {
                                $vacation->users = Vacation::
                                where('year', date('Y'))
                                ->where('month', $vacation->month)
                                    ->with('user')
                                    ->get()
                                    ->pluck('user');
                                return $vacation;
                            });
        return view('page.vacation', ['vacations' => $vacations, 'sectors' => $sectors, 'roles' => $roles]);
    }

    public function workload(){
        $sectors = Sector::with(['users' => function($query) {
            $query->where('leave', 0)->with(['tasks' => function($q) {
                $q->select('id', 'user_id', 'name', 'deadline', 'status')->where('status', '<>', 'Выполнено');
            }]);
        }])->whereIn('id', [2,3,4,5,6,7,8,9,10,12,13,14,15,16])->get();

        return view('page.reports.workload', compact('sectors'));
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

    public function directMessages(){
        if (!Auth::user()->isDeputy() && !Auth::user()->isDirector()) {
            abort(403);
        }

        return view('page.direct-messages');
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
        $tasks = Task::with('response')->where('score_id', 1)->where('deadline', '>', '2025-01-01')->where('status', 'Выполнено')->get();

        $zip = new ZipArchive;
        $zipPath = sys_get_temp_dir() . '/files_2025.zip';

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            return back()->with('error', 'Не удалось создать ZIP архив.');
        }

        $fileCount = 0;
        foreach ($tasks as $task) {
            if (!$task->response || empty($task->response->filename)) {
                continue;
            }
            $filePath = storage_path('app/files/responses/' . $task->response->filename);
            if (file_exists($filePath)) {
                $zip->addFromString(basename($filePath), file_get_contents($filePath));
                $fileCount++;
            }
        }

        if ($fileCount === 0) {
            $zip->close();
            if (file_exists($zipPath)) {
                unlink($zipPath);
            }
            return back()->with('error', 'Нет файлов для скачивания.');
        }

        $zip->close();

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    public function exportTopReportWriters()
    {
        $year = now()->year;

        return Excel::download(
            new TopUsersExport($year),
            "top_report_writers_{$year}.xlsx"
        );
    }
}
