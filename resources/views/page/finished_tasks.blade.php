@extends('layouts.main')

@section('main')
    @include('partials._navigation_header')

    @livewire('finished-tasks')
@endsection
