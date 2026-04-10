<div class="page-header">
    <div class="row">
        <div class="col-sm-12">
            <ul class="nav nav-tabs nav-tabs-bottom">
                <li class="nav-item">
                    <a class="nav-link {{ (Route::current()->uri == '/') ? 'active' : '' }}" href="{{ route('home') }}" wire:navigate>{{ __('ui.nav.my_tasks') }}</a>
                </li>
                @if(Auth::user()->isDirector() || Auth::user()->isMailer() || Auth::user()->isHead() || Auth::user()->isDeputy())
                    <li class="nav-item">
                        <a class="nav-link {{ (Route::current()->uri == 'ordered') ? 'active' : '' }}" href="{{ route('ordered') }}" wire:navigate>{{ __('ui.nav.assigned') }}</a>
                    </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link {{ (Route::current()->uri == 'finished') ? 'active' : '' }}" href="{{ route('finished') }}" wire:navigate>{{ __('ui.nav.completed') }}</a>
                </li>

            </ul>
        </div>
    </div>
</div>
