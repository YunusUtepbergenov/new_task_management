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
 * "Свод_шўбалар" / "Свод_ходимлар" sheets of the edo.ijro.uz Excel file:
 *
 * - one row = one deadline of one item, counted once for the item's main executor
 *   ("Асосий Ижрочи"); an item given to many sector heads without a main executor counts
 *   under a single "Барча шўъба мудирлари" entry;
 * - rows are split into "not due yet" and "past due" (the deadline date has passed, whatever
 *   the status), and each part is broken down by status;
 * - in the sector summary, items given to every sector head count under "Барча шўъбалар"
 *   (or "Барча шўъба ва филиаллар"), and items of people who left under a separate row.
 */
class MailReportService
{
    /**
     * Status columns in the order of the Excel sheets.
     *
     * @var list<string>
     */
    public const STATUS_COLUMNS = [
        MailDeadline::STATUS_PENDING,
        MailDeadline::STATUS_IN_REVIEW,
        MailDeadline::STATUS_RETURNED,
        MailDeadline::STATUS_DONE,
    ];

    private const GROUP = 'group';

    private const LEFT = 'left';

    /**
     * @var Collection<int, array<string, mixed>>|null
     */
    private ?Collection $rows = null;

    /**
     * People who share deadlines as additional executors, keyed by person, with their details.
     *
     * @var Collection<string, array<string, mixed>>|null
     */
    private ?Collection $extraPeople = null;

    /**
     * @return array{rows: list<array<string, mixed>>, total: array<string, mixed>}
     */
    public function bySector(): array
    {
        $sectorNames = Sector::pluck('name', 'id');

        $rows = $this->rows()
            ->groupBy('sector')
            ->map(fn (Collection $rows, string $sector): array => $this->summarize($rows) + [
                'name' => match ($sector) {
                    MailItem::HEADS_SECTORS => __('mails.report.all_sectors'),
                    MailItem::HEADS_SECTORS_AND_BRANCHES => __('mails.report.all_sectors_and_branches'),
                    self::LEFT => __('mails.report.left_sector'),
                    default => $sectorNames[(int) $sector] ?? __('mails.report.no_sector'),
                },
            ])
            ->sortBy([['past_due', 'desc'], ['required', 'desc'], ['name', 'asc']])
            ->values()
            ->all();

        return ['rows' => $rows, 'total' => $this->summarize($this->rows())];
    }

    /**
     * Every main executor, plus people who are only additional executors (with zero rows,
     * like the Excel sheet lists them), so their shared tasks can still be opened.
     *
     * @return array{rows: list<array<string, mixed>>, total: array<string, mixed>}
     */
    public function byEmployee(): array
    {
        $byPerson = $this->rows()->groupBy('person');
        $people = $this->rows()->keyBy('person')->union($this->extraPeople);

        $rows = $people
            ->map(fn (array $person, string $key): array => $this->summarize($byPerson->get($key, collect())) + [
                'name' => $person['person_name'],
                'initials' => $person['initials'],
                'left' => $person['left'],
                'person' => $key,
                'is_group' => $key === self::GROUP,
            ])
            ->sortBy([['past_due', 'desc'], ['required', 'desc'], ['name', 'asc']])
            ->values()
            ->all();

        return ['rows' => $rows, 'total' => $this->summarize($this->rows())];
    }

