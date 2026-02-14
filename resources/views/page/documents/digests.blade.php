@extends('layouts.main')
@section('main')
	<!-- Page Content -->
	<div class="content container-fluid">
        @livewire('documents.digest-filter')
        @include('partials._digest_modal')
        @include('partials._digest_formatter')

        @include('partials._edit_digest')
    </div>
	<!-- /Page Content -->
@endsection

@section('scripts')
    {{-- <script src="{{ asset('assets/js/ddtf.js') }}"></script> --}}
    <script>

        // $('#myTable').ddTableFilter();

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
