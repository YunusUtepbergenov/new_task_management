<?php

namespace Tests\Feature;

use App\Models\Digest;
use App\Models\Scraper;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PrivateFileDownloadsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        Storage::fake('local');
    }

    private function user(): User
    {
        return User::factory()->create(['role_id' => 3, 'sector_id' => 1]);
    }

    private function docx(string $name): UploadedFile
    {
        return UploadedFile::fake()->create(
            $name,
            10,
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        );
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function downloadRoutes(): array
    {
        return [
            'digest source' => ['paper.download', 'files/digest_sources'],
            'digest file' => ['digest.download', 'files/digests'],
            'note source' => ['note.source', 'files/note_sources'],
            'note file' => ['note.download', 'files/notes'],
            'article file' => ['article.download', 'files/articles'],
            'task response' => ['response.download', 'files/responses'],
        ];
    }

    #[DataProvider('downloadRoutes')]
    public function test_guests_cannot_download_files(string $routeName, string $directory): void
    {
        Storage::disk('local')->put($directory.'/report.docx', 'secret');

        $this->get(route($routeName, 'report.docx'))->assertRedirect(route('login'));
    }

    #[DataProvider('downloadRoutes')]
    public function test_authenticated_users_download_files_from_private_storage(string $routeName, string $directory): void
    {
        Storage::disk('local')->put($directory.'/report.docx', 'secret');

        $this->actingAs($this->user())
            ->get(route($routeName, 'report.docx'))
            ->assertOk()
            ->assertDownload('report.docx');
    }

    #[DataProvider('downloadRoutes')]
    public function test_path_traversal_outside_the_directory_is_rejected(string $routeName, string $directory): void
    {
        Storage::disk('local')->put('passwords/user-passwords.xlsx', 'plaintext passwords');

        $user = $this->user();

        foreach (['..\\..\\passwords\\user-passwords.xlsx', '..\\..\\..\\passwords\\user-passwords.xlsx', '..'] as $filename) {
            $this->actingAs($user)
                ->get(url(str_replace('{name}', rawurlencode($filename), $this->routeTemplate($routeName))))
                ->assertNotFound();
        }
    }

    public function test_missing_file_returns_not_found(): void
    {
        $this->actingAs($this->user())
            ->get(route('paper.download', 'missing.docx'))
            ->assertNotFound();
    }

    public function test_digest_source_is_stored_privately_and_not_in_public(): void
    {
        $this->actingAs($this->user())
            ->post(route('digests.store'), [
                'name' => 'Digest',
                'file' => $this->docx('digest.docx'),
                'paper' => $this->docx('source.docx'),
            ])
            ->assertOk();

        $digest = Digest::sole();

        Storage::disk('local')->assertExists('files/digest_sources/'.$digest->paper);
        $this->assertFileDoesNotExist(public_path('digest_sources/'.$digest->paper));
    }

    public function test_scrape_upload_is_stored_privately_and_downloadable(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->post(route('scrape.upload'), [
                'name' => 'Houses',
                'category' => 'houses',
                'date' => '2026-09-01',
                'file' => UploadedFile::fake()->create('houses.zip', 10, 'application/zip'),
            ])
            ->assertRedirect();

        Storage::disk('local')->assertExists('files/scraper/houses/houses.zip');
        $this->assertFileDoesNotExist(public_path('scraper/houses/houses.zip'));

        $this->actingAs($user)
            ->get(route('scrape.download', Scraper::sole()->id))
            ->assertOk()
            ->assertDownload('houses.zip');
    }

    public function test_scrape_upload_rejects_unknown_category(): void
    {
        $this->actingAs($this->user())
            ->post(route('scrape.upload'), [
                'name' => 'Shell',
                'category' => '../..',
                'file' => UploadedFile::fake()->create('shell.php', 1, 'text/plain'),
            ])
            ->assertSessionHasErrors('category');

        $this->assertSame(0, Scraper::count());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_guests_cannot_download_scrapes(): void
    {
        $scrape = Scraper::create(['name' => 'Houses', 'category' => 'houses', 'date' => '2026-09-01', 'file' => 'houses.zip']);
        Storage::disk('local')->put('files/scraper/houses/houses.zip', 'data');

        $this->get(route('scrape.download', $scrape->id))->assertRedirect(route('login'));
    }

    public function test_formatted_digest_is_served_from_private_storage_and_deleted_after_send(): void
    {
        $formatted = tempnam(sys_get_temp_dir(), 'digest');
        file_put_contents($formatted, 'formatted digest');

        Http::fake(['*' => Http::response(json_encode($formatted))]);

        $response = $this->actingAs($this->user())
            ->post(route('upload.test'), ['file' => $this->docx('draft.docx')])
            ->assertOk()
            ->assertDownload();

        $this->assertCount(1, Storage::disk('local')->files('files/tmp_digests'));
        $this->assertFalse(File::isDirectory(public_path('tmp_digests')) && File::allFiles(public_path('tmp_digests')) !== []);

        ob_start();
        $response->baseResponse->sendContent();
        ob_end_clean();

        $this->assertSame([], Storage::disk('local')->files('files/tmp_digests'));

        @unlink($formatted);
    }

    public function test_command_moves_legacy_public_uploads_into_private_storage(): void
    {
        $publicPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'public-'.uniqid();
        File::ensureDirectoryExists($publicPath.'/digest_sources');
        File::ensureDirectoryExists($publicPath.'/scraper/houses');
        File::put($publicPath.'/digest_sources/paper.docx', 'paper');
        File::put($publicPath.'/scraper/houses/houses.zip', 'zip');
        File::put($publicPath.'/index.php', 'front controller');
        $this->app->usePublicPath($publicPath);

        $this->artisan('uploads:make-private')->assertSuccessful();

        Storage::disk('local')->assertExists('files/digest_sources/paper.docx');
        Storage::disk('local')->assertExists('files/scraper/houses/houses.zip');
        $this->assertDirectoryDoesNotExist($publicPath.'/digest_sources');
        $this->assertDirectoryDoesNotExist($publicPath.'/scraper');
        $this->assertFileExists($publicPath.'/index.php');

        File::deleteDirectory($publicPath);
    }

    public function test_command_dry_run_does_not_move_anything(): void
    {
        $publicPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'public-'.uniqid();
        File::ensureDirectoryExists($publicPath.'/note_sources');
        File::put($publicPath.'/note_sources/source.pdf', 'pdf');
        $this->app->usePublicPath($publicPath);

        $this->artisan('uploads:make-private', ['--dry-run' => true])->assertSuccessful();

        $this->assertFileExists($publicPath.'/note_sources/source.pdf');
        Storage::disk('local')->assertMissing('files/note_sources/source.pdf');

        File::deleteDirectory($publicPath);
    }

    public function test_command_leaves_conflicting_files_in_place_and_fails(): void
    {
        $publicPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'public-'.uniqid();
        File::ensureDirectoryExists($publicPath.'/digest_sources');
        File::put($publicPath.'/digest_sources/paper.docx', 'old version');
        Storage::disk('local')->put('files/digest_sources/paper.docx', 'new version');
        $this->app->usePublicPath($publicPath);

        $this->artisan('uploads:make-private')->assertFailed();

        $this->assertFileExists($publicPath.'/digest_sources/paper.docx');
        $this->assertSame('new version', Storage::disk('local')->get('files/digest_sources/paper.docx'));

        File::deleteDirectory($publicPath);
    }

    private function routeTemplate(string $routeName): string
    {
        $uri = app('router')->getRoutes()->getByName($routeName)->uri();

        return preg_replace('/\{[^}]+\}/', '{name}', $uri);
    }
}
