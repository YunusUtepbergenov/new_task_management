<?php

namespace App\Services;

use App\Models\MailDeadline;
use App\Models\MailItem;
use App\Models\Sector;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Builds the workload overview: every active employee of the research sectors and branches
 * with their open internal tasks and their open edo.ijro.uz items.
 *
 * Work waiting for confirmation (tasks "Ждет подтверждения", edo deadlines "in_review") is
 * kept apart from the load: the employee has already handed it in.
 */
class WorkloadService
{
    /**
     * Research sectors and regional branches.
     *
     * @var list<int>
     */
    public const SECTOR_IDS = [2, 3, 4, 5, 6, 7, 8, 9, 10, 12, 13, 14, 15, 16];

    public const MEDIUM_LOAD = 3;

    public const HIGH_LOAD = 6;

    /**
     * Deadline horizon buckets, nearest first.
     *
     * @var list<string>
     */
    public const BUCKETS = ['overdue', 'week', 'next_week', 'month', 'later'];

    /**
     * @var array<string, string>
     */
    private const TASK_STATES = [
        'Не прочитано' => 'new',
        'Выполняется' => 'doing',
        'Дорабатывается' => 'rework',
        'Ждет подтверждения' => 'review',
    ];

    /**
     * @var array<string, string>
     */
    private const MAIL_STATES = [
        MailDeadline::STATUS_PENDING => 'doing',
        MailDeadline::STATUS_RETURNED => 'rework',
        MailDeadline::STATUS_IN_REVIEW => 'review',
    ];

