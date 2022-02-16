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
                @if (Auth::user()->isHR())
                    <div class="col-auto float-right ml-auto" style="margin-top: 10px;">
                        <a href="#" class="btn add-btn" data-toggle="modal" data-target="#create_employee"> Добавить сотрудника</a>
                    </div>
                @endif
			</div>
		</div>
		<!-- /Page Header -->

		<div class="row">
			<div class="col-md-12">
				<div class="table-responsive" id="employeeTable">
					<table class="table custom-table" style="overflow-y: auto; height: 110px;">
						<thead>
							<tr>
								<th style="position: sticky;top: 0; background: #ABDD93;">Ф.И.О</th>
								<th style="position: sticky;top: 0; background: #ABDD93;">Почта</th>
								<th style="position: sticky;top: 0; background: #ABDD93;">Сектор</th>
								<th style="position: sticky;top: 0; background: #ABDD93;">Должность</th>
								<th style="position: sticky;top: 0; background: #ABDD93;">Тел.Номер</th>
								<th style="position: sticky;top: 0; background: #ABDD93;">Внутренный номер</th>
                            </tr>
						</thead>
						<tbody style="overflow: auto;">
                            @foreach ($sectors as $sector)
                                <tr>
                                    <th></th>
                                    <th></th>
                                    <th id="employee_normal">{{ $sector->name }}</th>
                                    <th></th>
                                    <th></th>
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
                                        <td> +998933667500</td>
                                        <td>450</td>
                                    </tr>
                                @endforeach
                            @endforeach
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
                        <form method="POST" action="{{ route('new.user') }}" id="createUser">
                            @csrf
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label>Введите имя</label>
                                        <input class="form-control" name="user_name" type="text">
                                        <input type="hidden" name="password" value="password">
                                        <input type="hidden" name="password_confirmation" value="password">
                                    </div>
                                    <div class="alert alert-danger" id="user_name"></div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label>Введите почта</label>
                                        <input class="form-control" name="email" type="email">
                                    </div>
                                    <div class="alert alert-danger" id="email"></div>
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

@section('scripts')
    <script>
        $(document).ready(function() {

            $("#user_name").addClass("d-none");
            $("#email").addClass("d-none");

            jQuery("#createUser").on("submit", function (e) {
                e.preventDefault();
                console.log("sheeeeeeesh");
                var formData = new FormData($("#createUser")[0]);
                var url = $(this).attr("action");

                $.ajax({
                    url: url,
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        location.reload();
                    },
                    error: function (data) {
                        $("#user_name").addClass("d-none");
                        $("#email").addClass("d-none");

                        var errors = data.responseJSON;
                        if ($.isEmptyObject(errors) == false) {
                            $.each(errors.errors, function (key, value) {
                                var ErrorId = "#" + key;
                                $(ErrorId).removeClass("d-none");
                                $(ErrorId).text(value);
                            });
                        }
                        console.log(errors);
                    },
                });
            });
        });
    </script>
@endsection
