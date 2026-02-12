<div class="page-header">
    <div class="row">
        <div class="col-sm-12">
            <ul class="nav nav-tabs nav-tabs-bottom">
                <li class="nav-item">
                    <a class="nav-link {{ (Route::current()->uri == '/') ? 'active' : '' }}" href="{{ route('home') }}">Мои задачи</a>
                </li>
                @if(Auth::user()->isDirector() || Auth::user()->isMailer() || Auth::user()->isHead() || Auth::user()->isDeputy())
                    <li class="nav-item">
                        <a class="nav-link {{ (Route::current()->uri == 'ordered') ? 'active' : '' }}" href="{{ route('ordered') }}">Поручено</a>
                    </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link {{ (Route::current()->uri == 'finished') ? 'active' : '' }}" href="{{ route('finished') }}">Завершённые задачи</a>
                </li>

            </ul>
        </div>
    </div>
</div>