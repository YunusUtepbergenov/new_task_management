@extends('layouts.main')

@section('main')
	<!-- Page Content -->
	<div class="content container-fluid">
		<!-- Page Header -->
		<div class="page-header">
			<div class="row align-items-center">
				<div class="col">
                    <h3 class="page-title">Аналитические записки</h3>
				</div>
                <div class="col-auto float-right ml-auto" style="margin-bottom: 10px;">
                    <a href="#" class="btn add-btn" data-toggle="modal" data-target="#create_note">Добавить записка</a>
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
								<th>Сектор</th>
								<th>Источник</th>
                                <th class="skip-filter">Дата</th>
                                <th class="skip-filter">Cсылка</th>
                                <th class="skip-filter">Файл</th>
                            </tr>
						</thead>
						<tbody style="overflow: auto;">
                            @php
                                $key = ($notes->currentpage()-1) * $notes->perpage()
                            @endphp

                            @foreach ($notes as $note)
                                <tr>
                                    <td>{{ ++$key }}</td>
                                    <td>
                                        @if ($note->user_id == auth()->user()->id)
                                            <div class="dropdown dropdown-action profile-action">
                                                <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <form action="{{ route('notes.destroy', $note->id) }}" method="POST">
                                                        <input type="hidden" name="_method" value="DELETE">
                                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                        <button class="dropdown-item"><i class="fa fa-pencil m-r-5"></i>Удалить</button>
                                                    </form>
                                                    <a href="#" onclick="editNote({{ $note->id }})" class="dropdown-item" data-toggle="modal" data-target="#edit_note"><i class="fa fa-trash-o m-r-5"></i>Изменить</a>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="#">{{ $note->name }}</a>
                                    </td>
                                    <td>{{ $note->user->name }}</td>
                                    <td>{{ $note->user->sector->name }}</td>
                                    <td>
                                        @if ($note->paper)
                                            <a href="{{ route('note.source', $note->paper) }}">{{ substr($note->paper, 13) }}</a></td>
                                        @else

                                        @endif
                                    <td>{{ $note->created_at->format('Y-m-d') }}</td>
                                    <td style="text-align: center">
                                        @if ($note->link)
                                            <?php
                                                $note_str = explode(";", $note->link);
                                            ?>

                                            @for ($i = 0; $i < count(explode(";", $note->link)); $i++ )
                                                <a href="{{ $note_str[$i] }}" target="_blank">{{ parse_url(trim($note_str[$i]), PHP_URL_HOST) }}</a><br>
                                            @endfor
                                        @else
                                        -
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('note.download', $note->file) }}">
                                            @if (pathinfo($note->file, PATHINFO_EXTENSION) == "pdf")
                                                <span class="badge bg-inverse-danger">Pdf</span>
                                            @elseif (pathinfo($note->file, PATHINFO_EXTENSION) == "doc" || pathinfo($note->file, PATHINFO_EXTENSION) == "docx")
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

        {{ $notes->links() }}
        @include('partials._note_modal')

        @include('partials._edit_note')
    </div>
	<!-- /Page Content -->
@endsection

@section('scripts')
    {{-- <script src="{{ asset('assets/js/ddtf.js') }}"></script> --}}
    <script>

        // $('#myTable').ddTableFilter();

        $("#note_name").addClass("d-none");
        $("#note_source").addClass("d-none");
        $("#note_file").addClass("d-none");

        function editNote(id) {
            $.get("/note/info/byid/" + id, function (note) {
                console.log(note.note);
                $("#note_name2").addClass("d-none");
                $("#note_paper2").addClass("d-none");

                $("#note_id1").val(note.note.id);
                $("#note_name1").val(note.note.name);
                $("#note_link1").val(note.note.link);
            });
        }

        jQuery("#create_note").on("submit", function (e) {
            e.preventDefault();
            var formData = new FormData($("#createNote")[0]);
            var url = $(this).attr("action");

            $.ajax({
                url: url,
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    document.location.href = '/notes';
                    toastr.options =
                    {
                        "closeButton" : true,
                        "progressBar" : true
                    }
                    toastr.success("Записка успешно создана");
                },
                error: function (data) {
                    console.log(data);
                    $("#note_name").addClass("d-none");
                    $("#note_source").addClass("d-none");
                    $("#note_file").addClass("d-none");

                    var errors = data.responseJSON;
                    if ($.isEmptyObject(errors) == false) {
                        $.each(errors.errors, function (key, value) {
                            var ErrorId = "#note_" + key;
                            $(ErrorId).removeClass("d-none");
                            $(ErrorId).text(value);
                        });
                    }
                    toastr.error(errors.message);
                },
            });
        });

        jQuery("#editNote").on("submit", function (e) {
            e.preventDefault();
            var formData1 = new FormData($("#editNote")[0]);
            var url = document.getElementById('editNote').getAttribute("action");

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
                    document.location.href = '/notes';
                },
                error: function (data) {
                    var errors = data.responseJSON;
                    if ($.isEmptyObject(errors) == false) {
                        $.each(errors.errors, function (key, value) {
                            var ErrorId = "#note_" + key + "2";
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
