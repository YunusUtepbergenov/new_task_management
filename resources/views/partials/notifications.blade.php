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
                    @if ($notification->type == "App\Notifications\NewTaskNotification")
                        <li class="notification-message">
                            <div class="notification-action">
                                <form method="POST" action="{{ route('notification.read', $notification->id) }}">
                                    <input type="hidden" name="_method" value="PUT">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <button class="action-icon"><i class="fa fa-trash" aria-hidden="true"></i></button>
                                </form>
                            </div>
                            <div class="media">
                                <span class="avatar">
                                    <img alt="" src="{{ asset('assets/img/avatar.jpg') }}">
                                </span>
                                <div class="media-body">
                                    <p class="noti-details"><span class="noti-title">{{ $notification->data["creator_name"] }}</span> добавил Не прочитано задание <a href="#" onclick="openModal({{ $notification->data['task_id'] }})" id="noti-link">{{ $notification->data["name"] }}</a></p>
                                    <p class="noti-time"><span class="notification-time">{{ time_elapsed_string($notification->created_at) }}</span></p>
                                </div>
                            </div>
                        </li>
                    @elseif ($notification->type == "App\Notifications\TaskSubmittedNotification")
                        <li class="notification-message">
                            <div class="notification-action">
                                <form method="POST" action="{{ route('notification.read', $notification->id) }}">
                                    <input type="hidden" name="_method" value="PUT">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <button class="action-icon"><i class="fa fa-trash" aria-hidden="true"></i></button>
                                </form>
                            </div>
                            <div class="media">
                                <span class="avatar">
                                    <img alt="" src="{{ asset('assets/img/avatar.jpg') }}">
                                </span>
                                <div class="media-body">
                                    <p class="noti-details"><span class="noti-title">{{ $notification->data["user_name"] }}</span> выполнил задание <a href="#" onclick="openModal({{ $notification->data['task_id'] }})" id="noti-link">{{ $notification->data["name"] }}</a>. Пожалуйста, проверьте это задание.</p>
                                    <p class="noti-time"><span class="notification-time">{{ time_elapsed_string($notification->created_at) }}</span></p>
                                </div>
                            </div>
                        </li>
                    @elseif ($notification->type == "App\Notifications\CommentStoredNotification")
                        <li class="notification-message">
                            <div class="notification-action">
                                <form method="POST" action="{{ route('notification.read', $notification->id) }}">
                                    <input type="hidden" name="_method" value="PUT">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <button class="action-icon"><i class="fa fa-trash" aria-hidden="true"></i></button>
                                </form>
                            </div>
                            <div class="media">
                                <span class="avatar">
                                    <img alt="" src="{{ asset('assets/img/avatar.jpg') }}">
                                </span>
                                <div class="media-body">
                                    <p class="noti-details"><span class="noti-title">{{ $notification->data["user_name"] }}</span> написал комментарий к заданию <a href="#" onclick="openModal({{ $notification->data['task_id'] }})" id="noti-link">{{ $notification->data["name"] }}</a></p>
                                    <p class="noti-time"><span class="notification-time">{{ time_elapsed_string($notification->created_at) }}</span></p>
                                </div>
                            </div>
                        </li>
                    @elseif ($notification->type == "App\Notifications\TaskConfirmedNotification")
                        <li class="notification-message">
                            <div class="notification-action">
                                <form method="POST" action="{{ route('notification.read', $notification->id) }}">
                                    <input type="hidden" name="_method" value="PUT">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <button class="action-icon"><i class="fa fa-trash" aria-hidden="true"></i></button>
                                </form>
                            </div>
                            <div class="media">
                                <span class="avatar">
                                    <img alt="" src="{{ asset('assets/img/avatar.jpg') }}">
                                </span>
                                <div class="media-body">
                                    <p class="noti-details"><span class="noti-title">{{ $notification->data["creator_name"] }}</span> принял ваше задание <a href="#" onclick="openModal({{ $notification->data['task_id'] }})" id="noti-link">{{ $notification->data["name"] }}</a></p>
                                    <p class="noti-time"><span class="notification-time">{{ time_elapsed_string($notification->created_at) }}</span></p>
                                </div>
                            </div>
                        </li>
                    @elseif ($notification->type == "App\Notifications\TaskRejectedNotification")
                        <li class="notification-message">
                            <div class="notification-action">
                                <form method="POST" action="{{ route('notification.read', $notification->id) }}">
                                    <input type="hidden" name="_method" value="PUT">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <button class="action-icon"><i class="fa fa-trash" aria-hidden="true"></i></button>
                                </form>
                            </div>
                            <div class="media">
                                <span class="avatar">
                                    <img alt="" src="{{ asset('assets/img/avatar.jpg') }}">
                                </span>
                                <div class="media-body">
                                    <p class="noti-details"><span class="noti-title">{{ $notification->data["creator_name"] }}</span> отклонил вашего задания <a href="#" onclick="openModal({{ $notification->data['task_id'] }})" id="noti-link">{{ $notification->data["name"] }}</a></p>
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
