			<!-- Sidebar -->
            <div class="sidebar" id="sidebar">
                <div class="sidebar-inner slimscroll">
					<div id="sidebar-menu" class="sidebar-menu">
						<ul>
							<li>
								<a href="{{ route('home') }}"><i class="la la-rocket"></i> <span> Проекты и задачи </span></a>
							</li>
                            <li class="submenu">
								<a href="#"><i class="fa fa-file-text la"></i> <span>Документы</span><span class="menu-arrow"></span></a>
								<ul style="display: none;">
									<li><a href="{{ route('articles.index') }}">Статьи</a></li>
									<li class="submenu">
                                        <a href="#"><span>Журналы</span><span class="menu-arrow"></span></a>
                                    <ul style="display: none;">
                                        <li><a href="{{ route('journal.ru', date('Y')) }}">Экономическое Обозрение</a></li>
                                        <li><a href="{{ route('journal.uz', date('Y')) }}">Иқтисодий Шарҳ</a></li>
                                    </ul>
                                    </li>
								</ul>
							</li>
							{{-- <li>
								<a href="#"><i class="fa fa-file-text la"></i> <span>Документы</span></a>
							</li> --}}
                            @if(Auth::user()->isDirector() || Auth::user()->isMailer() || Auth::user()->isHead() || Auth::user()->isDeputy())
                                <li>
                                    <a href="{{ route('reports') }}"><i class="la la-pie-chart"></i> <span>Отчеты</span></a>
                                </li>
                            @endif
							<li>
								<a href="{{ route('employees') }}"><i class="la la-user"></i> <span> Сотрудники </span></a>
							</li>
						</ul>

						<ul style="position: absolute; bottom: 0px; margin-bottom: 50px;">
							<li>
								<a href="#"><i class="fa fa-book" aria-hidden="true"></i><span>Справочники</span></a>
							</li>
							<li>
								<a href="{{ route('settings') }}"><i class="fa fa-cog"></i><span>Настройки</span></a>
							</li>
						</ul>
					</div>
                </div>
            </div>
			<!-- /Sidebar -->
