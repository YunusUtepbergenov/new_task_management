@extends('layouts.main')

@section('styles')
    @livewireStyles
@endsection

@section('main')
    @include('partials._navigation_header')
    
    @livewire('ordered-table')

    @include('partials._edit_task')

    @livewire('view-modal')
@endsection

@section('scripts')
    @livewireScripts
    <script src="{{ asset('assets/js/ddtf.js') }}"></script>
    <script>
        $('#myTable').ddTableFilter();
        $("#name2").addClass("d-none");
        $("#deadline2").addClass("d-none");

        function editTask(id) {
            $.get("/task/info/byid/" + id, function (task) {
                const group_users = task.group_users;
                $('#helpers1').val(null).trigger('change');
                $("#kpi_type1").val(task.task.score_id).trigger('change');
                $("#id1").val(task.task.id);
                $("#name1").val(task.task.name);
                
                if(task.task.extended_deadline === null){
                    $("#deadline1").val(task.task.deadline);                    
                }else{
                    $("#deadline1").val(task.task.extended_deadline);
                }
                
                $('#user_id1').val(group_users).trigger('change');

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
                    document.location.href = '/ordered';
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
