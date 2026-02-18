@extends('layouts.main')

@section('main')
    @include('partials._navigation_header')

    @livewire('tasks-table')

    @livewire('create-task-modal')
@endsection