    /**
     * @return array{sectors: list<array<string, mixed>>, totals: array<string, int>, today: string}
     */
    public function overview(): array
    {
        $today = today();
        $mailsByUser = $this->openMailEntriesByUser($today);

        $sectors = Sector::query()
            ->whereIn('id', self::SECTOR_IDS)
            ->with(['users' => fn ($users) => $users->where('leave', 0)
                ->orderBy('role_id')
                ->orderBy('name')
                ->with(['tasks' => fn ($tasks) => $tasks
                    ->select(['id', 'user_id', 'name', 'deadline', 'extended_deadline', 'status', 'priority_id'])
                    ->where('status', '<>', 'Выполнено')])])
            ->orderBy('id')
            ->get()
            ->map(function (Sector $sector) use ($mailsByUser, $today): array {
                $employees = $sector->users
                    ->map(fn (User $user): array => $this->employee($user, $mailsByUser->get($user->id, collect()), $today))
                    ->values();

                return [
                    'id' => $sector->id,
                    'name' => $sector->name,
                    'head' => $employees->firstWhere('is_head', true)['name'] ?? null,
                    'employees' => $employees->all(),
                    'totals' => $this->totals($employees),
                ];
            })
            ->values();

        return [
            'sectors' => $sectors->all(),
            'totals' => $this->totals($sectors->flatMap(fn (array $sector): array => $sector['employees'])),
            'today' => $today->format('d.m.Y'),
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $mails
     * @return array<string, mixed>
     */
    private function employee(User $user, Collection $mails, Carbon $today): array
    {
        $tasks = $user->tasks
            ->map(fn (Task $task): array => $this->taskEntry($task, $today))
            ->sortBy(fn (array $entry): int => $entry['days_left'] ?? PHP_INT_MAX)
            ->values();

        $mails = $mails->sortBy(fn (array $entry): int => $entry['days_left'] ?? PHP_INT_MAX)->values();

        $activeTasks = $tasks->where('review', false);
        $activeMails = $mails->where('review', false);
        $active = $activeTasks->concat($activeMails);

        $load = $active->count();
        $upcoming = $active->filter(fn (array $entry): bool => $entry['days_left'] !== null && $entry['days_left'] >= 0);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'short_name' => $user->short_name,
            'initials' => $user->initials(),
            'role' => $user->role?->name,
            'is_head' => $user->isHead(),
            'level' => $this->level($load),
            'counts' => [
                'load' => $load,
                'tasks' => $activeTasks->count(),
                'tasks_overdue' => $this->overdueCount($activeTasks),
                'tasks_review' => $tasks->count() - $activeTasks->count(),
                'mails' => $activeMails->count(),
                'mails_overdue' => $this->overdueCount($activeMails),
                'mails_review' => $mails->count() - $activeMails->count(),
                'mails_main' => $activeMails->where('main', true)->count(),
                'overdue' => $this->overdueCount($active),
                'due_week' => $upcoming->where('days_left', '<=', 6)->count(),
            ],
            'buckets' => collect(self::BUCKETS)->mapWithKeys(fn (string $bucket): array => [$bucket => [
                'tasks' => $activeTasks->where('bucket', $bucket)->count(),
                'mails' => $activeMails->where('bucket', $bucket)->count(),
            ]])->all(),
            'worst_overdue' => $active->max(fn (array $entry): int => max(0, -($entry['days_left'] ?? 0))),
            'tasks' => $tasks->all(),
            'mails' => $mails->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function taskEntry(Task $task, Carbon $today): array
    {
        $deadline = $task->extended_deadline ?? $task->deadline;
        $deadline = $deadline ? Carbon::parse($deadline)->startOfDay() : null;
        $state = self::TASK_STATES[$task->status] ?? 'doing';

        return $this->entry('task', $task->id, $task->name, null, $deadline, $state, $today) + [
            'priority' => (int) $task->priority_id,
        ];
    }

    /**
     * Open edo.ijro.uz items per executor, each with its nearest open deadline.
     *
     * @return Collection<int, Collection<int, array<string, mixed>>>
     */
    private function openMailEntriesByUser(Carbon $today): Collection
    {
        $openDeadlines = fn ($deadlines) => $deadlines->where('status', '!=', MailDeadline::STATUS_DONE);

        $entries = [];

        MailItem::query()
            ->whereHas('deadlines', $openDeadlines)
            ->with(['document:id,type,document_number,title', 'executors', 'deadlines' => $openDeadlines])
            ->get()
            ->each(function (MailItem $item) use ($today, &$entries): void {
                /** @var MailDeadline $current */
                $current = $item->deadlines->first();
                $document = $item->document;
                $meta = collect([$document->type, $document->document_number ? '№ '.$document->document_number : null, $item->clause])
                    ->filter()
                    ->implode(' · ');

                $entry = $this->entry(
                    'mail',
                    $item->id,
                    $item->content ?: $document->title,
                    $meta,
                    $current->deadline->copy()->startOfDay(),
                    self::MAIL_STATES[$current->status] ?? 'doing',
                    $today,
                ) + [
                    'open_deadlines' => $item->deadlines->count(),
                    'url' => route('mails.show', $document->id),
                ];

                foreach ($item->executors as $executor) {
                    $entries[$executor->id][] = $entry + ['main' => (bool) $executor->pivot->is_main];
                }
            });

        return collect($entries)->map(fn (array $userEntries): Collection => collect($userEntries));
    }

    /**
     * @return array<string, mixed>
     */
    private function entry(string $source, int $id, ?string $title, ?string $meta, ?Carbon $deadline, string $state, Carbon $today): array
    {
        $daysLeft = $deadline ? (int) $today->diffInDays($deadline, false) : null;

        return [
            'source' => $source,
            'id' => $id,
            'title' => trim((string) $title),
            'meta' => $meta,
            'deadline' => $deadline?->format('d.m.Y'),
            'days_left' => $daysLeft,
            'bucket' => $this->bucket($daysLeft),
            'state' => $state,
            'review' => $state === 'review',
        ];
    }

    private function bucket(?int $daysLeft): string
    {
        return match (true) {
            $daysLeft === null => 'later',
            $daysLeft < 0 => 'overdue',
            $daysLeft <= 6 => 'week',
            $daysLeft <= 13 => 'next_week',
            $daysLeft <= 30 => 'month',
            default => 'later',
        };
    }

    private function level(int $load): string
    {
        return match (true) {
            $load >= self::HIGH_LOAD => 'high',
            $load >= self::MEDIUM_LOAD => 'medium',
            $load > 0 => 'low',
            default => 'idle',
        };
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $entries
     */
    private function overdueCount(Collection $entries): int
    {
        return $entries->filter(fn (array $entry): bool => $entry['days_left'] !== null && $entry['days_left'] < 0)->count();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $employees
     * @return array<string, int>
     */
    private function totals(Collection $employees): array
    {
        $sum = fn (string $key): int => (int) $employees->sum(fn (array $employee): int => $employee['counts'][$key]);

        return [
            'employees' => $employees->count(),
            'load' => $sum('load'),
            'tasks' => $sum('tasks'),
            'tasks_overdue' => $sum('tasks_overdue'),
            'tasks_review' => $sum('tasks_review'),
            'mails' => $sum('mails'),
            'mails_overdue' => $sum('mails_overdue'),
            'mails_review' => $sum('mails_review'),
            'overdue' => $sum('overdue'),
            'due_week' => $sum('due_week'),
            'max_load' => (int) $employees->max(fn (array $employee): int => $employee['counts']['load']),
            'high' => $employees->where('level', 'high')->count(),
            'medium' => $employees->where('level', 'medium')->count(),
            'low' => $employees->where('level', 'low')->count(),
            'idle' => $employees->where('level', 'idle')->count(),
            'with_overdue' => $employees->filter(fn (array $employee): bool => $employee['counts']['overdue'] > 0)->count(),
        ];
    }
}
