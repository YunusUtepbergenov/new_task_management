@extends('layouts.main')

@section('styles')
	<!-- Select2 CSS -->
	<link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
	<!-- Datatable CSS -->
	<link rel="stylesheet" href="{{ asset('assets/css/dataTables.bootstrap4.min.css') }}">

	<!-- Datetimepicker CSS -->
	<link rel="stylesheet" href="{{ asset('assets/css/bootstrap-datetimepicker.min.css') }}">
    @livewireStyles()
@endsection

@section('main')
    @php
        $months = array(
            1 => "Январь",
            2 => "Февраль",
            3 => "Март",
            4 => "Апрель",
            5 => "Май",
            6 => "Июнь",
            7 => "Июль",
            8 => "Август",
            9 => "Сентябрь",
            10 => "Октябрь",
            11 => "Ноябрь",
            12 => "Декабрь"
        );
    @endphp
	<!-- Page Content -->
	<div class="content container-fluid">
		<!-- Page Header -->
		<div class="page-header">
			<div class="row align-items-center">
				<div class="col">
					<h3 class="page-title">График Отпусков</h3>
				</div>
			</div>
		</div>
		<!-- /Page Header -->

		<div class="row">
			<div class="col-md-12">
				<div class="table-responsive" id="employeeTable">
                    <table class="table custom-table mb-0 datatable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Месяц</th>
                                <th>Сотрудник</th>
                                <th>Должность</th>
                                <th>За период работы:</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($vacations as $vacation)
                                <tr style="{{(now()->month == $vacation->month) ? 'background-color: #ebe9a8' : ''}}">
                                    <td>{{ $vacation->month }}</td>
                                    <td>{{ $months[$vacation->month] }}</td>
                                    <td>
                                        {!! $vacation->users->map(function ($user) {
                                            return $user->name;
                                        })->join('<br>') !!}
                                    </td>
                                    <td>
                                        {!! $vacation->users->map(function ($user) {
                                            return $user->role->name;
                                        })->join('<br>') !!}
                                    </td>
                                    <td>
                                        {!! $vacation->users->map(function ($user) {
                                            $date = \Carbon\Carbon::parse($user->join_date);
                                            return $date->year(now()->year - 1)->format('Y.m.d') . ' - ' . 
                                                $date->year(now()->year)->format('Y.m.d');
                                        })->join('<br>') !!}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
				</div>
			</div>
		</div>

        @livewire('view-modal')
	</div>
	<!-- /Page Content -->
@endsection

@section('scripts')
    @livewireScripts()

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
