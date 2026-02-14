<li class="nav-item dropdown" x-data="{ open: false }" @click.outside="open = false">
    <a href="#" class="nav-link" @click.prevent="open = !open">
        <i class="fa fa-bell-o"></i> <span class="badge badge-pill">{{ $count }}</span>
    </a>
    <div class="dropdown-menu notifications"
         :style="open ? 'display: block; right: 0; left: auto;' : ''">
        <div class="topnav-dropdown-header">
            <span class="notification-title">Уведомления</span>
            <a href="javascript:void(0)" wire:click.prevent="dismissAll" class="clear-notifications">Удалить все уведомления</a>
        </div>
        <div class="noti-content">
            <ul class="notification-list">
                @foreach ($notifications as $notification)
                    @php
                        $data = $notification->data;
                        $message = match($notification->type) {
                            'App\Notifications\NewTaskNotification' => '<span class="noti-title">' . e($data['creator_name']) . '</span> добавил Не прочитано задание',
                            'App\Notifications\TaskSubmittedNotification' => '<span class="noti-title">' . e($data['user_name']) . '</span> выполнил задание',
                            'App\Notifications\CommentStoredNotification' => '<span class="noti-title">' . e($data['user_name']) . '</span> написал комментарий к заданию',
                            'App\Notifications\TaskConfirmedNotification' => '<span class="noti-title">' . e($data['creator_name']) . '</span> принял ваше задание',
                            'App\Notifications\TaskRejectedNotification' => '<span class="noti-title">' . e($data['creator_name']) . '</span> отклонил вашего задания',
                            default => null,
                        };
                        $suffix = $notification->type === 'App\Notifications\TaskSubmittedNotification'
                            ? '. Пожалуйста, проверьте это задание.'
                            : '';
                    @endphp

                    @if ($message)
                        <li class="notification-message" wire:key="noti-{{ $notification->id }}">
                            <div class="notification-action">
                                <button type="button" class="action-icon" wire:click.stop="dismiss('{{ $notification->id }}')">
                                    <i class="fa fa-times" aria-hidden="true"></i>
                                </button>
                            </div>
                            <div class="media">
                                <span class="avatar">
                                    <img alt="" src="{{ asset('assets/img/avatar.jpg') }}">
                                </span>
                                <div class="media-body">
                                    <p class="noti-details">{!! $message !!} <a href="#" onclick="openModal({{ $data['task_id'] }})" id="noti-link">{{ $data['name'] }}</a>{{ $suffix }}</p>
                                    <p class="noti-time"><span class="notification-time">{{ time_elapsed_string($notification->created_at) }}</span></p>
                                </div>
                            </div>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    </div>
</li>
