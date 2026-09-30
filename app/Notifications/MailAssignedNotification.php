<?php

namespace App\Notifications;

use App\Models\MailDocument;
use App\Models\MailItem;
use App\Models\User;
use App\Notifications\Channels\TelegramChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/**
 * Sent to an executor when items of an edo.ijro.uz document are assigned to them.
 */
class MailAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  list<int>  $itemIds  The document's items assigned to the recipient.
     */
    public function __construct(public MailDocument $document, public array $itemIds, public User $assignedBy)
    {
    }

    /**
     * @return list<string>
     */
    public function via(User $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->telegram_chat_id) {
            $channels[] = TelegramChannel::class;
        }

        return $channels;
    }

    public function toTelegram(User $notifiable): string
    {
        $locale = $notifiable->locale ?? 'ru';
        $t = fn (string $key, array $replace = []): string => __("notifications.mails.{$key}", $replace, $locale);

        $lines = [
            $t('assigned'),
            '',
            $t('document').' <b>'.e($this->document->document_number ?: '#'.$this->document->id).'</b>',
            e($this->document->title),
        ];

        if ($this->document->document_date) {
            $lines[] = $t('date').' '.$this->document->document_date->format('d.m.Y');
        }

        $lines[] = '';
        $lines[] = $t('your_items');

        $items = $this->document->items()->whereIn('id', $this->itemIds)->with(['executors', 'deadlines'])->get();

        foreach ($items as $item) {
            /** @var MailItem $item */
            $role = $item->mainExecutor()?->id === $notifiable->id ? $t('as_main') : $t('as_co');
            $lines[] = '• <b>'.e($item->clause ?: __('mails.fields.item', [], $locale)).'</b> ('.$role.')';

            if ($item->content) {
                $lines[] = '   '.e(Str::limit($item->content, 200));
            }

            if ($item->deadlines->isNotEmpty()) {
                $lines[] = '   '.$t('deadline').' '.$item->deadlines->map(fn ($deadline) => $deadline->deadline->format('d.m.Y'))->join(', ');
            }
        }

        return implode("\n", $lines);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(User $notifiable): array
    {
        return [
            'document_id' => $this->document->id,
            'document_number' => $this->document->document_number ?: '#'.$this->document->id,
            'title' => $this->document->title,
            'items' => count($this->itemIds),
            'assigned_by' => $this->assignedBy->short_name,
            'assigned_by_id' => $this->assignedBy->id,
        ];
    }
}
