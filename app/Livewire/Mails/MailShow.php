<?php

namespace App\Livewire\Mails;

use App\Models\MailDeadline;
use App\Models\MailDocument;
use App\Models\Sector;
use App\Notifications\MailStatusChangedNotification;
use App\Services\MailService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Detail pane of the inbox. Only the mailer may change statuses and manage files.
 */
class MailShow extends Component
{
    use WithFileUploads;

    #[Locked]
    public int $documentId;

    /**
     * Status form state keyed by deadline id.
     *
     * @var array<int, array{status: string, note: string}>
     */
    public array $statusForms = [];

    /**
     * Response files keyed by deadline id; attached as soon as they finish uploading.
     *
     * @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile>
     */
    public array $deadlineUploads = [];

    public function mount(MailDocument $mailDocument): void
    {
        Gate::authorize('view', $mailDocument);

        $this->documentId = $mailDocument->id;
        $this->resetStatusForms($mailDocument);
    }

    public function updateStatus(int $deadlineId): void
    {
        $document = $this->authorizedDocument('update');
        $deadline = $document->deadlines()->findOrFail($deadlineId);

        $this->validate([
            "statusForms.$deadlineId.status" => ['required', Rule::in(MailDeadline::STATUSES)],
            "statusForms.$deadlineId.note" => ['nullable', 'string', 'max:2000'],
        ]);

        $form = $this->statusForms[$deadlineId];
        $previousStatus = $deadline->status;
        $deadline->changeStatus($form['status'], filled($form['note']) ? $form['note'] : null);

        // "Not sent" is a correction, not news for the executors.
        if ($deadline->status !== $previousStatus && $deadline->status !== MailDeadline::STATUS_PENDING) {
            $recipients = $deadline->item->executors->where('leave', 0)->where('id', '!=', Auth::id());
            Notification::send($recipients, new MailStatusChangedNotification($deadline, $previousStatus, Auth::user()));
        }

        $this->dispatch('mail-updated');
        $this->dispatch('mail-deadline-saved');
    }

    public function updatedDeadlineUploads(mixed $value, string $deadlineId): void
    {
        $document = $this->authorizedDocument('update');
        $deadline = $document->deadlines()->findOrFail((int) $deadlineId);

        $this->validate(["deadlineUploads.$deadlineId" => ['required', 'file', 'max:51200']]);

        app(MailService::class)->attachFile($document, $this->deadlineUploads[$deadlineId], Auth::user(), $deadline);

        unset($this->deadlineUploads[$deadlineId]);
    }

    /**
     * Response files only; document files are managed in the edit form.
     */
    public function deleteFile(int $fileId): void
    {
        $document = $this->authorizedDocument('update');
        $document->files()->whereNotNull('mail_deadline_id')->findOrFail($fileId)->delete();
    }

    public function deleteDocument(MailService $service): void
    {
        $document = $this->authorizedDocument('delete');
        $service->delete($document);

        session()->flash('success', __('mails.messages.deleted'));
        $this->redirectRoute('mails.index', navigate: true);
    }

    private function authorizedDocument(string $ability): MailDocument
    {
        $document = MailDocument::findOrFail($this->documentId);
        Gate::authorize($ability, $document);

        return $document;
    }

    private function resetStatusForms(MailDocument $document): void
    {
        $this->statusForms = $document->deadlines()->get()
            ->mapWithKeys(fn (MailDeadline $deadline): array => [$deadline->id => [
                'status' => $deadline->status,
                'note' => (string) $deadline->note,
            ]])
            ->all();
    }

    public function render(): View
    {
        $document = MailDocument::with([
            'items.executors',
            'items.deadlines.files.uploader',
            'files' => fn ($query) => $query->whereNull('mail_deadline_id')->with('uploader'),
            'creator',
        ])->findOrFail($this->documentId);

        $deadlines = $document->items->flatMap->deadlines;
        $total = max($deadlines->count(), 1);
        $statusCounts = $deadlines->countBy('status');

        return view('livewire.mails.mail-show', [
            'document' => $document,
            'sectorNames' => Sector::pluck('name', 'id'),
            'deadlineCount' => $deadlines->count(),
            'overdueCount' => $deadlines->filter->isOverdue()->count(),
            'statusCounts' => $statusCounts,
            'statusShares' => collect(MailDeadline::STATUSES)->mapWithKeys(fn (string $status): array => [
                $status => round(($statusCounts[$status] ?? 0) / $total * 100, 2),
            ]),
            'nextDeadline' => $deadlines->reject(fn (MailDeadline $deadline): bool => $deadline->status === MailDeadline::STATUS_DONE)->sortBy('deadline')->first(),
            'canManage' => Auth::user()->can('update', $document),
        ]);
    }
}
