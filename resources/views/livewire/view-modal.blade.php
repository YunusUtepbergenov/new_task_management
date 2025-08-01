<div>
    @if ($task)
        <div id="view_task" class="modal custom-modal fade" role="dialog" wire:ignore.self>
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header" id="left_header">
                        <h5 class="modal-title" id="task_title">{{ $task->name }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-8 col-xl-9">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title mb-0">Прикрепленный файлы</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            @forelse ($task->files as $file)
                                            <div class="col-md-3 col-sm-4 col-lg-4 col-xl-3">
                                                    <div class="uploaded-box">
                                                        <div class="files-cont">
                                                            <div class="file-type">
                                                                <span class="files-icon"><i class="fa fa-file-pdf-o"></i></span>
                                                            </div>
                                                            <div class="files-info">
                                                                <span class="file-name text-ellipsis"><a href="{{ route('file.download', $file->id)}}">{{ $file->name }}</a></span>
                                                                <span class="file-date">{{ $file->created_at->format('Y-m-d') }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <p style="margin-left: 15px">Файлов нет</p>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>

                                @if (!$task->response && $task->user_id == Auth::user()->id)
                                        <div class="card">
                                            <div class="card-header">
                                                <h4 class="card-title mb-0">Завершить задачу</h4>
                                            </div>
                                            <div class="card-body">
                                                <form wire:submit.prevent="storeResponse" method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="form-group row">
                                                        <label class="col-sm-1 col-form-label">Текст</label>
                                                        <div class="col-sm-11">
                                                            <textarea rows="3" cols="5" class="form-control" wire:model.lazy="description" name="description" placeholder="Введите текст"></textarea>
                                                        </div>
                                                        @error('description')
                                                            <div class="col-sm-12 m-t-10">
                                                                <div class="alert alert-danger" style="margin-bottom: 10px">{{ $message }}</div>
                                                            </div>
                                                        @enderror
                                                    </div>
                                                    <input type="hidden" name="task_id" value="{{ $task->id }}">
                                                    <div class="form-group row">
                                                        <label class="col-lg-1 col-form-label">Файл</label>
                                                        <div class="col-lg-11">
                                                            @error('upload')
                                                                <div class="alert alert-danger" style="margin-bottom: 10px">{{ $message }}</div>
                                                            @enderror
                                                            <input class="form-control" wire:model="upload" type="file">
                                                            <div wire:loading wire:target="upload">
                                                                <div class="loading">Loading&#8230;</div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="text-right">
                                                        <button type="submit" class="btn btn-primary">Завершить</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                @elseif ($task->response)
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title mb-0">Завершенная задача</h4>
                                        </div>
                                        <div class="card-body">
                                            <p>{{ $task->response->description }}</p>
                                            @if($task->response->filename)
                                                <ul class="files-list">
                                                    <li>
                                                        <div class="files-cont">
                                                            <div class="file-type">
                                                                <span class="files-icon"><i class="fa fa-file-pdf-o"></i></span>
                                                            </div>
                                                            <div class="files-info">
                                                                <span class="file-name text-ellipsis"><a href="{{ route('response.download', $task->response->filename) }}">{{ $task->response->filename }}</a></span>
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                            @endif

                                        </div>
                                    </div>
                                @endif
                                <div class="project-task">
                                    <ul class="nav nav-tabs nav-tabs-top nav-justified mb-0">
                                        <li class="nav-item"><a class="nav-link active" href="#comments" data-toggle="tab" aria-expanded="true">Комментарии</a></li>
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane show active" id="comments">
                                            <div class="task-wrapper">
                                                <div class="task-list-container">
                                                    <div class="task-list-body">
                                                        <div class="card">
                                                            <div class="card-body">
                                                                <div class="form-group">
                                                                    <form wire:submit.prevent="storeComment({{ $task->id }})" method="POST">
                                                                        @csrf
                                                                        <div class="form-group">
                                                                            <textarea class="form-control" wire:model.defer="comment" rows="2" name="comment" id="comment_textarea" placeholder="Введите комментарий" required></textarea>
                                                                        </div>
                                                                        <button class="btn btn-primary" wire:click="$refresh" style="float: right;">Отправить</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12" id="comment-section">
                                                                @foreach ($comments as $cmt)
                                                                    <div class="card withoutBorder">
                                                                        <div class="d-flex justify-content-between align-items-center">
                                                                            <div class="user d-flex flex-row align-items-center">
                                                                                <img src="{{ ($cmt->user->avatar) ? asset('user_image/'.$cmt->user->avatar) : asset('user_image/avatar.jpg') }}" width="30" class="user-img rounded-circle mr-2">
                                                                                <span>
                                                                                    <small class="font-weight-bold text-secondary" style="font-size: 14px">{{ $cmt->user->name }}</small>
                                                                                    <small class="font-weight-bold" style="font-size: 13px; margin-left: 8px;">{{ $cmt->comment }}</small>
                                                                                </span>
                                                                            </div>
                                                                            <small style="margin-right: 10px;">{{ $cmt->created_at }}</small>
                                                                        </div>
                                                                        @if ($cmt->user->id == auth()->user()->id)
                                                                            <div class="user d-flex flex-row align-items-center" style="margin-left: 50px">
                                                                                <div class="btn-toolbar" role="toolbar" aria-label="Toolbar with button groups">
                                                                                    <div class="btn-group" role="group" aria-label="Second group">
                                                                                        <form action="#" method="post">
                                                                                            <input type="hidden" name="_method" value="DELETE">
                                                                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                                                            <button type="button" wire:click.prevent="deleteComment({{ $cmt->id }})" class="btn btn-primary search_btn btn-sm" style="line-height: 1">Удалить</button>
                                                                                        </form>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-xl-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title m-b-15">Сведения о задаче</h6>
                                        <table class="table table-striped table-border">
                                            <tbody>
                                                <tr>
                                                    <td>Начало:</td>
                                                    <td class="text-right" id="task_created">{{ $task->created_at->format('Y-m-d') }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Срок:</td>
                                                    <td class="text-right" id="task_deadline">{{ $task->deadline }}</td>
                                                </tr>
                                                @isset($task->extended_deadline)
                                                    <tr>
                                                        <td>Продление:</td>
                                                        <td class="text-right" id="task_extended_deadline">{{ $task->extended_deadline }}</td>
                                                    </tr>
                                                @endisset
                                                <tr>
                                                    <td>Категория:</td>
                                                    <td class="text-right" id="task_type">{{ ($task->score) ? $task->score->name : '' }}</td>
                                                </tr>

                                                <tr>
                                                    <td>Постановщик:</td>
                                                    <td class="text-right"><a href="#" id="task_creator">{{ $task->username($task->creator_id) }}</a></td>
                                                </tr>

                                                <tr>
                                                    <td>Состояние:</td>
                                                    <td>
                                                        @if ($task->overdue)
                                                            <span class="badge bg-inverse-warning" style="float: right">Просроченный</span>
                                                        @else
                                                            <span class="badge bg-inverse-{{ ($task->status == "Не прочитано") ? 'success' : (($task->status == "Выполняется") ? 'primary' : (($task->status == "Ждет подтверждения") ? 'danger' : (($task->status == "Выполнено") ? 'purple' : 'warning') )) }}" style="float:right" >{{ $task->status }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                
                                                @if ($task->status == "Выполнено")
                                                    <tr>
                                                        <td>Балл:</td>
                                                        @isset($task->score)
                                                            <td class="text-right">{{$task->total}}/{{$task->score->max_score}}</td>                                                        
                                                        @endisset
                                                    </tr>                                                    
                                                @endif
                                                
                                                @if ($task->response)
                                                    <tr>
                                                        <td>Время выполнения:</td>
                                                        <td class="text-right" id="task_deadline">{{ $task->response->created_at->format('Y-m-d') }}</td>
                                                    </tr>
                                                @endif

                                                @can('evaluate', $task)
                                                    @if ($task->status == "Ждет подтверждения")
                                                        <tr>
                                                            <td>Действия: (Макс: {{ $task->score->max_score }})</td>
                                                            <td class="nowrap">
                                                                <div class="row">
                                                                    @isset($task->score)
                                                                        <div class="form-group">
                                                                            {{-- <input type="number" class="form-control" wire:model="taskScore" id="taskScore" placeholder="Макс: {{$task->score->max_score}}" onkeydown="return event.key !== ',' && event.key !== 'e' && event.key !== 'E'" oninput="this.value = this.value.replace(/[^0-9-]/g, '')"> --}}

                                                                            @if ($task->group_id)
                                                                                @foreach ($coTasks as $t)
                                                                                    <div class="form-group">
                                                                                        <label>{{ $t->user->name }}</label>
                                                                                        <input type="number"
                                                                                            class="form-control"
                                                                                            wire:model.defer="groupScores.{{ $t->id }}"
                                                                                            placeholder="Оценка"
                                                                                            min="{{ $t->score->min_score }}"
                                                                                            max="{{ $t->score->max_score }}"
                                                                                            onkeydown="return event.key !== ',' && event.key !== 'e' && event.key !== 'E'"
                                                                                            oninput="this.value = this.value.replace(/[^0-9-]/g, '')">
                                                                                    </div>
                                                                                @endforeach
                                                                            @else
                                                                                <div class="form-group">
                                                                                    <input type="number" class="form-control" wire:model="taskScore"
                                                                                        placeholder="Макс: {{$task->score->max_score}}"
                                                                                        onkeydown="return event.key !== ',' && event.key !== 'e' && event.key !== 'E'"
                                                                                        oninput="this.value = this.value.replace(/[^0-9-]/g, '')">
                                                                                </div>
                                                                            @endif

                                                                            @isset($errorMsg)
                                                                                <div class="invalid-feedback" style="display: block;">
                                                                                    {{ $errorMsg }}
                                                                                </div>
                                                                            @endisset
                                                                        </div>                                                                      
                                                                    @endisset
                                                                    <div class="btn-toolbar" role="toolbar" aria-label="Toolbar with button groups" style="flex-wrap:initial">
                                                                        <div class="btn-group mr-2" role="group" aria-label="First group">
                                                                            <button class="btn btn-primary btn-sm" wire:click="taskConfirmed({{ $task->id }})">Подтвердить</button>
                                                                        </div>
                                                                        <div class="btn-group mr-2" role="group" aria-label="Second group">
                                                                            <form action="#" method="post">
                                                                                <input type="hidden" name="_method" value="DELETE">
                                                                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                                                <button class="btn btn-secondary btn-sm" wire:click.prevent="taskRejected({{ $task->id }})">Отменить</button>
                                                                            </form>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endcan
                                                @if ($task->deadline >= date('Y-m-d') && $task->user_id == auth()->user()->id && $task->status == "Ждет подтверждения")
                                                    <tr>
                                                        <td>Действия:</td>
                                                        <td class="nowrap">
                                                            <div class="row">
                                                                <div class="btn-toolbar" role="toolbar" aria-label="Toolbar with button groups">
                                                                    <div class="btn-group mr-2" role="group" aria-label="Second group">
                                                                        <form action="#" method="post">
                                                                            <input type="hidden" name="_method" value="DELETE">
                                                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                                            <button class="btn btn-secondary btn-sm" wire:click.prevent="reSubmit({{ $task->id }})">Отменить</button>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card project-user">
                                    <div class="card-body">
                                        <h6 class="card-title m-b-20">Ответственный</h6>
                                        <ul class="list-box">
                                            @forelse ($coTasks as $task)
                                                <li>
                                                    <a href="#">
                                                        <div class="list-item">
                                                            <div class="list-left">
                                                                <span class="avatar"><img alt="" src="{{ ($task->user->avatar) ? asset('user_image/'.$task->user->avatar) : asset('user_image/avatar.jpg') }}"></span>
                                                            </div>
                                                            <div class="list-body">
                                                                <span class="message-author">{{ $task->username($task->user_id) }} ( {{ $task->total }}/{{ $task->score->max_score }} )</span>
                                                                <div class="clearfix"></div>
                                                                <span class="message-content">{{ $task->user->role->name }}</span>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>                                                
                                            @empty
                                                <li>
                                                    <a href="#">
                                                        <div class="list-item">
                                                            <div class="list-left">
                                                                <span class="avatar"><img alt="" src="{{ ($task->user->avatar) ? asset('user_image/'.$task->user->avatar) : asset('user_image/avatar.jpg') }}"></span>
                                                            </div>
                                                            <div class="list-body">
                                                                <span class="message-author">{{ $task->username($task->user_id) }}</span>
                                                                <div class="clearfix"></div>
                                                                <span class="message-content">{{ $task->user->role->name }}</span>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>                                                
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($profile)
        <div id="profile_modal" class="modal custom-modal fade" role="dialog" wire:ignore.self>
            <div class="modal-dialog modal-dialog-centered modal-lg profile_modal" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-3">
                                <div class="profile-img-wrap edit-img">
                                    <img class="inline-block" src="{{ ($profile->avatar) ? asset('user_image/'.$profile->avatar) : asset('user_image/avatar.jpg') }}" alt="user">
                                </div>
                            </div>
                            <div class="col-sm-9">
                                <div class="profile-info-left">
                                    <h3 class="user-name m-t-0 mb-3" style="text-align: center"><b>{{ $profile->name }}</b></h3>
                                    <h4>Сектор: <b>{{ $profile->sector->name }}</b></h6>
                                    <h4>Должность: <b>{{ $profile->role->name }}</b></h4>
                                    @if($profile->birth_date)
                                        <h4>Дата рождения: <b>{{ $profile->birth_date->format('d-m-Y') }}</b></h4>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if ($profile->id == Auth::user()->id)
                            <form wire:submit.prevent="changeUserInfo" method="POST">
                                @csrf
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title mb-0">Контакты</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group row">
                                            <div class="col-sm-1"></div>
                                                <label class="col-sm-4 col-form-label">Номер телефона:</label>
                                            <div class="col-sm-4">
                                                <input class="form-control" type="text" wire:model="phone">
                                            </div>
                                            @error('phone')
                                            <div class="col-sm-12 m-t-10">
                                                <div class="alert alert-danger" style="margin-bottom: 10px">{{ $message }}</div>
                                            </div>
                                        @enderror
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-sm-1"></div>
                                                <label class="col-sm-4 col-form-label">Внутренный номер:</label>
                                            <div class="col-sm-4">
                                                <input class="form-control" type="text" wire:model="internal">
                                            </div>
                                            @error('internal')
                                            <div class="col-sm-12 m-t-10">
                                                <div class="alert alert-danger" style="margin-bottom: 10px">{{ $message }}</div>
                                            </div>
                                        @enderror

                                        </div>
                                        <div class="form-group row">
                                            <div class="col-sm-1"></div>
                                            <label class="col-sm-4 col-form-label">
                                                Адрес электронной почты:
                                            </label>
                                            <div class="col-sm-4 col-form-label">
                                                <label>{{ $profile->email }}</label>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-2 offset-md-9">
                                                <button class="btn btn-primary">Изменить</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <form wire:submit.prevent="updatePassword" method="POST">
                                @csrf
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title mb-0">Безопасность</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group row">
                                            <div class="col-sm-1"></div>
                                            <label class="col-lg-4 col-form-label">Прежний пароль</label>
                                            <div class="col-lg-4">
                                                @error('oldPassword')
                                                    <div class="alert alert-danger" style="margin-bottom: 10px">{{ $message }}</div>
                                                @enderror
                                                <input type="password" class="form-control" wire:model="oldPassword" value="{{ old('old_password') }}" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-sm-1"></div>
                                            <label class="col-lg-4 col-form-label">Новый пароль</label>
                                            <div class="col-lg-4">
                                                @error('newPassword')
                                                    <div class="alert alert-danger" style="margin-bottom: 10px">{{ $message }}</div>
                                                @enderror
                                                <input type="password" class="form-control" wire:model="newPassword" value="{{ old('new_password') }}" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-sm-1"></div>
                                            <label class="col-lg-4 col-form-label">Подтвердите пароль</label>
                                            <div class="col-lg-4">
                                                @error('confirmPassword')
                                                    <div class="alert alert-danger" style="margin-bottom: 10px">{{ $message }}</div>
                                                @enderror
                                                <input type="password" class="form-control" wire:model="confirmPassword" value="{{ old('confirm_password') }}" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-2 offset-md-9">
                                                <button class="btn btn-primary">Изменить</button>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </form>

                        @else
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">Контакты</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <h4 class="col-sm-5">
                                            Номер телефона:
                                        </h4>
                                        <div class="col-sm-7">
                                            <h4 class="user-name m-t-0 mb-3">{{ $profile->phone }}</h4>
                                        </div>
                                        <h4 class="col-sm-5">
                                            Внутренный номер:
                                        </h4>
                                        <div class="col-sm-7">
                                            <h4 class="user-name m-t-0 mb-3">{{ $profile->internal }}</h4>
                                        </div>
                                        <h4 class="col-sm-5">
                                            Адрес электронной почты:
                                        </h4>
                                        <div class="col-sm-7">
                                            <h4 class="user-name m-t-0 mb-3">{{ $profile->email }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
