<?php

namespace App\Exports;

use App\Models\User;
use App\Models\TurnstileLog;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LateComersExport implements FromView, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $allowedSectors = [2,3,4,5,6,7,8,9,10];
        $today = Carbon::today();
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();

        $users = User::with('sector')
            ->whereNotNull('log_id')
            ->whereIn('sector_id', $allowedSectors)
            ->where('leave', 0)
            ->orderBy('sector_id')
            ->get();

        $data = [];

        foreach ($users as $user) {
            $lateCount = 0;

            $current = $startOfMonth->copy();
            while ($current->lte($endOfMonth)) {
                // Skip weekends (Saturday, Sunday)
                if (in_array($current->dayOfWeekIso, [6, 7])) {
                    $current->addDay();
                    continue;
                }

                // Day window: 06:00 -> next day 05:59
                $start = $current->copy()->setTime(6, 0, 0);
                $end = $current->copy()->addDay()->setTime(5, 59, 59);

                $come = TurnstileLog::on('turnstile')
                    ->where('id', $user->log_id)
                    ->whereBetween('auth_datetime', [$start, $end])
                    ->where('device_name', 'Ð¢ÑƒÑ€Ð½Ð¸ÐºÐµÑ‚ 1')
                    ->orderBy('auth_datetime', 'asc')
                    ->first();

                if ($come) {
                    $comeTime = Carbon::parse($come->auth_time);

                    if ($comeTime->gt(Carbon::createFromTime(9, 15, 0))) {
                        $lateCount++;
                    }
                }

                $current->addDay();
            }

            $data[] = [
                'name'   => $user->name,
                'sector' => $user->sector ? $user->sector->name : 'Без сектора',
                'late'   => $lateCount ? $lateCount : '0',
            ];
        }

        // Sort descending by late count
        usort($data, function ($a, $b) {
            return $b['late'] <=> $a['late'];
        });

        return view('exports.late_comers', [
            'month' => $today->format('F Y'),
            'data'  => $data,
        ]);
    }
}
