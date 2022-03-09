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
	</div>

    @livewire('view-modal')
	<!-- /Page Content -->
@endsection

@section('scripts')
    @livewireScripts
@endsection