    /**
     * One employee's (or the "all heads" group's) items that have deadlines, split into the
     * items they answer for as main executor and the ones they share as additional executor,
     * each grouped by document.
     *
     * @return array{main: Collection<int, array{document: MailDocument, items: Collection<int, MailItem>}>, extra: Collection<int, array{document: MailDocument, items: Collection<int, MailItem>}>}|null
     */
    public function personTasks(string $person): ?array
    {
        $query = MailItem::query()
            ->whereHas('deadlines')
            ->with(['document', 'deadlines', 'executors'])
            ->orderBy('mail_document_id')
            ->orderBy('position');

        if ($person === self::GROUP) {
            $query->whereDoesntHave('executors', fn ($executors) => $executors->where('mail_item_user.is_main', true))
                ->has('executors', '>', 1);
        } elseif (ctype_digit($person)) {
            $query->whereHas('executors', fn ($executors) => $executors->where('users.id', (int) $person));
        } else {
            return null;
        }

        // Same ownership rule as the report rows: the main executor, or the only executor.
        [$main, $extra] = $query->get()->partition(
            fn (MailItem $item): bool => $person === self::GROUP || ($this->owner($item)['person'] ?? null) === $person
        );

        $byDocument = fn (Collection $items): Collection => $items
            ->groupBy('mail_document_id')
            ->map(fn (Collection $items): array => ['document' => $items->first()->document, 'items' => $items->values()])
            ->sortByDesc(fn (array $group) => $group['document']->document_date)
            ->values();

        return ['main' => $byDocument($main), 'extra' => $byDocument($extra)];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return array{required: int, not_due: int, not_due_statuses: array<string, int>, past_due: int, past_due_statuses: array<string, int>, closed: int, executors: int}
     */
    private function summarize(Collection $rows): array
    {
        [$pastDue, $notDue] = $rows->partition(fn (array $row): bool => $row['past_due']);
        $byStatus = fn (Collection $rows): array => collect(self::STATUS_COLUMNS)
            ->mapWithKeys(fn (string $status): array => [$status => $rows->where('status', $status)->count()])
            ->all();

        return [
            'required' => $rows->count(),
            'not_due' => $notDue->count(),
            'not_due_statuses' => $byStatus($notDue),
            'past_due' => $pastDue->count(),
            'past_due_statuses' => $byStatus($pastDue),
            'closed' => $rows->where('status', MailDeadline::STATUS_DONE)->count(),
            'executors' => $rows->pluck('person')->unique()->count(),
        ];
    }

    /**
     * One entry per deadline, for the item's main executor (or the group entry).
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function rows(): Collection
    {
        if ($this->rows) {
            return $this->rows;
        }

        $items = MailItem::query()
            ->whereHas('deadlines')
            ->with(['executors', 'deadlines'])
            ->get();

        $rows = collect();
        $extraPeople = collect();

        foreach ($items as $item) {
            $owner = $this->owner($item);

            if (! $owner) {
                continue;
            }

            $sector = match (true) {
                $item->heads_group !== null => $item->heads_group,
                $owner['person'] === self::GROUP => MailItem::HEADS_SECTORS,
                $owner['left'] => self::LEFT,
                default => $owner['sector'],
            };

            foreach ($item->deadlines as $deadline) {
                $rows->push([
                    ...$owner,
                    'sector' => $sector,
                    'status' => $deadline->status,
                    'past_due' => $deadline->isPastDue(),
                ]);
            }

            if ($item->mainExecutor()) {
                foreach ($item->coExecutors() as $extra) {
                    $extraPeople->put((string) $extra->id, $this->person($extra));
                }
            }
        }

        $this->extraPeople = $extraPeople;

        return $this->rows = $rows;
    }

    /**
     * Who an item's deadlines are counted for: the main executor; without one, a single group
     * entry when the item went to many sector heads, or the only co-executor.
     *
     * @return array{person: string, person_name: string, initials: string, left: bool, sector: string}|null
     */
    private function owner(MailItem $item): ?array
    {
        $main = $item->mainExecutor();
        $coExecutors = $item->coExecutors();

        if ($main) {
            return $this->person($main);
        }

        if ($coExecutors->count() > 1) {
            return [
                'person' => self::GROUP,
                'person_name' => __('mails.report.all_heads'),
                'initials' => '∑',
                'left' => false,
                'sector' => MailItem::HEADS_SECTORS,
            ];
        }

        return $coExecutors->isNotEmpty() ? $this->person($coExecutors->first()) : null;
    }

    /**
     * @return array{person: string, person_name: string, initials: string, left: bool, sector: string}
     */
    private function person(User $user): array
    {
        return [
            'person' => (string) $user->id,
            'person_name' => $user->short_name,
            'initials' => $user->initials(),
            'left' => (bool) $user->leave,
            'sector' => (string) ($user->pivot->sector_id ?? 0),
        ];
    }
}
