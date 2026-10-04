<div>
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fa fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="mx-inbox {{ $selectedDocument ? 'has-selection' : '' }}">
        {{-- Document list --}}
        <section class="mx-list" aria-label="{{ __('mails.title') }}">
            <div class="mx-list-head">
                <div class="mx-list-title">
                    <h3>{{ __('mails.title') }}</h3>
                    <div style="display: flex; gap: 6px;">
                        @if ($canViewAll)
                            <a href="{{ route('mails.report') }}" class="mx-btn mx-btn--icon" title="{{ __('mails.actions.report') }}" aria-label="{{ __('mails.actions.report') }}" wire:navigate>
                                <i class="fa fa-bar-chart"></i>
                            </a>
                        @endif
                        @if ($canManage)
                            <button type="button" class="mx-btn mx-btn--primary" wire:click="openForm">
                                <i class="fa fa-plus"></i> {{ __('mails.actions.create') }}
                            </button>
                        @endif
                    </div>
                </div>
                @if ($scopes)
                    <div class="mx-segmented mx-scope" role="tablist" aria-label="{{ __('mails.scope.label') }}">
                        @foreach (['mine', 'all'] as $scopeOption)
                            <button type="button" role="tab" aria-selected="{{ $activeScope === $scopeOption ? 'true' : 'false' }}"
                                    class="{{ $activeScope === $scopeOption ? 'is-active' : '' }}" wire:click="setScope('{{ $scopeOption }}')" wire:key="scope-{{ $scopeOption }}">
                                {{ __('mails.scope.'.$scopeOption) }} <span>{{ $scopes[$scopeOption] }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif
                <div class="mx-list-tools">
                    <label class="mx-search">
                        <i class="fa fa-search"></i>
                        <input type="text" wire:model.live.debounce.400ms="search" placeholder="{{ __('mails.filters.search') }}" aria-label="{{ __('mails.filters.search') }}">
                    </label>
                    <div class="mx-filter" x-data="{ open: false }" @click.outside="open = false" @keydown.escape="open = false">
                        <button type="button" class="mx-filter-btn {{ $tab !== 'all' ? 'is-filtered' : '' }}" @click="open = !open" :aria-expanded="open" aria-haspopup="menu">
                            <span class="mx-filter-dot mx-filter-dot--{{ $tab }}"></span>
                            <span class="mx-filter-label">{{ __('mails.tabs.'.$tab) }}</span>
                            <span class="mx-filter-count">{{ $tabs[$tab] }}</span>
                            <i class="fa fa-angle-down"></i>
                        </button>
                        <div class="mx-menu" role="menu" x-show="open" x-transition.opacity.duration.100ms style="display: none;">
                            @foreach ($tabs as $tabKey => $tabCount)
                                <button type="button" role="menuitemradio" aria-checked="{{ $tab === $tabKey ? 'true' : 'false' }}"
                                        class="mx-menu-item {{ $tab === $tabKey ? 'is-active' : '' }}"
                                        wire:click="setTab('{{ $tabKey }}')" @click="open = false" wire:key="tab-{{ $tabKey }}">
                                    <span class="mx-filter-dot mx-filter-dot--{{ $tabKey }}"></span>
                                    <span class="mx-menu-label">{{ __('mails.tabs.'.$tabKey) }}</span>
                                    <span class="mx-menu-count">{{ $tabCount }}</span>
                                    <i class="fa fa-check mx-menu-check"></i>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="mx-docs">
                @forelse ($documents as $doc)
                    <button type="button" class="mx-doc {{ $selected === $doc['id'] ? 'is-active' : '' }}" wire:click="select({{ $doc['id'] }})" wire:key="doc-{{ $doc['id'] }}">
                        <div class="mx-doc-top">
                            <span class="mx-doc-num">{{ $doc['number'] }} <span class="mx-muted" style="font-weight: 400;">· {{ $doc['date'] }}</span></span>
                            @if ($doc['badge'])
                                <span class="mx-pill {{ $doc['badgeClass'] }}">{{ $doc['badge'] }}</span>
                            @endif
                        </div>
                        <div class="mx-doc-title mx-clamp">{{ $doc['title'] }}</div>
                        <div class="mx-doc-meta mx-muted">
                            <span>
                                {{ $doc['executors'] ?: '—' }}
                            </span>
                            @if ($doc['total'])
                                <span>{{ __('mails.messages.overdue_of_total', ['overdue' => $doc['overdue'], 'total' => $doc['total']]) }}</span>
                            @endif
                        </div>
                    </button>
                @empty
                    <div class="mx-empty">{{ __('mails.messages.no_documents') }}</div>
                @endforelse

                @if ($hasMore)
                    <button type="button" class="mx-more" wire:click="loadMore">{{ __('mails.actions.load_more') }}</button>
                @endif
            </div>
        </section>

        {{-- Selected document --}}
        @if ($selectedDocument)
            <livewire:mails.mail-show :mail-document="$selectedDocument" :key="'mail-show-'.$selectedDocument->id" />
        @else
            <section class="mx-detail">
                <div class="mx-placeholder">
                    <i class="fa fa-envelope-open-o" style="font-size: 36px;"></i>
                    <span>{{ __('mails.messages.select_document') }}</span>
                </div>
            </section>
        @endif
    </div>

    {{-- Create / edit drawer --}}
    @if ($showForm)
        <div class="mx-backdrop" wire:click="closeForm" x-on:keydown.escape.window="$wire.closeForm()"></div>
        <livewire:mails.mail-form :mail-document="$formDocument" :key="'mail-form-'.$form" />
    @endif
</div>
