<?php

namespace App\Services;

use App\Models\MailDeadline;
use App\Models\MailDocument;
use App\Models\MailItem;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Execution summaries by sector and by employee, counted the same way as the
 * "Свод_шўбалар" / "Свод_ходимлар" sheets of the edo.ijro.uz Excel export:
 *
 * - one assignment row = one deadline of one item for one executor (co-executors get their
 *   own rows, like the extra rows in the Excel "База" sheet);
 * - an item given to many sector heads at once ("Барча шўъба мудирлари") is one row per
 *   deadline under a single group entry, not one row per head;
 * - "past due" means the deadline date has passed, whatever the status;
 * - status columns count all rows, not only past-due ones, except deadlines that are not due
 *   yet and not started (the Excel sheet leaves their status blank);
 * - the document count goes to the document's responsible person (first item's executor).
 */
class MailReportService
{
    /**
     * Status columns in the order of the Excel sheets.
     *
     * @var list<string>
     */
    public const STATUS_COLUMNS = [
        MailDeadline::STATUS_DONE,
        MailDeadline::STATUS_PENDING,
        MailDeadline::STATUS_IN_REVIEW,
        MailDeadline::STATUS_RETURNED,
    ];

    private const GROUP = 'group';

    /**
     * @var Collection<int, array<string, mixed>>|null
     */
    private ?Collection $rows = null;

    /**
     * @return array{rows: list<array<string, mixed>>, total: array<string, mixed>}
     */
    public function bySector(): array
    {
        $sectorNames = Sector::pluck('name', 'id');

        $rows = $this->rows()
            ->groupBy('sector')
            ->map(fn (Collection $rows, string $sector): array => $this->summarize($rows, 'sector', $sector) + [
                'name' => match (true) {
                    $sector === self::GROUP => __('mails.report.all_sectors'),
                    default => $sectorNames[(int) $sector] ?? __('mails.report.no_sector'),
                },
                'executors' => $rows->pluck('person')->unique()->count(),
            ])
            ->sortBy([['past_due', 'desc'], ['required', 'desc'], ['name', 'asc']])
            ->values()
            ->all();

        return ['rows' => $rows, 'total' => $this->total()];
    }

    /**
     * @return array{rows: list<array<string, mixed>>, total: array<string, mixed>}
     */
    public function byEmployee(): array
    {
        $rows = $this->rows()
            ->groupBy('person')
            ->map(function (Collection $rows, string $person): array {
                $first = $rows->first();

                return $this->summarize($rows, 'person', $person) + [
                    'name' => $first['person_name'],
                    'initials' => $first['initials'],
                    'left' => $first['left'],
                    'is_group' => $person === self::GROUP,
                ];
            })
            ->sortBy([['past_due', 'desc'], ['required', 'desc'], ['name', 'asc']])
            ->values()
            ->all();

        return ['rows' => $rows, 'total' => $this->total()];
    }

    /**
     * @return array<string, mixed>
     */
    private function total(): array
    {
        $rows = $this->rows();

        return $this->summarize($rows) + [
            'executors' => $rows->pluck('person')->unique()->count(),
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return array{documents: int, required: int, past_due: int, statuses: array<string, int>, multi: int}
     */
    private function summarize(Collection $rows, ?string $key = null, ?string $value = null): array
    {
        $responsible = $rows->where('responsible', true);

        return [
            'documents' => ($key ? $this->rows()->where('responsible', true)->where($key, $value) : $responsible)->pluck('document')->unique()->count(),
            'required' => $rows->count(),
            'past_due' => $rows->where('past_due', true)->count(),
            'statuses' => collect(self::STATUS_COLUMNS)->mapWithKeys(fn (string $status): array => [
                $status => $rows->where('status', $status)
                    ->reject(fn (array $row): bool => $row['status'] === MailDeadline::STATUS_PENDING && ! $row['past_due'])
                    ->count(),
            ])->all(),
            'multi' => $rows->where('multi', true)->count(),
        ];
    }

    /**
     * One entry per deadline and executor.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function rows(): Collection
    {
        if ($this->rows) {
            return $this->rows;
        }

        $documents = MailDocument::query()
            ->with(['items' => fn ($items) => $items->whereHas('deadlines')->with(['executors', 'deadlines'])])
            ->get();

        $rows = collect();

        foreach ($documents as $document) {
            foreach ($document->items->values() as $index => $item) {
                $assignees = $this->assignees($item);
                $multi = count($assignees) > 1;

                foreach ($item->deadlines as $deadline) {
                    foreach ($assignees as $position => $assignee) {
                        $rows->push($assignee + [
                            'document' => $document->id,
                            // The document counts once, for whoever answers for its first item.
                            'responsible' => $index === 0 && $position === 0,
                            'status' => $deadline->status,
                            'past_due' => $deadline->deadline->lt(today()),
                            'multi' => $multi,
                        ]);
                    }
                }
            }
        }

        return $this->rows = $rows;
    }

    /**
     * Who an item's rows are counted for: the main executor and each co-executor, or a single
     * group entry when the item went to many sector heads without a main executor.
     *
     * @return list<array{person: string, person_name: string, initials: string, left: bool, sector: string}>
     */
    private function assignees(MailItem $item): array
    {
        $main = $item->mainExecutor();
        $coExecutors = $item->coExecutors();

        if (! $main && $coExecutors->count() > 1) {
            return [[
                'person' => self::GROUP,
                'person_name' => __('mails.report.all_heads'),
                'initials' => '∑',
                'left' => false,
                'sector' => self::GROUP,
            ]];
        }

        return collect([$main])->filter()->merge($coExecutors)
            ->map(fn (User $user): array => [
                'person' => (string) $user->id,
                'person_name' => $user->short_name,
                'initials' => $user->initials(),
                'left' => (bool) $user->leave,
                'sector' => (string) ($user->pivot->sector_id ?? 0),
            ])
            ->values()
            ->all();
    }
}
