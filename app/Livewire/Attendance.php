<?php

namespace App\Livewire;

use Livewire\Attributes\Lazy;
use Livewire\Component;
use App\Models\User;
use App\Models\TurnstileLog;
use Illuminate\Support\Carbon;

#[Lazy]
class Attendance extends Component
{
    public function placeholder(): \Illuminate\Contracts\View\View
    {
        return view('livewire.placeholders.loading');
    }

    public $dates = [];
    public $dataBySector = [];

public function mount()
    {
        $allowedSectors = [2,3,4,5,6,7,8,9,10];
        $start = Carbon::now()->startOfMonth();
        $today = Carbon::today();

        // Build reversed date list
        $dates = [];
        while ($start->lte($today)) {
            $dates[] = $start->copy();
            $start->addDay();
        }
        $this->dates = array_reverse($dates);

        $users = User::with('sector')
            ->whereNotNull('log_id')
            ->whereIn('sector_id', $allowedSectors)
            ->where('leave', 0)
            ->orderBy('sector_id', 'ASC')
            ->orderBy('role_id', 'ASC')
            ->get();

        foreach ($users->groupBy(function ($u) {
            return $u->sector ? $u->sector->name : 'Без сектора';
        }) as $sector => $groupedUsers) {

            $userData = [];

            foreach ($groupedUsers as $user) {
                $dayData = [];

                foreach ($this->dates as $date) {
                    // Calculate 06:00 to 06:00 next day range
                    $startTime = $date->copy()->setTime(5, 0, 0);
                    $endTime = $date->copy()->addDay()->setTime(4, 59, 59);

                    // Fetch logs within that 24-hour period
                    $logs = TurnstileLog::on('turnstile')
                        ->where('id', $user->log_id)
                        ->whereBetween('auth_datetime', [$startTime, $endTime])
                        ->get();

                    // Determine come and leave times
                    $come = $logs->firstWhere('device_name', 'Ð¢ÑƒÑ€Ð½Ð¸ÐºÐµÑ‚ 1');
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
}
