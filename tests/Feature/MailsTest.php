<?php

namespace Tests\Feature;

use App\Exports\MailReportExport;
use App\Livewire\Mails\MailForm;
use App\Livewire\Mails\MailInbox;
use App\Livewire\Mails\MailReport;
use App\Livewire\Mails\MailShow;
use App\Models\MailDeadline;
use App\Models\MailDocument;
use App\Models\MailFile;
use App\Models\MailItem;
use App\Models\User;
use App\Services\MailReportService;
use App\Services\MailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class MailsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        Storage::fake('local');
    }

    private function employee(int $sectorId = 2): User
    {
        return User::factory()->create(['sector_id' => $sectorId]);
    }

    /**
     * @param  list<User>  $coExecutors
     */
    private function documentAssignedTo(?User $main = null, array $coExecutors = []): MailDocument
    {
        return app(MailService::class)->save(
            MailDocument::factory()->raw(),
            [[
                'clause' => '4-банд',
                'content' => 'Тест топшириғи',
                'main_executor_id' => $main?->id,
                'co_executor_ids' => array_map(fn (User $user) => $user->id, $coExecutors),
                'deadlines' => [['deadline' => today()->addWeek()->toDateString()]],
            ]],
            User::factory()->mailer()->create(),
        );
    }

    /**
     * Fill every required field of the create form, with one item for the given executor.
     */
    private function validForm(\Livewire\Features\SupportTesting\Testable $form, ?User $executor = null): \Livewire\Features\SupportTesting\Testable
    {
        return $form
            ->set('type', 'ЎзР Президенти ҳужжатлари')
            ->set('document_number', 'ПФ-99')
            ->set('document_date', '2026-09-01')
            ->set('title', 'Хат')
            ->set('items.0.main_executor_id', $executor ? (string) $executor->id : null)
            ->call('addDeadline', 0)
            ->set('items.0.deadlines.0.deadline', '2026-10-01');
    }

    public function test_every_authenticated_user_can_open_the_mail_list(): void
    {
        $this->actingAs($this->employee())->get(route('mails.index'))->assertOk();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('mails.index'))->assertRedirect(route('login'));
    }

    public function test_only_the_mailer_can_open_the_create_drawer(): void
    {
        $this->actingAs($this->employee())->get(route('mails.create'))->assertForbidden();
        $this->actingAs(User::factory()->director()->create())->get(route('mails.create'))->assertForbidden();
        $this->actingAs(User::factory()->mailer()->create())->get(route('mails.create'))->assertRedirect(route('mails.index', ['form' => 'new']));

        Livewire::actingAs(User::factory()->mailer()->create())
            ->withQueryParams(['form' => 'new'])
            ->test(MailInbox::class)
            ->assertSeeLivewire(MailForm::class);

        Livewire::actingAs(User::factory()->director()->create())
            ->withQueryParams(['form' => 'new'])
            ->test(MailInbox::class)
            ->assertDontSeeLivewire(MailForm::class);
    }

    public function test_show_links_open_the_document_in_the_inbox(): void
    {
        $main = $this->employee();
        $document = $this->documentAssignedTo($main);

        $this->actingAs($main)
            ->get(route('mails.show', $document))
            ->assertRedirect(route('mails.index', ['document' => $document->id]));

        Livewire::actingAs($main)
            ->withQueryParams(['document' => $document->id])
            ->test(MailInbox::class)
            ->assertSeeLivewire(MailShow::class);
    }

    public function test_main_and_co_executors_see_the_document(): void
    {
        $main = $this->employee();
        $coExecutor = $this->employee(3);
        $document = $this->documentAssignedTo($main, [$coExecutor]);

        $this->actingAs($main)->get(route('mails.show', $document))->assertRedirect();
        $this->actingAs($coExecutor)->get(route('mails.show', $document))->assertRedirect();
    }

    public function test_unrelated_employees_including_sector_colleagues_cannot_see_the_document(): void
    {
        $document = $this->documentAssignedTo($this->employee(2));
        $colleague = $this->employee(2);

        $this->actingAs($colleague)->get(route('mails.show', $document))->assertForbidden();

        Livewire::actingAs($colleague)
            ->withQueryParams(['document' => $document->id])
            ->test(MailInbox::class)
            ->assertDontSee($document->document_number)
            ->assertDontSeeLivewire(MailShow::class);
    }

    public function test_sector_head_sees_documents_assigned_to_their_employees(): void
    {
        $document = $this->documentAssignedTo($this->employee(3));

        $this->actingAs(User::factory()->head()->create(['sector_id' => 3]))->get(route('mails.show', $document))->assertRedirect();
        $this->actingAs(User::factory()->head()->create(['sector_id' => 4]))->get(route('mails.show', $document))->assertForbidden();
    }

    public function test_director_deputy_and_mailer_see_every_document(): void
    {
        $document = $this->documentAssignedTo($this->employee());

        foreach ([User::factory()->director()->create(), User::factory()->deputy()->create(), User::factory()->mailer()->create()] as $user) {
            Livewire::actingAs($user)->test(MailInbox::class)->call('setScope', 'all')->assertSee($document->document_number);
        }
    }

    public function test_tabs_filter_documents_and_show_counts(): void
    {
        $overdue = $this->documentAssignedTo($this->employee());
        $overdue->deadlines()->update(['deadline' => today()->subDays(3)]);
        $thisWeek = $this->documentAssignedTo($this->employee());
        $thisWeek->deadlines()->update(['deadline' => today()->addDays(2)]);
        $inReview = $this->documentAssignedTo($this->employee());
        $inReview->deadlines()->update(['deadline' => today()->addMonth(), 'status' => MailDeadline::STATUS_IN_REVIEW]);

        $inbox = Livewire::actingAs(User::factory()->director()->create())->test(MailInbox::class)->call('setScope', 'all');

        $inbox->assertViewHas('tabs', fn ($tabs) => $tabs->all() === ['all' => 3, 'late' => 1, 'week' => 1, 'review' => 1, 'returned' => 0]);

        $inbox->call('setTab', 'late')
            ->assertSee($overdue->document_number)
            ->assertDontSee($thisWeek->document_number)
            ->call('setTab', 'week')
            ->assertSee($thisWeek->document_number)
            ->assertDontSee($overdue->document_number)
            ->call('setTab', 'review')
            ->assertSee($inReview->document_number)
            ->assertDontSee($thisWeek->document_number);
    }

    public function test_search_matches_executor_names(): void
    {
        $executor = User::factory()->create(['name' => 'Қидирилувчи Ходим Тестович', 'sector_id' => 2]);
        $match = $this->documentAssignedTo($executor);
        $other = $this->documentAssignedTo($this->employee());

        Livewire::actingAs(User::factory()->mailer()->create())
            ->test(MailInbox::class)
            ->set('search', 'Қидирилувчи')
            ->assertSee($match->document_number)
            ->assertDontSee($other->document_number);
    }

    public function test_mailer_creates_a_document_with_items_executors_and_deadlines(): void
    {
        $mailer = User::factory()->mailer()->create();
        $main = $this->employee(2);
        $coExecutor = $this->employee(3);

        Livewire::actingAs($mailer)
            ->test(MailForm::class)
            ->set('type', 'ЎзР Президенти ҳужжатлари')
            ->set('document_number', 'ПФ-21')
            ->set('document_date', '2026-02-16')
            ->set('title', 'Ислоҳотларни давом эттириш тўғрисида')
            ->set('items.0.clause', '4-илова 12-банд')
            ->set('items.0.main_executor_id', (string) $main->id)
            ->set('items.0.co_executor_ids', [(string) $coExecutor->id])
            ->call('addDeadline', 0)
            ->set('items.0.deadlines.0.deadline', '2026-10-25')
            ->call('addDeadline', 0)
            ->set('items.0.deadlines.1.deadline', '2026-11-25')
            ->set('pendingFiles', [UploadedFile::fake()->create('pf-21.pdf', 100, 'application/pdf')])
            ->call('save')
            ->assertHasNoErrors();

        $document = MailDocument::sole();
        $item = $document->items()->with('executors', 'deadlines')->sole();

        $this->assertSame('ПФ-21', $document->document_number);
        $this->assertSame($mailer->id, $document->creator_id);
        $this->assertSame($main->id, $item->mainExecutor()->id);
        $this->assertSame(2, $item->mainExecutor()->pivot->sector_id);
        $this->assertSame([$coExecutor->id], $item->coExecutors()->pluck('id')->all());
        $this->assertSame(['2026-10-25', '2026-11-25'], $item->deadlines->map(fn ($d) => $d->deadline->toDateString())->all());
        $this->assertSame(MailDeadline::STATUS_PENDING, $item->deadlines->first()->status);

        $file = MailFile::sole();
        $this->assertSame('pf-21.pdf', $file->original_name);
        Storage::disk('local')->assertExists($file->path());
    }

    public function test_items_require_an_executor_or_a_sector(): void
    {
        $this->validForm(Livewire::actingAs(User::factory()->mailer()->create())->test(MailForm::class))
            ->call('save')
            ->assertHasErrors(['items.0.main_executor_id']);

        $this->assertSame(0, MailDocument::count());
    }

    public function test_title_is_required(): void
    {
        Livewire::actingAs(User::factory()->mailer()->create())
            ->test(MailForm::class)
            ->set('items.0.main_executor_id', (string) $this->employee()->id)
            ->call('save')
            ->assertHasErrors(['title' => 'required']);
    }

    public function test_non_mailer_cannot_use_the_form(): void
    {
        Livewire::actingAs(User::factory()->director()->create())
            ->test(MailForm::class)
            ->assertForbidden();
    }

    public function test_editing_keeps_deadline_statuses_and_executor_sector_history(): void
    {
        $executor = $this->employee(2);
        $document = $this->documentAssignedTo($executor);
        $deadline = $document->deadlines()->sole();
        $deadline->changeStatus(MailDeadline::STATUS_IN_REVIEW, 'ijro');

        // The executor moves to another sector; the assignment keeps the original sector.
        $executor->update(['sector_id' => 7]);

        Livewire::actingAs(User::factory()->mailer()->create())
            ->test(MailForm::class, ['mailDocument' => $document])
            ->set('items.0.content', 'Янгиланган мазмун')
            ->call('addDeadline', 0)
            ->set('items.0.deadlines.1.deadline', today()->addMonth()->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        $item = MailItem::with('executors', 'deadlines')->sole();

        $this->assertSame('Янгиланган мазмун', $item->content);
        $this->assertSame(2, $item->mainExecutor()->pivot->sector_id);
        $this->assertCount(2, $item->deadlines);
        $this->assertSame(MailDeadline::STATUS_IN_REVIEW, $deadline->fresh()->status);
        $this->assertSame('ijro', $deadline->fresh()->note);
    }

    public function test_removing_an_item_on_edit_deletes_it(): void
    {
        $document = $this->documentAssignedTo($this->employee());
        $replacement = $this->employee(3);

        Livewire::actingAs(User::factory()->mailer()->create())
            ->test(MailForm::class, ['mailDocument' => $document])
            ->call('addItem')
            ->set('items.1.main_executor_id', (string) $replacement->id)
            ->call('addDeadline', 1)
            ->set('items.1.deadlines.0.deadline', today()->addMonth()->toDateString())
            ->call('removeItem', 0)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame([$replacement->id], MailItem::sole()->executors()->pluck('users.id')->all());
        $this->assertSame(1, MailDeadline::count());
    }

    public function test_mailer_changes_a_deadline_status(): void
    {
        $document = $this->documentAssignedTo($this->employee());
        $deadline = $document->deadlines()->sole();

        Livewire::actingAs(User::factory()->mailer()->create())
            ->test(MailShow::class, ['mailDocument' => $document])
            ->set("statusForms.{$deadline->id}.status", MailDeadline::STATUS_DONE)
            ->set("statusForms.{$deadline->id}.note", 'ijro тасдиқлади')
            ->call('updateStatus', $deadline->id)
            ->assertHasNoErrors()
            ->assertDispatched('mail-updated');

        $deadline->refresh();
        $this->assertSame(MailDeadline::STATUS_DONE, $deadline->status);
        $this->assertSame('ijro тасдиқлади', $deadline->note);
        $this->assertNotNull($deadline->completed_at);
    }

    public function test_invalid_status_is_rejected(): void
    {
        $document = $this->documentAssignedTo($this->employee());
        $deadline = $document->deadlines()->sole();

        Livewire::actingAs(User::factory()->mailer()->create())
            ->test(MailShow::class, ['mailDocument' => $document])
            ->set("statusForms.{$deadline->id}.status", 'hacked')
            ->call('updateStatus', $deadline->id)
            ->assertHasErrors(["statusForms.{$deadline->id}.status"]);
    }

    public function test_executor_cannot_change_status_or_upload_files(): void
    {
        $executor = $this->employee();
        $document = $this->documentAssignedTo($executor);
        $deadline = $document->deadlines()->sole();

        Livewire::actingAs($executor)
            ->test(MailShow::class, ['mailDocument' => $document])
            ->set("statusForms.{$deadline->id}.status", MailDeadline::STATUS_DONE)
            ->call('updateStatus', $deadline->id)
            ->assertForbidden();

        Livewire::actingAs($executor)
            ->test(MailShow::class, ['mailDocument' => $document])
            ->set('upload', UploadedFile::fake()->create('answer.pdf', 10))
            ->assertForbidden();

        Livewire::actingAs($executor)
            ->test(MailShow::class, ['mailDocument' => $document])
            ->set("deadlineUploads.{$deadline->id}", UploadedFile::fake()->create('answer.pdf', 10))
            ->assertForbidden();

        $this->assertSame(MailDeadline::STATUS_PENDING, $deadline->fresh()->status);
        $this->assertSame(0, MailFile::count());
    }

    public function test_mailer_uploads_a_file_to_a_deadline_and_executor_downloads_it(): void
    {
        $executor = $this->employee();
        $document = $this->documentAssignedTo($executor);
        $deadline = $document->deadlines()->sole();

        Livewire::actingAs(User::factory()->mailer()->create())
            ->test(MailShow::class, ['mailDocument' => $document])
            ->set("deadlineUploads.{$deadline->id}", UploadedFile::fake()->create('javob.docx', 10))
            ->assertHasNoErrors()
            ->assertSet('deadlineUploads', []);

        $file = MailFile::sole();
        $this->assertSame($deadline->id, $file->mail_deadline_id);
        $this->assertSame(10 * 1024, $file->size);

        $this->actingAs($executor)
            ->get(route('mails.files.download', $file))
            ->assertOk()
            ->assertDownload('javob.docx');

        $this->actingAs($this->employee(5))
            ->get(route('mails.files.download', $file))
            ->assertForbidden();
    }

    public function test_deleting_a_document_removes_its_files(): void
    {
        $mailer = User::factory()->mailer()->create();
        $document = $this->documentAssignedTo($this->employee());
        $file = app(MailService::class)->attachFile($document, UploadedFile::fake()->create('a.pdf', 10), $mailer);

        Livewire::actingAs($mailer)
            ->test(MailShow::class, ['mailDocument' => $document])
            ->call('deleteDocument')
            ->assertRedirect(route('mails.index'));

        $this->assertSame(0, MailDocument::count());
        $this->assertSame(0, MailItem::count());
        Storage::disk('local')->assertMissing($file->path());
    }

    public function test_reports_are_limited_to_roles_that_see_everything(): void
    {
        $this->actingAs($this->employee())->get(route('mails.report'))->assertForbidden();
        $this->actingAs(User::factory()->head()->create())->get(route('mails.report.export'))->assertForbidden();
        $this->actingAs(User::factory()->director()->create())->get(route('mails.report'))->assertOk();
    }

    public function test_report_counts_one_row_per_deadline_and_executor_like_the_excel_sheet(): void
    {
        $executor = $this->employee(2);
        $coExecutor = $this->employee(3);
        $document = $this->documentAssignedTo($executor, [$coExecutor]);
        $item = $document->items()->sole();
        $item->deadlines()->update(['deadline' => today()->subDays(5), 'status' => MailDeadline::STATUS_IN_REVIEW]);
        // A second deadline that is not due yet and not started: counted as required, not in a status column.
        MailDeadline::factory()->for($item, 'item')->create(['deadline' => today()->addMonth()]);

        $reports = app(MailReportService::class);
        $total = $reports->bySector()['total'];

        // 2 deadlines x 2 executors.
        $this->assertSame(1, $total['documents']);
        $this->assertSame(4, $total['required']);
        $this->assertSame(2, $total['past_due']);
        $this->assertSame(['done' => 0, 'pending' => 0, 'in_review' => 2, 'returned' => 0], $total['statuses']);
        $this->assertSame(4, $total['multi']);
        $this->assertSame(2, $total['executors']);

        $sectorTwo = collect($reports->bySector()['rows'])->firstWhere('name', \App\Models\Sector::find(2)->name);
        $this->assertSame(1, $sectorTwo['documents']);
        $this->assertSame(1, $sectorTwo['executors']);
        $this->assertSame(2, $sectorTwo['required']);
        $this->assertSame(1, $sectorTwo['past_due']);

        $byEmployee = collect($reports->byEmployee()['rows']);
        $this->assertSame(1, $byEmployee->firstWhere('name', $executor->short_name)['documents']);
        $this->assertSame(0, $byEmployee->firstWhere('name', $coExecutor->short_name)['documents']);
        $this->assertSame(2, $byEmployee->firstWhere('name', $coExecutor->short_name)['required']);

        Livewire::actingAs(User::factory()->director()->create())
            ->test(MailReport::class)
            ->assertSee(\App\Models\Sector::find(2)->name)
            ->call('setTab', 'employee')
            ->assertSee($executor->short_name);
    }

    public function test_items_given_to_all_heads_count_once_under_a_group_row(): void
    {
        $heads = User::factory()->head()->count(3)->sequence(['sector_id' => 2], ['sector_id' => 3], ['sector_id' => 4])->create();
        $document = $this->documentAssignedTo(null, $heads->all());
        $document->deadlines()->update(['deadline' => today()->subDay()]);

        $reports = app(MailReportService::class);

        $this->assertSame(1, $reports->bySector()['total']['required']);
        $this->assertSame(0, $reports->bySector()['total']['multi']);

        $group = collect($reports->byEmployee()['rows'])->sole();
        $this->assertTrue($group['is_group']);
        $this->assertSame(__('mails.report.all_heads'), $group['name']);
        $this->assertSame(1, $group['required']);
        $this->assertSame(1, $group['statuses']['pending']);
    }

    public function test_report_export_downloads_an_excel_file(): void
    {
        Excel::fake();

        $this->actingAs(User::factory()->deputy()->create())->get(route('mails.report.export'));

        Excel::assertDownloaded('mail-report-'.today()->format('Y-m-d').'.xlsx', fn (MailReportExport $export) => count($export->sheets()) === 2);
    }

    public function test_all_heads_shortcut_adds_every_active_head_as_co_executor(): void
    {
        $main = $this->employee();
        $leftHead = User::factory()->head()->create(['sector_id' => 3, 'leave' => 1]);

        $component = Livewire::actingAs(User::factory()->mailer()->create())
            ->test(MailForm::class)
            ->set('items.0.main_executor_id', (string) $main->id)
            ->call('addAllHeads', 0);

        $expected = User::sectorHeads()->pluck('id')->map(fn ($id) => (string) $id)->sort()->values()->all();
        $actual = collect($component->get('items.0.co_executor_ids'))->sort()->values()->all();

        $this->assertNotEmpty($expected);
        $this->assertSame($expected, $actual);
        $this->assertNotContains((string) $leftHead->id, $actual);
    }

    public function test_repeat_monthly_adds_the_next_month_after_the_latest_deadline(): void
    {
        Livewire::actingAs(User::factory()->mailer()->create())
            ->test(MailForm::class)
            ->call('addDeadline', 0)
            ->set('items.0.deadlines.0.deadline', '2026-01-31')
            ->call('repeatMonthly', 0)
            ->assertSet('items.0.deadlines.1.deadline', '2026-02-28')
            ->call('repeatMonthly', 0)
            ->assertSet('items.0.deadlines.2.deadline', '2026-03-28');
    }

    public function test_saving_redirects_to_the_document_in_the_inbox(): void
    {
        $this->validForm(Livewire::actingAs(User::factory()->mailer()->create())->test(MailForm::class), $this->employee())
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('mails.index', ['document' => MailDocument::sole()->id]));
    }

    public function test_mailer_uploads_a_document_file_as_soon_as_it_is_chosen(): void
    {
        $document = $this->documentAssignedTo($this->employee());

        Livewire::actingAs(User::factory()->mailer()->create())
            ->test(MailShow::class, ['mailDocument' => $document])
            ->set('upload', UploadedFile::fake()->create('asl-hujjat.pdf', 20))
            ->assertHasNoErrors()
            ->assertSet('upload', null)
            ->assertSee('asl-hujjat.pdf');

        $file = MailFile::sole();
        $this->assertNull($file->mail_deadline_id);
        Storage::disk('local')->assertExists($file->path());
    }

    public function test_form_file_picks_add_up_instead_of_replacing_each_other(): void
    {
        $component = Livewire::actingAs(User::factory()->mailer()->create())
            ->test(MailForm::class)
            ->set('pendingFiles', [UploadedFile::fake()->create('a.pdf', 10)])
            ->set('pendingFiles', [UploadedFile::fake()->create('b.docx', 10), UploadedFile::fake()->create('c.xlsx', 10)])
            ->assertSet('pendingFiles', []);

        $names = collect($component->get('newFiles'))->map->getClientOriginalName()->all();
        $this->assertSame(['a.pdf', 'b.docx', 'c.xlsx'], $names);

        $component->call('removeNewFile', 1);
        $this->assertSame(['a.pdf', 'c.xlsx'], collect($component->get('newFiles'))->map->getClientOriginalName()->all());
    }

    public function test_file_kind_and_size_helpers(): void
    {
        $this->assertSame('pdf', MailFile::kindFor('Хат.PDF'));
        $this->assertSame('doc', MailFile::kindFor('javob.docx'));
        $this->assertSame('xls', MailFile::kindFor('svod.xlsx'));
        $this->assertSame('img', MailFile::kindFor('scan.jpeg'));
        $this->assertSame('other', MailFile::kindFor('noextension'));
        $this->assertSame('512 B', MailFile::formatSize(512));
        $this->assertSame('15 KB', MailFile::formatSize(15 * 1024));
        $this->assertSame('2.5 MB', MailFile::formatSize((int) (2.5 * 1048576)));
        $this->assertNull(MailFile::formatSize(null));
    }

    public function test_employee_report_has_no_sector_column(): void
    {
        $executor = $this->employee(2);
        $this->documentAssignedTo($executor);
        $sectorName = \App\Models\Sector::find(2)->name;

        Livewire::actingAs(User::factory()->director()->create())
            ->test(MailReport::class)
            ->assertSee($sectorName)
            ->call('setTab', 'employee')
            ->assertSee($executor->short_name)
            ->assertDontSee($sectorName);

        $employeeSheet = (new \App\Exports\Sheets\MailReportSheet('employee'))->view()->render();
        $this->assertStringContainsString($executor->short_name, $employeeSheet);
        $this->assertStringNotContainsString($sectorName, $employeeSheet);

        $sectorSheet = (new \App\Exports\Sheets\MailReportSheet('sector'))->view()->render();
        $this->assertStringContainsString($sectorName, $sectorSheet);
    }

    public function test_items_are_numbered_in_the_detail_pane(): void
    {
        $executor = $this->employee();
        $document = app(MailService::class)->save(
            MailDocument::factory()->raw(),
            collect(['1-банд', '2-банд', '3-банд'])->map(fn (string $clause) => [
                'clause' => $clause,
                'main_executor_id' => $executor->id,
                'deadlines' => [['deadline' => today()->addWeek()->toDateString()]],
            ])->all(),
            User::factory()->mailer()->create(),
        );

        Livewire::actingAs($executor)
            ->test(MailShow::class, ['mailDocument' => $document])
            ->assertSeeHtmlInOrder([
                '<span class="mx-item-number"', '>1</span>', '1-банд',
                '<span class="mx-item-number"', '>2</span>', '2-банд',
                '<span class="mx-item-number"', '>3</span>', '3-банд',
            ]);
    }

    public function test_sidebar_badge_counts_open_items_the_user_can_see(): void
    {
        $executor = $this->employee(3);
        $colleague = $this->employee(3);
        $head = User::factory()->head()->create(['sector_id' => 3]);

        $open = $this->documentAssignedTo($executor);
        $overdue = $this->documentAssignedTo($executor);
        $overdue->deadlines()->update(['deadline' => today()->subDay()]);
        $done = $this->documentAssignedTo($executor);
        $done->deadlines()->update(['status' => MailDeadline::STATUS_DONE]);
        $this->documentAssignedTo($colleague);
        $this->documentAssignedTo($this->employee(4));

        $this->assertSame(['count' => 2, 'overdue' => true], $executor->mailBadge());
        $this->assertSame(['count' => 1, 'overdue' => false], $colleague->fresh()->mailBadge());
        $this->assertSame(3, $head->mailBadge()['count']);
        $this->assertSame(4, User::factory()->director()->create()->mailBadge()['count']);
        $this->assertSame(['count' => 0, 'overdue' => false], $this->employee(5)->mailBadge());
    }

    public function test_sidebar_shows_the_badge_only_when_there_are_open_items(): void
    {
        $executor = $this->employee();
        $this->documentAssignedTo($executor);

        $this->actingAs($executor)->get(route('mails.index'))
            ->assertOk()
            ->assertSee('<b class="sidebar-count ', false);

        $this->actingAs($this->employee(5))->get(route('mails.index'))
            ->assertOk()
            ->assertDontSee('<b class="sidebar-count', false);
    }

    public function test_deputies_start_on_their_own_items_and_can_switch_to_all(): void
    {
        $deputy = User::factory()->deputy()->create();
        $own = $this->documentAssignedTo($deputy);
        $other = $this->documentAssignedTo($this->employee());

        Livewire::actingAs($deputy)
            ->test(MailInbox::class)
            ->assertViewHas('activeScope', 'mine')
            ->assertViewHas('scopes', ['mine' => 1, 'all' => 2])
            ->assertSee($own->document_number)
            ->assertDontSee($other->document_number)
            ->call('setScope', 'all')
            ->assertSee($own->document_number)
            ->assertSee($other->document_number);
    }

    public function test_director_also_starts_on_own_items(): void
    {
        $this->documentAssignedTo($this->employee());

        Livewire::actingAs(User::factory()->director()->create())
            ->test(MailInbox::class)
            ->assertViewHas('activeScope', 'mine')
            ->assertViewHas('tabs', fn ($tabs) => $tabs['all'] === 0);
    }

    public function test_mailer_starts_on_all_documents(): void
    {
        $document = $this->documentAssignedTo($this->employee());

        Livewire::actingAs(User::factory()->mailer()->create())
            ->test(MailInbox::class)
            ->assertViewHas('activeScope', 'all')
            ->assertSee($document->document_number);
    }

    public function test_regular_employees_do_not_get_the_scope_switch(): void
    {
        $executor = $this->employee();
        $this->documentAssignedTo($executor);

        Livewire::actingAs($executor)
            ->test(MailInbox::class)
            ->assertViewHas('scopes', null)
            ->assertDontSeeHtml('mx-scope');
    }

    public function test_mailer_can_delete_an_outdated_deadline_file(): void
    {
        $mailer = User::factory()->mailer()->create();
        $executor = $this->employee();
        $document = $this->documentAssignedTo($executor);
        $deadline = $document->deadlines()->sole();
        $file = app(MailService::class)->attachFile($document, UploadedFile::fake()->create('eski-javob.docx', 10), $mailer, $deadline);

        Livewire::actingAs($mailer)
            ->test(MailShow::class, ['mailDocument' => $document])
            ->assertSee('eski-javob.docx')
            ->assertSeeHtml('deleteFile('.$file->id.')');

        Livewire::actingAs($executor)
            ->test(MailShow::class, ['mailDocument' => $document])
            ->assertDontSeeHtml('deleteFile('.$file->id.')')
            ->call('deleteFile', $file->id)
            ->assertForbidden();
        $this->assertModelExists($file);

        Livewire::actingAs($mailer)
            ->test(MailShow::class, ['mailDocument' => $document])
            ->call('deleteFile', $file->id)
            ->assertDontSee('eski-javob.docx');

        $this->assertModelMissing($file);
        Storage::disk('local')->assertMissing($file->path());
    }

    public function test_type_number_date_items_and_deadlines_are_required(): void
    {
        Livewire::actingAs(User::factory()->mailer()->create())
            ->test(MailForm::class)
            ->set('title', 'Фақат мазмун')
            ->set('items.0.main_executor_id', (string) $this->employee()->id)
            ->call('save')
            ->assertHasErrors(['type' => 'required', 'document_number' => 'required', 'document_date' => 'required', 'items.0.deadlines' => 'required']);

        Livewire::actingAs(User::factory()->mailer()->create())
            ->test(MailForm::class)
            ->set('type', 'ЎзР Президенти ҳужжатлари')
            ->set('document_number', 'ПФ-99')
            ->set('document_date', '2026-09-01')
            ->set('title', 'Топшириқсиз')
            ->call('removeItem', 0)
            ->call('save')
            ->assertHasErrors(['items' => 'required']);

        $this->assertSame(0, MailDocument::count());
    }
}
