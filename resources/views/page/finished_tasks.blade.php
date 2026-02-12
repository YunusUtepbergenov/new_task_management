@extends('layouts.main')

@section('styles')
    @livewireStyles
@endsection

@section('main')
    @include('partials._navigation_header')

    @livewire('finished-tasks')

    @livewire('view-modal')
@endsection

@section('scripts')
    @livewireScripts
    @stack('scripts')
@endsection

