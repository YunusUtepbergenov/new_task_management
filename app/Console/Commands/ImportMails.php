<?php

namespace App\Console\Commands;

use App\Models\MailDeadline;
use App\Models\MailDocument;
use App\Models\User;
use App\Services\MailService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Imports the edo.ijro.uz control sheet ("База"). Columns are found by their header names
 * (document number, date, content, clause, executor, status…), so both the old and the newer
 * export layouts work. Merged cells only carry a value in their first row, so empty cells
 * inherit from above. With --sync the database is made to match the file: documents in the
 * file are updated (items, executors, deadlines, statuses) and everything else is deleted.
 */
class ImportMails extends Command
{
    protected $signature = 'mails:import
        {path : Path to the .xlsx file}
        {--dry-run : Parse and report without saving}
        {--sync : Update existing documents and delete documents and items missing from the file}
        {--force : Do not ask for confirmation before deleting}';

    protected $description = 'Import control documents from the edo.ijro.uz Excel sheet';

    /**
     * Header keyword => column key. The first matching header wins; unmatched keys fall back
     * to the positions used by the original export.
     *
     * @var array<string, array{0: list<string>, 1: int}>
     */
    private const COLUMNS = [
        'number' => [['№'], 0],
        'type' => [['ҳужжат тури'], 1],
        'deadline' => [['бажариш муддати'], 2],
        'document_number' => [['топшириқ рақами'], 4],
        'document_date' => [['ҳужжат санаси'], 5],
        'title' => [['ҳужжат мазмуни'], 6],
        'clause' => [['топшириқ мазмуни'], 7],
        'executor' => [['ижрочи'], 8],
        'status' => [['ҳолати'], 11],
    ];

    /**
     * @var array<string, string>
     */
    private const STATUS_MAP = [
        'бажарилмади' => MailDeadline::STATUS_PENDING,
        'кўриб чиқилмоқда' => MailDeadline::STATUS_IN_REVIEW,
        'қайтарилди' => MailDeadline::STATUS_RETURNED,
        'бажарилди' => MailDeadline::STATUS_DONE,
    ];

    /**
     * @var list<string>
     */
    private array $unmatched = [];

    private ?Collection $users = null;

