<?php

namespace App\Exports;

use App\Models\User;
use App\Models\TurnstileLog;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class OffDaysWorkExport implements FromView, ShouldAutoSize
{
    /** Optional: limit which sectors to include */
    protected $allowedSectors = [2,3,4,5,6,7,8,9,10];

    /** Handle possible device_name encodings */
    protected $entryNames = ['Турникет 1', 'Ð¢ÑƒÑ€Ð½Ð¸ÐºÐµÑ‚ 1'];
    protected $exitNames  = ['Турникет 2', 'Ð¢ÑƒÑ€Ð½Ð¸ÐºÐµÑ‚ 2'];

    public function view(): View
    {
        $today = Carbon::today();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd   = $today->copy()->endOfMonth();

        // Build all weekend dates (Sat=6, Sun=7 in ISO)
        $weekendDays = [];
        $cursor = $monthStart->copy();
        while ($cursor->lte($monthEnd)) {
            if (in_array($cursor->dayOfWeekIso, [6, 7])) {
                $weekendDays[] = $cursor->copy();
            }
            $cursor->addDay();
        }

        // Users to include
        $users = User::with('sector')
            ->whereNotNull('log_id')
            ->whereIn('sector_id', $this->allowedSectors)
            ->where('leave', 0)
            ->orderBy('sector_id')
            ->orderBy('role_id')
            ->get();

        // Load ALL logs for the month in a single query (06:00 of 1st → 05:59:59 of day after last)
        // This keeps logic consistent with your 06:00 cutoff.
        $globalStart = $monthStart->copy()->setTime(6, 0, 0);
        $globalEnd   = $monthEnd->copy()->addDay()->setTime(5, 59, 59);

        $allLogs = TurnstileLog::on('turnstile')
            ->select(['id', 'auth_datetime', 'auth_date', 'auth_time', 'device_name'])
            ->whereBetween('auth_datetime', [$globalStart, $globalEnd])
            ->get()
            ->groupBy('id'); // group by turnstile user id (same as users.log_id)

        $rows = [];

        foreach ($users as $user) {
            $userLogs = isset($allLogs[$user->log_id]) ? $allLogs[$user->log_id] : collect();

            $offDaysWorked = 0;
            $totalMinutes  = 0;

            foreach ($weekendDays as $wDate) {
                // Window for this off-day: 06:00 -> next day 05:59:59
                $start = $wDate->copy()->setTime(6, 0, 0);
                $end   = $wDate->copy()->addDay()->setTime(5, 59, 59);

                // Logs for this user inside this window
                $dayLogs = $userLogs->filter(function ($log) use ($start, $end) {
                    $dt = Carbon::parse($log->auth_datetime);
                    return $dt->between($start, $end);
                });

                if ($dayLogs->isEmpty()) {
                    continue; // no activity at all on this off-day
                }

                // Mark as "came" if any activity exists on the off day
                $offDaysWorked++;

                // Find earliest entry and latest exit within window (by auth_datetime)
                $entry = $dayLogs->filter(function ($log) {
                        return in_array($log->device_name, $this->entryNames, true);
                    })
                    ->sortBy('auth_datetime')
                    ->first();

                $exit = $dayLogs->filter(function ($log) {
                        return in_array($log->device_name, $this->exitNames, true);
                    })
                    ->sortByDesc('auth_datetime')
                    ->first();

                // Only compute duration if we have both come & leave
                if ($entry && $exit) {
                    $startDT = Carbon::parse($entry->auth_datetime);
                    $endDT   = Carbon::parse($exit->auth_datetime);

                    // Extra safety: ensure end >= start
                    if ($endDT->gte($startDT)) {
                        $mins = $startDT->diffInMinutes($endDT);
                        $totalMinutes += $mins;
                    }
                }
            }

            $rows[] = [
                'name'         => $user->name,
                'off_days'     => $offDaysWorked,
                'total_minutes'=> $totalMinutes,                // for sorting
                'total_hhmm'   => $this->formatMinutes($totalMinutes), // for display
            ];
        }

        // Sort by total off-day hours (descending)
        usort($rows, function ($a, $b) {
            return $b['total_minutes'] <=> $a['total_minutes'];
        });

        return view('exports.off_days_work', [
            'monthLabel' => $today->format('F Y'),
            'rows'       => $rows,
        ]);
    }

    protected function formatMinutes($minutes)
    {
        $h = (int) floor($minutes / 60);
        $m = (int) ($minutes % 60);
        return sprintf('%02d:%02d', $h, $m);
    }
}
