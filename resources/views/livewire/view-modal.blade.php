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
                                    <div class="card-body">
                                        <p id="task_description">{!! nl2br(e($task->description)) !!}</p>
                                    </div>
                                </div>
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
                                                                {{-- <div class="file-size">{{ round(Storage::size(storage_path('/app/files/'.$file->name)) / 1024, 1)  }} KB</div> --}}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <p style="margin-left: 15px">No files</p>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>

                                @if (!$task->response && $task->user_id == Auth::user()->id)
                                    @if ($task->status != "Просроченный")
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
                                    @else
                                        <div class="card">
                                            <div class="card-body">
                                                <h4>Срок истек</h4>
                                            </div>
                                        </div>
                                    @endif
                                @elseif ($task->response)
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title m-b-20">Завершенная задача</h5>
                                            <p>{{ $task->response->description }}</p>
                                            <ul class="files-list">
                                                <li>
                                                    <div class="files-cont">
                                                        <div class="file-type">
                                                            <span class="files-icon"><i class="fa fa-file-pdf-o"></i></span>
                                                        </div>
                                                        @if($task->response->filename)
                                                        <div class="files-info">
                                                            <span class="file-name text-ellipsis"><a href="{{ route('response.download', $task->response->filename) }}">{{ $task->response->filename }}</a></span>
                                                            {{-- <div class="file-size">{{ round(Storage::size('/files/responses/'.$task->response->filename) / 1024, 1)  }} KB</div> --}}
                                                        </div>
                                                        @endif
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                @endif
                                <div class="project-task">
                                    <ul class="nav nav-tabs nav-tabs-top nav-justified mb-0">
                                        <li class="nav-item"><a class="nav-link active" href="#comments" data-toggle="tab" aria-expanded="true">Комментарии</a></li>
                                        {{-- <li class="nav-item"><a class="nav-link" href="#history" data-toggle="tab" aria-expanded="false">История</a></li> --}}
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
                                                                                <img src="{{ asset('assets/img/avatar.jpg') }}" width="30" class="user-img rounded-circle mr-2">
                                                                                <span>
                                                                                    <small class="font-weight-bold text-secondary" style="font-size: 14px">{{ $cmt->user->name }}</small>
                                                                                    <small class="font-weight-bold" style="font-size: 13px; margin-left: 8px;">{{ $cmt->comment }}</small>
                                                                                </span>
                                                                            </div>
                                                                            <small style="margin-right: 10px;">{{ $cmt->created_at }}</small>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- <div class="tab-pane" id="history">
                                            <p>Task was given</p>
                                        </div> --}}
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
                                                    <td>Крайний срок:</td>
                                                    <td class="text-right" id="task_deadline">{{ $task->deadline }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Постановщик:</td>
                                                    <td class="text-right"><a href="profile.html" id="task_creator">{{ $task->username($task->creator_id) }}</a></td>
                                                </tr>
                                                <tr>
                                                    <td>Состояние:</td>
                                                    <td class="text-right" id="task_status"><span class="badge bg-inverse-{{ ($task->status == "Новое") ? 'success' : (($task->status == "Выполняется") ? 'primary' : (($task->status == "Ждет подтверждения") ? 'danger' : (($task->status == "Выполнено") ? 'purple' : 'primary') )) }}">{{ $task->status }}</span></td>
                                                </tr>

                                                @can('creator', $task)
                                                    @if ($task->status == "Ждет подтверждения")
                                                        <tr>
                                                            <td>Действия:</td>
                                                            <td class="nowrap">
                                                                <div class="row">
                                                                    <div class="btn-toolbar" role="toolbar" aria-label="Toolbar with button groups">
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
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endcan

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card project-user">
                                    <div class="card-body">
                                        <h6 class="card-title m-b-20">Ответственный</h6>
                                        <ul class="list-box">
                                            <li>
                                                <a href="profile.html">
                                                    <div class="list-item">
                                                        <div class="list-left">
                                                            <span class="avatar"><img alt="" src="assets/img/avatar.jpg"></span>
                                                        </div>
                                                        <div class="list-body">
                                                            <span class="message-author">{{ $task->username($task->user_id) }}</span>
                                                            <div class="clearfix"></div>
                                                            <span class="message-content">{{ $task->user->role->name }}</span>
                                                        </div>
                                                    </div>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-title m-b-20">
                                            Соисполнители
                                        </h6>
                                        <ul class="list-box">
                                            @forelse ($task->executers as $user)
                                                <li>
                                                    <a href="profile.html">
                                                        <div class="list-item">
                                                            <div class="list-left">
                                                                <span class="avatar"><img alt="" src="assets/img/avatar.jpg"></span>
                                                            </div>
                                                            <div class="list-body">
                                                                <span class="message-author">{{ $user->name }}</span>
                                                                <div class="clearfix"></div>
                                                                <span class="message-content">{{ $user->role->name }}</span>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                            @empty
                                                <li>Нет Соисполнители</li>
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
</div>
