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
					<h3 class="page-title"> Сотрудники</h3>
				</div>
                <div class="col-auto float-right ml-auto" style="margin-top: 10px;">
                    <a href="#" class="btn add-btn" data-toggle="modal" data-target="#create_employee"> Добавить сотрудника</a>
                </div>
			</div>
		</div>
		<!-- /Page Header -->

		<div class="row">
			<div class="col-md-12">
				<div class="table-responsive">
					<table class="table table-striped custom-table">
						<thead>
							<tr>
								<th>Ф.И.О</th>
								<th>Почта</th>
								<th>Сектор</th>
								<th>Должность</th>
							</tr>
						</thead>
						<tbody>
                            @foreach ($sectors as $sector)
                                <tr>
                                    <th></th>
                                    <th></th>
                                    <th>{{ $sector->name }}</th>
                                    <th></th>
                                </tr>
                                @foreach ($sector->users as $employee)
                                    <tr>
                                        <td>
                                            <h2 class="table-avatar">
                                                <a href="#">{{ $employee->name }}</a>
                                            </h2>
                                        </td>
                                        <td>{{ $employee->email }}</td>
                                        <td class="text-wrap"></td>
                                        <td>{{ $employee->role->name }}</td>
                                    </tr>
                                @endforeach
                            @endforeach

							{{-- @foreach ($employees as $employee)
								<tr>
									<td>
										<h2 class="table-avatar">
											<a href="#">{{ $employee->name }}</a>
										</h2>
									</td>
									<td>{{ $employee->email }}</td>
									<td class="text-wrap">{{ $employee->sector->name }}</td>
									<td>{{ $employee->role->name }}</td>
								</tr>
							@endforeach --}}
						</tbody>
					</table>
				</div>
			</div>
		</div>

            <!-- Create Task Modal -->
        <div id="create_employee" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Новый сотрудник</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="{{ route('register') }}">
                            @csrf
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label>Введите имя</label>
                                        <input class="form-control" name="name" type="text">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label>Введите почта</label>
                                        <input class="form-control" name="email" type="email">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Сектор</label>
                                <div class="col-sm-4">
                                    <select class="form-control" name="sector_id">
                                        @foreach ($sectors as $sector)
                                            <option value="{{ $sector->id }}">{{ $sector->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Должность</label>
                                <div class="col-sm-4">
                                    <select class="form-control" name="role_id">
                                        <option value="">Не проект</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="submit-section">
                                <button class="btn btn-primary submit-btn">Добавить нового сотрудника</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Create Task Modal -->

	</div>
	<!-- /Page Content -->
@endsection

{{-- @section('scripts')
	<!-- Select2 JS -->
	<script src="{{ asset('assets/js/select2.min.js') }}"></script>
	<script src="{{ asset('assets/js/moment.min.js') }}"></script>
	<script src="{{ asset('assets/js/bootstrap-datetimepicker.min.js') }}"></script>
	<!-- Datatable JS -->
	<script src="{{ asset('assets/js/jquery.dataTables.min.js') }}"></script>
	<script src="{{ asset('assets/js/dataTables.bootstrap4.min.js') }}"></script>

@endsection --}}
