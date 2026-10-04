<?php

namespace App\Livewire\Mails;

use App\Models\MailDocument;
use App\Models\MailFile;
use App\Models\MailItem;
use App\Models\User;
use App\Services\MailService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class MailForm extends Component
{
    use WithFileUploads;

    #[Locked]
    public ?int $documentId = null;

    public string $type = '';

    public string $document_number = '';

    public string $document_date = '';

    public string $title = '';

    /**
     * @var list<array{uid: string, id: int|null, clause: string, content: string, main_executor_id: string|null, co_executor_ids: list<string>, deadlines: list<array{id: int|null, deadline: string}>}>
     */
    public array $items = [];

    /**
     * Files already attached to the document being edited.
     *
     * @var list<array{id: int, name: string, size: int|null, url: string}>
     */
    public array $existingFiles = [];

    /**
     * @var list<int>
     */
    public array $removedFileIds = [];

    /**
     * @var list<\Livewire\Features\SupportFileUploads\TemporaryUploadedFile>
     */
    public array $newFiles = [];

    /**
     * @var list<\Livewire\Features\SupportFileUploads\TemporaryUploadedFile>
     */
    public array $pendingFiles = [];

    public function mount(?MailDocument $mailDocument = null): void
    {
        if ($mailDocument?->exists) {
            Gate::authorize('update', $mailDocument);
            $this->fillFromDocument($mailDocument);

            return;
        }

        Gate::authorize('create', MailDocument::class);
        $this->addItem();
    }

    public function addItem(): void
    {
        $this->items[] = [
            'uid' => Str::random(8),
            'id' => null,
            'clause' => '',
            'content' => '',
            'main_executor_id' => null,
            'co_executor_ids' => [],
            // A deadline is mandatory, so the first row is already there to fill in.
            'deadlines' => [['id' => null, 'deadline' => '']],
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function addDeadline(int $index): void
    {
        $this->items[$index]['deadlines'][] = ['id' => null, 'deadline' => ''];
    }

    /**
     * Add the next deadline one month after the item's latest one.
     */
    public function repeatMonthly(int $index): void
    {
        $latest = collect($this->items[$index]['deadlines'])->pluck('deadline')->filter()->max();

        if (! $latest) {
            return;
        }

        $this->items[$index]['deadlines'][] = [
            'id' => null,
            'deadline' => Carbon::parse($latest)->addMonthNoOverflow()->toDateString(),
        ];
    }

    /**
     * Shortcut for "Барча шўъба мудирлари": every active sector head becomes a co-executor.
     */
    public function addAllHeads(int $index): void
    {
        $headIds = User::sectorHeads()->pluck('id')->map(fn (int $id): string => (string) $id);
        $mainExecutorId = $this->items[$index]['main_executor_id'];

        $this->items[$index]['co_executor_ids'] = collect($this->items[$index]['co_executor_ids'])
            ->merge($headIds)
            ->reject(fn (string $id): bool => $id === $mainExecutorId)
            ->unique()
            ->values()
            ->all();
    }

    public function removeDeadline(int $index, int $deadlineIndex): void
    {
        unset($this->items[$index]['deadlines'][$deadlineIndex]);
        $this->items[$index]['deadlines'] = array_values($this->items[$index]['deadlines']);
    }

    /**
     * Each pick or drop adds to the files already chosen instead of replacing them.
     */
    public function updatedPendingFiles(): void
    {
        $this->validate(['pendingFiles.*' => ['file', 'max:51200']]);

        $this->newFiles = [...$this->newFiles, ...$this->pendingFiles];
        $this->pendingFiles = [];
    }

    public function removeNewFile(int $index): void
    {
        unset($this->newFiles[$index]);
        $this->newFiles = array_values($this->newFiles);
    }

    /**
     * Marks an attached file for deletion; it is removed when the form is saved.
     */
    public function removeExistingFile(int $fileId): void
    {
        if (collect($this->existingFiles)->contains('id', $fileId)) {
            $this->removedFileIds = array_values(array_unique([...$this->removedFileIds, $fileId]));
        }
    }

    public function save(MailService $service): void
    {
        $document = $this->documentId ? MailDocument::findOrFail($this->documentId) : null;

        $document ? Gate::authorize('update', $document) : Gate::authorize('create', MailDocument::class);

        $validated = $this->validate();
        $this->ensureItemsHaveAssignees();

        $attributes = collect($validated)
            ->only(['type', 'document_number', 'document_date', 'title'])
            ->map(fn ($value) => $value === '' ? null : $value)
            ->all();

        $document = $service->save($attributes, $validated['items'] ?? [], Auth::user(), $document);

        $document->files()->whereNull('mail_deadline_id')->whereIn('id', $this->removedFileIds)->get()->each->delete();

        foreach ($this->newFiles as $upload) {
            $service->attachFile($document, $upload, Auth::user());
        }

        session()->flash('success', __('mails.messages.saved'));

        $this->redirectRoute('mails.index', ['document' => $document->id], navigate: true);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'type' => ['required', 'string', 'max:255'],
            'document_number' => ['required', 'string', 'max:100'],
            'document_date' => ['required', 'date'],
            'title' => ['required', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['nullable', 'integer'],
            'items.*.clause' => ['nullable', 'string', 'max:255'],
            'items.*.content' => ['nullable', 'string', 'max:5000'],
            'items.*.main_executor_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'items.*.co_executor_ids' => ['array'],
            'items.*.co_executor_ids.*' => ['integer', Rule::exists('users', 'id')],
            'items.*.deadlines' => ['required', 'array', 'min:1'],
            'items.*.deadlines.*.id' => ['nullable', 'integer'],
            'items.*.deadlines.*.deadline' => ['required', 'date'],
            'newFiles.*' => ['file', 'max:51200'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'type' => __('mails.fields.type'),
            'document_number' => __('mails.fields.document_number'),
            'document_date' => __('mails.fields.document_date'),
            'title' => __('mails.fields.title'),
            'items.*.deadlines.*.deadline' => __('mails.fields.deadline'),
            'items.*.main_executor_id' => __('mails.fields.main_executor'),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'items.required' => __('mails.messages.item_required'),
            'items.min' => __('mails.messages.item_required'),
            'items.*.deadlines.required' => __('mails.messages.deadline_required'),
            'items.*.deadlines.min' => __('mails.messages.deadline_required'),
        ];
    }

    private function ensureItemsHaveAssignees(): void
    {
        $messages = [];

        foreach ($this->items as $index => $item) {
            if (blank($item['main_executor_id']) && empty($item['co_executor_ids'])) {
                $messages["items.{$index}.main_executor_id"] = __('mails.messages.assignee_required');
            }
        }

        if ($messages) {
            throw ValidationException::withMessages($messages);
        }
    }

    private function fillFromDocument(MailDocument $document): void
    {
        $document->load(['items.executors', 'items.deadlines', 'files' => fn ($files) => $files->whereNull('mail_deadline_id')]);

        $this->documentId = $document->id;
        $this->existingFiles = $document->files->map(fn (MailFile $file): array => [
            'id' => $file->id,
            'name' => $file->original_name,
            'size' => $file->size,
            'url' => route('mails.files.download', $file),
        ])->all();
        $this->type = (string) $document->type;
        $this->document_number = (string) $document->document_number;
        $this->document_date = (string) $document->document_date?->toDateString();
        $this->title = $document->title;

        $this->items = $document->items->map(fn (MailItem $item): array => [
            'uid' => Str::random(8),
            'id' => $item->id,
            'clause' => (string) $item->clause,
            'content' => (string) $item->content,
            'main_executor_id' => $item->mainExecutor() ? (string) $item->mainExecutor()->id : null,
            'co_executor_ids' => $item->coExecutors()->map(fn (User $user): string => (string) $user->id)->all(),
            'deadlines' => $item->deadlines->map(fn ($deadline): array => [
                'id' => $deadline->id,
                'deadline' => $deadline->deadline->toDateString(),
            ])->all(),
        ])->all();
    }

    public function render(): View
    {
        $assignedUserIds = collect($this->items)
            ->flatMap(fn (array $item): array => array_filter([$item['main_executor_id'], ...$item['co_executor_ids']]))
            ->map(fn ($id): int => (int) $id);

        $users = User::query()
            ->with('sector:id,name')
            ->where(fn ($query) => $query->where('leave', 0)->orWhereIn('id', $assignedUserIds))
            ->orderBy('sector_id')
            ->orderBy('name')
            ->get(['id', 'name', 'sector_id', 'leave']);

        $userOptions = $users->map(fn (User $user): array => [
            'id' => (string) $user->id,
            'label' => $user->short_name,
            'group' => $user->sector?->name ?? '—',
            'initials' => $user->initials(),
            'left' => (bool) $user->leave,
        ])->values();

        return view('livewire.mails.mail-form', [
            'userOptions' => $userOptions,
            'types' => MailDocument::query()->whereNotNull('type')->distinct()->orderBy('type')->pluck('type'),
        ]);
    }
}
