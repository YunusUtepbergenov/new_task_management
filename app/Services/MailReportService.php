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
 * - one row = one deadline of one item, counted once for the item's main executor
 *   ("Асосий Ижрочи"); an item given to many sector heads without a main executor counts
 *   under a single "Барча шўъба мудирлари" entry;
 * - additional executors ("Қўшимча ижрочи") do not get rows of their own; the employee
 *   summary shows how many deadlines they share as an extra column instead;
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
     * Deadlines shared as an additional executor, one entry per deadline and person.
     *
     * @var Collection<int, array<string, mixed>>|null
     */
    private ?Collection $extraRows = null;

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
        $this->rows();
        $mainRows = $this->rows->groupBy('person');
        $extraRows = $this->extraRows->groupBy('person');

        $rows = $mainRows->keys()->merge($extraRows->keys())->unique()
            ->map(function (string $person) use ($mainRows, $extraRows): array {
                $own = $mainRows->get($person, collect());
                $first = $own->first() ?? $extraRows->get($person)->first();

                return $this->summarize($own, 'person', $person) + [
                    'name' => $first['person_name'],
                    'initials' => $first['initials'],
                    'left' => $first['left'],
                    'person' => $person,
                    'is_group' => $person === self::GROUP,
                    'as_extra' => $extraRows->get($person, collect())->count(),
                ];
            })
            ->sortBy([['past_due', 'desc'], ['required', 'desc'], ['as_extra', 'desc'], ['name', 'asc']])
            ->values()
            ->all();

        return ['rows' => $rows, 'total' => $this->total() + ['as_extra' => $this->extraRows->count()]];
    }

    /**
     * One employee's (or the "all heads" group's) items that have deadlines, split into the
     * items they answer for as main executor and the ones they share as additional executor,
     * each grouped by document.
     *
     * "responsible" names the person the document itself is counted for when that is someone else.
     *
     * @return array{main: Collection<int, array{document: MailDocument, items: Collection<int, MailItem>, responsible: string|null}>, extra: Collection<int, array{document: MailDocument, items: Collection<int, MailItem>, responsible: string|null}>}|null
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

        $items = $query->get();
        // Same ownership rule as the report rows: the main executor, or the only executor.
        [$main, $extra] = $items->partition(
            fn (MailItem $item): bool => $person === self::GROUP || ($this->owner($item)['person'] ?? null) === $person
        );

        // Whoever answers for a document's first item gets the document in the "documents" column.
        $responsible = MailItem::query()
            ->whereIn('mail_document_id', $items->pluck('mail_document_id')->unique())
            ->whereHas('deadlines')
            ->with('executors')
            ->orderBy('position')
            ->orderBy('id')
            ->get()
            ->unique('mail_document_id')
            ->mapWithKeys(fn (MailItem $item): array => [$item->mail_document_id => $this->owner($item)]);

        $byDocument = fn (Collection $items): Collection => $items
            ->groupBy('mail_document_id')
            ->map(fn (Collection $items, int $documentId): array => [
                'document' => $items->first()->document,
                'items' => $items->values(),
                'responsible' => ($responsible[$documentId]['person'] ?? null) === $person
                    ? null
                    : ($responsible[$documentId]['person_name'] ?? null),
            ])
            ->sortByDesc(fn (array $group) => $group['document']->document_date)
            ->values();

        return ['main' => $byDocument($main), 'extra' => $byDocument($extra)];
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
        $responsible = $key ? $this->rows()->where('responsible', true)->where($key, $value) : $rows->where('responsible', true);

        return [
            'documents' => $responsible->pluck('document')->unique()->count(),
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
     * One entry per deadline, for the item's main executor (or the group entry).
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
        $extraRows = collect();

        foreach ($documents as $document) {
            foreach ($document->items->values() as $index => $item) {
                $main = $item->mainExecutor();
                $extras = $main ? $item->coExecutors() : collect();
                $owner = $this->owner($item);

                if (! $owner) {
                    continue;
                }

                foreach ($item->deadlines as $deadline) {
                    $rows->push($owner + [
                        'document' => $document->id,
                        // The document counts once, for whoever answers for its first item.
                        'responsible' => $index === 0,
                        'status' => $deadline->status,
                        'past_due' => $deadline->deadline->lt(today()),
                        'multi' => $extras->isNotEmpty(),
                    ]);

                    foreach ($extras as $extra) {
                        $extraRows->push($this->person($extra));
                    }
                }
            }
        }

        $this->extraRows = $extraRows;

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
                'sector' => self::GROUP,
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
