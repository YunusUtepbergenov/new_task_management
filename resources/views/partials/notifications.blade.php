<!-- Notifications -->
<li class="nav-item dropdown">
    <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
        <i class="fa fa-bell-o"></i> <span class="badge badge-pill">{{ auth()->user()->unreadNotifications()->count() }}</span>
    </a>
    <div class="dropdown-menu notifications">
        <div class="topnav-dropdown-header">
            <span class="notification-title">{{ __('notifications.notifications_title') }}</span>
            <a href="{{ route('read.noti') }}" class="clear-notifications">{{ __('notifications.clear_all') }}</a>
        </div>
        <div class="noti-content">
            <ul class="notification-list">
                @foreach (auth()->user()->unreadNotifications as $notification)
                    @php
                        $data = $notification->data;
                        $message = match($notification->type) {
                            'App\Notifications\NewTaskNotification' => '<span class="noti-title">' . e($data['creator_name']) . '</span> ' . __('notifications.added_task'),
                            'App\Notifications\TaskSubmittedNotification' => '<span class="noti-title">' . e($data['user_name']) . '</span> ' . __('notifications.completed_task'),
                            'App\Notifications\CommentStoredNotification' => '<span class="noti-title">' . e($data['user_name']) . '</span> ' . __('notifications.wrote_comment'),
                            'App\Notifications\TaskConfirmedNotification' => '<span class="noti-title">' . e($data['creator_name']) . '</span> ' . __('notifications.accepted_task'),
                            'App\Notifications\TaskRejectedNotification' => '<span class="noti-title">' . e($data['creator_name']) . '</span> ' . __('notifications.rejected_task'),
                            default => null,
                        };
                        $suffix = $notification->type === 'App\Notifications\TaskSubmittedNotification'
                            ? __('notifications.please_check_this')
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
