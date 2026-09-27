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
 * Imports the legacy edo.ijro.uz control sheet ("База"). Columns: A №, B type, C deadline,
 * E document number, F document date, G document content, H clause, I executor, L status.
 * Merged cells only carry a value in their first row, so empty cells inherit from above.
 */
class ImportMails extends Command
{
    protected $signature = 'mails:import {path : Path to the .xlsx file} {--dry-run : Parse and report without saving}';

    protected $description = 'Import control documents from the legacy edo.ijro.uz Excel sheet';

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
        $author = User::whereHas('role', fn ($query) => $query->where('name', 'Заведующий канцелярии'))->where('leave', 0)->first();
        $created = 0;
        $skipped = 0;

        foreach ($documents as $document) {
            $exists = MailDocument::query()
                ->where('document_number', $document['attributes']['document_number'])
                ->whereDate('document_date', $document['attributes']['document_date'])
                ->exists();

            if ($exists) {
                $skipped++;

                continue;
            }

            if (! $this->option('dry-run')) {
                $service->save($document['attributes'], $document['items'], $author ?? User::findOrFail(1));
            }

            $created++;
        }

        $this->info(($this->option('dry-run') ? 'Would import' : 'Imported')." {$created} documents, skipped {$skipped} already imported.");

        if ($this->unmatched) {
            $this->warn('Executors not matched to users (items saved without them):');
            foreach (array_unique($this->unmatched) as $name) {
                $this->line(" - {$name}");
            }
        }

        return self::SUCCESS;
    }

    /**
     * @return list<array{attributes: array<string, mixed>, items: list<array<string, mixed>>}>
     */
    private function parse(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getSheetByName('База') ?? $spreadsheet->getSheet(0);
        $rows = $sheet->toArray(null, true, false, false);

        $documents = [];
        $doc = null;
        $item = null;
        $lastExecutor = null;

        foreach (array_slice($rows, 2) as $row) {
            [$number, $type, $deadline, , $documentNumber, $documentDate, $title, $clause, $executor] = array_pad($row, 9, null);
            $status = $row[11] ?? null;

            $clause = $this->clean($clause);
            $executor = $this->clean($executor);
            $deadline = $this->date($deadline);

            if ($this->clean($number) !== null) {
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

        try {
            return CarbonImmutable::parse((string) $value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
