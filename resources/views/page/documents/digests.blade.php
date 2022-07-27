@extends('layouts.main')

@section('main')
	<!-- Page Content -->
	<div class="content container-fluid">
		<!-- Page Header -->
		<div class="page-header">
			<div class="row align-items-center">
				<div class="col">
                    <h3 class="page-title">Дайджесты</h3>
				</div>
                <div class="col-auto float-right ml-auto" style="margin-bottom: 10px;">
                    <a href="#" class="btn add-btn" data-toggle="modal" data-target="#format_digest">Форматирование дайджеста</a>
                </div>
                <div class="col-auto float-right ml-auto" style="margin-bottom: 10px;">
                    <a href="#" class="btn add-btn" data-toggle="modal" data-target="#create_digest">Добавить дайджест</a>
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
                                <th class="skip-filter"></th>
                                <th class="skip-filter">Название</th>
								<th>Автор</th>
								<th>Отдел</th>
								<th>Источник</th>
                                <th class="skip-filter">Дата</th>
                                <th class="skip-filter">Cсылка</th>
                                <th class="skip-filter">Файл</th>
                            </tr>
						</thead>
						<tbody style="overflow: auto;">
                            @foreach ($digests as $key => $article)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>
                                        @if ($article->user_id == auth()->user()->id)
                                            <div class="dropdown dropdown-action profile-action">
                                                <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                        <form action="{{ route('digests.destroy', $article->id) }}" method="POST">
                                                            <input type="hidden" name="_method" value="DELETE">
                                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                            <button class="dropdown-item"><i class="fa fa-pencil m-r-5"></i>Удалить</button>
                                                        </form>
                                                        <a href="#" onclick="editDigest({{ $article->id }})" class="dropdown-item" data-toggle="modal" data-target="#edit_digest"><i class="fa fa-trash-o m-r-5"></i>Изменить</a>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                            <a href="#">{{ $article->name }}</a>
                                    </td>
                                    <td>{{ $article->user->name }}</td>
                                    <td>{{ $article->user->sector->name }}</td>
                                    <td>
                                        @if ($article->paper)
                                            <a href="{{ route('paper.download', $article->paper) }}">{{ substr($article->paper, 13) }}</a></td>
                                        @else

                                        @endif
                                    <td>{{ $article->created_at->format('Y-m-d') }}</td>
                                    <td style="text-align: center">
                                        @if ($article->link)
                                            <?php
                                                $article_str = explode(";", $article->link);
                                            ?>

                                            @for ($i = 0; $i < count(explode(";", $article->link)); $i++ )
                                                <a href="{{ $article_str[$i] }}" target="_blank">{{ parse_url(trim($article_str[$i]), PHP_URL_HOST) }}</a><br>
                                            @endfor
                                        @else
                                        -
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('digest.download', $article->file) }}">
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
        @include('partials._digest_modal')
        @include('partials._digest_formatter')

        @include('partials._edit_digest')
    </div>
	<!-- /Page Content -->
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/ddtf.js') }}"></script>
    <script>

        $('#myTable').ddTableFilter();

        $("#digest_name").addClass("d-none");
        $("#digest_description").addClass("d-none");
        $("#digest_file").addClass("d-none");

        function editDigest(id) {
            $.get("/digest/info/byid/" + id, function (digest) {
                $("#digest_id").val(digest.digest.id);
                $("#digest_name1").val(digest.digest.name);
                $("#digest_paper1").val(digest.digest.paper);
                $("#digest_link1").val(digest.digest.link);
            });
        }

        jQuery("#create_digest").on("submit", function (e) {
            e.preventDefault();
            var formData = new FormData($("#createDigest")[0]);
            var url = $(this).attr("action");

            $.ajax({
                url: url,
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    document.location.href = '/digests';
                    toastr.options =
                    {
                        "closeButton" : true,
                        "progressBar" : true
                    }
                    toastr.success("Дайжест успешно создана");
                },
                error: function (data) {
                    console.log(data);
                    $("#digest_name").addClass("d-none");
                    $("#digest_description").addClass("d-none");
                    $("#digest_file").addClass("d-none");

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

        jQuery("#editDigest").on("submit", function (e) {
            e.preventDefault();
            var formData1 = new FormData($("#editDigest")[0]);
            var url = document.getElementById('editDigest').getAttribute("action");

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
                    document.location.href = '/digests';
                },
                error: function (data) {
                    var errors = data.responseJSON;
                    if ($.isEmptyObject(errors) == false) {
                        $.each(errors.errors, function (key, value) {
                            var ErrorId = "#digest_" + key + "2";
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
