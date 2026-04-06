@extends('layouts.main')

@section('main')
    @include('partials._navigation_header')

    @livewire('tasks-table')

    @livewire('edit-task-modal')
    @livewire('create-task-modal')
@endsection
