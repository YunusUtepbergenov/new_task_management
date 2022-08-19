@extends('layouts.main')

@section('styles')
    @livewireStyles
@endsection

@section('main')
	<!-- Page Content -->
	<div class="content container-fluid">
		<!-- Page Header -->
		<div class="page-header">
			<div class="row align-items-center">
				<div class="col">
					<h3 class="page-title">Задачи</h3>
				</div>
			</div>
		</div>
		<!-- /Page Header -->
        @livewire('reports.user', ['userId' => $user_id, 'start' => $start, 'end'=> $end])
        @livewire('view-modal')
	</div>
	<!-- /Page Content -->
@endsection

@section('scripts')
    @livewireScripts
@endsection
