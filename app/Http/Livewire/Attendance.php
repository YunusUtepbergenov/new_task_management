<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Sector;
use App\Models\TurnstileLog;
use Illuminate\Support\Carbon;

class Attendance extends Component
{
    public $dates = [];
    public $dataBySector = [];

public function mount()
    {
        $allowedSectors = [2,3,4,5,6,7,8,9,10];
        // Generate all days in current month, reversed (latest first)
        $start = Carbon::now()->startOfMonth();
        $today = Carbon::today();

        $dates = [];
        while ($start->lte($today)) {
            $dates[] = $start->copy();
            $start->addDay();
        }
        $this->dates = array_reverse($dates);

        // Get users with sectors and log_id (required for matching)
        $users = User::with('sector')->whereNotNull('log_id')->whereIn('sector_id', $allowedSectors)->orderBy('sector_id', 'ASC')->orderBy('role_id', 'ASC')->get();

        foreach ($users->groupBy(fn($u) => $u->sector->name ?? 'Без сектора') as $sector => $groupedUsers) {
            $userData = [];

            foreach ($groupedUsers as $user) {
                $dayData = [];

                foreach ($this->dates as $date) {
                    $logs = TurnstileLog::on('turnstile')
                        ->where('id', $user->log_id)
                        ->whereDate('auth_date', $date->format('Y-m-d'))
                        ->get();

                    $come = $logs->firstWhere('device_name', 'Ð¢ÑƒÑ€Ð½Ð¸ÐºÐµÑ‚ 1'); // Entry
                    $leave = $logs->reverse()->firstWhere('device_name', 'Ð¢ÑƒÑ€Ð½Ð¸ÐºÐµÑ‚ 2'); 

                    $dayData[$date->format('Y-m-d')] = [
                        'come' => $come ? $come->auth_time : null,
                        'leave' => $leave ? $leave->auth_time : null,
                    ];
                }

                $userData[] = [
                    'name' => $user->name,
                    'days' => $dayData,
                ];
            }

            $this->dataBySector[$sector] = $userData;
        }
    }

    public function render()
    {
        return view('livewire.attendance');
    }
}
