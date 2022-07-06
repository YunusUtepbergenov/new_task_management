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
        @livewire('research.scraping')
	</div>
	<!-- /Page Content -->
    @include('partials._scrape_modal')
    @include('partials._profile')
@endsection

@section('scripts')
    @livewireScripts
@endsection
