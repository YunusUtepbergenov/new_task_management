@extends('layouts.main')

@section('styles')
    @livewireStyles
@endsection
@section('main')
    <!-- Page Header -->
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
                    {{-- <li class="nav-item">
                        <a class="nav-link {{ (Route::current()->uri == 'helping') ? 'active' : '' }}" href="{{ route('helping') }}">Помогаю</a>
                    </li> --}}
                </ul>
            </div>
        </div>
    </div>
    <!-- /Page Header -->


    @livewire('tasks-table')

    @include('partials._project_modal')
    @include('partials._task_modal')

    <!-- View Project Modal -->
    {{-- @include('partials._view_modal')--}}
    @livewire('view-modal')
    <!-- /View Project Modal -->
@endsection

@section('scripts')
    @livewireScripts
@endsection
