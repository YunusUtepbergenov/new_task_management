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
        <div class="row">
            <div class="col-lg-7 m-auto">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Настройки</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('update.password') }}" method="POST">
                            <input type="hidden" name="_method" value="PUT">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label">Прежний пароль</label>
                                <div class="col-lg-9">
                                    @error('old_password')
                                        <div class="alert alert-danger" style="margin-bottom: 10px">{{ $message }}</div>
                                    @enderror
                                    <input type="password" class="form-control" name="old_password" value="{{ old('old_password') }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label">Новый пароль</label>
                                <div class="col-lg-9">
                                    @error('new_password')
                                        <div class="alert alert-danger" style="margin-bottom: 10px">{{ $message }}</div>
                                    @enderror
                                    <input type="password" class="form-control" name="new_password" value="{{ old('new_password') }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label">Подтвердите пароль</label>
                                <div class="col-lg-9">
                                    @error('confirm_password')
                                        <div class="alert alert-danger" style="margin-bottom: 10px">{{ $message }}</div>
                                    @enderror
                                    <input type="password" class="form-control" name="confirm_password" value="{{ old('confirm_password') }}">
                                </div>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="btn btn-primary">Изменить</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
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
