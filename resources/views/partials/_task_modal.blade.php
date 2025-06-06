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
                <form action="{{ route('task.store') }}" method="POST" enctype="multipart/form-data" id="createTask">
                    @csrf

                    <x-form.select-group label="Категория" name="score_id" :multiple='false'>
                        @foreach ($scoresGrouped as $group => $items)
                            <optgroup label="{{ $group }}">
                                @foreach ($items as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }} (Макс: {{ $type->max_score }})</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </x-form.select-group>

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Введите Название Задачи</label>
                                <input class="form-control" name="name" type="text">
                            </div>
                            <div class="alert alert-danger" id="name"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-1"></div>
                        <div class="col-sm-10">
                            <div class="form-group file-upload">
                                <label for="file-input"><img src="assets/img/attachment.png"> ( Макс: 5 MB )</label>
                                <input id="file-input" type="file" name="file[]" multiple onchange="javascript:updateList()">
                                <div id="fileList"></div>
                            </div>
                            <div class="alert alert-danger" id="file"></div>
                        </div>
                    </div>

                    @if (Auth::user()->isDirector() || Auth::user()->isMailer())
                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <label class="col-sm-3 col-form-label">Ответственный</label>
                            <div class="col-sm-4">
                                <select class="form-control select2" name="users[]" multiple>
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
                            <div class="col-sm-1"></div>
                            <label class="col-sm-3 col-form-label">Постановщик</label>
                            <div class="col-sm-4">
                                <select class="form-control" name="creator_id" id="task_creator9">
                                        <option value="{{ Auth::user()->id }}">{{ Auth::user()->name }}</option>
                                </select>
                            </div>
                        </div>

                    @elseif(Auth::user()->isDeputy())
                        @if(Auth::user()->id == 2)
                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <label class="col-sm-3 col-form-label">Ответственный</label>
                                <div class="col-sm-4">
                                    <select class="form-control select2" name="users[]" multiple>
                                        @foreach ($sectors as $sector)
                                            <optgroup label="{{ $sector->name }}">
                                                @foreach ($sector->users as $user)
                                                    @if (!$user->isDirector() && $user->id != 3)
                                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                    @endif
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @elseif (Auth::user()->id == 3)
                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <label class="col-sm-3 col-form-label">Ответственный</label>
                                <div class="col-sm-4">
                                    <select class="form-control select2" name="users[]" multiple>
                                        @foreach ($sectors as $sector)
                                            <optgroup label="{{ $sector->name }}">
                                                @foreach ($sector->users as $user)
                                                    @if (!$user->isDirector() && $user->id != 2)
                                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                    @endif
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                    @endif

                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <label class="col-sm-3 col-form-label">Постановщик</label>
                            <div class="col-sm-4">
                                <select class="form-control" name="creator_id" id="">
                                        <option value="{{ Auth::user()->id }}">{{ Auth::user()->name }}</option>
                                </select>
                            </div>
                        </div>

                    @elseif(Auth::user()->isHead())
                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <label class="col-sm-3 col-form-label">Ответственный</label>
                            <div class="col-sm-4">
                                <select class="form-control select2" name="users[]" multiple>
                                    @foreach (Auth::user()->sector->users->where('leave', 0) as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <label class="col-sm-3 col-form-label">Постановщик</label>
                            <div class="col-sm-4">
                                <select class="form-control" name="creator_id" id="">
                                    <option value="{{ Auth::user()->id }}">{{ Auth::user()->name }}</option>
                                </select>
                            </div>
                        </div>

                    @elseif (Auth::user()->isResearcher())
                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <label class="col-sm-3 col-form-label">Ответственный</label>
                            <div class="col-sm-4">
                                <select class="form-control select2" name="users[]" multiple>
                                    <option value="{{ Auth::user()->id }}">{{ Auth::user()->name }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <label class="col-sm-3 col-form-label">Постановщик</label>
                            <div class="col-sm-4">
                                <select class="form-control" name="creator_id">
                                    <option value="{{ Auth::user()->sector->head()->id }}">{{ Auth::user()->sector->head()->name }}</option>
                                </select>
                            </div>
                        </div>

                    @endif
                    <div class="form-group row">
                        <div class="col-sm-1"></div>
                        <label class="col-sm-3 col-form-label">Крайний срок</label>
                        <div class="col-sm-4">
                            <div class="form-group cal-icon">
                                <input class="form-control datetimepicker" name="deadline" type="text">
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="alert alert-danger" id="deadline"></div>
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
