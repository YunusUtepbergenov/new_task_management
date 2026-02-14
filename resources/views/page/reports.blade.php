@extends('layouts.main')

@section('main')
	<!-- Page Header -->
	<div class="page-header">
		<div class="row align-items-center">
			<div class="col">
				<h3 class="page-title">Отчёты</h3>
			</div>
		</div>
	</div>
	<!-- /Page Header -->

	<div class="row">
		<div class="col-md-7">
			@livewire('reports.sector')
		</div>
		<div class="col-md-5">
			@livewire('reports.user-section')
		</div>
		<div class="col-md-12">
			@livewire('reports.tasks-section')
		</div>
	</div>
@endsection
