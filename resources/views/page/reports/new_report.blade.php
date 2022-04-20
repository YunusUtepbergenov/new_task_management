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
					<h3 class="page-title">Сотрудники</h3>
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
								<th>Ф.И.О</th>
								<th>Сектор</th>
								<th>Должность</th>
								<th>Все задачи</th>
								<th>Выполнено</th>
                            </tr>
						</thead>
						<tbody style="overflow: auto;">
                            @foreach ($sectors as $sector)
                                <tr>
                                    <th colspan="6" id="employee_normal">{{ $sector->name }}</th>
                                </tr>
                                @foreach ($sector->users as $employee)
                                    @if (!$employee->isDirector())
                                    <tr>
                                        <td>
                                            <h2 class="table-avatar">
                                                <a href="{{ route('user.report', $employee->id) }}">{{ $employee->name }}</a>
                                            </h2>
                                        </td>
                                        <td class="text-wrap"></td>
                                        <td>{{ $employee->role->name }}</td>
                                        <td> {{$employee->tasks->count()}}</td>
                                        <td>{{$employee->closedTasks()->count()}}</td>
                                    </tr>

                                    @endif
                                @endforeach
                            @endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>

	</div>
	<!-- /Page Content -->
@endsection
