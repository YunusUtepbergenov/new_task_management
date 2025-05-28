@extends('layouts.main')

@section('styles')
    @livewireStyles
@endsection

@section('main')
	<div class="content container-fluid">
        <!-- Page Content -->
		<div class="content container-fluid">

        @livewire('reports.weekly-tasks-overview')

        @include('partials._edit_task')

@endsection

@section('scripts')
    @livewireScripts
    <script>
        $("#name2").addClass("d-none");
        $("#deadline2").addClass("d-none");

        function editTask(id) {
            $("#name2").addClass("d-none");
            $("#description2").addClass("d-none");
            
            $.get("/task/info/byid/" + id, function (task) {
                console.log(task.task.score_id);
                $("#kpi_type1").val(task.task.score_id).trigger('change');
                $("#id1").val(task.task.id);
                $("#name1").val(task.task.name);
                
                if(task.task.extended_deadline === null){
                    $("#deadline1").val(task.task.deadline);                    
                }else{
                    $("#deadline1").val(task.task.extended_deadline);
                }
                
                $("#user_id1").val(task.task.user_id);
                $("#creator_id1").val(task.task.creator_id);
            });
        }
        updateList1 = function() {
            var input = document.getElementById('file-input1');
            var output = document.getElementById('fileList1');
            var children = "";
            for (var i = 0; i < input.files.length; ++i) {
                children += '<li>' + input.files.item(i).name + '</li>';
                console.log(input.files.item(i).size / 1024 );
            }
            output.innerHTML = '<ul>'+children+'</ul>';
        }
        jQuery("#editTask").on("submit", function (e) {
            e.preventDefault();
            var formData1 = new FormData($("#editTask")[0]);
            var url = document.getElementById('editTask').getAttribute("action");
            
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
                    document.location.href = '/weekly-tasks';
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
        });
    </script>
@endsection