@extends('layouts.main')

@section('styles')
    @livewireStyles
@endsection

@section('main')
	<!-- Page Content -->
	<div class="content container-fluid">
        <!-- Page Content -->
	<div class="content container-fluid">
		<!-- Page Filter -->
        @livewire('research.survey')
	</div>
    @livewire('view-modal')

@endsection

@section('scripts')
    @livewireScripts
@endsection
