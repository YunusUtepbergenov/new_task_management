@extends('layouts.main')

@section('styles')
	<!-- Select2 CSS -->
	<link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
	<!-- Datatable CSS -->
	<link rel="stylesheet" href="{{ asset('assets/css/dataTables.bootstrap4.min.css') }}">
	<!-- Datetimepicker CSS -->
	<link rel="stylesheet" href="{{ asset('assets/css/bootstrap-datetimepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/journals.css') }}">
@endsection

@section('main')
	<!-- Page Content -->
	<div class="content container-fluid">
		<!-- Page Header -->
		<div class="page-header">
			<div class="row align-items-center">
				<div class="col">
                    <h3 class="page-title">Экономическое Обозрение</h3>
				</div>
                <div class="col-auto float-right ml-auto" style="margin-bottom: 10px;">
                    <a href="#" class="btn add-btn" data-toggle="modal" data-target="#create_article">Добавить журнал</a>
                </div>
			</div>
            <ul class="nav nav-tabs nav-tabs-bottom">
                <li class="nav-item">
                </li>
            </ul>
		</div>
		<!-- /Page Header -->

		<div class="row">
            <div class="col-lg-12">
                <div class="col-auto float-right ml-auto" style="margin-bottom: 30px;">
                    <ul class="nav nav-tabs nav-tabs-bottom">
                        <li class="nav-item">
                            <a href="#" class="nav-link1">2022</a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link1">2021</a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link1">2020</a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link1">2019</a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link1">2018</a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link1">2017</a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link1">2016</a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link1">2015</a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link1">2014</a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link1">2013</a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link1">2012</a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link1">2011</a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link1">2010</a>
                        </li>

                    </ul>
                </div>
            </div>
            <div class="col-sm-6 col-md-4 col-lg-2">
                <div class="card mb-4 box-shadow">
                  <img class="card-img-top" style="height: 300px; width: 100%; display: block;" src="https://static.review.uz/crop/3/7/360__80_3794985784.jpg?v=1640692532" data-holder-rendered="true">
                  <div class="card-body">
                    <p class="card-text" style="text-align: center">12-264-2021</p>
                    <h4 style="text-align: center">Экономическое Обозрение</h4>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-md-4 col-lg-2">
                <div class="card mb-4 box-shadow">
                  <img class="card-img-top" style="height: 300px; width: 100%; display: block;" src="https://static.review.uz/crop/3/7/360__80_3794985784.jpg?v=1640692532" data-holder-rendered="true">
                  <div class="card-body">
                    <p class="card-text">12-264-2021</p>
                    <h4>Экономическое Обозрение</h4>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-md-4 col-lg-2">
                <div class="card mb-4 box-shadow">
                  <img class="card-img-top" style="height: 300px; width: 100%; display: block;" src="https://static.review.uz/crop/3/7/360__80_3794985784.jpg?v=1640692532" data-holder-rendered="true">
                  <div class="card-body">
                    <p class="card-text">12-264-2021</p>
                    <h4>Экономическое Обозрение</h4>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-md-4 col-lg-2">
                <div class="card mb-4 box-shadow">
                  <img class="card-img-top" style="height: 300px; width: 100%; display: block;" src="https://static.review.uz/crop/3/7/360__80_3794985784.jpg?v=1640692532" data-holder-rendered="true">
                  <div class="card-body">
                    <p class="card-text">12-264-2021</p>
                    <h4>Экономическое Обозрение</h4>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-md-4 col-lg-2">
                <div class="card mb-4 box-shadow">
                  <img class="card-img-top" style="height: 300px; width: 100%; display: block;" src="https://static.review.uz/crop/3/7/360__80_3794985784.jpg?v=1640692532" data-holder-rendered="true">
                  <div class="card-body">
                    <p class="card-text">12-264-2021</p>
                    <h4>Экономическое Обозрение</h4>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-md-4 col-lg-2">
                <div class="card mb-4 box-shadow">
                  <img class="card-img-top" style="height: 300px; width: 100%; display: block;" src="https://static.review.uz/crop/3/7/360__80_3794985784.jpg?v=1640692532" data-holder-rendered="true">
                  <div class="card-body">
                    <p class="card-text">12-264-2021</p>
                    <h4>Экономическое Обозрение</h4>
                  </div>
                </div>
              </div>
    </div>
    </div>
	<!-- /Page Content -->
@endsection

@section('scripts')

@endsection
