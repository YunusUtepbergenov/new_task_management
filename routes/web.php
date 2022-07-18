<?php

use App\Http\Controllers\DigestController;
use App\Http\Controllers\Documents\ArticleController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ResearchController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/', [PageController::class, 'dashboard'])->name('home');
    Route::get('ordered', [PageController::class, 'ordered'])->name('ordered');
    Route::get('helping', [PageController::class, 'helping'])->name('helping');
    Route::get('reports', [PageController::class, 'reports'])->name('reports');
    Route::get('employee', [PageController::class, 'employees'])->name('employees');
    Route::get('settings', [PageController::class, 'settings'])->name('settings');
    Route::get('/task/info/byid/{id}', [PageController::class, 'getTaskInfo']);
    Route::get('/article/info/byid/{id}', [PageController::class, 'getArticleInfo']);
    Route::get('/digest/info/byid/{id}', [PageController::class, 'getDigestInfo']);
    Route::get('task/download/{id}', [PageController::class, 'download'])->name('file.download');
    Route::get('task/response/download/{name}', [PageController::class, 'responseDownload'])->name('response.download');
    Route::get('article/download/{name}', [PageController::class, 'articleDownload'])->name('article.download');
    Route::get('digest/download/{name}', [PageController::class, 'digestDownload'])->name('digest.download');
    Route::get('journals/{year}/ru', [PageController::class, 'journalRu'])->name('journal.ru');
    Route::get('journals/{year}/uz', [PageController::class, 'journalUz'])->name('journal.uz');
    Route::get('journal/{id}', [PageController::class, 'journal'])->name('journal');
    Route::get('reports/table', [PageController::class, 'reportTable'])->name('table.report');
    Route::get('report/{id}', [PageController::class, 'userReport'])->name('user.report');
    Route::get('notifications/read/all', [PageController::class, 'readNoti'])->name('read.noti');
    Route::get('reports/download/{start}/{end}', [PageController::class, 'downloadReport'])->name('download.report');
    Route::get('/research/scraping', [ResearchController::class, 'scraping'])->name('scraping');
    Route::get('/scrape/download/{id}', [ResearchController::class, 'download'])->name('scrape.download');
    Route::post('upload/test/digest', [PageController::class, 'uploadTest'])->name('upload.test');

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

    Route::put('task/update', [TaskController::class, 'update'])->name('task.update');
    Route::put('/notification/read/{id}', [PageController::class, 'read'])->name('notification.read');
    Route::post('register/new/employee', [PageController::class, 'register'])->name('new.user');
    Route::put('user/settings', [PageController::class, 'updatePassword'])->name('update.password');
    Route::put('article/update', [ArticleController::class, 'update'])->name('article.update');
    Route::put('digest/update', [DigestController::class, 'update'])->name('digest.update');
    Route::put('user/leave/', [UserController::class, 'userLeave'])->name('user.leave');
    Route::post('task/search', [PageController::class, 'searchTasks'])->name('task.search');
    Route::post('change/profile/picture', [PageController::class, 'changeProfilePicture'])->name('profile.change');
    Route::post('upload/scraper', [ResearchController::class, 'storeScrape'])->name('scrape.upload');

    Route::delete('task/repeat/destroy/{id}', [TaskController::class, 'destroyRepeat'])->name('repeat.delete');
});

Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->middleware('guest')->name('password.request');
