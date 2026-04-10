<?php

namespace App\Livewire;

use App\Models\Repeat;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class EmployeesTable extends Component
{
    public $sectors, $roles;

    public $userName, $email, $sectorId, $roleId, $birthDate, $phone;

    public function mount(): void
    {
        $this->roles = TaskService::cachedRoles();
        $this->sectorId = TaskService::cachedSectors()->first()?->id;
        $this->roleId = TaskService::cachedRoles()->first()?->id;
    }

    public function createEmployee(): void
    {
        $this->validate([
            'userName' => 'required|string|max:128',
            'email' => 'required|string|email|max:128|unique:users',
            'sectorId' => 'required|exists:sectors,id',
            'roleId' => 'required|exists:roles,id',
            'birthDate' => 'nullable|date',
            'phone' => 'nullable|string|max:20',
        ]);

        User::create([
            'name' => $this->userName,
            'email' => $this->email,
            'sector_id' => $this->sectorId,
            'role_id' => $this->roleId,
            'birth_date' => $this->birthDate,
            'phone' => $this->phone,
            'password' => Hash::make('password'),
        ]);

        $this->reset(['userName', 'email', 'birthDate', 'phone']);
        $this->sectorId = TaskService::cachedSectors()->first()?->id;
        $this->roleId = TaskService::cachedRoles()->first()?->id;

        $this->dispatch('close-create-modal');
        $this->dispatch('success', msg: __('notifications.employee_added'));
    }

    public function markAsLeft(int $userId): void
    {
        $user = User::findOrFail($userId);

        $repeatTasks = Task::where('creator_id', $user->id)
            ->whereNotNull('repeat_id')
            ->get();

        foreach ($repeatTasks as $task) {
            Repeat::find($task->repeat_id)?->delete();
        }

        $user->update(['leave' => 1]);

        $this->dispatch('success', msg: __('notifications.employee_removed'));
    }

    public function viewProfile(int $id): void
    {
        $this->dispatch('profileClicked', id: $id);
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $this->sectors = TaskService::cachedSectorsWithUsers();

        return view('livewire.employees-table');
    }
}
