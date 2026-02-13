@extends('layouts.main')

@section('styles')
    @livewireStyles
@endsection

@section('main')
    @include('partials._navigation_header')

    @livewire('ordered-table')

    @livewire('edit-task-modal')

    @livewire('view-modal')
@endsection

@section('scripts')
    @livewireScripts
    <script src="{{ asset('assets/js/ddtf.js') }}"></script>
    <script>
        $('#myTable').ddTableFilter();
    </script>
@endsection
