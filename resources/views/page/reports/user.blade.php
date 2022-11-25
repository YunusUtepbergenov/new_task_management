@extends('layouts.main')

@section('styles')
    @livewireStyles
@endsection

@section('main')
	<!-- Page Content -->
	<div class="content container-fluid">
		<!-- Page Header -->

		<!-- /Page Header -->
        @livewire('reports.user', ['userId' => $user_id, 'start' => $start, 'end'=> $end])
        @livewire('view-modal')
	</div>
	<!-- /Page Content -->
@endsection

@section('scripts')
    @livewireScripts
@endsection
