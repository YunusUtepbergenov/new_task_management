<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MailFile extends Model
{
    public const DIRECTORY = 'files/mails';

    protected $fillable = [
        'mail_document_id',
        'mail_deadline_id',
        'original_name',
        'stored_name',
        'size',
        'uploaded_by',
    ];

    protected static function booted(): void
    {
        static::deleted(fn (MailFile $file) => Storage::disk('local')->delete($file->path()));
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(MailDocument::class, 'mail_document_id');
    }

    public function deadline(): BelongsTo
    {
        return $this->belongsTo(MailDeadline::class, 'mail_deadline_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function path(): string
    {
        return self::DIRECTORY.'/'.$this->stored_name;
    }

    /**
     * Visual file category used for the colored type badge: pdf, doc, xls, img, zip or other.
     */
    public static function kindFor(string $fileName): string
    {
        return match (strtolower(pathinfo($fileName, PATHINFO_EXTENSION))) {
            'pdf' => 'pdf',
            'doc', 'docx', 'odt', 'rtf', 'txt' => 'doc',
            'xls', 'xlsx', 'ods', 'csv' => 'xls',
            'png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp', 'tif', 'tiff' => 'img',
            'zip', 'rar', '7z' => 'zip',
            default => 'other',
        };
    }

    public static function formatSize(?int $bytes): ?string
    {
        if ($bytes === null) {
            return null;
        }

        return match (true) {
            $bytes >= 1048576 => round($bytes / 1048576, 1).' MB',
            $bytes >= 1024 => round($bytes / 1024).' KB',
            default => $bytes.' B',
        };
    }
}
