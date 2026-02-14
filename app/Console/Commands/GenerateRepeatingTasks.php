<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\{Repeat, Task};

class GenerateRepeatingTasks extends Command
{
    protected $signature = 'tasks:generate-repeats';
    protected $description = 'Create tasks based on repeat rules';


    public function handle()
    {
        $today = now();
        $dayOfWeek = $today->dayOfWeekIso;
        $dayOfMonth = $today->day;

        $repeats = Repeat::with('task.user')->get();

        foreach ($repeats as $repeat) {
            $template = $repeat->task;

            // Skip if user is missing or left
            if (!$template || !$template->user || $template->user->leave) {
                continue;
            }

            $deadline = null;
            $type = $repeat->repeat;
            $repeatDay = (int) $repeat->day;

            if ($type === 'weekly' && $today->isMonday()) {
                $deadline = $today->startOfWeek()->addDays($repeatDay - 1);
            }

            if ($type === 'monthly' && $dayOfMonth === 1) {
                $daysInMonth = $today->daysInMonth;
                $day = min($repeatDay, $daysInMonth);
                $deadline = $today->startOfMonth()->addDays($day - 1);
            }

            if ($type === 'quarterly' && $today->isSameDay($today->startOfQuarter())) {
                $lastQuarterEnd = $today->subMonths(3)->endOfQuarter();
                $deadline = $lastQuarterEnd->addDays($repeatDay);
            }

            if (!$deadline || $this->alreadyGenerated($repeat->id, $template->user_id, $deadline)) {
                continue;
            }

            Task::create([
                'creator_id' => $template->creator_id,
                'user_id' => $template->user_id,
                'sector_id' => $template->sector_id,
                'project_id' => null,
                'type_id' => $template->type_id,
                'priority_id' => $template->priority_id,
                'score_id' => $template->score_id,
                'name' => $template->name,
                'description' => $template->description,
                'deadline' => $deadline->toDateString(),
                'status' => 'Не прочитано',
                'planning_type' => $template->planning_type,
                'repeat_id' => $repeat->id, // Link to repeat rule
            ]);
        }

        $this->info('Повторяющиеся задачи созданы.');
    }

    protected function alreadyGenerated($repeatId, $userId, $deadline)
    {
        return Task::where('repeat_id', $repeatId)
            ->where('user_id', $userId)
            ->whereDate('deadline', $deadline)
            ->exists();
    }
}
