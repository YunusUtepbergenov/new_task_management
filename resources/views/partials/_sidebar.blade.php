<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <!-- Collapse Toggle Button (desktop only) -->
    <button class="sidebar-collapse-btn" id="sidebar_collapse_btn" title="{{ __('ui.sidebar.collapse') }}">
        <i class="fa fa-chevron-left"></i>
    </button>
    <div class="sidebar-inner">
        <!-- Logo -->
        <div class="sidebar-logo">
            <a href="{{ route('home') }}" wire:navigate>
                <img src="{{ asset('assets/img/logo.svg') }}" width="80" height="40" alt="CERR">
            </a>
        </div>

        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                <li>
                    <a href="{{ route('home') }}" wire:navigate.hover><i class="la la-stream"></i> <span>{{ __('ui.sidebar.projects_tasks') }}</span></a>
                </li>

                @if(Auth::user()->isDirector() || Auth::user()->isMailer() || Auth::user()->isHead() || Auth::user()->isDeputy() || Auth::user()->isHR() || Auth::user()->isAccountant())
                    <li>
                        <a href="{{ route('weekly.tasks') }}" wire:navigate.hover><i class="las la-tasks"></i><span>{{ __('ui.sidebar.weekly_tasks') }}</span></a>
                    </li>
                @endif

                @if(Auth::user()->isDirector() || Auth::user()->isMailer() || Auth::user()->isDeputy() || Auth::user()->isHR())
                    <li>
                        <a href="{{ route('protocol.tasks') }}" wire:navigate.hover><i class="las la-file-alt"></i><span>{{ __('ui.sidebar.protocol') }}</span></a>
                    </li>
                @endif

                @if(Auth::user()->isDeputy() || Auth::user()->isHead())
                    <li>
                        <a href="{{ route('archive') }}" wire:navigate.hover><i class="las la-archive"></i><span>{{ __('ui.sidebar.archive') }}</span></a>
                    </li>
                @endif

                @if(Auth::user()->isDirector() || Auth::user()->isDeputy())
                    <li>
                        <a href="{{ route('direct.messages') }}" wire:navigate.hover><i class="la la-paper-plane"></i><span>{{ __('ui.sidebar.mailing') }}</span></a>
                    </li>
                @endif

                <li><a href="{{ route('kpi') }}" wire:navigate.hover><i class="las la-poll"></i><span>{{ __('ui.sidebar.kpi') }}</span></a></li>

                <li class="submenu">
                    <a href="#"><i class="fa fa-server" aria-hidden="true"></i><span>{{ __('ui.sidebar.data_analysis') }}</span><span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li><a href="{{ route('scraping') }}" wire:navigate><span>{{ __('ui.sidebar.scraping') }}</span></a></li>
                        <li><a href="{{ route('surveys') }}" wire:navigate><span>{{ __('ui.sidebar.surveys') }}</span></a></li>
                    </ul>
                </li>

                <li class="submenu">
                    <a href="#"><i class="la la-users" aria-hidden="true"></i> <span>{{ __('ui.sidebar.personnel') }}</span><span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li>
                            <a href="{{ route('employees') }}" wire:navigate><span>{{ __('ui.sidebar.employees') }}</span></a>
                        </li>
                        <li>
                            <a href="{{ route('vacations') }}" wire:navigate><span>{{ __('ui.sidebar.vacation_schedule') }}</span></a>
                        </li>
                        @if (Auth::user()->isDirector() || Auth::user()->isDeputy() || Auth::user()->isHR())
                            <li>
                                <a href="{{ route('attendance') }}" wire:navigate><span>{{ __('ui.sidebar.turnstile') }}</span></a>
                            </li>
                        @endif
                    </ul>
                </li>

            </ul>

            <div class="sidebar-section-title">{{ __('ui.sidebar.system') }}</div>

            <ul>
                <li>
                    <a href="{{ route('settings') }}" wire:navigate><i class="fa fa-cog"></i><span>{{ __('ui.sidebar.settings') }}</span></a>
                </li>
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="sidebar-logout-btn"><i class="fa fa-sign-out"></i><span>{{ __('ui.sidebar.logout') }}</span></button>
                    </form>
                </li>
            </ul>
        </div>

        <!-- User Profile -->
        <div class="sidebar-user-profile dropdown">
            <a href="#" class="d-flex align-items-center" data-toggle="dropdown" style="text-decoration: none;">
                <img src="{{ (Auth::user()->avatar) ? asset('user_image/'.Auth::user()->avatar) : asset('user_image/avatar.jpg') }}" class="user-avatar" alt="">
                <div class="user-info">
                    <span class="user-name">{{ Auth::user()->short_name }}</span>
                </div>
            </a>
        </div>
    </div>
</div>
<!-- Sidebar Overlay -->
<div class="sidebar-overlay"></div>
<!-- /Sidebar -->
