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
        $pastDueShare = $total['deadlines'] ? round($total['past_due'] / $total['deadlines'] * 100) : 0;
        $isEmployee = $tab === 'employee';
        $statusDots = ['pending' => 'late', 'in_review' => 'in_review', 'returned' => 'returned', 'done' => 'done'];
    @endphp
    <div class="mx-stats mx-stats--5">
        <div class="mx-stat">
            <span class="mx-stat-label">{{ __('mails.report.items') }}</span>
            <span class="mx-stat-value">{{ $total['items'] }}</span>
        </div>
        <div class="mx-stat">
            <span class="mx-stat-label">{{ __('mails.report.deadlines') }}</span>
            <span class="mx-stat-value">{{ $total['deadlines'] }}</span>
        </div>
        <div class="mx-stat {{ $total['past_due'] ? 'mx-stat--late' : '' }}">
            <span class="mx-stat-label">{{ __('mails.report.overdue') }}</span>
            <span class="mx-stat-value">{{ $total['past_due'] }} <small class="mx-stat-share">{{ $pastDueShare }}%</small></span>
        </div>
        <div class="mx-stat">
            <span class="mx-stat-label">{{ __('mails.fields.executors') }}</span>
            <span class="mx-stat-value">{{ $total['employees'] }}</span>
        </div>
        <div class="mx-stat">
            <span class="mx-stat-label">{{ __('mails.report.multi') }}</span>
            <span class="mx-stat-value">{{ $total['multi'] }}</span>
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
            <table class="mx-grid-table">
                <colgroup>
                    <col style="width: 48px;">
                    <col>
                    <col style="width: 104px;"><col style="width: 96px;">
                    <col style="width: 112px;"><col style="width: 124px;"><col style="width: 172px;"><col style="width: 116px;"><col style="width: 108px;">
                    <col style="width: 120px;">
                </colgroup>
                <thead>
                    <tr class="mx-grid-groups">
                        <th colspan="2"></th>
                        <th colspan="2" class="mx-grid-group mx-bl">{{ __('mails.report.volume') }}</th>
                        <th colspan="5" class="mx-grid-group mx-grid-group--late mx-bl">{{ __('mails.report.past_due_group') }}</th>
                        <th class="mx-bl"></th>
                    </tr>
                    <tr>
                        <th>№</th>
                        <th>{{ $isEmployee ? __('mails.report.employee') : __('mails.report.sector') }}</th>
                        <th class="mx-n mx-bl">{{ $isEmployee ? __('mails.report.main_items') : __('mails.report.items_short') }}</th>
                        <th class="mx-n">{{ __('mails.report.deadlines_short') }}</th>
                        <th class="mx-n mx-bl">{{ __('mails.report.total_short') }}</th>
                        @foreach (\App\Models\MailDeadline::STATUSES as $status)
                            <th class="mx-n">{{ __('mails.statuses.'.$status) }}</th>
                        @endforeach
                        <th class="mx-n mx-bl">{{ __('mails.report.multi_short') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($rows)
                        <tr class="mx-grid-total">
                            <td></td>
                            <td>{{ __('mails.report.total') }}</td>
                            <td class="mx-n mx-bl">{{ $isEmployee ? collect($rows)->sum('main_items') : $total['items'] }}</td>
                            <td class="mx-n">{{ $total['deadlines'] }}</td>
                            <td class="mx-n mx-bl"><span class="mx-grid-past">{{ $total['past_due'] }}</span><span class="mx-grid-pct">{{ $pastDueShare }}%</span></td>
                            @foreach ($total['past_due_by_status'] as $status => $count)
                                <td class="mx-n">
                                    @if ($count)
                                        <span class="mx-grid-dot"><i class="mx-dot mx-dot--{{ $statusDots[$status] }}"></i>{{ $count }}</span>
                                    @else
                                        <span class="mx-grid-zero">–</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="mx-n mx-bl">{{ $total['multi'] ?: '–' }}</td>
                        </tr>
                    @endif

                    @forelse ($rows as $row)
                        @php $share = $row['deadlines'] ? round($row['past_due'] / $row['deadlines'] * 100) : 0; @endphp
                        <tr wire:key="{{ $tab }}-row-{{ $loop->index }}" x-show="! query || @js(mb_strtolower($row['name'])).includes(query.toLowerCase())">
                            <td class="mx-muted">{{ $loop->iteration }}</td>
                            <td>
                                <span class="mx-grid-name" title="{{ $row['name'] }}">
                                    @if ($isEmployee)
                                        <span class="mx-avatar mx-avatar--sm">{{ $row['initials'] }}</span>
                                    @endif
                                    <span>{{ $row['name'] }}</span>
                                    @if ($isEmployee && $row['left'])
                                        <small class="mx-muted">{{ __('mails.fields.left') }}</small>
                                    @endif
                                </span>
                            </td>
                            <td class="mx-n mx-bl">{{ $isEmployee ? $row['main_items'] : $row['items'] }}</td>
                            <td class="mx-n">{{ $row['deadlines'] }}</td>
                            <td class="mx-n mx-bl">
                                <span class="mx-grid-past {{ $row['past_due'] ? '' : 'is-zero' }}">{{ $row['past_due'] }}</span><span class="mx-grid-pct">{{ $share }}%</span>
                            </td>
                            @foreach ($row['past_due_by_status'] as $status => $count)
                                <td class="mx-n">
                                    @if ($count)
                                        <span class="mx-grid-dot"><i class="mx-dot mx-dot--{{ $statusDots[$status] }}"></i>{{ $count }}</span>
                                    @else
                                        <span class="mx-grid-zero">–</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="mx-n mx-bl">{{ $row['multi'] ?: '–' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="mx-empty">{{ __('mails.report.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
