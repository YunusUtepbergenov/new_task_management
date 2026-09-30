<?php

namespace App\Notifications;

use App\Models\MailDeadline;
use App\Models\User;
use App\Notifications\Channels\TelegramChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to an item's executors when the mailer changes the status of one of its deadlines.
 */
class MailStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(public MailDeadline $deadline, public string $previousStatus, public User $changedBy)
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
        $item = $this->deadline->item;
        $document = $item->document;

        $title = match ($this->deadline->status) {
            MailDeadline::STATUS_IN_REVIEW => $t('status_sent'),
            MailDeadline::STATUS_RETURNED => $t('status_returned'),
            MailDeadline::STATUS_DONE => $t('status_done'),
            default => $t('status_changed'),
        };

        $lines = [
            $title,
            '',
            $t('document').' <b>'.e($document->document_number ?: '#'.$document->id).'</b>',
            e($document->title),
            $t('item').' '.e($item->clause ?: __('mails.fields.item', [], $locale)),
            $t('deadline').' '.$this->deadline->deadline->format('d.m.Y'),
            $t('status').' <b>'.__('mails.statuses.'.$this->deadline->status, [], $locale).'</b>',
        ];

        if ($this->deadline->note) {
            $lines[] = $t('note').' '.e($this->deadline->note);
        }

        return implode("\n", $lines);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(User $notifiable): array
    {
        $document = $this->deadline->item->document;

        return [
            'document_id' => $document->id,
            'document_number' => $document->document_number ?: '#'.$document->id,
            'title' => $document->title,
            'deadline_id' => $this->deadline->id,
            'status' => $this->deadline->status,
            'note' => $this->deadline->note,
            'changed_by' => $this->changedBy->short_name,
            'changed_by_id' => $this->changedBy->id,
        ];
    }
}
