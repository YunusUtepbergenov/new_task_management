<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
        <meta name="description" content="CERR Task Manager">
		<meta name="keywords" content="admin, tasks, ijro, task management, CERR">
        <meta name="author" content="Yunus Utepbergenov">
        <meta name="robots" content="noindex, nofollow">
        <title>CERR Task Management</title>
		<!-- Favicon -->
        <link rel="shortcut icon" type="image/x-icon" href="https://cer.uz/themes/cer/icon/favicon.ico">
        {{-- <link rel="stylesheet" href="{{ asset('css/app.css') }}"> --}}
		<!-- Bootstrap CSS -->
        <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
		<!-- Fontawesome CSS -->
        <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
		<!-- Lineawesome CSS -->
        <link rel="stylesheet" href="{{ asset('assets/css/line-awesome.min.css') }}">
		<!-- Datatable CSS -->
		<link rel="stylesheet" href="{{ asset('assets/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/bootstrap-colorselector.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/docs.css') }}">
		<!-- Select2 CSS -->
		<link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/1.1.1/css/bootstrap-multiselect.css" integrity="sha512-Lif7u83tKvHWTPxL0amT2QbJoyvma0s9ubOlHpcodxRxpZo4iIGFw/lDWbPwSjNlnas2PsTrVTTcOoaVfb4kwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
		<!-- Datetimepicker CSS -->
		<link rel="stylesheet" href="{{ asset('assets/css/bootstrap-datetimepicker.min.css') }}">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css" integrity="sha512-6S2HWzVFxruDlZxI3sXOZZ4/eJ8AcxkQH1+JjSe/ONCEqR9L4Ysq5JdT5ipqtzU7WHalNwzwBv+iE51gNHJNqQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        @yield('styles')
		<!-- Main CSS -->
        <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    </head>
    <body>
		<!-- Main Wrapper -->
        <div class="main-wrapper">
			<!-- Header -->
            <div class="header">
				<!-- Logo -->
                <div class="header-left">
                    <a href="{{ route('home') }}" class="logo">
						<img src="{{ asset('assets/img/logo.svg') }}" width="80" height="40" alt="">
					</a>
                </div>
				<!-- /Logo -->
				<a id="toggle_btn" href="javascript:void(0);">
					<span class="bar-icon">
						<span></span>
						<span></span>
						<span></span>
					</span>
				</a>
				<a id="mobile_btn" class="mobile_btn" href="#sidebar"><i class="fa fa-bars"></i></a>
				<!-- Header Menu -->
                <ul class="nav search-menu">
                    <li class="nav-item">
						<div class="top-nav-search">
							<a href="javascript:void(0);" class="responsive-search">
								<i class="fa fa-search"></i>
						   </a>
							<form action="{{ route('task.search') }}" id="searchForm" method="POST">
                                @csrf
								<input class="form-control" type="text" name="term" id="search_field" placeholder="Поиск" autocomplete="off">
								<button class="btn" type="submit" disabled><i class="fa fa-search"></i></button>
                                <ul class="list-group search_group result_search">
                                </ul>
							</form>
						</div>
					</li>
                </ul>
				<ul class="nav user-menu">
					<li class="nav-item dropdown flag-nav">
                        @if (auth()->user()->tasks()->count())
    						<a class="nav-link dropdown-toggle">KPI (норма): {{ auth()->user()->kpiCalculate() }} баллов </a>
                        @else
    						<a class="nav-link dropdown-toggle">KPI: 0 баллов</a>
                        @endif
					</li>
                    <li class="nav-item dropdown flag-nav">
                        @if (auth()->user()->tasks()->count())
    						<a class="nav-link dropdown-toggle">KPI (итого): {{ auth()->user()->ovrKpiCalculate() }} баллов </a>
                        @else
    						<a class="nav-link dropdown-toggle">KPI (итого): 0 баллов</a>
                        @endif
					</li>
                    @include('partials.notifications')
					<li class="nav-item dropdown has-arrow main-drop">
						<a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
							<span class="user-img"><img src="{{ (Auth::user()->avatar) ? asset('user_image/'.Auth::user()->avatar) : asset('user_image/avatar.jpg') }}" class="user_image" alt="">
							<span class="status online"></span></span>
							<span>{{ Auth::user()->name }}</span>
						</a>
						<div class="dropdown-menu">
							{{-- <a class="dropdown-item" href="profile.html">My Profile</a> --}}
							<a class="dropdown-item" href="{{ route('settings') }}">Настройки</a>
							<form action="{{ route('logout') }}" method="POST">
								@csrf
								<button class="dropdown-item">Выйти</button>
							</form>
						</div>
					</li>
				</ul>
				<!-- /Header Menu -->
				<!-- Mobile Menu -->
				<div class="dropdown mobile-user-menu">
					<a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
					<div class="dropdown-menu dropdown-menu-right">
						{{-- <a class="dropdown-item" href="profile.html">My Profile</a> --}}
						<a class="dropdown-item" href="settings.html">Настройки</a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="dropdown-item">Выйти</button>
                        </form>
					</div>
				</div>
				<!-- /Mobile Menu -->
            </div>
			<!-- /Header -->
            @include('partials._sidebar')
			<!-- Page Wrapper -->
            <div class="page-wrapper">
				<!-- Page Content -->
                <div class="content container-fluid">
                    @yield('main')
                </div>
            </div>
				<!-- /Page Content -->
            </div>
			<!-- /Page Wrapper -->
			<!-- Right Sidebar -->
			<div class="sidebar_right" id="sidebar">
                <div class="accordion" id="accordionExample">
                    <div class="card" style="border: 0">
                        <div class="card-header sidebar_right_header" id="headingOne">
                          <h6 class="sidebar_right_header_title m-b-5">
                            <button class="btn btn-block text-left collapsed" data-toggle="collapse" data-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne" style="width: 100%; text-align:left">
                                Ссылки <span class="menu-arrow"></span>
                            </button>
                          </h6>
                        </div>

                        <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
                          <div class="card-body" style="background: #34444c; color: #fff; padding-top: 0; padding-bottom:1px;">
                            <ul class="list-box">
                                <li>
                                    <a href="https://link.springer.com/" data-toggle="tooltip" target="blank" style="color: white">
                                        <div class="list-item">
                                            <div class="list-body" style="padding: 0;">
                                                <img class="links-websites" src="{{ asset('img/springer.svg') }}" alt="Springer">
                                            </div>
                                        </div>
                                    </a>
                                </li>

                                <li>
                                    <a href="https://search.ebscohost.com" data-toggle="tooltip" data-html="true" data-placement="left" title="User ID: <b>ns123207</b> <br> Password: <b>Databases1!</b>" target="blank" style="color: white">
                                        <div class="list-item">
                                            <div class="list-body" style="padding: 0;">
                                                <img class="links-websites" src="{{ asset('img/ebsco.png') }}" alt="Alisher Navoiy">
                                            </div>
                                        </div>
                                    </a>
                                </li>

                                <li>
                                    <a href="https://cer.uz" target="blank" style="color: white">
                                        <div class="list-item">
                                            <div class="list-body" style="padding: 0;">
                                                <img class="links-websites" src="{{ asset('assets/img/logo.svg') }}" alt="Alisher Navoiy">
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a href="https://review.uz" target="blank" style="color: white">
                                        <div class="list-item">
                                            <div class="list-body" style="padding: 0;">
                                                <h3>REVIEW.UZ</h3>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a href="https://mail.cerr.uz" target="blank" style="color: white">
                                        <div class="list-item">
                                            <div class="list-body" style="padding: 0;">
                                                <h3>MAIL.CERR.UZ</h3>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            </ul>
                          </div>
                        </div>
                    </div>

                    <div class="card" style="border: 0">
                        <div class="card-header sidebar_right_header" id="headingOne">
                            <h6 class="sidebar_right_header_title m-b-5">
                              <button class="btn btn btn-block text-left" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo" style="width: 100%; text-align:left">
                                Ближайшие дни рождения <span class="menu-arrow"></span>
                              </button>
                            </h6>
                        </div>

                        <div id="collapseTwo" class="collapse show" aria-labelledby="headingTwo">
                            <div class="card-body" style="background: #34444c; color: #fff; padding-top: 0; padding-bottom:5px;">
                                <ul class="list-box">
                                    @php
                                        $counter = 0;
                                    @endphp

                                    @foreach ($birthdays->where('leave', 0)->take(3) as $birthday)
                                        <li>
                                            <a href="#" onclick='profileModal("{{ $birthday->id }}")'>
                                                <div class="list-item">
                                                    <div class="list-left">
                                                        <span class="avatar"><img alt="" src="{{ ($birthday->avatar) ? asset('user_image/'.$birthday->avatar) : asset('user_image/avatar.jpg') }}"></span>
                                                    </div>
                                                    <div class="list-body">
                                                        <span class="birthday-author">{{ $birthday->name }}</span>
                                                        <div class="clearfix"></div>
                                                        <span class="birth-date">{{ $birthday->birth_date->format('d-m-Y') }}</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
			<!-- End Right Sidebar -->
        </div>
		<!-- /Main Wrapper -->
		<!-- jQuery -->
        <script src="{{ asset('assets/js/jquery-3.5.1.min.js') }}"></script>
		<!-- Bootstrap Core JS -->
        <script src="{{ asset('assets/js/popper.min.js') }}"></script>
        <script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
		<!-- Slimscroll JS -->
		<script src="{{ asset('assets/js/jquery.slimscroll.min.js') }}"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/1.1.1/js/bootstrap-multiselect.min.js" integrity="sha512-fp+kGodOXYBIPyIXInWgdH2vTMiOfbLC9YqwEHslkUxc8JLI7eBL2UQ8/HbB5YehvynU3gA3klc84rAQcTQvXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
		<!-- Select2 JS -->
		<script src="{{ asset('assets/js/select2.min.js') }}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js" integrity="sha512-lbwH47l/tPXJYG9AcFNoJaTMhGvYWhVM9YI43CT+uteTRRaiLCui8snIgyAN8XWgNjNhCqlAUdzZptso6OCoFQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
		<!-- Datetimepicker JS -->
		<script src="{{ asset('assets/js/moment.min.js') }}"></script>
        <script src="{{ asset('js/bootstrap-colorselector.min.js') }}"></script>
		<script src="{{ asset('assets/js/bootstrap-datetimepicker.min.js') }}"></script>
		<!-- Custom JS -->
		<script src="{{ asset('assets/js/app.js') }}"></script>
        @yield('scripts')
        <script src="{{ asset('js/app.js') }}"></script>
		<script type="text/javascript">
			$(document).ready(function() {
				$('#example-getting-started').multiselect();
                $('#colorselector_1').colorselector();
			});


            $('#wordForm').submit(function(event){
                event.preventDefault();
                var formData2 = $("#wordForm");
                console.log(formData2);
                
                $.ajax({
                    url: 'http://192.168.1.60:8888/add',
                    type: "POST",
                    data: formData2.serialize(),
                    success: function (res) {
                        window.location.reload();
                    },
                    error: function (data) {
                        var errors = data.responseJSON;
                        console.log(errors);
                    },
                });
            });

            $('#colorselector_1').change(function(){
                var searchField1 = $('#added_word').val();
                $('#wordButton').prop('disabled', false);

                var formData2 = $("#wordForm");
                if(searchField1.length > 2){
                    console.log(formData2.serialize())
                    $.ajax({
                    url: 'http://192.168.1.60:8888/search',
                    type: "GET",
                    data: formData2.serialize(),
                    success: function (res) {
                        console.log(res.data.words);
                        $('.words_search').html('');
                        $('.words_search').show();
                        $.each(res.data.words, function(key, value){
                            if(value == searchField1.toLowerCase()){
                                $('#wordButton').prop('disabled', true);
                            }else{
                                $('#wordButton').prop('disabled', false);
                            }
                            $('.words_search').append("<li class='list-group-item search_dropdown'><a href='#'>" + value + "</a></li>");
                        });
                        res = '';
                    },
                    error: function (data) {
                        var errors = data.responseJSON;
                        console.log(errors);
                    },
                });
            }});
            $('#added_word').keyup(function(){
                $('.words_search').html('');
                var searchField1 = $('#added_word').val();
                $('#wordButton').prop('disabled', false);


                var formData2 = $("#wordForm");
                if(searchField1.length > 2){
                    $.ajax({
                    url: 'http://192.168.1.60:8888/search',
                    type: "GET",
                    data: formData2.serialize(),
                    success: function (res) {
                        console.log(res.data.words);
                        $('.words_search').html('');
                        $('.words_search').show();
                        $.each(res.data.words, function(key, value){
                            if(value == searchField1.toLowerCase()){
                                $('#wordButton').prop('disabled', true);
                            }else{
                                $('#wordButton').prop('disabled', false);
                            }
                            console.log(value);
                            $('.words_search').append("<li class='list-group-item search_dropdown'><a href='#'>" + value + "</a></li>");
                        });
                        res = '';
                    },
                    error: function (data) {
                        var errors = data.responseJSON;
                        console.log(errors);
                    },
                });
                }else{
                    // $('.result_search').hide();
                }

            });


            
            let taskIndex = 1;

            $('#add-task').on('click', function () {
                let templateHtml = $('#task-template').html().replace(/__index__/g, taskIndex);
                let $newTask = $(templateHtml);

                // Remove any old Select2 residue
                $newTask.find('.select2').each(function () {
                    $(this).removeClass('select2-hidden-accessible')
                        .removeAttr('data-select2-id')
                        .removeAttr('tabindex')
                        .removeAttr('aria-hidden');
                    $(this).next('.select2').remove(); // Remove the attached container
                });

                // Append to the page
                $('#task-entries').append($newTask);

                // Re-initialize select2 on the newly added select
                $newTask.find('.select2').select2({ width: '100%' });

                if($('.datetimepicker').length > 0) {
		            $('.datetimepicker').datetimepicker({
                    format: 'YYYY-MM-DD',
                    icons: {
                        up: "fa fa-angle-up",
                        down: "fa fa-angle-down",
                        next: 'fa fa-angle-right',
                        previous: 'fa fa-angle-left'
                    }
		            });
	            }

                taskIndex++;
            });

            $(document).on('click', '.remove-task', function () {
                if ($('.task-group').length > 1) {
                    $(this).closest('.task-group').remove();
                }
            });

            $(document).ready(function () {
                $('.select2').select2({ width: '100%' });
            });


            $('#search_field').keyup(function(){
                $('.result_search').html('');
                var searchField = $('#search_field').val();
                // var expression = new RegExp(searchField, "i");
                var formData1 = new FormData($("#searchForm")[0]);

                var url = document.getElementById('searchForm').getAttribute("action");
                if(searchField.length > 2){
                    $.ajax({
                    url: url,
                    type: "POST",
                    headers: {
                        "X-CSRF-TOKEN": $('input[name="_token"]').val(),
                    },
                    data: formData1,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        $('.result_search').html('');
                        $('.result_search').show();
                        $.each(JSON.parse(res), function(key, value){
                            // console.log(va);
                            if(value.model == "Task"){
                                $('.result_search').append("<li class='list-group-item search_dropdown'><a href='#' onclick='searchResult(" + value.id + ")'>" + value.name + "</a></li>");

                            }else if(value.model == "User"){
                                $('.result_search').append("<li class='list-group-item search_dropdown'><a href='#' onclick='profileModal(" + value.id + ")'>" + value.name + "</a></li>");
                            }
                        });
                        res = '';
                    },
                    error: function (data) {
                        var errors = data.responseJSON;
                        if ($.isEmptyObject(errors) == false) {
                            $.each(errors.errors, function (key, value) {
                                var ErrorId = "#" + key + "2";
                                console.log(ErrorId);
                                $(ErrorId).removeClass("d-none");
                                $(ErrorId).text(value);
                            });
                        }
                        console.log(errors);
                    },
                });
                }else{
                    $('.result_search').hide();
                }
            });

            path = window.location.pathname;
            if(path == "/articles" || path.indexOf('/journal') >= 0 || path == "/digests" || path == "/notes"){
                $('#journals_menu').show();
                $('#journals_menu').prev().addClass('subdrop');
            }else if(path.indexOf('/reports') >= 0){
                $('#reports_menu').show();
                $('#reports_menu').prev().addClass('subdrop');
            }

            $("#name").addClass("d-none");
            $("#project_name").addClass("d-none");
            $("#deadline").addClass("d-none");
            $("#file").addClass("d-none");

            searchResult = function(id){
                window.livewire.emit('taskClicked', id);
            }

            profileModal = function(id){
                window.livewire.emit('profileClicked', id);
            }

            window.addEventListener('show-modal', event => {
                $('#view_task').modal('show');
            });

            window.addEventListener('profile-show-modal', event => {
                $('#profile_modal').modal('show');
            });

            window.addEventListener('success', event => {
                toastr.success(event.detail.msg);
            });

            $('#flexCheckDefault').click(function() {
                $("#repeat_container").toggle(this.checked);
                $(".repeat_div").toggle(this.checked);
                var val = document.getElementById("repeat").value;

                if(this.checked){
                    if(val == 'weekly'){
                        $("#days_container").toggle(true);
                        $("#month_container").toggle(false);
                    }else if(val == "monthly"){
                        $("#days_container").toggle(false);
                        $("#month_container").toggle(true);
                    }
                }
            });

            $("#repeat_container select").on('change', function(){
                if(this.value == 'weekly'){
                    $("#days_container").toggle(true);
                    $("#month_container").toggle(false);
                }else{
                    $("#days_container").toggle(false);
                    $("#month_container").toggle(true);
                }
            });

            $('#flexCheckDefault3').click(function() {
                $("#repeat_container1").toggle(this.checked);
                $("#repeat_div_cont").toggle(this.checked);
                var val = document.getElementById("repeat1").value;
                if(this.checked){
                    if(val == 'weekly'){
                        $("#days_container1").toggle(true);
                        $("#month_container1").toggle(false);
                    }else if(val == "monthly"){
                        $("#days_container1").toggle(false);
                        $("#month_container1").toggle(true);
                    }
                }
            });

            $("#repeat_container1 select").on('change', function(){
                if(this.value == 'weekly'){
                    $("#days_container1").toggle(true);
                    $("#month_container1").toggle(false);
                }else{
                    $("#days_container1").toggle(false);
                    $("#month_container1").toggle(true);
                }
            });

			@if(Session::has('message'))
                toastr.options =
                {
                    "closeButton" : true,
                    "progressBar" : true
                }
                toastr.success("{{ session('message') }}");
            @endif

            @if(Session::has('error'))
                toastr.options =
                {
                    "closeButton" : true,
                    "progressBar" : true
                }
                toastr.error("{{ session('error') }}");
            @endif
		</script>
    </body>
</html>
