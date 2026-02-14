@extends('layouts.main')

@section('main')
    @include('partials._navigation_header')

    @livewire('tasks-table')

    @include('partials._task_modal')
@endsection
