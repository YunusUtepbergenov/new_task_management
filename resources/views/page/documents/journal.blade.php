@extends('layouts.main')

@section('styles')
	<!-- Select2 CSS -->
	<link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
	<!-- Datatable CSS -->
	<link rel="stylesheet" href="{{ asset('assets/css/dataTables.bootstrap4.min.css') }}">
	<!-- Datetimepicker CSS -->
	<link rel="stylesheet" href="{{ asset('assets/css/bootstrap-datetimepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/journals.css') }}">
    <link rel="stylesheet" href="https://static.review.uz/assets/5e1a9dca/css/dflip.css?v=1608372810">
    <link href="https://static.review.uz/assets/5e1a9dca/css/themify-icons.css?v=1608372810" rel="stylesheet">

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
			</div>
            <ul class="nav nav-tabs nav-tabs-bottom">
                <li class="nav-item">
                </li>
            </ul>
		</div>
		<!-- /Page Header -->

		<div class="row">
            <div class="_df_book" id="flipbookContainer"></div>
        </div>
    </div>
	<!-- /Page Content -->
@endsection

@section('scripts')
<script src="{{ asset('js/libs/dflip.js') }}"></script>

<script>
    @if (str_contains($journal->file, ','))
        var flipBook = $('#flipbookContainer').flipBook([{!! $journal->file !!}], {
        height: '900px',
        enableDownload:false,
        scrollWheel:false,
        moreControls: 'pageMode,startPage,endPage,sound',
        backgroundColor:'red'
        });
    @else
        var flipBook = $('#flipbookContainer').flipBook({!! $journal->file !!}, {
        height: '900px',
        enableDownload:false,
        scrollWheel:false,
        moreControls: 'pageMode,startPage,endPage,sound',
        backgroundColor:'red'
        });
    @endif
</script>
@endsection
