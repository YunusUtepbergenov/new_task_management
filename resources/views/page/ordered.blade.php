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
                    @if(Auth::user()->isDirector() || Auth::user()->isMailer() || Auth::user()->isHead() )
                        <li class="nav-item">
                            <a class="nav-link {{ (Route::current()->uri == 'ordered') ? 'active' : '' }}" href="{{ route('ordered') }}">Поручил</a>
                        </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link {{ (Route::current()->uri == 'helping') ? 'active' : '' }}" href="{{ route('helping') }}">Помогаю</a>
                    </li>
                </ul>
            </div>

            <div class="col-auto float-right ml-auto" style="margin-top: 10px;">
                @if(Auth::user()->isDirector() || Auth::user()->isMailer() || Auth::user()->isHead() )
                <a href="#" class="btn add-btn" data-toggle="modal" data-target="#create_project"> Добавить Проект</a>
                <a href="#" class="btn add-btn" data-toggle="modal" data-target="#create_task"> Добавить Задачу</a>
                @endif
            </div>
        </div>
    </div>
    <!-- /Page Header -->

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        @livewire('ordered-table', ['projects' => $projects, 'tasks' => $tasks, 'user_projects' => $user_projects])
                    </div>
                </div>
            </div>
        </div>
    </div>

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
