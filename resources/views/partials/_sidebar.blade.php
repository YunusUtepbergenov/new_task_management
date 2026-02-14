<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <!-- Collapse Toggle Button (desktop only) -->
    <button class="sidebar-collapse-btn" id="sidebar_collapse_btn" title="Свернуть">
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
                    <a href="{{ route('home') }}" wire:navigate.hover><i class="la la-stream"></i> <span>Проекты и задачи</span></a>
                </li>

                <li class="submenu">
                    <a href="#"><i class="fa fa-file-text la"></i> <span>Документы</span><span class="menu-arrow"></span></a>
                    <ul style="display: none;" id="journals_menu">
                        <li><a href="{{ route('digests.index') }}" wire:navigate>Дайджесты</a></li>
                        <li><a href="{{ route('articles.index') }}" wire:navigate>Статьи</a></li>
                        <li><a href="{{ route('notes.index') }}" wire:navigate>Аналитические записки</a></li>
                        <li class="submenu">
                            <a href="#"><span>Журналы</span><span class="menu-arrow"></span></a>
                            <ul style="display: none;">
                                <li><a href="{{ route('journal.ru', date('Y')) }}" wire:navigate>Экономическое Обозрение</a></li>
                                <li><a href="{{ route('journal.uz', date('Y')) }}" wire:navigate>Иқтисодий Шарҳ</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>

                @if(Auth::user()->isDirector() || Auth::user()->isMailer() || Auth::user()->isHead() || Auth::user()->isDeputy() || Auth::user()->isHR() || Auth::user()->isAccountant())
                    <li class="submenu">
                        <a href="#"><i class="la la-pie-chart"></i><span>Отчеты</span><span class="menu-arrow"></span></a>
                        <ul style="display: none;" id="reports_menu">
                            <li><a href="{{ route('reports') }}" wire:navigate><span>Отчеты</span></a></li>
                            <li><a href="{{ route('table.report') }}" wire:navigate><span>Ежемесячный Отчет</span></a></li>
                            <li><a href="{{ route('weekly.tasks') }}" wire:navigate><span>Недельные задачи по секторам</span></a></li>
                            <li><a href="{{ route('kpi') }}" wire:navigate><span>KPI</span></a></li>
                        </ul>
                    </li>
                @endif

                <li class="submenu">
                    <a href="#"><i class="fa fa-server" aria-hidden="true"></i><span>Анализ данных</span><span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li><a href="{{ route('scraping') }}" wire:navigate><span>Дата скрапинг</span></a></li>
                        <li><a href="{{ route('surveys') }}" wire:navigate><span>Опросники</span></a></li>
                    </ul>
                </li>

                <li>
                    <a href="{{ route('employees') }}" wire:navigate><i class="la la-users"></i> <span>Сотрудники</span></a>
                </li>

                <li>
                    <a href="{{ route('vacations') }}" wire:navigate><i class="la la-calendar"></i> <span>График Отпусков</span></a>
                </li>

                @if (Auth::user()->isDirector() || Auth::user()->isDeputy() || Auth::user()->isHR())
                    <li>
                        <a href="{{ route('attendance') }}" wire:navigate><i class="la la-calendar"></i> <span>Турникет</span></a>
                    </li>
                @endif
            </ul>

            <div class="sidebar-section-title">СИСТЕМА</div>

            <ul>
                <li>
                    <a href="#"><i class="fa fa-book" aria-hidden="true"></i><span>Справочники</span></a>
                </li>
                <li>
                    <a href="{{ route('settings') }}" wire:navigate><i class="fa fa-cog"></i><span>Настройки</span></a>
                </li>
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="sidebar-logout-btn"><i class="fa fa-sign-out"></i><span>Выйти</span></button>
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
