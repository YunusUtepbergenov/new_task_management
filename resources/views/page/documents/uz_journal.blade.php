@extends('layouts.main')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/journals.css') }}">
@endsection

@section('main')
	<!-- Page Content -->
	<div class="content container-fluid">
		<!-- Page Header -->
		<div class="page-header">
			<div class="row align-items-center">
				<div class="col">
                    <h3 class="page-title">Иқтисодий Шарҳ</h3>
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
                        @foreach ($years as $year)
                            <li class="nav-item">
                                <a href="{{ route('journal.uz', $year->year) }}" class="nav-link1">{{ $year->year }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @foreach ($journals as $journal)
                <div class="col-sm-6 col-md-4 col-lg-2">
                    <div class="card mb-4 box-shadow">
                    <a href="{{ route('journal', $journal->id) }}"><img class="card-img-top" style="height: 260px; width: 100%; display: block;" src="{{ $journal->img }}" data-holder-rendered="true"></a>
                    <div class="card-body">
                        <p class="card-text" style="text-align: center">{{ $journal->name }}</p>
                        <h4 style="text-align: center">Иқтисодий Шарҳ</h4>
                    </div>
                    </div>
                </div>
            @endforeach
    </div>
    </div>
	<!-- /Page Content -->
@endsection

@section('scripts')

@endsection
