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
                <h3 class="page-title">Статьи</h3>
				</div>
                <div class="col-auto float-right ml-auto" style="margin-bottom: 10px;">
                    <a href="#" class="btn add-btn" data-toggle="modal" data-target="#create_article">Добавить статью</a>
                </div>
			</div>
            <ul class="nav nav-tabs nav-tabs-bottom">
                <li class="nav-item">
                </li>
            </ul>
		</div>
		<!-- /Page Header -->
		<div class="row">
			<div class="col-md-12">
				<div class="table-responsive" id="employeeTable">
					<table class="table custom-table article-table" id="myTable" style="overflow-y: auto;">
						<thead id="employee">
							<tr>
								<th class="skip-filter"><span>&#8470;</span></th>
                                <th class="skip-filter">Название</th>
								<th>Автор</th>
								<th>Отдел</th>
								<th>Тематика</th>
                                <th class="skip-filter">Дата</th>
                                <th class="skip-filter">Cсылка</th>
                                <th class="skip-filter">Файл</th>
                            </tr>
						</thead>
						<tbody style="overflow: auto;">
                            @foreach ($articles as $key => $article)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>
                                        @if ($article->user_id == auth()->user()->id)
                                            <a href="#" onclick="editArticle({{ $article->id }})" data-toggle="modal" data-target="#edit_article">{{ $article->name }}</a>
                                        @else
                                            <a href="#">{{ $article->name }}</a>
                                        @endif
                                    </td>
                                    <td>{{ $article->user->name }}</td>
                                    <td>{{ $article->user->sector->name }}</td>
                                    <td>{{ $article->category->name }}</td>
                                    <td>{{ $article->created_at->format('Y-m-d') }}</td>
                                    <td style="text-align: center">
                                        @if ($article->link)
                                            <?php
                                                $article_str = explode(";", $article->link);
                                            ?>

                                            @for ($i = 0; $i < count(explode(";", $article->link)); $i++ )
                                                 {{-- {{ print($article_str[$i]) }} --}}
                                                <a href="{{ $article_str[$i] }}" target="_blank">{{ parse_url(trim($article_str[$i]), PHP_URL_HOST) }}</a><br>
                                            @endfor
                                        @else
                                        -
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('article.download', $article->file) }}">
                                            @if (pathinfo($article->file, PATHINFO_EXTENSION) == "pdf")
                                                <span class="badge bg-inverse-danger">Pdf</span>
                                            @elseif (pathinfo($article->file, PATHINFO_EXTENSION) == "doc" || pathinfo($article->file, PATHINFO_EXTENSION) == "docx")
                                                <span class="badge bg-inverse-primary">Doc</span>
                                            @endif
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
					</table>
				</div>
			</div>
		</div>
        @include('partials._article_modal')

        @include('partials._edit_article')
    </div>
	<!-- /Page Content -->
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/ddtf.js') }}"></script>
    <script>

        $('#myTable').ddTableFilter();

        $("#article_name").addClass("d-none");
        $("#article_description").addClass("d-none");
        $("#article_file").addClass("d-none");

        function editArticle(id) {
            $.get("/article/info/byid/" + id, function (article) {
                $("#article_id").val(article.article.id);
                $("#article_name1").val(article.article.name);
                $("#article_description1").val(article.article.description);
                $("#article_user_id1").val(article.article.user_id);
                $("#article_category_id1").val(article.article.category_id);
                $("#article_link1").val(article.article.link);
            });
        }

        jQuery("#create_article").on("submit", function (e) {
            e.preventDefault();
            var formData = new FormData($("#createArticle")[0]);
            var url = $(this).attr("action");

            $.ajax({
                url: url,
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    document.location.href = '/articles';
                    toastr.options =
                    {
                        "closeButton" : true,
                        "progressBar" : true
                    }
                    toastr.success("Статья успешно создана");
                },
                error: function (data) {
                    console.log(data);
                    $("#article_name").addClass("d-none");
                    $("#article_description").addClass("d-none");
                    $("#article_file").addClass("d-none");

                    var errors = data.responseJSON;
                    if ($.isEmptyObject(errors) == false) {
                        $.each(errors.errors, function (key, value) {
                            var ErrorId = "#article_" + key;
                            $(ErrorId).removeClass("d-none");
                            $(ErrorId).text(value);
                        });
                    }
                    toastr.error(errors.message);
                },
            });
        });

        jQuery("#editArticle").on("submit", function (e) {
            e.preventDefault();
            var formData1 = new FormData($("#editArticle")[0]);
            var url = document.getElementById('editArticle').getAttribute("action");

            $.ajax({
                url: url,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                data: formData1,
                processData: false,
                contentType: false,
                success: function (res) {
                    document.location.href = '/articles';
                },
                error: function (data) {
                    var errors = data.responseJSON;
                    if ($.isEmptyObject(errors) == false) {
                        $.each(errors.errors, function (key, value) {
                            var ErrorId = "#article_" + key + "2";
                            console.log(ErrorId);
                            $(ErrorId).removeClass("d-none");
                            $(ErrorId).text(value);
                        });
                    }
                    console.log(errors);
                },
            });
        });
    </script>
@endsection
