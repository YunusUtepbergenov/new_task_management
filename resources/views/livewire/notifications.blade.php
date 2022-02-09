<div>
    <!-- Notifications -->
    <li class="nav-item dropdown">
        <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
            <i class="fa fa-bell-o"></i> <span class="badge badge-pill">{{ auth()->user()->unreadNotifications()->count() }}</span>
        </a>
        <div class="dropdown-menu notifications">
            <div class="topnav-dropdown-header">
                <span class="notification-title">Notifications</span>
            </div>
            <div class="noti-content">
                <ul class="notification-list">
                    @foreach (auth()->user()->unreadNotifications as $notification)
                        @if ($notification->type == "App\Notifications\NewTaskNotification")
                                <li class="notification-message">
                                    <div class="notification-action">
                                        <form method="POST">
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
                                            <p class="noti-details"><span class="noti-title">{{ $notification->data["creator_name"] }}</span> добавил новое задание <a href="#" wire:click.prevent="view({{ $notification->data["task_id"] }})" id="noti-link">{{ $notification->data['name'] }}</a></p>
                                            <p class="noti-time"><span class="notification-time">{{ \App\Helpers\AppHelper::time_elapsed_string($notification->created_at) }}</span></p>
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

</div>
