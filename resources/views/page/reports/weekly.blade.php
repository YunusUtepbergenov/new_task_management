@extends('layouts.main')

@section('styles')
    @livewireStyles
@endsection

@section('main')
	<div class="content container-fluid">
        <!-- Page Content -->
		<div class="content container-fluid">


        @livewire('reports.weekly-tasks-overview')

@endsection

@section('scripts')
    @livewireScripts
@endsection