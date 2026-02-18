<?php

use App\Http\Controllers\DigestController;
use App\Http\Controllers\Documents\ArticleController;
use App\Http\Controllers\Documents\NoteController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ResearchController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/', [PageController::class, 'dashboard'])->name('home');
    Route::get('ordered', [PageController::class, 'ordered'])->name('ordered');
    Route::get('finished', [PageController::class, 'finished_tasks'])->name('finished');
    Route::get('reports', [PageController::class, 'reports'])->name('reports');
    Route::get('employee', [PageController::class, 'employees'])->name('employees');
    Route::get('vacations', [PageController::class, 'vacations'])->name('vacations');
    Route::get('settings', [PageController::class, 'settings'])->name('settings');
    Route::get('surveys', [PageController::class, 'surveys'])->name('surveys');
    Route::get('notes', [NoteController::class, 'index'])->name('notes.index');

    Route::get('/task/info/byid/{id}', [TaskController::class, 'getTaskInfo']);
    Route::get('/article/info/byid/{id}', [ArticleController::class, 'getArticleInfo']);
    Route::get('/digest/info/byid/{id}', [DigestController::class, 'getDigestInfo']);
    Route::get('/note/info/byid/{id}', [NoteController::class, 'getNoteInfo']);
    Route::get('task/download/{id}', [TaskController::class, 'download'])->name('file.download');
    Route::get('task/response/download/{name}', [TaskController::class, 'responseDownload'])->name('response.download');
    Route::get('article/download/{name}', [ArticleController::class, 'articleDownload'])->name('article.download');
    Route::get('digest/download/{name}', [DigestController::class, 'digestDownload'])->name('digest.download');
    Route::get('note/download/{name}', [NoteController::class, 'noteDownload'])->name('note.download');
    Route::get('journals/{year}/ru', [PageController::class, 'journalRu'])->name('journal.ru');
    Route::get('journals/{year}/uz', [PageController::class, 'journalUz'])->name('journal.uz');
    Route::get('journal/{id}', [PageController::class, 'journal'])->name('journal');
    Route::get('kpi/table', [PageController::class, 'kpiReport'])->name('kpi');
    Route::get('reports/table', [PageController::class, 'reportTable'])->name('table.report');
    Route::get('reports/test', [PageController::class, 'testReport'])->name('report.test');
    Route::get('report/{id}/{start}/{end}', [PageController::class, 'userReport'])->name('user.report');
    Route::get('notifications/read/all', [PageController::class, 'readNoti'])->name('read.noti');
    Route::get('reports/download/{start}/{end}', [PageController::class, 'downloadReport'])->name('download.report');
    Route::get('/research/scraping', [ResearchController::class, 'scraping'])->name('scraping');
    Route::get('/scrape/download/{id}', [ResearchController::class, 'download'])->name('scrape.download');
    Route::get('/digest/source/download/{filename}', [DigestController::class, 'paperDownload'])->name('paper.download');
    Route::get('/note/source/download/{filename}', [NoteController::class, 'sourceDownload'])->name('note.source');
    Route::post('/digest/adding/word', [DigestController::class, 'newWord'])->name('digest.new_word');
    Route::post('upload/test/digest', [DigestController::class, 'uploadTest'])->name('upload.test');
    Route::get('digest/formatter', [DigestController::class, 'formatter'])->name('digest.formatter');
    Route::get('workload', [PageController::class, 'workload'])->name('workload');

    Route::get('/user_logs', function(){
        return view('page.attendance');
    })->name('attendance');


    Route::get('/weekly-tasks', [PageController::class, 'weeklyTasks'])->name('weekly.tasks');

    Route::put('task/change/status/{id}', [TaskController::class, 'changeStatus'])->name('change.status');

    Route::resource('project', ProjectController::class)->only([
        'store', 'update'
    ]);

    Route::resource('task', TaskController::class)->only([
        'store', 'destroy',
    ]);

    Route::resource('articles', ArticleController::class)->only([
        'index', 'store', 'destroy',
    ]);
    Route::resource('digests', DigestController::class)->only([
        'index', 'store', 'destroy',
    ]);

    Route::resource('notes', NoteController::class)->only([
        'index', 'store', 'destroy'
    ]);

    Route::put('task/update', [TaskController::class, 'update'])->name('task.update');
    Route::put('/notification/read/{id}', [PageController::class, 'read'])->name('notification.read');
    Route::post('register/new/employee', [UserController::class, 'register'])->name('new.user');
    Route::put('user/settings', [UserController::class, 'updatePassword'])->name('update.password');
    Route::put('article/update', [ArticleController::class, 'update'])->name('article.update');
    Route::put('digest/update', [DigestController::class, 'update'])->name('digest.update');
    Route::put('note/update', [NoteController::class, 'update'])->name('note.update');
    Route::put('user/leave/', [UserController::class, 'userLeave'])->name('user.leave');
    Route::post('task/search', [TaskController::class, 'searchTasks'])->name('task.search');
    Route::post('change/profile/picture', [UserController::class, 'changeProfilePicture'])->name('profile.change');
    Route::post('upload/scraper', [ResearchController::class, 'storeScrape'])->name('scrape.upload');

    Route::delete('task/repeat/destroy/{id}', [TaskController::class, 'destroyRepeat'])->name('repeat.delete');

    Route::post('tasks/bulk-store', [TaskController::class, 'bulkStore'])->name('tasks.bulk_store');
    Route::get('/reports/weekly-tasks', [TaskController::class, 'exportWeeklyTasks'])->name('tasks.weekly_report');
});


Route::get('check-telegram-login', [UserController::class, 'checkUserLogin']);
Route::get('users/export', [UserController::class, 'export']);
Route::get('users/late/export', [UserController::class, 'lateComersExport']);
Route::get('sectors/export', [UserController::class, 'sector']);
Route::get('/getdocuments', [PageController::class, 'getDocuments']);

Route::get('/export/off-days', [PageController::class, 'offDaysWorkExport'])
    ->name('export.off-days');

Route::get('/reports/top-writers/export', [PageController::class, 'exportTopReportWriters']);