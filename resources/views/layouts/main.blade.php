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
        <link rel="shortcut icon" type="image/x-icon" href="https://cerr.uz/themes/cer/icon/favicon.ico">
		<!-- Bootstrap CSS -->
        <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
		<!-- Fontawesome CSS -->
        <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
		<!-- Lineawesome CSS -->
        <link rel="stylesheet" href="{{ asset('assets/css/line-awesome.min.css') }}">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/material-icons@1.13.14/iconfont/material-icons.min.css">
		<!-- Datatable CSS -->
		<link rel="stylesheet" href="{{ asset('assets/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/bootstrap-colorselector.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/docs.css') }}">
        <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
		<!-- Select2 CSS -->
		<link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/1.1.1/css/bootstrap-multiselect.css" integrity="sha512-Lif7u83tKvHWTPxL0amT2QbJoyvma0s9ubOlHpcodxRxpZo4iIGFw/lDWbPwSjNlnas2PsTrVTTcOoaVfb4kwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
		<!-- Datetimepicker CSS -->
		<link rel="stylesheet" href="{{ asset('assets/css/bootstrap-datetimepicker.min.css') }}">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css" integrity="sha512-6S2HWzVFxruDlZxI3sXOZZ4/eJ8AcxkQH1+JjSe/ONCEqR9L4Ysq5JdT5ipqtzU7WHalNwzwBv+iE51gNHJNqQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        @yield('styles')
		<!-- Main CSS -->
        <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
        <!-- Layout Override CSS -->
        <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
        @vite('resources/js/app.js')
    </head>
    <body>
		<!-- Main Wrapper -->
        <div class="main-wrapper">
			<!-- Header -->
            <div class="header">
				<!-- Logo (hidden by CSS, kept for compatibility) -->
                <div class="header-left">
                    <a href="{{ route('home') }}" class="logo" wire:navigate>
						<img src="{{ asset('assets/img/logo.svg') }}" width="80" height="40" alt="">
					</a>
                </div>
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
								<input class="form-control" type="text" name="term" id="search_field" placeholder="Поиск задач, документов..." autocomplete="off">
								<i class="fa fa-search search-icon-right"></i>
								<button type="button" class="search-clear-btn" id="search_clear">
									<i class="fa fa-times"></i>
								</button>
                                <ul class="list-group search_group result_search">
                                </ul>
							</form>
						</div>
					</li>
                </ul>
				@php $kpi = auth()->user()->kpiBoth(); @endphp
				<ul class="nav user-menu">
					<li class="nav-item flag-nav">
                        <div class="kpi-item">
                            <span class="kpi-label">KPI (норма)</span>
                            <span class="kpi-value">{{ $kpi['kpi'] }} баллов</span>
                        </div>
					</li>
                    <li class="nav-item flag-nav">
                        <div class="kpi-item">
                            <span class="kpi-label">KPI (итого)</span>
                            <span class="kpi-value">{{ $kpi['ovr_kpi'] }} баллов</span>
                        </div>
					</li>
                    <!-- Links Dropdown -->
                    <li class="nav-item dropdown">
                        <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
                            <i class="fa fa-link"></i>
                        </a>
                        <div class="dropdown-menu notifications">
                            <div class="topnav-dropdown-header">
                                <span class="notification-title">Ссылки</span>
                            </div>
                            <div class="noti-content">
                                <ul class="notification-list">
                                    <li class="notification-message">
                                        <a href="https://link.springer.com/" target="_blank" style="padding: 10px 15px; display: block; font-size: 14px;">SPRINGER NATURE</a>
                                    </li>
                                    <li class="notification-message">
                                        <a href="https://search.ebscohost.com" target="_blank" style="padding: 10px 15px; display: block; font-size: 14px;" data-toggle="tooltip" data-html="true" data-placement="left" title="User ID: <b>ns123207</b> <br> Password: <b>Databases1!</b>">EBSCO host</a>
                                    </li>
                                    <li class="notification-message">
                                        <a href="https://cerr.uz" target="_blank" style="padding: 10px 15px; display: block; font-size: 14px;">CERR.UZ</a>
                                    </li>
                                    <li class="notification-message">
                                        <a href="https://review.uz" target="_blank" style="padding: 10px 15px; display: block; font-size: 14px;">REVIEW.UZ</a>
                                    </li>
                                    <li class="notification-message">
                                        <a href="https://mail.cerr.uz" target="_blank" style="padding: 10px 15px; display: block; font-size: 14px;">MAIL.CERR.UZ</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </li>
                    <!-- /Links Dropdown -->
                    <!-- Birthdays Dropdown -->
                    <li class="nav-item dropdown">
                        <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
                            <i class="fa fa-birthday-cake"></i>
                        </a>
                        <div class="dropdown-menu notifications">
                            <div class="topnav-dropdown-header">
                                <span class="notification-title">Ближайшие дни рождения</span>
                            </div>
                            <div class="noti-content">
                                <ul class="notification-list">
                                    @foreach ($birthdays->where('leave', 0)->take(5) as $birthday)
                                        <li class="notification-message">
                                            <a href="#" onclick='profileModal("{{ $birthday->id }}")' style="display: flex; align-items: center; padding: 10px 15px; gap: 10px;">
                                                <img alt="" src="{{ $birthday->avatar ? asset('user_image/'.$birthday->avatar) : asset('user_image/avatar.jpg') }}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
                                                <div>
                                                    <div style="font-size: 14px; font-weight: 500;">{{ $birthday->short_name }}</div>
                                                    <div style="font-size: 13px; color: #888;">{{ $birthday->birth_date->format('d-m-Y') }}</div>
                                                </div>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </li>
                    <!-- /Birthdays Dropdown -->
                    @livewire('notifications')
				</ul>
				<!-- /Header Menu -->
            </div>
			<!-- /Header -->
            @persist('sidebar')
                @include('partials._sidebar')
            @endpersist
			<!-- Page Wrapper -->
            <div class="page-wrapper">
				<!-- Page Content -->
                <div class="content container-fluid">
                    <div class="content-flex-wrapper">
                        <div class="content-main">
                            @yield('main')
                        </div>
                    </div>
                </div>
            </div>
			<!-- /Page Wrapper -->

			@livewire('view-modal')

            <!-- Old right sidebar (hidden by CSS) -->
            <div class="sidebar_right"></div>
        </div>
		<!-- /Main Wrapper -->

        <!-- Dark Mode Toggle -->
        <button class="dark-mode-toggle" title="Темная тема">
            <i class="fa fa-moon-o"></i>
            <i class="fa fa-sun-o"></i>
        </button>

		<!-- jQuery -->
        <script src="{{ asset('assets/js/jquery-3.5.1.min.js') }}" data-navigate-once></script>
		<!-- Bootstrap Core JS -->
        <script src="{{ asset('assets/js/popper.min.js') }}" data-navigate-once></script>
        <script src="{{ asset('assets/js/bootstrap.min.js') }}" data-navigate-once></script>
		<!-- Slimscroll JS -->
		<script src="{{ asset('assets/js/jquery.slimscroll.min.js') }}" data-navigate-once></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/1.1.1/js/bootstrap-multiselect.min.js" integrity="sha512-fp+kGodOXYBIPyIXInWgdH2vTMiOfbLC9YqwEHslkUxc8JLI7eBL2UQ8/HbB5YehvynU3gA3klc84rAQcTQvXA==" crossorigin="anonymous" referrerpolicy="no-referrer" data-navigate-once></script>
		<!-- Select2 JS -->
		<script src="{{ asset('assets/js/select2.min.js') }}" data-navigate-once></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js" integrity="sha512-lbwH47l/tPXJYG9AcFNoJaTMhGvYWhVM9YI43CT+uteTRRaiLCui8snIgyAN8XWgNjNhCqlAUdzZptso6OCoFQ==" crossorigin="anonymous" referrerpolicy="no-referrer" data-navigate-once></script>
		<!-- Datetimepicker JS -->
		<script src="{{ asset('assets/js/moment.min.js') }}" data-navigate-once></script>
        <script src="{{ asset('js/bootstrap-colorselector.min.js') }}" data-navigate-once></script>
		<script src="{{ asset('assets/js/bootstrap-datetimepicker.min.js') }}" data-navigate-once></script>
		<!-- DataTables JS -->
		<script src="{{ asset('assets/js/jquery.dataTables.min.js') }}" data-navigate-once></script>
		<script src="{{ asset('assets/js/dataTables.bootstrap4.min.js') }}" data-navigate-once></script>
		<script src="{{ asset('assets/js/ddtf.js') }}" data-navigate-once></script>
		<!-- Custom JS -->
        <script src="{{ asset('assets/js/app.js') }}"></script>
        @yield('scripts')
        @stack('scripts')
		<script type="text/javascript">
            if (!window._layoutInitialized) {
                window._layoutInitialized = true;

                $(document).ready(function() {
                    $('#example-getting-started').multiselect();
                    $('#colorselector_1').colorselector();

                    // Mobile hamburger menu toggle
                    $(document).on('click', '#mobile_btn', function(e) {
                        e.preventDefault();
                        $('html').addClass('menu-opened');
                        $('.main-wrapper').addClass('slide-nav');
                        $('.sidebar-overlay').addClass('opened');
                    });

                    // Close sidebar on overlay click
                    $(document).on('click', '.sidebar-overlay.opened', function() {
                        $('html').removeClass('menu-opened');
                        $('.main-wrapper').removeClass('slide-nav');
                        $('.sidebar-overlay').removeClass('opened');
                    });

                    // Desktop sidebar collapse toggle
                    $('#sidebar_collapse_btn').on('click', function() {
                        $('body').toggleClass('sidebar-collapsed');
                        if ($('body').hasClass('sidebar-collapsed')) {
                            localStorage.setItem('sidebar-collapsed', 'true');
                            $('#sidebar-menu .submenu ul').slideUp(0);
                            $('#sidebar-menu .submenu a').removeClass('subdrop');
                        } else {
                            localStorage.removeItem('sidebar-collapsed');
                        }
                    });

                    // Expand sidebar when submenu is clicked in collapsed state
                    $(document).on('click', '#sidebar-menu .submenu > a', function() {
                        if ($('body').hasClass('sidebar-collapsed')) {
                            $('body').removeClass('sidebar-collapsed');
                            localStorage.removeItem('sidebar-collapsed');
                        }
                    });

                    // Restore sidebar state from localStorage
                    if (localStorage.getItem('sidebar-collapsed') === 'true') {
                        $('body').addClass('sidebar-collapsed');
                    }

                    // Dark mode toggle (delegated to survive wire:navigate morphing)
                    $(document).on('click', '.dark-mode-toggle', function() {
                        $('body').toggleClass('dark-mode');
                        if ($('body').hasClass('dark-mode')) {
                            localStorage.setItem('dark-mode', 'true');
                        } else {
                            localStorage.removeItem('dark-mode');
                        }
                    });

                    // Restore dark mode state from localStorage
                    if (localStorage.getItem('dark-mode') === 'true') {
                        $('body').addClass('dark-mode');
                    }

                    $('.select2').select2({ width: '100%' });
                });

                $('#wordForm').submit(function(event){
                    event.preventDefault();
                    var formData2 = $("#wordForm");

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
                        $.ajax({
                            url: 'http://192.168.1.60:8888/search',
                            type: "GET",
                            data: formData2.serialize(),
                            success: function (res) {
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
                    }
                });

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
                    }
                });

                var taskIndex = 1;

                $('#add-task').on('click', function () {
                    var templateHtml = $('#task-template').html().replace(/__index__/g, taskIndex);
                    var $newTask = $(templateHtml);

                    $newTask.find('.select2').each(function () {
                        $(this).removeClass('select2-hidden-accessible')
                            .removeAttr('data-select2-id')
                            .removeAttr('tabindex')
                            .removeAttr('aria-hidden');
                        $(this).next('.select2').remove();
                    });

                    $('#task-entries').append($newTask);
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

                // Search clear button
                $('#search_clear').on('click', function(){
                    $('#search_field').val('').focus();
                    $('.result_search').hide().html('');
                    window.toggleSearchIcons('');
                });

                window.toggleSearchIcons = function(val) {
                    if (val.length > 0) {
                        $('.search-icon-right').hide();
                        $('#search_clear').show();
                    } else {
                        $('.search-icon-right').show();
                        $('#search_clear').hide();
                    }
                };

                $('#search_field').on('input', function(){
                    window.toggleSearchIcons($(this).val());
                });

                $('#search_field').keyup(function(){
                    $('.result_search').html('');
                    var searchField = $('#search_field').val();
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
                                        $(ErrorId).removeClass("d-none");
                                        $(ErrorId).text(value);
                                    });
                                }
                            },
                        });
                    }else{
                        $('.result_search').hide();
                    }
                });

                $("#name").addClass("d-none");
                $("#project_name").addClass("d-none");
                $("#deadline").addClass("d-none");
                $("#file").addClass("d-none");

                document.addEventListener('livewire:init', function () {
                    window.searchResult = function(id){
                        Livewire.dispatch('taskClicked', { id: id });
                    }

                    window.profileModal = function(id){
                        Livewire.dispatch('profileClicked', { id: id });
                    }

                    window.openModal = function(id){
                        Livewire.dispatch('taskClicked', { id: id });
                    }

                    Livewire.on('show-modal', () => {
                        $('#view_task').modal('show');
                    });

                    Livewire.on('profile-show-modal', () => {
                        $('#profile_modal').modal('show');
                    });

                    Livewire.on('success', (params) => {
                        toastr.options = { "closeButton" : true, "progressBar" : true };
                        toastr.success(params.msg);
                    });

                    Livewire.on('toastr:success', (params) => {
                        toastr.options = { "closeButton" : true, "progressBar" : true };
                        toastr.success(params.message);
                    });
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

                // SPA navigation handler — restore UI state and re-init plugins
                document.addEventListener('livewire:navigated', () => {
                    // Restore dark mode and sidebar state
                    if (localStorage.getItem('dark-mode') === 'true') {
                        document.body.classList.add('dark-mode');
                    }
                    if (localStorage.getItem('sidebar-collapsed') === 'true') {
                        document.body.classList.add('sidebar-collapsed');
                    }

                    // Update active sidebar link and submenu
                    var path = window.location.pathname;

                    // Remove all active classes from sidebar links
                    $('#sidebar-menu a').removeClass('active');
                    $('#sidebar-menu li').removeClass('active');
                    $('#journals_menu, #reports_menu').hide();
                    $('#sidebar-menu .submenu > a').removeClass('subdrop');

                    // Find and highlight the matching sidebar link
                    $('#sidebar-menu a[href]').each(function() {
                        var href = $(this).attr('href');
                        if (href && href !== '#' && href !== 'javascript:void(0)') {
                            try {
                                var linkPath = new URL(href, window.location.origin).pathname;
                                if (linkPath === path || (path.indexOf(linkPath) === 0 && linkPath !== '/')) {
                                    $(this).addClass('active');
                                    $(this).closest('li').addClass('active');
                                }
                            } catch(e) {}
                        }
                    });

                    // Open the correct submenu
                    if (path == "/articles" || path.indexOf('/journal') >= 0 || path == "/digests" || path == "/notes") {
                        $('#journals_menu').show();
                        $('#journals_menu').prev().addClass('subdrop');
                    } else if (path.indexOf('/reports') >= 0 || path.indexOf('/kpi') >= 0 || path.indexOf('/weekly') >= 0) {
                        $('#reports_menu').show();
                        $('#reports_menu').prev().addClass('subdrop');
                    }

                    // Re-init jQuery plugins on navigated-to pages
                    if ($('.select2').length) {
                        $('.select2').select2({ width: '100%' });
                    }
                    $('[data-toggle="tooltip"]').tooltip();

                    // Re-init datetimepickers
                    if ($('.datetimepicker').length) {
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
                });
            } // end guard

            // These run on every page load (session flash messages)
            @if(Session::has('message'))
                toastr.options = { "closeButton" : true, "progressBar" : true };
                toastr.success("{{ session('message') }}");
            @endif

            @if(Session::has('error'))
                toastr.options = { "closeButton" : true, "progressBar" : true };
                toastr.error("{{ session('error') }}");
            @endif

            // Initial page highlighting (runs on first load)
            (function() {
                var path = window.location.pathname;

                // Highlight matching sidebar link
                $('#sidebar-menu a[href]').each(function() {
                    var href = $(this).attr('href');
                    if (href && href !== '#' && href !== 'javascript:void(0)') {
                        try {
                            var linkPath = new URL(href, window.location.origin).pathname;
                            if (linkPath === path || (path.indexOf(linkPath) === 0 && linkPath !== '/')) {
                                $(this).addClass('active');
                                $(this).closest('li').addClass('active');
                            }
                        } catch(e) {}
                    }
                });

                // Open correct submenu
                if(path == "/articles" || path.indexOf('/journal') >= 0 || path == "/digests" || path == "/notes"){
                    $('#journals_menu').show();
                    $('#journals_menu').prev().addClass('subdrop');
                }else if(path.indexOf('/reports') >= 0 || path.indexOf('/kpi') >= 0 || path.indexOf('/weekly') >= 0){
                    $('#reports_menu').show();
                    $('#reports_menu').prev().addClass('subdrop');
                }
            })();
		</script>
    </body>
</html>
