<?php

namespace App\AsyncSelects;

use App\Models\Sector;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use LennyLabs\LivewireAsyncSelect\AsyncSelect;

class EmployeeSelect extends AsyncSelect
{
    public function options($searchTerm = null): array
    {
        $user = Auth::user();
        $sectors = Sector::with(['users' => function ($query) use ($searchTerm) {
            $query->where('leave', 0);
            if ($searchTerm) {
                $query->where('name', 'like', "%{$searchTerm}%");
            }
        }])->get();

        $options = [];

        foreach ($sectors as $sector) {
            foreach ($sector->users as $employee) {
                // Apply role-based filtering
                if ($user->isDirector() || $user->isMailer()) {
                    // Directors and Mailers can see everyone
                } elseif ($user->isDeputy()) {
                    if ($employee->isDirector()) continue;
                    if ($employee->isDeputy() && $employee->id !== $user->id) continue;
                } elseif ($user->isHead()) {
                    if ($employee->isDirector() || $employee->isDeputy()) continue;
                }

                $options[] = [
                    'value' => $employee->id,
                    'label' => $employee->employee_name(),
                    'group' => $sector->name,
                ];
            }
        }

        return $options;
    }
}