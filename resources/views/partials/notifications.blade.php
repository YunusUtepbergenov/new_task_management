<!-- Notifications -->
<li class="nav-item dropdown">
    <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
        <i class="fa fa-bell-o"></i> <span class="badge badge-pill">{{ auth()->user()->unreadNotifications()->count() }}</span>
    </a>
    <div class="dropdown-menu notifications">
        <div class="topnav-dropdown-header">
            <span class="notification-title">Уведомления</span>
            <a href="{{ route('read.noti') }}" class="clear-notifications">Удалить все уведомления</a>
        </div>
        <div class="noti-content">
            <ul class="notification-list">
                @foreach (auth()->user()->unreadNotifications as $notification)
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
                        <li class="notification-message">
                            <div class="notification-action">
                                <form method="POST" action="{{ route('notification.read', $notification->id) }}">
                                    @method('PUT')
                                    @csrf
                                    <button class="action-icon"><i class="fa fa-times" aria-hidden="true"></i></button>
                                </form>
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
<!-- /Notifications -->
