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
		@livewire('reports.test-report')
	</div>
    <iframe id="txtArea1" style="display:none"></iframe>

	<!-- /Page Content -->
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/js/dataTables.bootstrap4.min.js') }}"></script>
@livewireScripts
@endsection
