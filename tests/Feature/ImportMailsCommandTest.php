<?php

namespace Tests\Feature;

use App\Models\MailDeadline;
use App\Models\MailDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
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

    /**
     * The newer export: two extra count columns before the document number, a "ЖАМИ" row,
     * status in column N, dates typed as text, and documents without a row number.
     *
     * @param  list<list<mixed>>  $rows
     */
    private function writeNewLayout(array $rows): void
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet()->setTitle('База');
        $sheet->fromArray(['edo.ijro.uz (28.09.2026)'], null, 'A1');
        $sheet->fromArray(['Уникал №', 'Ҳужжат тури', 'Бажариш муддати', 'Ижро қилиш зарур хужжат сони', 'Муддати ўтиб кетган топшириқлар', 'Муддати ўтган кунлар сони', 'Топшириқ рақами', 'Ҳужжат санаси', 'Ҳужжат мазмуни', 'Топшириқ мазмуни', 'Ижрочи', 'Шўъба номи', 'Бир нечта ходимга бириктирилган', 'Ҳолати / изоҳ', 'Ҳисоб ID', 'Ҳисоб ижрочи'], null, 'A2');
        $sheet->fromArray([12, 'ЖАМИ', null, 64, 57], null, 'A3');

        foreach ($rows as $index => [$number, $deadline, $documentNumber, $documentDate, $title, $clause, $executor, $status]) {
            $sheet->fromArray([$number, $documentNumber ? 'ЎзР Президенти ҳужжатлари' : null, $deadline, 1, 1, 10, $documentNumber, $documentDate, $title, $clause, $executor, null, null, $status, 1, 'ignored'], null, 'A'.($index + 4), true);
        }

        (new Xlsx($spreadsheet))->save($this->path);
    }

    public function test_it_reads_the_newer_layout_by_header_names(): void
    {
        $user = User::factory()->create(['name' => 'Янгиев Равшан Ботирович', 'sector_id' => 4]);

        $this->writeNewLayout([
            [1, '25.09.2026', 'ПФ-50', '21.05.2026', 'Янги ҳужжат', '2-банд', 'Р.Янгиев', 'Кўриб чиқилмоқда'],
            [null, '25.12 2026', null, null, null, '3-банд', null, 'Қайтарилди'],
            [null, '30.07.2026', '19-PA 1/1-3665', '08.07.2026', 'Рақамсиз ҳужжат', '1-хатбоши', 'Р.Янгиев', 'Бажарилмади'],
        ]);

        $this->artisan('mails:import', ['path' => $this->path])
            ->expectsOutputToContain('Imported 2 documents')
            ->assertSuccessful();

        $first = MailDocument::where('document_number', 'ПФ-50')->with('items.deadlines', 'items.executors')->sole();
        $this->assertSame('2026-05-21', $first->document_date->toDateString());
        $this->assertSame(['2-банд', '3-банд'], $first->items->pluck('clause')->all());
        $this->assertSame('2026-12-25', $first->items[1]->deadlines->sole()->deadline->toDateString());
        $this->assertSame(MailDeadline::STATUS_RETURNED, $first->items[1]->deadlines->sole()->status);
        $this->assertSame($user->id, $first->items[1]->mainExecutor()->id);
        $this->assertSame(1, MailDocument::where('document_number', '19-PA 1/1-3665')->count());
    }

    public function test_sync_updates_existing_documents_and_deletes_what_is_missing(): void
    {
        $old = User::factory()->create(['name' => 'Эскиев Олим', 'sector_id' => 3]);
        $new = User::factory()->create(['name' => 'Янгиев Равшан', 'sector_id' => 4]);

        $this->writeNewLayout([
            [1, '25.05.2026', 'ПФ-21', '16.02.2026', 'Ислоҳотлар', '26-банд', 'О.Эскиев', 'Бажарилмади'],
            [null, '25.06.2026', null, null, null, '12-банд', 'О.Эскиев', 'Бажарилмади'],
            [2, '01.12.2025', '03-РА 2-926', '06.02.2024', 'Эски ҳужжат', '11-банд', 'О.Эскиев', 'Бажарилмади'],
        ]);
        $this->artisan('mails:import', ['path' => $this->path])->assertSuccessful();

        $kept = MailDocument::where('document_number', 'ПФ-21')->sole()->items()->where('clause', '26-банд')->sole();

        $this->writeNewLayout([
            [1, '25.05.2026', 'ПФ-21', '16.02.2026', 'Ислоҳотлар', '26-банд', 'Р.Янгиев', 'Кўриб чиқилмоқда'],
            [null, '25.09.2026', null, null, null, '30-банд', 'Р.Янгиев', 'Қайтарилди'],
            [2, '25.09.2026', '02-PA 1/1-2652', '21.05.2026', 'Янги ҳужжат', '2-банд', 'Р.Янгиев', 'Бажарилмади'],
        ]);

        $this->artisan('mails:import', ['path' => $this->path, '--sync' => true, '--force' => true])
            ->expectsOutputToContain('Imported 1 new documents and updated 1')
            ->expectsOutputToContain('03-РА 2-926')
            ->assertSuccessful();

        $this->assertSame(['ПФ-21', '02-PA 1/1-2652'], MailDocument::orderBy('id')->pluck('document_number')->all());

        $document = MailDocument::where('document_number', 'ПФ-21')->with('items.deadlines', 'items.executors')->sole();
        $this->assertSame(['26-банд', '30-банд'], $document->items->pluck('clause')->all());

        $item = $document->items->firstWhere('clause', '26-банд');
        $this->assertSame($kept->id, $item->id);
        $this->assertSame($new->id, $item->mainExecutor()->id);
        $this->assertSame(MailDeadline::STATUS_IN_REVIEW, $item->deadlines->sole()->status);
        $this->assertSame(MailDeadline::STATUS_RETURNED, $document->items->firstWhere('clause', '30-банд')->deadlines->sole()->status);
        $this->assertNotContains($old->id, $document->items->flatMap->executors->pluck('id')->all());
    }

    public function test_sync_dry_run_changes_nothing(): void
    {
        $this->writeNewLayout([[1, '25.05.2026', 'ПФ-21', '16.02.2026', 'Ислоҳотлар', '26-банд', null, 'Бажарилмади']]);
        $this->artisan('mails:import', ['path' => $this->path])->assertSuccessful();
        MailDocument::factory()->create(['document_number' => 'ЭСКИ-1']);

        $this->artisan('mails:import', ['path' => $this->path, '--sync' => true, '--dry-run' => true])
            ->expectsOutputToContain('Would delete 1 documents')
            ->assertSuccessful();

        $this->assertSame(2, MailDocument::count());
    }

    /**
     * The newest export: "Асосий Ижрочи" and "Қўшимча ижрочи" columns, one row per deadline.
     *
     * @param  list<array{0: int|null, 1: string, 2: string|null, 3: string|null, 4: string|null, 5: string|null, 6: string|null, 7: string|null, 8: string|null}>  $rows
     */
    private function writeSplitLayout(array $rows): void
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet()->setTitle('База');
        $sheet->fromArray(['edo.ijro.uz (28.09.2026)'], null, 'A1');
        $sheet->fromArray(['Уникал №', 'Ҳужжат тури', 'Бажариш муддати', 'Ижро қилиш зарур хужжат сони', 'Муддати ўтиб кетган топшириқлар', 'Муддати ўтган кунлар сони', 'Топшириқ рақами', 'Ҳужжат санаси', 'Ҳужжат мазмуни', 'Топшириқ мазмуни', 'Асосий Ижрочи', 'Қўшимча ижрочи', 'Шўъба номи', 'Бир нечта ходимга бириктирилган', 'Ҳолати / изоҳ', 'Ҳисоб ID', 'Ҳисоб ижрочи'], null, 'A2');
        $sheet->fromArray([13, 'ЖАМИ', null, 57, 50], null, 'A3');

        foreach ($rows as $index => [$number, $deadline, $documentNumber, $documentDate, $title, $clause, $main, $extra, $status]) {
            $sheet->fromArray([$number, $documentNumber ? 'ЎзР Президенти ҳужжатлари' : null, $deadline, 1, 1, 10, $documentNumber, $documentDate, $title, $clause, $main, $extra, null, $extra ? 1 : null, $status, 1, 'ignored'], null, 'A'.($index + 4), true);
        }

        (new Xlsx($spreadsheet))->save($this->path);
    }

    public function test_it_reads_main_and_additional_executor_columns(): void
    {
        $main = User::factory()->create(['name' => 'Асосийев Бахтиёр', 'sector_id' => 7]);
        $first = User::factory()->create(['name' => 'Қўшимчаева Зиёда', 'sector_id' => 8]);
        $second = User::factory()->create(['name' => 'Ёрдамчиев Руслан', 'sector_id' => 4]);
        $mailer = User::factory()->mailer()->create(['name' => 'Мактубова Дилафруз', 'sector_id' => 2]);

        $this->writeSplitLayout([
            [1, '15.08.2026', '19-РА 1-13412', '28.07.2026', 'Баённома', 'Bayonning 1-bandi', 'Б.Асосийев', "З.Қўшимчаева\nР.Ёрдамчиев", 'Кўриб чиқилмоқда'],
            [null, '25.09.2026', null, null, null, 'Bayonning 3-bandi', null, null, 'Бажарилмади'],
            [2, '01.11.2025', 'ПФ-117', '25.07.2025', 'Ижро интизоми', '85-банд.', 'Диля опа', 'Барча шўъба мудирлари', 'Бажарилмади'],
            [null, '01.12.2025', null, null, null, null, null, null, 'Бажарилмади'],
        ]);

        $this->artisan('mails:import', ['path' => $this->path, '--alias' => ['Диля опа=Мактубова Дилафруз']])
            ->expectsOutputToContain('Imported 2 documents')
            ->doesntExpectOutputToContain('not matched')
            ->assertSuccessful();

        $protocol = MailDocument::where('document_number', '19-РА 1-13412')->with('items.executors', 'items.deadlines')->sole();
        $this->assertCount(2, $protocol->items);
        foreach ($protocol->items as $item) {
            // The second item leaves the executor cells empty and inherits them from the first.
            $this->assertSame($main->id, $item->mainExecutor()->id);
            $this->assertEqualsCanonicalizing([$first->id, $second->id], $item->coExecutors()->pluck('id')->all());
            $this->assertCount(1, $item->deadlines);
        }

        $order = MailDocument::where('document_number', 'ПФ-117')->with('items.executors', 'items.deadlines')->sole();
        $item = $order->items->sole();
        $this->assertSame($mailer->id, $item->mainExecutor()->id);
        $this->assertEqualsCanonicalizing(User::sectorHeads(false)->pluck('id')->all(), $item->coExecutors()->pluck('id')->all());
        $this->assertCount(2, $item->deadlines);
    }

    public function test_it_reads_the_flat_layout_with_new_status_names(): void
    {
        Notification::fake();
        User::factory()->head()->count(2)->sequence(['sector_id' => 3], ['sector_id' => 4])->create();
        $head = User::factory()->head()->create(['name' => 'Мудиров Ҳасан', 'sector_id' => 7]);
        $executor = User::factory()->create(['name' => 'Асосийев Бахтиёр', 'sector_id' => 7]);

        // Every row repeats the № and the document columns.
        $this->writeSplitLayout([
            [1, '25.05.2026', 'ПҚ-314', '22.10.2025', 'Қарор', '2-илова 8-банд.', 'Б.Асосийев', null, 'Юборилмаган'],
            [2, '25.07.2026', 'ПҚ-314', '22.10.2025', 'Қарор', '2-илова 8-банд.', 'Б.Асосийев', null, 'Юборилган, кўриб чиқилмоқда'],
            [3, '25.09.2026', 'ПҚ-314', '22.10.2025', 'Қарор', '3-банд.', 'Б.Асосийев', null, 'Ёпилган'],
            [4, '14.02.2026', '2-2026', '14.01.2026', 'Фармон', '4-банд.', 'Ҳ.Мудиров', 'Барча шўъба мудирлари', 'Қайтарилган'],
        ]);

        $this->artisan('mails:import', ['path' => $this->path])
            ->expectsOutputToContain('Imported 2 documents')
            ->assertSuccessful();

        $decree = MailDocument::where('document_number', 'ПҚ-314')->with('items.deadlines')->sole();
        $this->assertSame(['2-илова 8-банд.', '3-банд.'], $decree->items->pluck('clause')->all());
        $this->assertSame([MailDeadline::STATUS_PENDING, MailDeadline::STATUS_IN_REVIEW], $decree->items[0]->deadlines->pluck('status')->all());
        $this->assertSame(MailDeadline::STATUS_DONE, $decree->items[1]->deadlines->sole()->status);
        // The file does not say when it was closed.
        $this->assertNull($decree->items[1]->deadlines->sole()->completed_at);
        $this->assertSame($executor->id, $decree->items[0]->executors->sole()->id);

        // A head as main executor with "all heads" as additional executors is still an "all heads" item.
        $order = MailDocument::where('document_number', '2-2026')->with('items.deadlines', 'items.executors')->sole()->items->sole();
        $this->assertSame($head->id, $order->mainExecutor()->id);
        $this->assertSame(\App\Models\MailItem::HEADS_SECTORS, $order->heads_group);
        $this->assertSame(MailDeadline::STATUS_RETURNED, $order->deadlines->sole()->status);
        Notification::assertNothingSent();
    }

    public function test_unknown_nicknames_are_reported_without_an_alias(): void
    {
        $this->writeSplitLayout([[1, '01.11.2025', 'ПФ-117', '25.07.2025', 'Ижро интизоми', '85-банд.', 'Диля опа', null, 'Бажарилмади']]);

        $this->artisan('mails:import', ['path' => $this->path])
            ->expectsOutputToContain('Диля опа')
            ->assertSuccessful();
    }

    public function test_sync_asks_before_overwriting_existing_data(): void
    {
        $this->writeNewLayout([[1, '25.05.2026', 'ПФ-21', '16.02.2026', 'Ислоҳотлар', '26-банд', null, 'Бажарилмади']]);
        $this->artisan('mails:import', ['path' => $this->path])->assertSuccessful();
        $deadline = MailDeadline::sole();
        $deadline->changeStatus(MailDeadline::STATUS_DONE);

        $this->artisan('mails:import', ['path' => $this->path, '--sync' => true])
            ->expectsConfirmation('--sync overwrites statuses, executors and deadlines changed in the app with the values from this file, and deletes documents that are not in it. Continue?', 'no')
            ->expectsOutputToContain('Nothing changed.')
            ->assertSuccessful();

        $this->assertSame(MailDeadline::STATUS_DONE, $deadline->fresh()->status);
    }
}
