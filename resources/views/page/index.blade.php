@extends('layouts.main')

@section('styles')
    @livewireStyles
@endsection

@section('main')
    @include('partials._navigation_header')

    @livewire('tasks-table')

    @include('partials._task_modal')

    @livewire('view-modal')

@endsection

@section('scripts')
    @livewireScripts
@endsection
