<?php

namespace App\Livewire\Mails;

use App\Models\MailDeadline;
use App\Models\MailDocument;
use App\Models\MailItem;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Split inbox: filterable document list on the left, the selected document on the right,
 * and the create/edit form as a slide-in drawer.
 */
class MailInbox extends Component
{
    /**
     * @var list<string>
     */
    public const TABS = ['all', 'late', 'week', 'review', 'returned'];

    private const PAGE_SIZE = 30;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'all')]
    public string $tab = 'all';

    #[Url(as: 'document')]
    public ?int $selected = null;

    /**
     * Drawer state: null (closed), "new", or the id of the document being edited.
     */
    #[Url(as: 'form')]
    public ?string $form = null;

    public int $limit = self::PAGE_SIZE;

    /**
     * For roles that see every document: "mine" (items assigned to them) or "all".
     * Null means the role's default — deputies and the director start on their own items.
     */
    #[Url(as: 'scope')]
    public ?string $scope = null;

    public function updatedSearch(): void
    {
        $this->limit = self::PAGE_SIZE;
    }

    public function setScope(string $scope): void
    {
        $this->scope = $scope === 'mine' ? 'mine' : 'all';
        $this->limit = self::PAGE_SIZE;
    }

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, self::TABS, true) ? $tab : 'all';
        $this->limit = self::PAGE_SIZE;
    }

    public function select(int $documentId): void
    {
        $this->selected = $documentId;
    }

    public function clearSelection(): void
    {
        $this->selected = null;
    }

    public function loadMore(): void
    {
        $this->limit += self::PAGE_SIZE;
    }

    #[On('open-mail-form')]
    public function openForm(?int $id = null): void
    {
        $this->form = $id ? (string) $id : 'new';
    }

    public function closeForm(): void
    {
        $this->form = null;
    }

    #[On('mail-updated')]
    public function refreshList(): void
    {
        // Re-rendering recomputes counts and badges after a status change in the detail pane.
    }

    public function render(): View
    {
        $user = Auth::user();

        $scope = $this->effectiveScope();

        $documents = $this->filtered($this->tab, $scope)
            ->with(['items.executors', 'items.deadlines'])
            ->orderByDesc('document_date')
            ->orderByDesc('id')
            ->limit($this->limit + 1)
            ->get();

        $hasMore = $documents->count() > $this->limit;
        $documents = $documents->take($this->limit);

        $selectedDocument = $this->selected
            ? MailDocument::query()->visibleTo($user)->find($this->selected)
            : null;

        $formDocument = $this->form && $this->form !== 'new' && $user->canManageMails()
            ? MailDocument::find((int) $this->form)
            : null;

        return view('livewire.mails.mail-inbox', [
            'documents' => $documents->map(fn (MailDocument $document): array => $this->summary($document)),
            'hasMore' => $hasMore,
            'tabs' => collect(self::TABS)->mapWithKeys(fn (string $tab): array => [$tab => $this->filtered($tab, $scope)->count()]),
            'activeScope' => $scope,
            'scopes' => $user->canViewAllMails()
                ? ['mine' => $this->filtered('all', 'mine')->count(), 'all' => $this->filtered('all', 'all')->count()]
                : null,
            'selectedDocument' => $selectedDocument,
            'showForm' => $user->canManageMails() && ($this->form === 'new' || $formDocument),
            'formDocument' => $formDocument,
            'canManage' => $user->canManageMails(),
            'canViewAll' => $user->canViewAllMails(),
        ]);
    }

    /**
     * The scope switch only exists for roles that see every document; everyone else is
     * already limited to their own items by visibleTo().
     */
    private function effectiveScope(): string
    {
        $user = Auth::user();

        if (! $user->canViewAllMails()) {
            return 'all';
        }

        return $this->scope ?? ($user->canManageMails() ? 'all' : 'mine');
    }

    private function filtered(string $tab, string $scope): Builder
    {
        return MailDocument::query()
            ->visibleTo(Auth::user())
            ->when($scope === 'mine', fn (Builder $q) => $q->whereHas('items.executors', fn (Builder $users) => $users->where('users.id', Auth::id())))
            ->when($this->search !== '', function (Builder $query): void {
                $term = '%'.$this->search.'%';
                $query->where(fn (Builder $q) => $q
                    ->where('title', 'like', $term)
                    ->orWhere('document_number', 'like', $term)
                    ->orWhereHas('items', fn (Builder $items) => $items->where('clause', 'like', $term)->orWhere('content', 'like', $term))
                    ->orWhereHas('items.executors', fn (Builder $users) => $users->where('users.name', 'like', $term)));
            })
            ->when($tab === 'late', fn (Builder $q) => $q->whereHas('deadlines', fn (Builder $d) => $d->overdue()))
            ->when($tab === 'week', fn (Builder $q) => $q->whereHas('deadlines', fn (Builder $d) => $d
                ->where('status', '!=', MailDeadline::STATUS_DONE)
                ->whereBetween('deadline', [today(), today()->addDays(7)])))
            ->when($tab === 'review', fn (Builder $q) => $q->whereHas('deadlines', fn (Builder $d) => $d->where('status', MailDeadline::STATUS_IN_REVIEW)))
            ->when($tab === 'returned', fn (Builder $q) => $q->whereHas('deadlines', fn (Builder $d) => $d->where('status', MailDeadline::STATUS_RETURNED)));
    }

    /**
     * @return array{id: int, number: string, date: string|null, title: string, type: string|null, executors: string, total: int, overdue: int, badge: string|null, badgeClass: string}
     */
    private function summary(MailDocument $document): array
    {
        $deadlines = $document->items->flatMap->deadlines;
        $next = $deadlines->where('status', '!=', MailDeadline::STATUS_DONE)->sortBy('deadline')->first();
        $mainExecutors = $document->items->map(fn (MailItem $item) => $item->mainExecutor()?->short_name)->filter()->unique();
        $allExecutors = $document->items->flatMap->executors->unique('id');

        $executors = match (true) {
            $mainExecutors->count() > 2 || ($mainExecutors->isEmpty() && $allExecutors->count() > 2) => __('mails.messages.executors_count', ['count' => $allExecutors->count()]),
            $mainExecutors->isNotEmpty() => $mainExecutors->join(', '),
            default => $allExecutors->map(fn (User $user) => $user->short_name)->join(', '),
        };

        [$badge, $badgeClass] = match (true) {
            $next === null => [$deadlines->isEmpty() ? null : __('mails.statuses.done'), 'mx-pill--done'],
            $next->isOverdue() => [__('mails.messages.days_overdue', ['days' => $next->overdueDays()]), 'mx-pill--late'],
            default => [__('mails.messages.days_left', ['days' => (int) today()->diffInDays($next->deadline)]), 'mx-pill--soon'],
        };

        return [
            'id' => $document->id,
            'number' => $document->document_number ?: '#'.$document->id,
            'date' => $document->document_date?->format('d.m.Y'),
            'title' => $document->title,
            'type' => $document->type,
            'executors' => $executors,
            'total' => $deadlines->count(),
            'overdue' => $deadlines->filter->isOverdue()->count(),
            'badge' => $badge,
            'badgeClass' => $badgeClass,
        ];
    }
}
