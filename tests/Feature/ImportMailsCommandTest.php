<?php

namespace Tests\Feature;

use App\Models\MailDeadline;
use App\Models\MailDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ImportMailsCommandTest extends TestCase
{
    use RefreshDatabase;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->path = tempnam(sys_get_temp_dir(), 'edo').'.xlsx';
    }

    protected function tearDown(): void
    {
        @unlink($this->path);

        parent::tearDown();
    }

    /**
     * Columns: A №, B type, C deadline, D overdue days, E number, F date, G content, H clause, I executor, J sector, K multi, L status.
     *
     * @param  list<list<mixed>>  $rows
     */
    private function writeSheet(array $rows): void
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet()->setTitle('База');
        $sheet->fromArray(['edo.ijro.uz'], null, 'A1');
        $sheet->fromArray(['№', 'Ҳужжат тури', 'Бажариш муддати', 'Кун', 'Рақам', 'Сана', 'Мазмуни', 'Банд', 'Ижрочи', 'Шўъба', 'Кўп', 'Ҳолати'], null, 'A2');

        foreach ($rows as $index => $row) {
            foreach ([2, 5] as $dateColumn) {
                if (isset($row[$dateColumn])) {
                    $row[$dateColumn] = ExcelDate::PHPToExcel($row[$dateColumn]);
                }
            }
            $sheet->fromArray($row, null, 'A'.($index + 3), true);
        }

        (new Xlsx($spreadsheet))->save($this->path);
    }

    /**
     * @return list<mixed>
     */
    private function row(?int $number, ?string $deadline, ?string $clause = null, ?string $executor = null, ?string $status = 'Бажарилмади', ?string $documentNumber = null, ?string $title = null): array
    {
        return [$number, $number ? 'ЎзР Президенти ҳужжатлари' : null, $deadline, null, $documentNumber, $number ? '2026-02-16' : null, $title, $clause, $executor, null, null, $status];
    }

    public function test_it_imports_documents_items_executors_and_deadlines(): void
    {
        $main = User::factory()->create(['name' => 'Тестқулов Зиёда Арзуевна', 'sector_id' => 8]);
        $second = User::factory()->create(['name' => 'Синовов Ботир Равшанович', 'sector_id' => 3]);
        $left = User::factory()->create(['name' => 'Кетганов Мухсин', 'sector_id' => 14, 'leave' => 1]);

        $this->writeSheet([
            $this->row(1, '2026-05-25', '4-илова 12-банд.', 'З.Тестқулов', 'Кўриб чиқилмоқда', 'ПФ-21', 'Ислоҳотлар тўғрисида'),
            $this->row(null, '2026-06-25'),
            $this->row(null, '2026-07-25', '4-илова 26-банд', 'Б.Синовов', 'Қайтарилди'),
            $this->row(null, '2026-08-25', '4-илова 36-банд.'),
            $this->row(null, '2026-09-25', '4-илова 36-банд.'),
            $this->row(2, '2026-08-15', 'Bayonning 1-bandi', 'Б.Синовов', 'Бажарилди', '19-РА 1-13412', 'Баённома'),
            $this->row(null, '2026-08-15', null, 'З.Тестқулов', 'Бажарилди'),
            $this->row(3, '2026-02-14', '4-банд.', 'Барча шўъба мудирлари', 'Бажарилмади', '2-2026', 'Селектор'),
            $this->row(4, '2025-12-01', '21.1-банд.', 'М.Кетганов', null, '5-2022', 'Стратегия'),
            $this->row(5, '2025-12-01', '1-банд.', 'Н.Номаълумов', null, '7-2022', 'Номаълум'),
        ]);

        $this->artisan('mails:import', ['path' => $this->path])
            ->expectsOutputToContain('Imported 5 documents')
            ->expectsOutputToContain('Н.Номаълумов')
            ->assertSuccessful();

        $first = MailDocument::where('document_number', 'ПФ-21')->with('items.executors', 'items.deadlines')->sole();
        $this->assertSame('2026-02-16', $first->document_date->toDateString());
        $this->assertCount(3, $first->items);

        [$firstItem, $secondItem, $thirdItem] = $first->items;
        $this->assertSame($main->id, $firstItem->mainExecutor()->id);
        $this->assertSame(['2026-05-25', '2026-06-25'], $firstItem->deadlines->map(fn ($d) => $d->deadline->toDateString())->all());
        $this->assertSame(MailDeadline::STATUS_IN_REVIEW, $firstItem->deadlines->first()->status);
        $this->assertSame($second->id, $secondItem->mainExecutor()->id);
        $this->assertSame(MailDeadline::STATUS_RETURNED, $secondItem->deadlines->sole()->status);
        // Executor inherited from the previous row; a repeated clause becomes an extra deadline.
        $this->assertSame($second->id, $thirdItem->mainExecutor()->id);
        $this->assertCount(2, $thirdItem->deadlines);

        $protocolItem = MailDocument::where('document_number', '19-РА 1-13412')->sole()->items()->with('executors', 'deadlines')->sole();
        $this->assertSame($second->id, $protocolItem->mainExecutor()->id);
        $this->assertSame([$main->id], $protocolItem->coExecutors()->pluck('id')->all());
        $this->assertSame(MailDeadline::STATUS_DONE, $protocolItem->deadlines->sole()->status);

        // "Барча шўъба мудирлари": every active head outside the branches becomes a co-executor.
        $groupItem = MailDocument::where('document_number', '2-2026')->sole()->items()->with('executors')->sole();
        $expectedHeads = User::sectorHeads(false)->pluck('id')->sort()->values()->all();
        $this->assertNotEmpty($expectedHeads);
        $this->assertLessThan(User::sectorHeads()->count(), count($expectedHeads));
        $this->assertNull($groupItem->mainExecutor());
        $this->assertSame($expectedHeads, $groupItem->coExecutors()->pluck('id')->sort()->values()->all());

        $leftItem = MailDocument::where('document_number', '5-2022')->sole()->items()->with('executors')->sole();
        $this->assertSame($left->id, $leftItem->mainExecutor()->id);
        $this->assertSame(14, $leftItem->mainExecutor()->pivot->sector_id);

        $unknownItem = MailDocument::where('document_number', '7-2022')->sole()->items()->with('executors')->sole();
        $this->assertCount(0, $unknownItem->executors);
    }

    public function test_running_twice_does_not_duplicate_documents(): void
    {
        $this->writeSheet([$this->row(1, '2026-05-25', '1-банд', 'Барча шўъба ва филиаллар мудирлари', 'Бажарилмади', 'ПҚ-1', 'Хат')]);

        $this->artisan('mails:import', ['path' => $this->path])->assertSuccessful();
        $this->artisan('mails:import', ['path' => $this->path])
            ->expectsOutputToContain('Imported 0 documents, skipped 1')
            ->assertSuccessful();

        $this->assertSame(1, MailDocument::count());
    }

    public function test_dry_run_saves_nothing(): void
    {
        $this->writeSheet([$this->row(1, '2026-05-25', '1-банд', 'Барча шўъба мудирлари', 'Бажарилмади', 'ПҚ-1', 'Хат')]);

        $this->artisan('mails:import', ['path' => $this->path, '--dry-run' => true])
            ->expectsOutputToContain('Would import 1 documents')
            ->assertSuccessful();

        $this->assertSame(0, MailDocument::count());
    }

    public function test_missing_file_fails(): void
    {
        $this->artisan('mails:import', ['path' => 'C:/nope/missing.xlsx'])->assertFailed();
    }
}
