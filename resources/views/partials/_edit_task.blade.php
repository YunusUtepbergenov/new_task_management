<!-- Edit Task Modal -->
<div id="edit_task" class="modal custom-modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Новая задания</h5>
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
                        <label class="col-sm-3 col-form-label">Проект</label>
                        <div class="col-sm-4">
                            <select class="form-control" id="project_id1" name="project_id">
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
                                <input type="hidden" name="id" id="id1">
                                <input class="form-control" id="name1" name="name" type="text">
                            </div>
                            <div class="alert alert-danger" id="name2"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Поручение / Комментария</label>
                                <textarea rows="4" class="form-control" name="description" id="description1" placeholder="Поручение / Комментария"></textarea>
                            </div>
                            <div class="alert alert-danger" id="description2"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-1"></div>
                        <div class="col-sm-10">
                            <div class="form-group file-upload">
                                <label for="file-input1"><img src="assets/img/attachment.png"> ( Макс: 5 MB )</label>
                                <input id="file-input1" type="file" name="file[]" multiple onchange="javascript:updateList1()">
                                <div id="fileList1"></div>
                            </div>
                            {{-- <div class="alert alert-danger" id="file"></div> --}}
                        </div>
                    </div>

                    @if (Auth::user()->isDirector())
                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <label class="col-sm-3 col-form-label">Ответственный</label>
                            <div class="col-sm-4">
                                <select class="form-control" name="user_id" id="user_id1">
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

                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <label class="col-sm-3 col-form-label">Соисполнитель</label>
                            <div class="col-sm-4">
                                <select class="form-control select" name="helpers[]" id="helpers1" multiple>
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

                        @elseif(Auth::user()->isDeputy())
                        @if(Auth::user()->id == 7)
                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <label class="col-sm-3 col-form-label">Ответственный</label>
                                <div class="col-sm-4">
                                <select class="form-control" name="user_id">
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
                        @elseif (Auth::user()->id == 3)
                            <div class="form-group row">
                                <div class="col-sm-1"></div>
                                <label class="col-sm-3 col-form-label">Ответственный</label>
                                <div class="col-sm-4">
                                    <select class="form-control" name="user_id">
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

                        <div class="form-group row">
                            <div class="col-sm-1"></div>
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

                    @elseif(Auth::user()->isMailer())
                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <label class="col-sm-3 col-form-label">Ответственный</label>
                            <div class="col-sm-4">
                                <select class="form-control" name="user_id" id="user_id1">
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

                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <label class="col-sm-3 col-form-label">Соисполнитель</label>
                            <div class="col-sm-4">

                                <select class="form-control select" name="helpers[]" id="helpers1" multiple>
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

                    @elseif(Auth::user()->isHead())
                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <label class="col-sm-3 col-form-label">Ответственный</label>
                            <div class="col-sm-4">
                                <select class="form-control" name="user_id" id="user_id1">
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

                        <div class="form-group row">
                            <div class="col-sm-1"></div>
                            <label class="col-sm-3 col-form-label">Соисполнитель</label>
                            <div class="col-sm-4">
                                <select class="form-control select" name="helpers[]" id="helpers1" multiple>
                                    @foreach ($sectors as $sector)
                                    <optgroup label="{{ $sector->name }}">
                                        @foreach ($sector->users as $user)
                                            @if($user->id != Auth::user()->id && $user->id != 1)
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
                        <label class="col-sm-3 col-form-label">Крайний срок</label>
                        <div class="col-sm-4">
                            <div class="form-group cal-icon">
                                <input class="form-control datetimepicker" name="deadline" id="deadline1" type="text">
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="alert alert-danger" id="deadline2"></div>
                        </div>
                    </div>
                    {{-- <div class="form-group row">
                        <div class="col-sm-1"></div>
                        <div class="col-sm-3 form-check">
                            <input class="form-check-input" type="checkbox" name="repeat_check" id="flexCheckDefault3" checked="false">
                            <label class="form-check-label" style="margin-top: 3px" for="flexCheckDefault3">
                                Повторяющаяся задача
                            </label>
                        </div>
                        <div class="col-sm-8" id="repeat_div_cont" style="display: none">
                            <div class="row">
                                <div class="col-sm-6" id="repeat_container1" style="display: none">
                                    <select class="form-control" name="repeat" id="repeat1">
                                        <option value="weekly" selected>Еженедельная</option>
                                        <option value="monthly">Ежемесячная</option>
                                    </select>
                                </div>
                                <div class="col-sm-6" id="days_container1" style="display:none">
                                    <select class="form-control select" id="dayss" name="days[]" multiple>
                                        <option value="1">Понедельник</option>
                                        <option value="2">Вторник</option>
                                        <option value="3">Среда</option>
                                        <option value="4">Четверг</option>
                                        <option value="5">Пятница</option>
                                        <option value="6">Суббота</option>
                                        <option value="7">Воскресенье</option>
                                    </select>
                                </div>
                                <div class="col-sm-3" id="month_container1" style="display:none">
                                    <select class="form-control" id="month_day" name="month_day">
                                        @for ($a = 1; $a <= 28; $a++)
                                            <option value="{{ $a }}">{{ $a }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div> --}}
                    <div class="submit-section">
                        <button class="btn btn-primary submit-btn">Поставить Задачу</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- /Edit Task Modal -->
