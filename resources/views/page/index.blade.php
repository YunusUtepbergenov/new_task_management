@extends('layouts.main')

@section('main')
    <!-- Page Header -->
    <div class="page-header">
        <div class="row">
            <div class="col-sm-12">
                <ul class="nav nav-tabs nav-tabs-bottom">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#tab_additions">Мои задачи</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab_overtime">Поручил</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab_deductions">Помогаю</a>
                    </li>
                </ul>
            </div>


            <div class="col-auto float-right ml-auto" style="margin-top: 10px;">
                <a href="#" class="btn add-btn" data-toggle="modal" data-target="#create_project"> Добавить Проект</a>
                <a href="#" class="btn add-btn" data-toggle="modal" data-target="#create_task"> Добавить Задачу</a>
            </div>
        </div>
    </div>
    <!-- /Page Header -->

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-nowrap mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Название</th>
                                    <th>Дата Создание</th>
                                    <th>Крайний срок</th>
                                    <th>Постановщик</th>
                                    <th>Ответственный</th>
                                    <th>Состаяние</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tasks as $key=>$task)
                                    <tr>
                                        <td>{{ $key+1 }}</td>
                                        <td>
                                            <a href="#" data-toggle="modal" data-target="#view_task">{{ $task->name }}</a>
                                        </td>
                                        <td>{{ $task->created_at->format('Y-m-d') }}</td>
                                        <td><span class="badge bg-inverse-warning">{{ $task->deadline }}</span></td>
                                        <td>{{ $task->username($task->creator_id) }}</td>
                                        <td>{{ $task->username($task->user_id) }}</td>
                                        <td><span class="badge bg-inverse-success">Новое</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                                @foreach ($projects as $project)
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>{{ $project->name }}</th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    @foreach ($project->tasks as $key=>$task)
                                        <tbody>
                                            <tr>
                                                <td>{{ $key+1 }}</td>
                                                <td><a href="#" data-toggle="modal" data-target="#view_task">{{ $task->name }}</a></td>
                                                <td>{{ $task->created_at->format('Y-m-d') }}</td>
                                                <td><span class="badge bg-inverse-warning">{{ $task->deadline }}</span></td>
                                                <td>{{ $task->username($task->creator_id) }}</td>
                                                <td>{{ $task->username($task->user_id) }}</td>
                                                <td><span class="badge bg-inverse-success">Новое</span></td>
                                            </tr>
                                        </tbody>
                                    @endforeach
                                @endforeach
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Project Modal -->
    <div id="create_project" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Новый Проект</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('project.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label>Название</label>
                                    <input class="form-control" type="text" name="name">
                                </div>
                            </div>
                        </div>
                        <div class="submit-section">
                            <button class="btn btn-primary submit-btn">Создать Проект</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /Create Project Modal -->

    <!-- Create Task Modal -->
    <div id="create_task" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Новая задания</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('task.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label>Введите Название Задачи</label>
                                    <input class="form-control" name="name" type="text">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label>Поручение / Комментария</label>
                                    <textarea rows="4" class="form-control" name="description" placeholder="Поручение / Комментария"></textarea>
                                </div>
                            </div>
                        </div>

                        {{-- <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group file-upload">
                                    <label for="file-input"><img src="assets/img/attachment.png"></label>
                                    <input id="file-input" type="file">
                                </div>
                            </div>
                        </div> --}}

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Ответственный</label>
                            <div class="col-sm-4">
                                <select class="form-control" name="user_id">
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Постановщик</label>
                            <div class="col-sm-4">
                                <select class="form-control" name="creator_id" id="">
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Соисполнитель</label>
                            <div class="col-sm-4">
                                <select class="form-control" id="example-getting-started" multiple="multiple">
                                    <option value="cheese">Someone 1</option>
                                    <option value="tomatoes">Someone 2</option>
                                    <option value="mozarella">Someone 3</option>
                                    <option value="mushrooms">Someone 4</option>
                                    <option value="pepperoni">Someone 5</option>
                                    <option value="onions">Someone 6</option>
                                </select>
                            </div>
                        </div> --}}

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Крайний срок</label>
                            <div class="col-sm-4">
                                <div class="cal-icon">
                                    <input class="form-control datetimepicker" name="deadline" type="text">
                                </div>
                        </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Проект</label>
                            <div class="col-sm-4">
                                <select class="form-control" name="project_id">
                                    <option value="">Не проект</option>
                                    @foreach ($projects as $project)
                                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="submit-section">
                            <button class="btn btn-primary submit-btn">Поставить Задачу</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- /Create Task Modal -->

    <!-- View Project Modal -->
    @include('partials._view_modal')
    <!-- /View Project Modal -->
@endsection
