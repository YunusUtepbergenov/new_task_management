<?php

namespace App\Exports;

use App\Models\User;
use App\Models\TurnstileLog;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AttendanceExport implements FromView, ShouldAutoSize
{
    protected Carbon $startDate;
    protected Carbon $endDate;

    protected array $allowedSectors = [2, 3, 4, 5, 6, 7, 8, 9, 10];
    protected array $entryNames = ['Турникет 1', 'Ð¢ÑƒÑ€Ð½Ð¸ÐºÐµÑ‚ 1'];
    protected array $exitNames = ['Турникет 2', 'Ð¢ÑƒÑ€Ð½Ð¸ÐºÐµÑ‚ 2'];

    public function __construct(string $startDate, string $endDate)
    {
        $this->startDate = Carbon::parse($startDate)->startOfDay();
        $this->endDate = Carbon::parse($endDate)->startOfDay();
    }

    public function view(): View
    {
        $dates = [];
        $cursor = $this->startDate->copy();
        while ($cursor->lte($this->endDate)) {
            $dates[] = $cursor->copy();
            $cursor->addDay();
        }

        $users = User::with('sector')
            ->whereNotNull('log_id')
            ->whereIn('sector_id', $this->allowedSectors)
            ->where('leave', 0)
            ->orderBy('sector_id')
            ->orderBy('role_id')
            ->get();

        $logIds = $users->pluck('log_id')->filter()->values()->toArray();

        $grouped = $this->fetchAndGroupLogs($logIds, $this->startDate, $this->endDate);

        $dataBySector = [];

        foreach ($users->groupBy(fn ($u) => $u->sector?->name ?? 'Без сектора') as $sector => $groupedUsers) {
            $userData = [];

            foreach ($groupedUsers as $user) {
                $dayData = [];

                foreach ($dates as $date) {
                    $key = $user->log_id . '_' . $date->format('Y-m-d');
                    $logs = collect($grouped->get($key, []));

                    $come = $logs->filter(fn ($log) => in_array($log->device_name, $this->entryNames, true))
                        ->sortBy('auth_datetime')
                        ->first();

                    $leave = $logs->filter(fn ($log) => in_array($log->device_name, $this->exitNames, true))
                        ->sortByDesc('auth_datetime')
                        ->first();

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

            $dataBySector[$sector] = $userData;
        }

        return view('exports.attendance', [
            'dates' => $dates,
            'dataBySector' => $dataBySector,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ]);
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
