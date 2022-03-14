@extends('layouts.main')

@section('styles')
	<!-- Select2 CSS -->
	<link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
	<!-- Datatable CSS -->
	<link rel="stylesheet" href="{{ asset('assets/css/dataTables.bootstrap4.min.css') }}">
	<!-- Datetimepicker CSS -->
	<link rel="stylesheet" href="{{ asset('assets/css/bootstrap-datetimepicker.min.css') }}">
@endsection

@section('main')
	<!-- Page Content -->
	<div class="content container-fluid">
		<!-- Page Header -->
		<div class="page-header">
			<div class="row align-items-center">
				<div class="col">
					<h3 class="page-title"></h3>
				</div>
                    <div class="col-auto float-right ml-auto" style="margin-top: 10px;">
                        <a href="#" class="btn add-btn" data-toggle="modal" data-target="#create_employee">Добавить</a>
                    </div>
			</div>
		</div>
		<!-- /Page Header -->

		<div class="row">
			<div class="col-md-12">
				<div class="table-responsive" id="employeeTable">
					<table class="table custom-table" style="overflow-y: auto; height: 110px;">
						<thead id="employee_header">
							<tr>
								<th><span>&#8470;</span></th>
                                <th>Ф.И.О</th>
								<th>Сектор</th>
								<th>Должность</th>
								<th>Дата</th>
								<th>Действия</th>
                            </tr>
						</thead>
						<tbody style="overflow: auto;">

						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<!-- /Page Content -->
@endsection

@section('scripts')

@endsection
