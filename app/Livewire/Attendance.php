<?php

namespace App\Livewire;

use Livewire\Attributes\Lazy;
use Livewire\Component;
use App\Models\User;
use App\Models\TurnstileLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

#[Lazy]
class Attendance extends Component
{
    public function placeholder(): \Illuminate\Contracts\View\View
    {
        return view('livewire.placeholders.loading');
    }

    public $dates = [];
    public $dataBySector = [];

public function mount(): void
    {
        $allowedSectors = [2,3,4,5,6,7,8,9,10];
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        $dates = [];
        $cursor = $monthStart->copy();
        while ($cursor->lte($today)) {
            $dates[] = $cursor->copy();
            $cursor->addDay();
        }
        $this->dates = array_reverse($dates);

        $users = User::with('sector')
            ->whereNotNull('log_id')
            ->whereIn('sector_id', $allowedSectors)
            ->where('leave', 0)
            ->orderBy('sector_id')
            ->orderBy('role_id')
            ->get();

        $logIds = $users->pluck('log_id')->filter()->values()->toArray();

        // Past days: cached until end of month (past data never changes)
        $yesterday = $today->copy()->subDay();
        $hasPastDays = $yesterday->gte($monthStart);

        $pastGrouped = collect();
        if ($hasPastDays) {
            $cacheKey = 'attendance:past:' . $yesterday->format('Y-m-d');
            $pastGrouped = Cache::remember($cacheKey, now()->endOfMonth(), function () use ($logIds, $monthStart, $yesterday) {
                return $this->fetchAndGroupLogs($logIds, $monthStart, $yesterday);
            });
        }

        // Today: always fresh
        $todayGrouped = $this->fetchAndGroupLogs($logIds, $today, $today);

        $grouped = collect(array_merge($pastGrouped->all(), $todayGrouped->all()));

        foreach ($users->groupBy(fn ($u) => $u->sector?->name ?? 'Без сектора') as $sector => $groupedUsers) {
            $userData = [];

            foreach ($groupedUsers as $user) {
                $dayData = [];

                foreach ($this->dates as $date) {
                    $key = $user->log_id . '_' . $date->format('Y-m-d');
                    $logs = collect($grouped->get($key, []));
                    $come = $logs->firstWhere('device_name', 'Ð¢ÑƒÑ€Ð½Ð¸ÐºÐµÑ‚ 1');
                    $leave = $logs->reverse()->firstWhere('device_name', 'Ð¢ÑƒÑ€Ð½Ð¸ÐºÐµÑ‚ 2');

                    $dayData[$date->format('Y-m-d')] = [
                        'come' => $come?->auth_time,
                        'leave' => $leave?->auth_time,
                    ];
                }

                $userData[] = [
                    'name' => $user->short_name,
                    'days' => $dayData,
                ];
            }

            $this->dataBySector[$sector] = $userData;
        }
    }

    private function fetchAndGroupLogs(array $logIds, Carbon $from, Carbon $to): \Illuminate\Support\Collection
    {
        if (empty($logIds)) {
            return collect();
        }

        $startTime = $from->copy()->setTime(5, 0, 0);
        $endTime = $to->copy()->addDay()->setTime(4, 59, 59);

        $logs = TurnstileLog::on('turnstile')
            ->whereIn('id', $logIds)
            ->whereBetween('auth_datetime', [$startTime, $endTime])
            ->get();

        return $logs->groupBy(function ($log) {
            $dt = Carbon::parse($log->auth_datetime);
            $date = $dt->hour < 5 ? $dt->copy()->subDay() : $dt;
            return $log->id . '_' . $date->format('Y-m-d');
        })->toBase();
    }
}
