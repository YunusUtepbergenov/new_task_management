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
        $statusDots = ['done' => 'done', 'pending' => 'late', 'in_review' => 'in_review', 'returned' => 'returned'];
        $share = fn (array $row): int => $row['required'] ? (int) round($row['past_due'] / $row['required'] * 100) : 0;
        $columnCount = $isEmployee ? 10 : 9;
    @endphp

    <div class="mx-stats mx-stats--5">
        <div class="mx-stat" title="{{ __('mails.report.help.documents') }}">
            <span class="mx-stat-label">{{ __('mails.report.documents') }}</span>
            <span class="mx-stat-value">{{ $total['documents'] }}</span>
        </div>
        <div class="mx-stat" title="{{ __('mails.report.help.required') }}">
            <span class="mx-stat-label">{{ __('mails.report.required') }}</span>
            <span class="mx-stat-value">{{ $total['required'] }}</span>
        </div>
        <div class="mx-stat {{ $total['past_due'] ? 'mx-stat--late' : '' }}" title="{{ __('mails.report.help.past_due') }}">
            <span class="mx-stat-label">{{ __('mails.report.past_due') }}</span>
            <span class="mx-stat-value">{{ $total['past_due'] }} <small class="mx-stat-share">{{ $share($total) }}%</small></span>
        </div>
        <div class="mx-stat" title="{{ __('mails.report.help.executors') }}">
            <span class="mx-stat-label">{{ __('mails.report.executors') }}</span>
            <span class="mx-stat-value">{{ $total['executors'] }}</span>
        </div>
        <div class="mx-stat" title="{{ __('mails.report.help.multi') }}">
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
                    <col style="width: 108px;">
                    <col style="width: 128px;"><col style="width: 136px;">
                    <col style="width: 110px;"><col style="width: 118px;"><col style="width: 160px;"><col style="width: 112px;">
                    <col style="width: 150px;">
                    @if ($isEmployee)
                        <col style="width: 150px;">
                    @endif
                </colgroup>
                <thead>
                    <tr class="mx-grid-groups">
                        <th colspan="2"></th>
                        <th class="mx-bl"></th>
                        <th colspan="2" class="mx-grid-group mx-bl">{{ __('mails.report.group_deadlines') }}</th>
                        <th colspan="4" class="mx-grid-group mx-grid-group--late mx-bl">{{ __('mails.report.group_status') }}</th>
                        <th colspan="{{ $isEmployee ? 2 : 1 }}" class="mx-bl"></th>
                    </tr>
                    <tr class="mx-grid-heads">
                        <th>№</th>
                        <th>{{ $isEmployee ? __('mails.report.employee') : __('mails.report.sector') }}</th>
                        <th class="mx-n mx-bl" title="{{ __('mails.report.help.documents') }}">{{ __('mails.report.documents') }}</th>
                        <th class="mx-n mx-bl" title="{{ __('mails.report.help.required') }}">{{ __('mails.report.required') }}</th>
                        <th class="mx-n" title="{{ __('mails.report.help.past_due') }}">{{ __('mails.report.past_due') }}</th>
                        @foreach ($statusColumns as $status)
                            <th class="mx-n {{ $loop->first ? 'mx-bl' : '' }}" title="{{ __('mails.report.help.status') }}">{{ __('mails.statuses.'.$status) }}</th>
                        @endforeach
                        <th class="mx-n mx-bl" title="{{ __('mails.report.help.multi') }}">{{ __('mails.report.multi') }}</th>
                        @if ($isEmployee)
                            <th class="mx-n" title="{{ __('mails.report.help.as_extra') }}">{{ __('mails.report.as_extra') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @if ($rows)
                        <tr class="mx-grid-total">
                            <td></td>
                            <td>{{ __('mails.report.total') }}</td>
                            <td class="mx-n mx-bl">{{ $total['documents'] }}</td>
                            <td class="mx-n mx-bl">{{ $total['required'] }}</td>
                            <td class="mx-n"><span class="mx-grid-past">{{ $total['past_due'] }}</span><span class="mx-grid-pct">{{ $share($total) }}%</span></td>
                            @foreach ($statusColumns as $status)
                                <td class="mx-n {{ $loop->first ? 'mx-bl' : '' }}">
                                    @if ($total['statuses'][$status])
                                        <span class="mx-grid-dot"><i class="mx-dot mx-dot--{{ $statusDots[$status] }}"></i>{{ $total['statuses'][$status] }}</span>
                                    @else
                                        <span class="mx-grid-zero">–</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="mx-n mx-bl">{{ $total['multi'] ?: '–' }}</td>
                            @if ($isEmployee)
                                <td class="mx-n">{{ $total['as_extra'] ?: '–' }}</td>
                            @endif
                        </tr>
                    @endif

                    @forelse ($rows as $row)
                        <tr wire:key="{{ $tab }}-row-{{ $loop->index }}" x-show="! query || @js(mb_strtolower($row['name'])).includes(query.toLowerCase())">
                            <td class="mx-muted">{{ $loop->iteration }}</td>
                            <td>
                                <span class="mx-grid-name" title="{{ $row['name'] }}">
                                    @if ($isEmployee)
                                        <span class="mx-avatar mx-avatar--sm {{ $row['is_group'] ? 'mx-avatar--group' : '' }}">{{ $row['initials'] }}</span>
                                    @endif
                                    <span>{{ $row['name'] }}</span>
                                    @if ($isEmployee && $row['left'])
                                        <small class="mx-muted">{{ __('mails.fields.left') }}</small>
                                    @endif
                                </span>
                            </td>
                            <td class="mx-n mx-bl">{{ $row['documents'] ?: '–' }}</td>
                            <td class="mx-n mx-bl">{{ $row['required'] }}</td>
                            <td class="mx-n">
                                <span class="mx-grid-past {{ $row['past_due'] ? '' : 'is-zero' }}">{{ $row['past_due'] }}</span><span class="mx-grid-pct">{{ $share($row) }}%</span>
                            </td>
                            @foreach ($statusColumns as $status)
                                <td class="mx-n {{ $loop->first ? 'mx-bl' : '' }}">
                                    @if ($row['statuses'][$status])
                                        <span class="mx-grid-dot"><i class="mx-dot mx-dot--{{ $statusDots[$status] }}"></i>{{ $row['statuses'][$status] }}</span>
                                    @else
                                        <span class="mx-grid-zero">–</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="mx-n mx-bl">{{ $row['multi'] ?: '–' }}</td>
                            @if ($isEmployee)
                                <td class="mx-n">{{ $row['as_extra'] ?: '–' }}</td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="{{ $columnCount }}" class="mx-empty">{{ __('mails.report.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <details class="mx-report-help">
            <summary><i class="fa fa-question-circle-o"></i> {{ __('mails.report.help_title') }}</summary>
            <dl>
                <dt>{{ __('mails.report.documents') }}</dt><dd>{{ __('mails.report.help.documents') }}</dd>
                <dt>{{ __('mails.report.required') }}</dt><dd>{{ __('mails.report.help.required') }}</dd>
                <dt>{{ __('mails.report.past_due') }}</dt><dd>{{ __('mails.report.help.past_due') }}</dd>
                <dt>{{ __('mails.report.group_status') }}</dt><dd>{{ __('mails.report.help.status') }}</dd>
                <dt>{{ __('mails.report.multi') }}</dt><dd>{{ __('mails.report.help.multi') }}</dd>
                @if ($isEmployee)
                    <dt>{{ __('mails.report.as_extra') }}</dt><dd>{{ __('mails.report.help.as_extra') }}</dd>
                @endif
            </dl>
        </details>
    </section>
</div>
