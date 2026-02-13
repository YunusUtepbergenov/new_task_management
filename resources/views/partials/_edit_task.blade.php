<!-- Edit Task Modal -->
<div id="edit_task" class="modal custom-modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('task.update') }}" method="POST" enctype="multipart/form-data" id="editTask">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">

                    <div class="form-group row">
                        <div class="col-sm-1"></div>
                        <label class="col-sm-3 col-form-label">Категория</label>
                        <div class="col-sm-4">
                            <select class="form-control select2" id="kpi_type1" name="score_id">
                                @foreach ($scoresGrouped as $group => $items)
                                <optgroup label="{{ $group }}">
                                    @foreach ($items as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }} (Макс: {{ $type->max_score }})</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Введите Название Задачи</label>
                                <input type="hidden" name="id" id="id1">
                                <input class="form-control" id="name1" name="name" type="text">
                            </div>
                            <div class="alert alert-danger" id="name2"></div>
                        </div>
                    </div>

                    @if (Auth::user()->isDirector())
                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <label class="col-sm-3 col-form-label">Ответственный</label>
                            <div class="col-sm-4">
                                <select class="form-control select2" name="user_ids[]" id="user_id1" multiple>
                                    @foreach ($sectors as $sector)
                                        <optgroup label="{{ $sector->name }}">
                                            @foreach ($sector->users as $user)
                                                @if($user->id != Auth::user()->id)
                                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                @endif
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
                                <select class="form-control" name="creator_id" id="creator_id1">
                                        <option value="{{ Auth::user()->id }}">{{ Auth::user()->name }}</option>
                                </select>
                            </div>
                        </div>

                        @elseif(Auth::user()->isDeputy())
                            @if(Auth::user()->id == 7)
                                <div class="form-group row">
                                    <div class="col-sm-1"></div>
                                    <label class="col-sm-3 col-form-label">Ответственный</label>
                                    <div class="col-sm-4">
                                    <select class="form-control  select2" name="user_ids[]" id="user_id1" multiple>
                                            @foreach ($sectors as $sector)
                                                <optgroup label="{{ $sector->name }}">
                                                    @foreach ($sector->users as $user)
                                                        @if(!$user->isDirector() && (! $user->isDeputy() || $user->id == Auth::id()))
                                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                        @endif
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @elseif (Auth::user()->id == 2)
                                <div class="form-group row">
                                    <div class="col-sm-1"></div>
                                    <label class="col-sm-3 col-form-label">Ответственный</label>
                                    <div class="col-sm-4">
                                        <select class="form-control select2" name="user_ids[]" id="user_id1" multiple>
                                                @foreach ($sectors as $sector)
                                                    <optgroup label="{{ $sector->name }}">
                                                        @foreach ($sector->users as $user)
                                                            @if(!$user->isDirector() && (! $user->isDeputy() || $user->id == Auth::id()))
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
                                <select class="form-control" name="creator_id" id="creator_id1">
                                    @foreach ($sectors as $sector)
                                        @foreach ($sector->users->whereIn('role_id', [2, 14, 19]) as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    @elseif(Auth::user()->isMailer())
                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <label class="col-sm-3 col-form-label">Ответственный</label>
                            <div class="col-sm-4">
                                <select class="form-control select2" name="user_ids[]" id="user_id1" multiple>
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
                                <select class="form-control" name="creator_id" id="creator_id1">
                                        <option value="{{ Auth::user()->id }}">{{ Auth::user()->name }}</option>
                                </select>
                            </div>
                        </div>

                    @elseif(Auth::user()->isHead())
                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <label class="col-sm-3 col-form-label">Ответственный</label>
                            <div class="col-sm-4">
                                <select class="form-control select2" name="user_ids[]" id="user_id1" multiple>
                                    @foreach (Auth::user()->sector->users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <label class="col-sm-3 col-form-label">Постановщик</label>
                            <div class="col-sm-4">
                                <select class="form-control" name="creator_id" id="creator_id1">
                                        <option value="{{ Auth::user()->id }}">{{ Auth::user()->name }}</option>
                                </select>
                            </div>
                        </div>

                    @endif
                    <div class="form-group row">
                        <div class="col-sm-1"></div>
                        <label class="col-sm-3 col-form-label">Срок</label>
                        <div class="col-sm-4">
                            <div class="form-group cal-icon">
                                <input class="form-control datetimepicker" name="deadline" id="deadline1" type="text">
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="alert alert-danger" id="deadline2"></div>
                        </div>
                    </div>
                    <div class="submit-section">
                        <button class="btn btn-primary submit-btn">Изменить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- /Edit Task Modal -->
