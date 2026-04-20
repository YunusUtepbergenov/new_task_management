<div>

    @include('partials.task-loading-overlay')

    <div class="wto-page-header mb-4">
        <div class="wto-page-header-icon">
            <i class="fa fa-file-text-o"></i>
        </div>
        <div>
            <h4 class="wto-page-title">{{ __('reports.protocol_title') }}</h4>
            <p class="wto-page-subtitle">{{ __('reports.protocol_subtitle') }}</p>
        </div>
    </div>

    <div class="wto-week-bar mb-4">
        <div class="wto-week-left">
            <i class="fa fa-calendar" style="color: var(--sidebar-active-bg);"></i>
            <select wire:model.live="selectedWeek" class="form-control wto-week-select">
                @foreach ($weeks as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button wire:click="export" class="btn export-btn">
            <i class="fa fa-file-excel-o"></i> {{ __('reports.export_excel') }}
        </button>
    </div>

    @forelse ($groupedTasks as $sectorName => $groups)
        <div class="card mb-4">
            <div class="card-header wto-sector-header">
                <span class="wto-sector-name">
                    <i class="fa fa-users"></i> {{ $sectorName }}
                </span>
                <span class="wto-task-count">{{ count($groups) }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-nowrap mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('reports.task_name') }}</th>
                            <th>{{ __('reports.deadline') }}</th>
                            <th>{{ __('reports.responsible') }}</th>
                            <th>{{ __('reports.category') }}</th>
                            <th>{{ __('reports.status') }}</th>
                            @if (Auth::user()->isDeputy() || Auth::user()->isHR())
                                <th>{{ __('reports.for_protocol') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($groups as $index => $item)
                            @php
                                $main = $item['main'];
                                $taskGroup = $item['members'];
                                $users = $main['user']['short_name'] ?? '';
                                $groupMemberCount = $main['group_member_count'] ?? null;
                            @endphp
                            <tr wire:key="protocol-row-{{ $main['id'] }}">
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <a href="#" wire:click.prevent="view({{ $main['id'] }})">{{ $main['name'] }}</a>
                                </td>
                                <td>
                                    @if ($main['extended_deadline'])
                                        <span class="badge bg-inverse-warning" title="{{ __('reports.original_deadline') }} {{ $main['deadline'] }}">
                                            {{ \Carbon\Carbon::parse($main['extended_deadline'])->format('Y-m-d') }} <i class="fa fa-clock-o text-danger" title="{{ __('reports.deadline_extended') }}"></i>
                                        </span>
                                    @else
                                        <span class="badge bg-inverse-warning">{{ \Carbon\Carbon::parse($main['deadline'])->format('Y-m-d') }}</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $users }}
                                    @if (!empty($main['group_id']) && $groupMemberCount > 1)
                                        <span style="background:rgba(59,130,246,0.1);color:var(--sidebar-active-bg);border-radius:20px;padding:1px 7px;font-size:11px;font-weight:600;margin-left:4px;">
                                            <i class="fa fa-users"></i> {{ $groupMemberCount }}
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $main['score']['name'] ?? '' }}</td>
                                <td>
                                    @php
                                        $statusClass = match($main['status']) {
                                            'Не прочитано'       => 'success',
                                            'Выполняется'        => 'primary',
                                            'Ждет подтверждения' => 'danger',
                                            'Выполнено'          => 'purple',
                                            default              => 'warning',
                                        };
                                    @endphp
                                    @php
                                        $ptStatusMap = [
                                            'Не прочитано' => __('tasks.status_unread'),
                                            'Выполняется' => __('tasks.status_in_progress'),
                                            'Ждет подтверждения' => __('tasks.status_waiting'),
                                            'Выполнено' => __('tasks.status_completed'),
                                            'Дорабатывается' => __('tasks.status_revision'),
                                        ];
                                    @endphp
                                    <span class="badge bg-inverse-{{ $statusClass }}">{{ $ptStatusMap[$main['status']] ?? $main['status'] }}</span>
                                </td>
                                @if (Auth::user()->isDeputy() || Auth::user()->isHR())
                                    <td>
                                        <input type="checkbox" checked
                                            wire:click="removeFromProtocol({{ $main['id'] }})"
                                            wire:confirm="{{ __('reports.remove_from_protocol') }}" />
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="card">
            <div class="card-body text-center text-muted py-4">
                <p class="mb-0">{{ __('reports.no_protocol_tasks') }}</p>
            </div>
        </div>
    @endforelse
</div>
