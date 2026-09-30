<div class="mx-report">
    <div class="mx-report-head">
        <div>
            <a href="{{ route('mails.index') }}" class="mx-btn mx-btn--link" style="padding: 0;" wire:navigate>
                <i class="fa fa-arrow-left"></i> {{ __('mails.title') }}
            </a>
            <h3 class="mx-report-title">{{ __('mails.report.title') }}</h3>
            <p class="mx-muted" style="margin: 0; font-size: 13px;">{{ __('mails.report.subtitle', ['date' => today()->format('d.m.Y')]) }}</p>
        </div>
        <a href="{{ route('mails.report.export') }}" class="mx-btn">
            <i class="fa fa-file-excel-o" style="color: #15803d;"></i> {{ __('mails.actions.export') }}
        </a>
    </div>

    @php
        $isEmployee = $tab === 'employee';
        $statusColumns = \App\Services\MailReportService::STATUS_COLUMNS;
        $share = fn (array $row): int => $row['required'] ? (int) round($row['past_due'] / $row['required'] * 100) : 0;
        $columnCount = 4 + 2 * (count($statusColumns) + 1);
    @endphp

    <div class="mx-stats mx-stats--5">
        <div class="mx-stat" title="{{ __('mails.report.help.required') }}">
            <span class="mx-stat-label">{{ __('mails.report.required') }}</span>
            <span class="mx-stat-value">{{ $total['required'] }}</span>
        </div>
        <div class="mx-stat" title="{{ __('mails.report.help.not_due') }}">
            <span class="mx-stat-label">{{ __('mails.report.not_due') }}</span>
            <span class="mx-stat-value">{{ $total['not_due'] }}</span>
        </div>
        <div class="mx-stat {{ $total['past_due'] ? 'mx-stat--late' : '' }}" title="{{ __('mails.report.help.past_due') }}">
            <span class="mx-stat-label">{{ __('mails.report.past_due') }}</span>
            <span class="mx-stat-value">{{ $total['past_due'] }} <small class="mx-stat-share">{{ $share($total) }}%</small></span>
        </div>
        <div class="mx-stat" title="{{ __('mails.report.help.closed') }}">
            <span class="mx-stat-label">{{ __('mails.report.closed') }}</span>
            <span class="mx-stat-value">{{ $total['closed'] }}</span>
        </div>
        <div class="mx-stat" title="{{ __('mails.report.help.executors') }}">
            <span class="mx-stat-label">{{ __('mails.report.executors') }}</span>
            <span class="mx-stat-value">{{ $total['executors'] }}</span>
        </div>
    </div>

    <section class="mx-report-card" x-data="{ query: '' }">
        <div class="mx-report-toolbar">
            <div class="mx-segmented" role="tablist">
                <button type="button" role="tab" aria-selected="{{ $isEmployee ? 'false' : 'true' }}" class="{{ $isEmployee ? '' : 'is-active' }}" wire:click="setTab('sector')" @click="query = ''">
                    {{ __('mails.report.by_sector') }} <span>{{ count($sectorRows) }}</span>
                </button>
                <button type="button" role="tab" aria-selected="{{ $isEmployee ? 'true' : 'false' }}" class="{{ $isEmployee ? 'is-active' : '' }}" wire:click="setTab('employee')" @click="query = ''">
                    {{ __('mails.report.by_employee') }} <span>{{ count($employeeRows) }}</span>
                </button>
            </div>
            <label class="mx-search" style="width: 260px;">
                <i class="fa fa-search"></i>
                <input type="text" x-model="query" placeholder="{{ __('mails.report.search') }}" aria-label="{{ __('mails.report.search') }}">
            </label>
        </div>

        <div class="mx-table-wrap">
            <table class="mx-grid-table mx-grid-table--split">
                <colgroup>
                    <col style="width: 44px;">
                    <col style="width: 260px;">
                    <col style="width: 90px;">
                    @foreach (['not_due', 'past_due'] as $part)
                        <col style="width: 64px;">
                        @foreach ($statusColumns as $status)
                            <col style="width: 92px;">
                        @endforeach
                    @endforeach
                    <col style="width: 92px;">
                </colgroup>
                <thead>
                    <tr class="mx-grid-groups">
                        <th colspan="2" class="mx-sticky"></th>
                        <th class="mx-bl"></th>
                        <th colspan="{{ count($statusColumns) + 1 }}" class="mx-grid-group mx-bl" title="{{ __('mails.report.help.not_due') }}">{{ __('mails.report.not_due') }}</th>
                        <th colspan="{{ count($statusColumns) + 1 }}" class="mx-grid-group mx-grid-group--late mx-bl" title="{{ __('mails.report.help.past_due') }}">{{ __('mails.report.past_due') }}</th>
                        <th class="mx-bl"></th>
                    </tr>
                    <tr class="mx-grid-heads">
                        <th class="mx-sticky">№</th>
                        <th class="mx-sticky mx-sticky--name">{{ $isEmployee ? __('mails.report.employee') : __('mails.report.sector') }}</th>
                        <th class="mx-n mx-bl" title="{{ __('mails.report.help.required') }}">{{ __('mails.report.required') }}</th>
                        @foreach (['not_due', 'past_due'] as $part)
                            <th class="mx-n mx-bl">{{ __('mails.report.part_total') }}</th>
                            @foreach ($statusColumns as $status)
                                <th class="mx-n" title="{{ __('mails.report.help.status') }}">{{ __('mails.statuses.'.$status) }}</th>
                            @endforeach
                        @endforeach
                        <th class="mx-n mx-bl" title="{{ __('mails.report.help.closed') }}">{{ __('mails.report.closed') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($rows)
                        @include('partials.mails._report_row', ['row' => $total, 'isTotal' => true])
                    @endif

                    @forelse ($rows as $row)
                        @include('partials.mails._report_row', ['row' => $row, 'isTotal' => false, 'number' => $loop->iteration])
                    @empty
                        <tr><td colspan="{{ $columnCount }}" class="mx-empty">{{ __('mails.report.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <details class="mx-report-help">
            <summary><i class="fa fa-question-circle-o"></i> {{ __('mails.report.help_title') }}</summary>
            <dl>
                <dt>{{ __('mails.report.required') }}</dt><dd>{{ __('mails.report.help.required') }}</dd>
                <dt>{{ __('mails.report.not_due') }}</dt><dd>{{ __('mails.report.help.not_due') }}</dd>
                <dt>{{ __('mails.report.past_due') }}</dt><dd>{{ __('mails.report.help.past_due') }}</dd>
                <dt>{{ __('mails.report.closed') }}</dt><dd>{{ __('mails.report.help.closed') }}</dd>
                @unless ($isEmployee)
                    <dt>{{ __('mails.report.all_sectors') }}</dt><dd>{{ __('mails.report.help.all_sectors') }}</dd>
                @endunless
            </dl>
        </details>
    </section>

    {{-- Selected employee's tasks --}}
    @if ($panel)
        <div class="mx-backdrop" wire:click="closePerson" x-on:keydown.escape.window="$wire.closePerson()"></div>
        <aside class="mx-drawer mx-person-drawer" role="dialog" aria-modal="true" aria-labelledby="person-drawer-title" wire:key="person-drawer-{{ $panel['person'] }}">
            <div class="mx-drawer-head">
                <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
                    <span class="mx-avatar mx-avatar--lg {{ $panel['is_group'] ? 'mx-avatar--group' : '' }}">{{ $panel['initials'] }}</span>
                    <div style="min-width: 0;">
                        <h3 id="person-drawer-title">{{ $panel['name'] }}</h3>
                        @if ($panel['sector'])
                            <div class="mx-muted" style="font-size: 12px;">{{ $panel['sector'] }}</div>
                        @endif
                    </div>
                </div>
                <button type="button" class="mx-btn mx-btn--icon" wire:click="closePerson" aria-label="{{ __('mails.actions.close') }}">
                    <i class="fa fa-times"></i>
                </button>
            </div>

            <div class="mx-drawer-body">
                @foreach (['main' => __('mails.report.role_main'), 'extra' => __('mails.report.as_extra')] as $role => $roleTitle)
                    @if ($panel[$role]->isNotEmpty())
                        @php($taskNumber = 0)
                        <section class="mx-person-role">
                            <h4 class="mx-person-role-title">
                                {{ $roleTitle }}
                                <span>{{ trans_choice('mails.report.documents_count', $panel[$role]->count()) }}, {{ trans_choice('mails.report.deadlines_count', $panel[$role]->sum(fn ($group) => $group['items']->sum(fn ($item) => $item->deadlines->count()))) }}</span>
                            </h4>
                            @foreach ($panel[$role] as $group)
                                <div class="mx-person-doc" wire:key="person-{{ $role }}-{{ $group['document']->id }}">
                                    <div class="mx-person-doc-head">
                                        <a href="{{ route('mails.index', ['document' => $group['document']->id, 'scope' => 'all']) }}" class="mx-person-doc-number" title="{{ __('mails.report.open_document') }}" wire:navigate>{{ $group['document']->document_number ?: '#'.$group['document']->id }}</a>
                                        @if ($group['document']->document_date)
                                            <span class="mx-muted">{{ $group['document']->document_date->format('d.m.Y') }}</span>
                                        @endif
                                        <div class="mx-person-doc-title">{{ $group['document']->title }}</div>
                                    </div>
                                    <table class="mx-person-tasks">
                                        @foreach ($group['items'] as $item)
                                            @foreach ($item->deadlines as $deadline)
                                                @php($taskNumber++)
                                                <tr class="{{ $deadline->isOverdue() ? 'is-late' : '' }}" wire:key="person-deadline-{{ $deadline->id }}">
                                                    <td class="mx-person-task-no">{{ $taskNumber }}.</td>
                                                    <td>
                                                        <div class="mx-person-task-clause">
                                                            {{ $item->clause ?: __('mails.fields.item') }}
                                                            @if ($item->deadlines->count() > 1)
                                                                <span class="mx-muted">({{ __('mails.report.deadline_of', ['number' => $loop->iteration, 'total' => $loop->count]) }})</span>
                                                            @endif
                                                        </div>
                                                        @if ($item->content)
                                                            <div class="mx-person-task-content">{{ $item->content }}</div>
                                                        @endif
                                                        @if ($role === 'extra' && $item->mainExecutor())
                                                            <div class="mx-person-task-content">{{ __('mails.fields.main_executor') }}: {{ $item->mainExecutor()->short_name }}</div>
                                                        @endif
                                                    </td>
                                                    <td class="mx-person-task-date">{{ $deadline->deadline->format('d.m.Y') }}</td>
                                                    <td class="mx-person-task-status">
                                                        <span class="mx-chip {{ $deadline->chipClass() }}">{{ __('mails.statuses.'.$deadline->status) }}</span>
                                                        @if ($deadline->isOverdue())
                                                            <small>{{ __('mails.messages.days_overdue', ['days' => $deadline->overdueDays()]) }}</small>
                                                        @elseif ($deadline->lateDays() !== null)
                                                            <small><x-mails.closed-on-time :deadline="$deadline" /></small>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </table>
                                </div>
                            @endforeach
                        </section>
                    @endif
                @endforeach
            </div>
        </aside>
    @endif
</div>
