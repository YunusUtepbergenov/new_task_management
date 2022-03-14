<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/', [PageController::class, 'dashboard'])->name('home');
    Route::get('ordered', [PageController::class, 'ordered'])->name('ordered');
    Route::get('helping', [PageController::class, 'helping'])->name('helping');
    Route::get('reports', [PageController::class, 'reports'])->name('reports');
    Route::get('documents', [PageController::class, 'documents'])->name('documents');
    Route::get('employee', [PageController::class, 'employees'])->name('employees');
    Route::get('settings', [PageController::class, 'settings'])->name('settings');
    Route::get('/task/info/byid/{id}', [PageController::class, 'getTaskInfo']);
    Route::get('task/download/{id}', [PageController::class, 'download'])->name('file.download');
    Route::get('task/response/download/{name}', [PageController::class, 'responseDownload'])->name('response.download');

    Route::put('task/change/status/{id}', [TaskController::class, 'changeStatus'])->name('change.status');

    Route::resource('project', ProjectController::class)->only([
        'store', 'update'
    ]);

    Route::resource('task', TaskController::class)->only([
        'store', 'destroy'
    ]);

    Route::put('task/update', [TaskController::class, 'update'])->name('task.update');
    Route::put('/notification/read/{id}', [PageController::class, 'read'])->name('notification.read');
    Route::post('register/new/employee', [PageController::class, 'register'])->name('new.user');
    Route::put('user/settings', [PageController::class, 'updatePassword'])->name('update.password');
});

Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->middleware('guest')->name('password.request');
