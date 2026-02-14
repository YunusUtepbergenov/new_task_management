@extends('layouts.main')

@section('main')
    @include('partials._navigation_header')

    @livewire('ordered-table')

    @livewire('edit-task-modal')
@endsection
