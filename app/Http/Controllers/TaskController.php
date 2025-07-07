<?php

namespace App\Http\Controllers;

use App\Events\TaskCreatedEvent;
use App\Exports\WeeklyTasksExport;
use App\Models\File;
use App\Models\Repeat;
use App\Models\Task;
use App\Models\TaskUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class TaskController extends Controller
{
    public $days = array(
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        7 => 'Sunday'
    );

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3|max:255',
            'deadline' => 'required|date_format:Y-m-d',
            'file.*' => 'nullable|file|max:5000'
        ]);

        $user = User::where('id', $request->user_id)->first();

        if($user->role_id == 2){
            $creator = 2;
        }else{
            $creator = $user->id;
        }

        $new_deadline = $request->deadline;
        foreach($request->users as $usr){
            $user = User::where('id', $usr)->first();
            $task = Task::create([
                'creator_id' => $creator,
                'user_id' => $usr,
                'project_id' => $request->project_id,
                'sector_id' => $user->sector->id,
                'type_id' => 1,
                'priority_id' => 1,
                'score_id' => $request->score_id,
                'name' => $request->name,
                'deadline' => $new_deadline,
                'status' => 'Не прочитано',
                'planning_type' => $request->plan_type,
            ]);

            $task->executers()->sync($request->helpers, false);
            if($request->hasFile('file')){
                foreach($request->file as $file){
                    $filename = time().$file->getClientOriginalName();
                    preg_replace( '/[\r\n\t -]+/', '-', $filename );
                    Storage::disk('local')->putFileAs(
                        'files/',
                        $file,
                        $filename
                    );
                    $fileModel = new File;
                    $fileModel->task_id = $task->id;
                    $fileModel->name = $filename;
                    $fileModel->save();
                }
            }
            event(new TaskCreatedEvent($task));
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3|max:255',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'file.*' => 'nullable|file|max:5000',
        ]);

        $baseTask = Task::findOrFail($request->id);
        $groupId = $baseTask->group_id;

        $newDeadline = Carbon::parse($request->deadline);
        $isExtended = $newDeadline->gt(Carbon::parse($baseTask->deadline));

        $groupTasks = $groupId
            ? Task::where('group_id', $groupId)->get()
            : collect([$baseTask]);

        $existingUserIds = $groupTasks->pluck('user_id')->toArray();
        $newUserIds = $request->user_ids;

        // Remove all old grouped tasks if user_ids changed
        if (array_diff($existingUserIds, $newUserIds) || array_diff($newUserIds, $existingUserIds)) {
            foreach ($groupTasks as $task) {
                File::where('task_id', $task->id)->delete();
                $task->delete();
            }

            // If it was not grouped, we generate a new group_id
            $newGroupId = $groupId ?? now()->timestamp;

            foreach ($newUserIds as $userId) {
                $user = User::findOrFail($userId);
                $newTask = Task::create([
                    'group_id' => $newGroupId,
                    'creator_id' => $request->creator_id,
                    'user_id' => $userId,
                    'sector_id' => $user->sector_id,
                    'score_id' => $request->score_id,
                    'name' => $request->name,
                    'deadline' => $baseTask->deadline,
                    'extended_deadline' => $isExtended ? $newDeadline : null,
                    'status' => 'Не прочитано',
                    'overdue' => 0,
                ]);

                // Save files for each recreated task
                if ($request->hasFile('file')) {
                    foreach ($request->file as $file) {
                        $filename = time().$file->getClientOriginalName();
                        Storage::disk('local')->putFileAs('files/', $file, $filename);

                        File::create([
                            'task_id' => $newTask->id,
                            'name' => $filename,
                        ]);
                    }
                }
            }
        } else {
            // If user_ids didn't change, update existing grouped tasks
            foreach ($groupTasks as $task) {
                $user = User::findOrFail($task->user_id);
                $task->update([
                    'creator_id' => $request->creator_id,
                    'sector_id' => $user->sector_id,
                    'score_id' => $request->score_id,
                    'name' => $request->name,
                    'extended_deadline' => $isExtended ? $newDeadline : null,
                    'status' => in_array($task->status, ['Ждет подтверждения', 'Выполнено']) ? $task->status : 'Не прочитано',
                    'overdue' => 0,
                ]);

                // Replace old files
                File::where('task_id', $task->id)->delete();
                if ($request->hasFile('file')) {
                    foreach ($request->file as $file) {
                        $filename = time().$file->getClientOriginalName();
                        Storage::disk('local')->putFileAs('files/', $file, $filename);

                        File::create([
                            'task_id' => $task->id,
                            'name' => $filename,
                        ]);
                    }
                }
            }
        }
    }


    public function destroy($id)
    {
        $task = Task::where('id', $id)->first();
        $executers = TaskUser::where('task_id', $task->id)->get();

        if($executers){
            foreach ($executers as $executer) {
                $executer->delete();
            }
        }
        if($task->response){
            if($task->response->filename){
                Storage::delete('files/responses/'.$task->response->filename);
            }
            $task->response->delete();
        }

        if($task->files){
            foreach($task->files as $file){
                Storage::delete('files/'.$file->name);
                $file->delete();
            }
        }
        if($task->repeat){
            $task->repeat->delete();
        }

        $task->delete();

        return back();
    }

    public function destroyRepeat($id){
        $repeat = Repeat::where('id', $id)->first();
        if($repeat)
            $repeat->delete();

        $tasks = Task::where('repeat_id', $repeat->id)->get();

        foreach($tasks as $task){
            $task->update([
                'repeat_id' => Null
            ]);
        }

        return back();
    }

    public function changeStatus(Request $request, $id){
        $task = Task::where('id', $id)->first();

        if($request->status == "Started"){
            $task->update(['status' => "Выполняется"]);
        }
        else if($request->status == "Submitted"){
            $task->update(['status' => "Ждет подтверждения"]);
        }
        return redirect()->back();
    }

    public function getTaskInfo($id){
        $task = Task::with(['executers', 'repeat'])->where('id', $id)->first();
        if($task->extended_deadline){
            $task->extended_deadline = \Carbon\Carbon::parse($task->extended_deadline)->format('Y-m-d');
        }

        if(isset($task->group_id)){
            $group_users = Task::select('user_id')->where('group_id', $task->group_id)->get()->pluck('user_id')->toArray();
        }else{
            $group_users = [$task->user_id];
        }

        return response()->json(['task' => $task, 'group_users' => $group_users]);
    }

    public function download($id){
        $file = File::where('id', $id)->first();
        return response()->download(storage_path('app/files/'.$file->name));
    }

    public function responseDownload($filename){
        return response()->download(storage_path('app/files/responses/'.$filename));
    }

    public function searchTasks(Request $request){
        $user = auth()->user();
        if($user->isDirector() || $user->isDeputy() || $user->isHead() || $user->isMailer() || $user->isHR() || $user->role_id == 2){
            $tasks = Task::select(['id', 'name'])->where('name', 'LIKE', "%{$request->term}%")->orWhere('description', 'LIKE', "%{$request->term}%")->get();
        }else{
            $tasks = Task::select(['id', 'name'])->where('name', 'LIKE', "%{$request->term}%")->where('user_id', $user->id)->get();
        }
        $users = User::select(['id', 'name'])->where('name', 'LIKE', "%{$request->term}%")->get();

        if($tasks){
            $tasks->map(function($res){
                $res->model = class_basename($res);
            });
        }

        if($users){
            $users->map(function($res){
                $res->model = class_basename($res);
            });
        }

        $tasks = $tasks->merge($users);

        return $tasks->toJson();
    }

    public function bulkStore(Request $request){

        $request->validate([
            'tasks' => 'required|array|min:1',
            'tasks.*.name' => 'required|string|max:255',
            'tasks.*.deadline' => 'required|date|after_or_equal:today',
            'tasks.*.workers' => 'required|array|min:1',
            'tasks.*.task_score' => 'exists:scores,id',
        ]);

        foreach ($request->tasks as $taskData) {
            foreach ($taskData['workers'] as $workerId) {
                Task::create([
                    'creator_id' => auth()->id(),
                    'user_id' => $workerId,
                    'name' => $taskData['name'],
                    'description' => $taskData['description'] ?? null,
                    'deadline' => $taskData['deadline'],
                    'status' => 'Не прочитано',
                    'overdue' => false,
                    'sector_id' => User::find($workerId)->sector_id,
                    'score_id' => $taskData['task_score'],
                    'total' => null,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Все задачи были успешно созданы для выбранных сотрудников.');
    }

    public function exportWeeklyTasks()
    {
        return Excel::download(new WeeklyTasksExport, 'weekly-tasks.xlsx');
    }
}
