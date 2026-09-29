<section class="mx-detail">
    <button type="button" class="mx-btn mx-btn--link mx-back" wire:click="$parent.clearSelection">
        <i class="fa fa-arrow-left"></i> {{ __('mails.actions.back') }}
    </button>

    {{-- Header --}}
    <header class="mx-doc-head">
        <div class="mx-doc-head-main">
            <div class="mx-doc-kicker">
                @if ($document->type)
                    <span class="mx-chip mx-chip--review">{{ $document->type }}</span>
                @endif
                @if ($document->document_date)
                    <span><i class="fa fa-calendar-o"></i> {{ $document->document_date->format('d.m.Y') }}</span>
                @endif
                @if ($document->creator)
                    <span><i class="fa fa-user-o"></i> {{ $document->creator->short_name }}</span>
                @endif
            </div>
            <h2 class="mx-doc-number">{{ $document->document_number ?: '#'.$document->id }}</h2>
        </div>
        @if ($canManage)
            <div class="mx-doc-actions">
                <button type="button" class="mx-btn" wire:click="$dispatch('open-mail-form', { id: {{ $document->id }} })">
                    <i class="fa fa-pencil"></i> {{ __('mails.actions.edit') }}
                </button>
                <button type="button" class="mx-btn mx-btn--danger mx-btn--icon" wire:click="deleteDocument" wire:confirm="{{ __('mails.actions.confirm_delete') }}" title="{{ __('mails.actions.delete') }}" aria-label="{{ __('mails.actions.delete') }}">
                    <i class="fa fa-trash-o"></i>
                </button>
            </div>
        @endif
    </header>

    <div class="mx-doc-summary">
        <p>{{ $document->title }}</p>
    </div>

    {{-- Execution overview --}}
    @if ($deadlineCount)
        <div class="mx-stats">
            <div class="mx-stat">
                <span class="mx-stat-label">{{ __('mails.fields.items') }}</span>
                <span class="mx-stat-value">{{ $document->items->count() }}</span>
                <span class="mx-stat-sub">{{ __('mails.stats.deadlines', ['count' => $deadlineCount]) }}</span>
            </div>
            <div class="mx-stat {{ $overdueCount ? 'mx-stat--late' : '' }}">
                <span class="mx-stat-label">{{ __('mails.tabs.late') }}</span>
                <span class="mx-stat-value">{{ $overdueCount }}</span>
                <span class="mx-stat-sub">{{ __('mails.stats.of_total', ['total' => $deadlineCount]) }}</span>
            </div>
            <div class="mx-stat">
                <span class="mx-stat-label">{{ __('mails.statuses.in_review') }}</span>
                <span class="mx-stat-value">{{ $statusCounts['in_review'] ?? 0 }}</span>
                <span class="mx-stat-sub">{{ __('mails.statuses.returned') }}: {{ $statusCounts['returned'] ?? 0 }}</span>
            </div>
            <div class="mx-stat">
                <span class="mx-stat-label">{{ __('mails.stats.next_deadline') }}</span>
                <span class="mx-stat-value mx-stat-value--sm">{{ $nextDeadline?->deadline->format('d.m.Y') ?? '—' }}</span>
                <span class="mx-stat-sub">{{ __('mails.statuses.done') }}: {{ $statusCounts['done'] ?? 0 }}</span>
            </div>
        </div>
        <div class="mx-progress" role="img" aria-label="{{ __('mails.fields.status') }}">
            @foreach ($statusShares as $status => $share)
                @if ($share > 0)
                    <span class="mx-progress-{{ $status }}" style="width: {{ $share }}%;" title="{{ __('mails.statuses.'.$status) }}: {{ $statusCounts[$status] }}"></span>
                @endif
            @endforeach
        </div>
    @endif

    {{-- Document files --}}
    @if ($canManage || $document->files->isNotEmpty())
        <section class="mx-section">
            <h3 class="mx-section-head">{{ __('mails.messages.document_files') }} <span>{{ $document->files->count() }}</span></h3>
            <div class="mx-file-grid">
                @foreach ($document->files as $file)
                    <x-mails.file-card wire:key="doc-file-{{ $file->id }}"
                        :name="$file->original_name"
                        :size="$file->size"
                        :meta="$file->created_at->format('d.m.Y')"
                        :href="route('mails.files.download', $file)"
                        :remove-action="$canManage ? 'deleteFile('.$file->id.')' : null"
                        :confirm="__('mails.actions.confirm_delete_file')" />
                @endforeach
                @if ($canManage)
                    <x-mails.dropzone model="upload" />
                @endif
            </div>
            @error('upload') <span class="mx-error">{{ $message }}</span> @enderror
        </section>
    @endif

    {{-- Items --}}
    <section class="mx-section">
        <h3 class="mx-section-head">{{ __('mails.fields.items') }} <span>{{ $document->items->count() }}</span></h3>

        @forelse ($document->items as $item)
            @php
                $mainExecutor = $item->mainExecutor();
                $coExecutors = $item->coExecutors();
            @endphp
            <article class="mx-item" wire:key="item-{{ $item->id }}">
                <div class="mx-item-head">
                    <div class="mx-item-heading">
                        <span class="mx-item-number" title="{{ __('mails.fields.item') }} {{ $loop->iteration }} / {{ $loop->count }}">{{ $loop->iteration }}</span>
                        <div style="min-width: 0;">
                            <span class="mx-item-clause">{{ $item->clause ?: __('mails.fields.item').' '.$loop->iteration }}</span>
                            @if ($item->content)
                                <div class="mx-item-content">{{ $item->content }}</div>
                            @endif
                        </div>
                    </div>

                </div>

                @if ($mainExecutor || $coExecutors->isNotEmpty())
                    <div class="mx-team" x-data="{ open: false }">
                        <div class="mx-team-row">
                            @if ($mainExecutor)
                                <div class="mx-team-main">
                                    <span class="mx-avatar mx-avatar--lg">{{ $mainExecutor->initials() }}</span>
                                    <span class="mx-team-text">
                                        <span class="mx-team-label">{{ __('mails.fields.main_executor') }}</span>
                                        <span class="mx-team-name">
                                            {{ $mainExecutor->short_name }}
                                            @if ($mainExecutor->leave)
                                                <span class="mx-team-left">{{ __('mails.fields.left') }}</span>
                                            @endif
                                        </span>
                                        <span class="mx-team-sector" title="{{ $sectorNames[$mainExecutor->pivot->sector_id] ?? '' }}">{{ $sectorNames[$mainExecutor->pivot->sector_id] ?? '' }}</span>
                                    </span>
                                </div>
                            @endif

                            @if ($coExecutors->isNotEmpty())
                                <button type="button" class="mx-team-co" @click="open = !open" :aria-expanded="open">
                                    <span class="mx-team-stack" aria-hidden="true">
                                        @foreach ($coExecutors->take(5) as $coExecutor)
                                            <span class="mx-avatar mx-avatar--ring">{{ $coExecutor->initials() }}</span>
                                        @endforeach
                                        @if ($coExecutors->count() > 5)
                                            <span class="mx-avatar mx-avatar--ring mx-avatar--more">+{{ $coExecutors->count() - 5 }}</span>
                                        @endif
                                    </span>
                                    <span class="mx-team-text">
                                        <span class="mx-team-label">{{ __('mails.fields.co_executors') }} · {{ $coExecutors->count() }}</span>
                                        <span class="mx-team-summary">
                                            {{ $coExecutors->take(2)->pluck('short_name')->join(', ') }}@if ($coExecutors->count() > 2)<span class="mx-muted"> {{ __('mails.messages.and_more', ['count' => $coExecutors->count() - 2]) }}</span>@endif
                                        </span>
                                    </span>
                                    <span class="mx-team-toggle">
                                        <span x-text="open ? @js(__('mails.actions.hide')) : @js(__('mails.actions.show_all'))">{{ __('mails.actions.show_all') }}</span>
                                        <i class="fa fa-angle-down" :style="open ? 'transform: rotate(180deg)' : ''"></i>
                                    </span>
                                </button>
                            @endif
                        </div>

                        @if ($coExecutors->isNotEmpty())
                            <div class="mx-team-grid" x-show="open" x-transition.opacity.duration.150ms style="display: none;">
                                @foreach ($coExecutors as $coExecutor)
                                    <div class="mx-team-card" wire:key="co-{{ $item->id }}-{{ $coExecutor->id }}">
                                        <span class="mx-avatar mx-avatar--sm">{{ $coExecutor->initials() }}</span>
                                        <span class="mx-team-text">
                                            <span class="mx-team-name">
                                                {{ $coExecutor->short_name }}
                                                @if ($coExecutor->leave)
                                                    <span class="mx-team-left">{{ __('mails.fields.left') }}</span>
                                                @endif
                                            </span>
                                            <span class="mx-team-sector" title="{{ $sectorNames[$coExecutor->pivot->sector_id] ?? '' }}">{{ $sectorNames[$coExecutor->pivot->sector_id] ?? '' }}</span>
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                @if ($item->deadlines->isNotEmpty())
                    <ul class="mx-deadline-list">
                        @foreach ($item->deadlines as $deadline)
                            @php
                                $late = $deadline->isOverdue();
                                $relative = match (true) {
                                    $deadline->status === 'done' => $deadline->completed_at ? __('mails.stats.completed_on', ['date' => $deadline->completed_at->format('d.m.Y')]) : '',
                                    $late => __('mails.messages.days_overdue', ['days' => $deadline->overdueDays()]),
                                    default => __('mails.messages.days_left', ['days' => (int) today()->diffInDays($deadline->deadline)]),
                                };
                            @endphp
                            <li class="mx-deadline {{ $late ? 'is-late' : '' }}" wire:key="deadline-{{ $deadline->id }}">
                                <span class="mx-deadline-date"><span class="mx-dot mx-dot--{{ $late ? 'late' : $deadline->status }}"></span>{{ $deadline->deadline->format('d.m.Y') }}</span>
                                <span class="mx-chip {{ $deadline->chipClass() }}">{{ __('mails.statuses.'.$deadline->status) }}</span>
                                <span class="mx-deadline-relative">{{ $relative }}</span>
                                <span class="mx-deadline-note" title="{{ $deadline->note }}">{{ $deadline->note }}</span>
                                <span class="mx-deadline-files">
                                    @foreach ($deadline->files as $file)
                                        <a href="{{ route('mails.files.download', $file) }}" class="mx-file-pill" title="{{ $file->original_name }}" wire:key="deadline-file-{{ $file->id }}">
                                            <span class="mx-file-badge mx-file-badge--sm mx-file-badge--{{ \App\Models\MailFile::kindFor($file->original_name) }}">{{ \Illuminate\Support\Str::limit(strtoupper(pathinfo($file->original_name, PATHINFO_EXTENSION)) ?: 'FILE', 4, '') }}</span>
                                            <span>{{ \Illuminate\Support\Str::limit($file->original_name, 22) }}</span>
                                        </a>
                                    @endforeach
                                </span>

                                @if ($canManage && isset($statusForms[$deadline->id]))
                                    <div class="mx-pop-wrap" x-data="{ open: false }" @click.outside="open = false" x-on:mail-deadline-saved.window="open = false">
                                        <button type="button" class="mx-icon-btn" @click="open = !open" :aria-expanded="open" title="{{ __('mails.actions.change_status') }}" aria-label="{{ __('mails.actions.change_status') }}">
                                            <i class="fa fa-pencil"></i>
                                        </button>
                                        <div class="mx-pop mx-pop--right" x-show="open" x-transition.opacity.duration.100ms style="display: none;">
                                            <div class="mx-pop-title">№ {{ $loop->parent->iteration }} · {{ $item->clause }} · {{ $deadline->deadline->format('d.m.Y') }}</div>
                                            <div class="mx-status-pills" role="radiogroup" aria-label="{{ __('mails.fields.status') }}">
                                                @foreach (\App\Models\MailDeadline::STATUSES as $statusOption)
                                                    <label class="mx-status-pill mx-status-pill--{{ $statusOption }}">
                                                        <input type="radio" value="{{ $statusOption }}" wire:model="statusForms.{{ $deadline->id }}.status">
                                                        <span>{{ __('mails.statuses.'.$statusOption) }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            @error("statusForms.$deadline->id.status") <span class="mx-error">{{ $message }}</span> @enderror
                                            <label class="mx-field">{{ __('mails.fields.note') }}
                                                <textarea class="mx-input" rows="2" wire:model="statusForms.{{ $deadline->id }}.note" placeholder="{{ __('mails.stats.note_placeholder') }}"></textarea>
                                            </label>
                                            @if ($deadline->files->isNotEmpty())
                                                <div class="mx-field">{{ __('mails.upload.attached') }}
                                                    <div class="mx-pop-files">
                                                        @foreach ($deadline->files as $file)
                                                            <x-mails.file-card wire:key="pop-file-{{ $file->id }}"
                                                                :name="$file->original_name"
                                                                :size="$file->size"
                                                                :meta="$file->created_at->format('d.m.Y')"
                                                                :href="route('mails.files.download', $file)"
                                                                :remove-action="'deleteFile('.$file->id.')'"
                                                                :confirm="__('mails.actions.confirm_delete_file')" />
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                            <x-mails.dropzone model="deadlineUploads.{{ $deadline->id }}" compact :title="__('mails.actions.attach')" />
                                            @error("deadlineUploads.$deadline->id") <span class="mx-error">{{ $message }}</span> @enderror
                                            <div style="display: flex; justify-content: flex-end; gap: 6px;">
                                                <button type="button" class="mx-btn" @click="open = false">{{ __('mails.actions.cancel') }}</button>
                                                <button type="button" class="mx-btn mx-btn--primary" wire:click="updateStatus({{ $deadline->id }})">{{ __('mails.actions.save') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </article>
        @empty
            <p class="mx-muted">{{ __('mails.messages.no_items') }}</p>
        @endforelse
    </section>
</section>
