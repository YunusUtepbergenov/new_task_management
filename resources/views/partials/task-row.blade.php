@php
    $main_task = $task[0];
    $responsibles = $main_task['user']['short_name'] ?? '';
    $groupMemberCount = $main_task['group_member_count'] ?? null;
@endphp

<tr wire:key="task-row-{{ $main_task['id'] }}">
    <td>{{ $key }}</td>
    <td>
        @if ($main_task['creator_id'] == Auth::user()->id)
            <div class="dropdown dropdown-action profile-action">
                <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <i class="material-icons">more_vert</i>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    @if ($main_task['status'] != "Выполнено" && $main_task['status'] != "Ждет подтверждения")
                        <a class="dropdown-item" href="javascript:void(0)" wire:click="$dispatch('editTaskClicked', { id: {{ $main_task['id'] }} })"><i class="fa fa-pencil m-r-5"></i> {{ __('tasks.edit') }}</a>
                    @endif
                    @if ($main_task['repeat_id'])
                        <a href="javascript:void(0)" class="dropdown-item" wire:click="deleteTask({{ $main_task['id'] }})" wire:confirm="{{ __('tasks.delete_current_confirmation') }}"><i class="fa fa-trash-o m-r-5"></i>{{ __('tasks.delete_task') }}</a>
                        <a href="javascript:void(0)" class="dropdown-item" wire:click="deleteRepeat({{ $main_task['repeat_id'] }})" wire:confirm="{{ __('tasks.stop_cycle_confirmation') }}"><i class="fa fa-trash-o m-r-5"></i>{{ __('tasks.stop_cycle') }}</a>
                    @else
                        <a href="javascript:void(0)" class="dropdown-item" wire:click="deleteTask({{ $main_task['id'] }})" wire:confirm="{{ __('tasks.delete_confirmation') }}"><i class="fa fa-trash-o m-r-5"></i>{{ __('tasks.delete') }}</a>
                    @endif
                </div>
            </div>
        @endif
    </td>
    <td>
        @if ($main_task['status'] == "Выполнено")
            <a href="#" wire:click.prevent="view({{ $main_task['id'] }})"><del>{{ $main_task['name'] }}</del></a>
        @else
            <a href="#" wire:click.prevent="view({{ $main_task['id'] }})">{{ $main_task['name'] }}</a>
        @endif
    </td>
    <td>
        @if ($main_task['extended_deadline'])
            <span class="badge bg-inverse-warning" title="{{ __('tasks.original_deadline') }} {{ $main_task['deadline'] }}">
                {{ \Carbon\Carbon::parse($main_task['extended_deadline'])->format('Y-m-d') }} <i class="fa fa-clock-o text-danger" title="{{ __('tasks.deadline_extended_tooltip') }}"></i>
            </span>
        @else
            <span class="badge bg-inverse-warning">{{ \Carbon\Carbon::parse($main_task['deadline'])->format('Y-m-d') }}</span>
        @endif

        @if (!empty($main_task['repeat']))
            <i class="fa fa-refresh text-info" title="{{ __('tasks.repeating_task_tooltip') }}"></i>
        @endif
    </td>
    <td>
        {{ $responsibles }}
        @if (!empty($main_task['group_id']) && $groupMemberCount > 1)
            <span style="background:rgba(59,130,246,0.1);color:var(--sidebar-active-bg);border-radius:20px;padding:1px 7px;font-size:11px;font-weight:600;margin-left:4px;">
                <i class="fa fa-users"></i> {{ $groupMemberCount }}
            </span>
        @endif
    </td>
    <td>
        @if ($main_task['overdue'] && $main_task['status'] != 'Ждет подтверждения')
            <span class="badge bg-inverse-warning">{{ __('tasks.overdue') }}</span>
        @else
        @php
            if ($main_task['status'] == 'Не прочитано') {
                $statusClass = 'success';
            } elseif ($main_task['status'] == 'Выполняется') {
                $statusClass = 'primary';
            } elseif ($main_task['status'] == 'Ждет подтверждения') {
                $statusClass = 'danger';
            } elseif ($main_task['status'] == 'Выполнено') {
                $statusClass = 'purple';
            } else {
                $statusClass = '';
            }
        @endphp
            @php
                $statusTranslations = [
                    'Не прочитано' => __('tasks.status_unread'),
                    'Выполняется' => __('tasks.status_in_progress'),
                    'Ждет подтверждения' => __('tasks.status_waiting'),
                    'Выполнено' => __('tasks.status_completed'),
                    'Дорабатывается' => __('tasks.status_revision'),
                ];
            @endphp
            <span class="badge bg-inverse-{{ $statusClass }}">{{ $statusTranslations[$main_task['status']] ?? $main_task['status'] }}</span>
        @endif
    </td>
</tr>