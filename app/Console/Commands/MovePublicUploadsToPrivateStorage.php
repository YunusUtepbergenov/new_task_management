<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class MovePublicUploadsToPrivateStorage extends Command
{
    protected $signature = 'uploads:make-private
        {--dry-run : List the files that would be moved without touching them}';

    protected $description = 'Move uploads that were saved under public/ (downloadable without login) into private storage';

    /**
     * Legacy public directory => directory on the private "local" disk.
     *
     * @var array<string, string>
     */
    private const DIRECTORIES = [
        'digest_sources' => 'files/digest_sources',
        'note_sources' => 'files/note_sources',
        'scraper' => 'files/scraper',
        'tmp_digests' => 'files/tmp_digests',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $disk = Storage::disk('local');
        $moved = 0;
        $conflicts = 0;

        foreach (self::DIRECTORIES as $publicDirectory => $privateDirectory) {
            $source = public_path($publicDirectory);

            if (! File::isDirectory($source)) {
                continue;
            }

            foreach (File::allFiles($source, true) as $file) {
                $target = $privateDirectory.'/'.str_replace('\\', '/', $file->getRelativePathname());

                if ($disk->exists($target) && md5_file($disk->path($target)) !== md5_file($file->getPathname())) {
                    $this->error("Conflict, left in place (still public!): {$file->getPathname()}");
                    $conflicts++;

                    continue;
                }

                $this->line(($dryRun ? 'Would move: ' : 'Moved: ')."public/{$publicDirectory}/{$file->getRelativePathname()} -> storage/app/{$target}");
                $moved++;

                if ($dryRun) {
                    continue;
                }

                if ($disk->exists($target)) {
                    File::delete($file->getPathname());

                    continue;
                }

                File::ensureDirectoryExists(dirname($disk->path($target)));
                File::move($file->getPathname(), $disk->path($target));
            }

            if (! $dryRun && File::allFiles($source, true) === []) {
                File::deleteDirectory($source);
            }
        }

        $this->info(($dryRun ? 'Files to move: ' : 'Files moved: ').$moved);

        if ($conflicts > 0) {
            $this->error("{$conflicts} file(s) could not be moved and are still publicly accessible.");

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
