@extends('layouts.main')

@section('styles')
    @livewireStyles
@endsection
@section('main')
    <!-- Page Header -->
    <div class="page-header">
        <div class="row">
            <div class="col-sm-12">
                <ul class="nav nav-tabs nav-tabs-bottom">
                    <li class="nav-item">
                        <a class="nav-link {{ (Route::current()->uri == '/') ? 'active' : '' }}" href="{{ route('home') }}">Мои задачи</a>
                    </li>
                    @if(Auth::user()->isDirector() || Auth::user()->isMailer() || Auth::user()->isHead() || Auth::user()->isDeputy())
                        <li class="nav-item">
                            <a class="nav-link {{ (Route::current()->uri == 'ordered') ? 'active' : '' }}" href="{{ route('ordered') }}">Поручил</a>
                        </li>
                    @endif

                </ul>
            </div>
        </div>
    </div>
    <!-- /Page Header -->
    @livewire('ordered-table', ['projects' => $projects])
    {{-- @if(Auth::user()->isDirector() || Auth::user()->isMailer() || Auth::user()->isHead() || Auth::user()->isDeputy())
        @include('partials._project_modal')
    @endif --}}

    @include('partials._task_modal')
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
        $("#description2").addClass("d-none");

        function editTask(id) {
            $.get("/task/info/byid/" + id, function (task) {
                $('#helpers1').val(null).trigger('change');
                $("#project_id1").val(task.task.project_id);
                $("#kpi_type").val(task.task.score_id);
                $("#id1").val(task.task.id);
                $("#name1").val(task.task.name);
                console.log(task.task.deadline);
                console.log(task.task.extended_deadline);
                
                if(task.task.extended_deadline === null){
                    $("#deadline1").val(task.task.deadline);                    
                }else{
                    $("#deadline1").val(task.task.extended_deadline);
                }
                
                $("#user_id1").val(task.task.user_id);
                $("#creator_id1").val(task.task.creator_id);
                if(task.task.executers.length > 0){
                    for(var i=0; i < task.task.executers.length; i++){
                        for(var c=0; c < helpers.length; c++){
                            if (task.task.executers[i].id == helpers[c].value) {
                                helpers[c].setAttribute('selected', 'selected');
                            }
                        }
                    }
                }

                $("#description1").val(task.task.description);
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
    @stack('scripts')
@endsection
