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
                <form action="{{ route('task.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Проект</label>
                        <div class="col-sm-4">
                            {{-- <input class="form-control" type="text" name="project_name" list="productName"/>
                            <datalist id="productName">
                                <option value="">Не проект</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->name }}">{{ $project->name }}</option>
                                @endforeach
                            </datalist> --}}
                            <select class="form-control" id="project_text" name="project_id">
                                <option value="">Не проект</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

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

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group file-upload">
                                <label for="file-input"><img src="assets/img/attachment.png"></label>
                                <input id="file-input" type="file" name="file[]" multiple onchange="javascript:updateList()">
                                <div id="fileList"></div>
                            </div>
                        </div>
                    </div>

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

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Соисполнитель</label>
                        <div class="col-sm-4">

                            <select class="form-control select" name="helpers[]" multiple>
                                @foreach ($sectors as $sector)
                                <optgroup label="{{ $sector->name }}">
                                    @foreach ($sector->users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Крайний срок</label>
                        <div class="col-sm-4">
                            <div class="cal-icon">
                                <input class="form-control datetimepicker" name="deadline" type="text">
                            </div>
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