    public function handle(MailService $service): int
    {
        $path = $this->argument('path');

        if (! is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $documents = $this->parse($path);
        $author = User::whereHas('role', fn ($query) => $query->where('name', 'Заведующий канцелярии'))->where('leave', 0)->first()
            ?? User::findOrFail(1);
        $dryRun = (bool) $this->option('dry-run');
        $sync = (bool) $this->option('sync');
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $keptIds = [];

        foreach ($documents as $document) {
            $existing = MailDocument::query()
                ->where('document_number', $document['attributes']['document_number'])
                ->whereDate('document_date', $document['attributes']['document_date'])
                ->first();

            if ($existing) {
                $keptIds[] = $existing->id;

                if (! $sync) {
                    $skipped++;

                    continue;
                }

                if (! $dryRun) {
                    $this->syncDocument($service, $existing, $document, $author);
                }

                $updated++;

                continue;
            }

            if (! $dryRun) {
                $keptIds[] = $service->save($document['attributes'], $document['items'], $author)->id;
            }

            $created++;
        }

        $verb = $dryRun ? 'Would import' : 'Imported';
        $this->info($sync
            ? "{$verb} {$created} new documents and ".($dryRun ? 'would update' : 'updated')." {$updated}."
            : "{$verb} {$created} documents, skipped {$skipped} already imported.");

        if ($sync) {
            $this->deleteMissing($service, $keptIds, $dryRun);
        }

        if ($this->unmatched) {
            $this->warn('Executors not matched to users (items saved without them):');
            foreach (array_unique($this->unmatched) as $name) {
                $this->line(" - {$name}");
            }
        }

        return self::SUCCESS;
    }

    /**
     * Update an existing document from the file: attributes, items (matched by clause),
     * executors and deadlines (matched by date). Statuses from the file win.
     *
     * @param  array{attributes: array<string, mixed>, items: list<array<string, mixed>>}  $document
     */
    private function syncDocument(MailService $service, MailDocument $existing, array $document, User $author): void
    {
        $existing->load('items.deadlines');
        $itemsByClause = $existing->items->keyBy(fn ($item) => $this->normalize((string) $item->clause));

        $items = array_map(function (array $item) use ($itemsByClause): array {
            $match = $itemsByClause->get($this->normalize((string) $item['clause']));

            if ($match) {
                $item['id'] = $match->id;
                $item['content'] = $match->content;
                $deadlinesByDate = $match->deadlines->keyBy(fn ($deadline) => $deadline->deadline->toDateString());
                $item['deadlines'] = array_map(
                    fn (array $deadline): array => ['id' => $deadlinesByDate->get($deadline['deadline'])?->id] + $deadline,
                    $item['deadlines'],
                );
            }

            return $item;
        }, $document['items']);

        $service->save($document['attributes'], $items, $author, $existing);

        foreach ($existing->fresh()->load('items.deadlines')->items as $item) {
            $fileItem = collect($document['items'])->first(fn (array $candidate): bool => $this->normalize((string) $candidate['clause']) === $this->normalize((string) $item->clause));

            foreach ($item->deadlines as $deadline) {
                $status = collect($fileItem['deadlines'] ?? [])->firstWhere('deadline', $deadline->deadline->toDateString())['status'] ?? null;

                if ($status && $status !== $deadline->status) {
                    $deadline->changeStatus($status, $deadline->note);
                }
            }
        }
    }

    /**
     * @param  list<int>  $keptIds
     */
    private function deleteMissing(MailService $service, array $keptIds, bool $dryRun): void
    {
        $missing = MailDocument::query()->whereNotIn('id', $keptIds)->orderBy('id')->get();

        if ($missing->isEmpty()) {
            return;
        }

        $this->warn(($dryRun ? 'Would delete' : 'Deleting').' '.$missing->count().' documents that are not in the file:');
        foreach ($missing as $document) {
            $this->line(' - '.($document->document_number ?: '#'.$document->id).' ('.$document->document_date?->format('d.m.Y').')');
        }

        if ($dryRun || (! $this->option('force') && ! $this->confirm('Delete them together with their items and files?'))) {
            return;
        }

        $missing->each(fn (MailDocument $document) => $service->delete($document));
    }

    /**
     * @return list<array{attributes: array<string, mixed>, items: list<array<string, mixed>>}>
     */
    private function parse(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getSheetByName('База') ?? $spreadsheet->getSheet(0);
        $rows = $sheet->toArray(null, true, false, false);
        [$headerIndex, $columns] = $this->columns($rows);

        $documents = [];
        $doc = null;
        $item = null;
        $lastExecutor = null;

        foreach (array_slice($rows, $headerIndex + 1) as $row) {
            $cell = fn (string $key): mixed => $row[$columns[$key]] ?? null;

            if ($this->normalize((string) $cell('type')) === 'жами') {
                continue;
            }

            [$number, $type, $documentNumber, $documentDate, $title, $status] = [
                $cell('number'), $cell('type'), $cell('document_number'), $cell('document_date'), $cell('title'), $cell('status'),
            ];
            $clause = $this->clean($cell('clause'));
            $executor = $this->clean($cell('executor'));
            $deadline = $this->date($cell('deadline'));

            if ($this->clean($number) !== null || $this->clean($documentNumber) !== null) {
                if ($doc) {
                    $documents[] = $this->finishDocument($doc, $item);
                }

                $doc = [
                    'attributes' => [
                        'type' => $this->clean($type),
                        'document_number' => $this->clean($documentNumber),
                        'document_date' => $this->date($documentDate),
                        'title' => $this->clean($title) ?? '—',
                    ],
                    'items' => [],
                ];
                $item = null;
                $lastExecutor = null;
            }

            if (! $doc || (! $deadline && ! $clause && ! $executor)) {
                continue;
            }

            $sameClause = $item && $clause !== null && $clause === $item['clause'] && ($executor === null || $executor === $lastExecutor);
            $coExecutorRow = $item && $clause === null && $executor !== null && $executor !== $lastExecutor;

            if (! $item || ($clause !== null && ! $sameClause)) {
                if ($item) {
                    $doc['items'][] = $item;
                }

                $lastExecutor = $executor ?? $lastExecutor;
                $item = $this->newItem($clause, $lastExecutor);
            } elseif ($coExecutorRow) {
                $this->addExecutor($item, $executor, false);
            }

            if ($deadline && ! collect($item['deadlines'])->contains('deadline', $deadline)) {
                $item['deadlines'][] = [
                    'deadline' => $deadline,
                    'status' => self::STATUS_MAP[Str::lower((string) $this->clean($status))] ?? MailDeadline::STATUS_PENDING,
                ];
            }
        }

        if ($doc) {
            $documents[] = $this->finishDocument($doc, $item);
        }

        return $documents;
    }

    /**
     * Find the header row (the one naming "Ҳужжат тури") and map column keys to indexes.
     *
     * @param  list<list<mixed>>  $rows
     * @return array{0: int, 1: array<string, int>}
     */
    private function columns(array $rows): array
    {
        $defaults = array_map(fn (array $column): int => $column[1], self::COLUMNS);

        foreach (array_slice($rows, 0, 10, true) as $index => $row) {
            $headers = array_map(fn ($value): string => $this->normalize(preg_replace('/\s+/u', ' ', (string) $value)), $row);

            if (! in_array($this->normalize('ҳужжат тури'), $headers, true)) {
                continue;
            }

            $columns = $defaults;
            foreach (self::COLUMNS as $key => [$keywords]) {
                foreach ($headers as $position => $header) {
                    $keyword = $this->normalize($keywords[0]);
                    // "Ижрочи" must be the whole header, not "Ҳисоб ижрочи" or "Кўп ижрочи".
                    $matches = $key === 'executor' ? $header === $keyword : str_contains($header, $keyword);

                    if ($matches) {
                        $columns[$key] = $position;
                        break;
                    }
                }
            }

            return [$index, $columns];
        }

        return [1, $defaults];
    }

    /**
     * @param  array<string, mixed>  $doc
     * @param  array<string, mixed>|null  $item
     * @return array{attributes: array<string, mixed>, items: list<array<string, mixed>>}
     */
    private function finishDocument(array $doc, ?array $item): array
    {
        if ($item) {
            $doc['items'][] = $item;
        }

        return $doc;
    }

    /**
     * @return array<string, mixed>
     */
    private function newItem(?string $clause, ?string $executor): array
    {
        $item = [
            'clause' => $clause,
            'content' => null,
            'main_executor_id' => null,
            'co_executor_ids' => [],
            'deadlines' => [],
        ];

        if ($executor) {
            $this->addExecutor($item, $executor, true);
        }

        return $item;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function addExecutor(array &$item, string $executor, bool $main): void
    {
        $normalized = $this->normalize($executor);

        if (str_contains($normalized, 'барча')) {
            $headIds = User::sectorHeads(str_contains($normalized, 'филиал'))->pluck('id')->all();
            $item['co_executor_ids'] = array_values(array_unique([...$item['co_executor_ids'], ...$headIds]));

            return;
        }

        $user = $this->findUser($executor);

        if (! $user) {
            $this->unmatched[] = $executor;

            return;
        }

        if ($main && ! $item['main_executor_id']) {
            $item['main_executor_id'] = $user->id;
        } elseif ($user->id !== $item['main_executor_id']) {
            $item['co_executor_ids'][] = $user->id;
        }
    }

    /**
     * Match short names like "З.Ризаева" by surname and first-name initial, preferring active employees.
     */
    private function findUser(string $shortName): ?User
    {
        $parts = preg_split('/[.\s]+/u', $this->normalize($shortName), -1, PREG_SPLIT_NO_EMPTY);

        if (count($parts) < 2) {
            return null;
        }

        [$initial, $surname] = [mb_substr($parts[0], 0, 1), $parts[count($parts) - 1]];

        $this->users ??= User::all(['id', 'name', 'sector_id', 'leave']);

        $matches = $this->users->filter(function (User $user) use ($initial, $surname): bool {
            $nameParts = preg_split('/\s+/u', $this->normalize($user->name), -1, PREG_SPLIT_NO_EMPTY);

            return ($nameParts[0] ?? null) === $surname && str_starts_with($nameParts[1] ?? '', $initial);
        });

        return $matches->sortBy('leave')->first();
    }

    private function normalize(string $value): string
    {
        return strtr(mb_strtolower(trim($value)), [
            'ҳ' => 'х', 'қ' => 'к', 'ғ' => 'г', 'ў' => 'у', 'ё' => 'е', 'ъ' => '', 'ь' => '',
        ]);
    }

    private function clean(mixed $value): ?string
    {
        $value = is_string($value) ? trim(preg_replace('/\s+/u', ' ', $value)) : $value;

        return $value === null || $value === '' ? null : (string) $value;
    }

    private function date(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return CarbonImmutable::instance(ExcelDate::excelToDateTimeObject((float) $value))->toDateString();
        }

        // Dates typed as text: "25.09.2026", "25.12 2026".
        if (preg_match('/^(\d{1,2})\.(\d{1,2})[.\s]+(\d{4})$/u', trim((string) $value), $parts)) {
            return checkdate((int) $parts[2], (int) $parts[1], (int) $parts[3])
                ? sprintf('%04d-%02d-%02d', $parts[3], $parts[2], $parts[1])
                : null;
        }

        try {
            return CarbonImmutable::parse((string) $value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
