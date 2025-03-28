<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Task $task)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Task $task)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Task $task)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Task $task)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Task $task)
    {
        //
    }

    public function creator(User $user, Task $task){
        return $task->creator_id === $user->id;
    }

    public function evaluate(User $user, Task $task){
        // Director, deputy, or mailer can ONLY evaluate tasks they created
        if ($user->isDirector() || $user->isDeputy() || $user->isMailer()) {
            return $task->creator_id === $user->id;
        }

        // Sector heads can evaluate:
        // 1. Tasks they created themselves
        // 2. Tasks created by researchers in their sector
        if ($user->isHead()) {
            // If sector head created the task
            if ($task->creator_id === $user->id) {
                return true;
            }
            
            // Check if task was created by a researcher in this sector head's sector
            $taskCreator = $task->creator;
            if ($taskCreator && $taskCreator->sector_id === $user->sector_id) {
                // Make sure creator is a researcher (not another head/director/etc)
                return !$taskCreator->isHead() && 
                    !$taskCreator->isDirector() && 
                    !$taskCreator->isDeputy() && 
                    !$taskCreator->isMailer();
            }
            
            return false;
        }

        // Research employees cannot evaluate tasks
        return false;
    }

    public function overdue(User $user, Task $task){
        return $task->deadline > Carbon::now();
    }
}
