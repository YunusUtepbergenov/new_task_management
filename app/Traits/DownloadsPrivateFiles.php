<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait DownloadsPrivateFiles
{
    /**
     * Stream a file from the private "local" disk (storage/app), which the web server never
     * serves directly. Any name that could climb out of $directory (slashes, backslashes, "..")
     * is rejected, because Flysystem treats "\" as a separator even on Linux.
     */
    protected function downloadPrivateFile(string $directory, ?string $filename): StreamedResponse
    {
        abort_unless($this->isSafeFilename($filename), 404);

        $path = trim($directory, '/').'/'.$filename;

        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path);
    }

    protected function isSafeFilename(?string $filename): bool
    {
        return filled($filename)
            && ! str_contains($filename, '/')
            && ! str_contains($filename, '\\')
            && ! str_contains($filename, "\0")
            && ! in_array($filename, ['.', '..'], true);
    }
}
