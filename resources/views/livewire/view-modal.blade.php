<div>
    @if ($task)
        <div id="view_task" class="modal custom-modal fade" role="dialog" wire:ignore.self>
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document" style="max-width: 820px;">
                <div class="modal-content">

                    {{-- Header --}}
                    <div class="vm-header">
                        <div class="vm-header-left">
                            <span class="vm-header-icon"><i class="fa fa-file-text-o"></i></span>
                            <h5 class="vm-header-title">{{ $task->name }}</h5>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body" style="padding: 0;">

                        {{-- Meta Row 1: Dates --}}
                        <div class="vm-meta-row">
                            <div class="vm-meta-field">
                                <span class="vm-meta-label">НАЧАЛО</span>
                                <span class="vm-meta-value">{{ $task->created_at->format('d.m.Y') }}</span>
                            </div>
                            <div class="vm-meta-field">
                                <span class="vm-meta-label">СРОК</span>
                                <span class="vm-meta-value">{{ \Carbon\Carbon::parse($task->deadline)->format('d.m.Y') }}</span>
                            </div>
                            <div class="vm-meta-field">
                                <span class="vm-meta-label">ВРЕМЯ ВЫПОЛНЕНИЯ</span>
                                <span class="vm-meta-value">
                                    @if ($task->response)
                                        {{ $task->response->created_at->format('d.m.Y') }}
                                    @else
                                        —
                                    @endif
                                </span>
                            </div>
                            @isset($task->extended_deadline)
                                <div class="vm-meta-field">
                                    <span class="vm-meta-label">ПРОДЛЕНИЕ</span>
                                    <span class="vm-meta-value">{{ \Carbon\Carbon::parse($task->extended_deadline)->format('d.m.Y') }}</span>
                                </div>
                            @endisset
                        </div>

                        {{-- Meta Row 2: Creator / Status / Category --}}
                        <div class="vm-meta-row vm-meta-row--bordered">
                            <div class="vm-meta-field">
                                <span class="vm-meta-label">ПОСТАНОВЩИК</span>
                                <span class="vm-meta-value">{{ $task->creator->short_name }}</span>
                            </div>
                            <div class="vm-meta-field">
                                <span class="vm-meta-label">СОСТОЯНИЕ</span>
                                <span class="vm-meta-value">
                                    @if ($task->overdue)
                                        <span class="badge bg-inverse-warning">Просроченный</span>
                                    @else
                                        <span class="badge bg-inverse-{{ ($task->status == 'Не прочитано') ? 'success' : (($task->status == 'Выполняется') ? 'primary' : (($task->status == 'Ждет подтверждения') ? 'danger' : (($task->status == 'Выполнено') ? 'purple' : 'warning'))) }}">{{ $task->status }}</span>
                                    @endif
                                </span>
                            </div>
                            <div class="vm-meta-field">
                                <span class="vm-meta-label">КАТЕГОРИЯ</span>
                                <span class="vm-meta-value">{{ ($task->score) ? $task->score->name : '—' }}</span>
                            </div>
                            @if ($task->status == 'Выполнено' && isset($task->score))
                                <div class="vm-meta-field">
                                    <span class="vm-meta-label">БАЛЛ</span>
                                    <span class="vm-meta-value">{{ $task->total }}/{{ $task->score->max_score }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Task Files --}}
                        @if ($task->files->count())
                            <div class="vm-section">
                                <div class="vm-section-header">
                                    <i class="fa fa-paperclip"></i>
                                    <span class="vm-section-title">Прикрепленные файлы</span>
                                </div>
                                <div class="d-flex flex-wrap" style="gap: 8px;">
                                    @foreach ($task->files as $file)
                                        <a href="{{ route('file.download', $file->id) }}" class="vm-file-chip">
                                            <i class="fa fa-file-o"></i> {{ $file->name }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Response Form (no response yet & current user is assignee) --}}
                        @if (!$task->response && $task->user_id == Auth::user()->id)
                            <div class="vm-section">
                                <div class="vm-section-header">
                                    <i class="fa fa-pencil-square-o"></i>
                                    <span class="vm-section-title">Завершить задачу</span>
                                </div>
                                <div class="vm-response-row">
                                    <div class="vm-response-col">
                                        <textarea class="form-control vm-response-input" wire:model.live.blur="description" placeholder="Опишите выполненную работу..."></textarea>
                                        @error('description')
                                            <div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="vm-response-col"
                                         x-data="{ dragging: false }"
                                         x-on:dragover.prevent="dragging = true"
                                         x-on:dragleave.prevent="dragging = false"
                                         x-on:drop.prevent="dragging = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change'));">
                                        <div class="vm-dropzone" :class="{ 'vm-dropzone--active': dragging }" x-on:click="$refs.fileInput.click()">
                                            <input type="file" wire:model="upload" class="vm-dropzone-input" x-ref="fileInput">
                                            <div class="vm-dropzone-content" wire:loading.remove wire:target="upload">
                                                @if ($upload)
                                                    <div class="vm-uploaded-file">
                                                        <i class="fa fa-check-circle" style="color: #22c55e; font-size: 18px; flex-shrink: 0;"></i>
                                                        <span>{{ Str::limit($upload->getClientOriginalName(), 15) }}</span>
                                                        <button type="button" class="vm-upload-remove" wire:click.stop="$set('upload', null)" title="Удалить файл">
                                                            <i class="fa fa-times"></i>
                                                        </button>
                                                    </div>
                                                @else
                                                    <i class="fa fa-cloud-upload vm-dropzone-icon"></i>
                                                    <span class="vm-dropzone-text">Нажмите или перетащите файл</span>
                                                    <span class="vm-dropzone-hint">PDF, DOC, XLS, JPG до 5 МБ</span>
                                                @endif
                                            </div>
                                            <div wire:loading wire:target="upload" style="width: 100%;">
                                                <div class="vm-upload-progress">
                                                    <div class="vm-progress-bar">
                                                        <div class="vm-progress-bar-fill"></div>
                                                    </div>
                                                    <span style="font-size: 12px; color: var(--text-secondary);">Загрузка файла...</span>
                                                </div>
                                            </div>
                                        </div>
                                        @error('upload')
                                            <div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Completed Response --}}
                        @if ($task->response)
                            <div class="vm-section">
                                <div class="vm-section-header">
                                    <i class="fa fa-check-circle" style="color: #22c55e;"></i>
                                    <span class="vm-section-title">Завершенная задача</span>
                                </div>
                                <p class="vm-response-text">{{ $task->response->description }}</p>
                                @if($task->response->filename)
                                    <div class="vm-file-card">
                                        <div class="vm-file-card-left">
                                            <div class="vm-file-card-icon"><i class="fa fa-file-pdf-o"></i></div>
                                            <div>
                                                <div class="vm-file-card-name">{{ $task->response->filename }}</div>
                                                <div class="vm-file-card-meta">{{ $task->response->created_at->format('d.m.Y H:i') }}</div>
                                            </div>
                                        </div>
                                        <a href="{{ route('response.download', $task->response->filename) }}" class="vm-file-card-download">
                                            <i class="fa fa-download"></i>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Responsible Users --}}
                        <div class="vm-section">
                            <div class="vm-section-header">
                                <i class="fa fa-users"></i>
                                <span class="vm-section-title">Ответственные</span>
                            </div>
                            <div class="vm-users-row">
                                @forelse ($coTasks as $ct)
                                    <div class="vm-user-card">
                                        <img class="vm-user-avatar" src="{{ ($ct->user->avatar) ? asset('user_image/'.$ct->user->avatar) : asset('user_image/avatar.jpg') }}" alt="">
                                        <div class="vm-user-info">
                                            <span class="vm-user-name">{{ $ct->user->short_name }}</span>
                                            <span class="vm-user-role">{{ $ct->user->role->name }}</span>
                                        </div>
                                        @if ($task->status == 'Выполнено' && isset($ct->score))
                                            <span class="vm-user-role" style="margin-left: auto; font-weight: 600;">{{ $ct->total }}/{{ $ct->score->max_score }}</span>
                                        @endif
                                        @can('evaluate', $task)
                                            @if ($task->status == 'Ждет подтверждения' && $task->group_id && isset($ct->score))
                                                <div class="vm-score-wrap" x-data="{ scoreErr: '' }">
                                                    <label style="font-size: 11px; font-weight: 600; color: var(--text-secondary);">Оценка (Макс: {{ $ct->score->max_score }})</label>
                                                    <input type="number"
                                                        class="vm-score-input"
                                                        wire:model="groupScores.{{ $ct->id }}"
                                                        placeholder="0"
                                                        min="{{ $ct->score->min_score }}"
                                                        max="{{ $ct->score->max_score }}"
                                                        x-on:input="scoreErr = (parseFloat($event.target.value) > {{ $ct->score->max_score }} || parseFloat($event.target.value) < {{ $ct->score->min_score }}) ? 'от {{ $ct->score->min_score }} до {{ $ct->score->max_score }}' : ''">
                                                    <span class="vm-score-error" x-show="scoreErr" x-text="scoreErr" x-cloak></span>
                                                </div>
                                            @endif
                                        @endcan
                                    </div>
                                @empty
                                    <div class="vm-user-card">
                                        <img class="vm-user-avatar" src="{{ ($task->user->avatar) ? asset('user_image/'.$task->user->avatar) : asset('user_image/avatar.jpg') }}" alt="">
                                        <div class="vm-user-info">
                                            <span class="vm-user-name">{{ $task->user->short_name }}</span>
                                            <span class="vm-user-role">{{ $task->user->role->name }}</span>
                                        </div>
                                        {{-- Single task score input --}}
                                        @can('evaluate', $task)
                                            @if ($task->status == 'Ждет подтверждения' && !$task->group_id && isset($task->score))
                                                <div class="vm-score-wrap" x-data="{ scoreErr: '' }" style="margin-left: auto;">
                                                    <label style="font-size: 11px; font-weight: 600; color: var(--text-secondary);">Оценка (Макс: {{ $task->score->max_score }})</label>
                                                    <input type="number" class="vm-score-input"
                                                        wire:model="taskScore"
                                                        placeholder="0"
                                                        min="{{ $task->score->min_score }}"
                                                        max="{{ $task->score->max_score }}"
                                                        x-on:input="scoreErr = (parseFloat($event.target.value) > {{ $task->score->max_score }} || parseFloat($event.target.value) < {{ $task->score->min_score }}) ? 'от {{ $task->score->min_score }} до {{ $task->score->max_score }}' : ''">
                                                    <span class="vm-score-error" x-show="scoreErr" x-text="scoreErr" x-cloak></span>
                                                </div>
                                            @endif
                                        @endcan
                                    </div>
                                @endforelse
                            </div>

                            @isset($errorMsg)
                                <div class="text-danger mt-2" style="font-size: 13px;">{{ $errorMsg }}</div>
                            @endisset
                        </div>

                        {{-- Comments --}}
                        <div class="vm-comments-section vm-section">
                            <div class="vm-section-header">
                                <i class="fa fa-comments-o"></i>
                                <span class="vm-section-title">Комментарии</span>
                                <span class="vm-comment-count">{{ $comments->count() }}</span>
                            </div>

                            {{-- Comment Form --}}
                            <div class="vm-comment-form" x-data>
                                <div class="vm-comment-input-wrap">
                                    <textarea class="form-control vm-comment-input" wire:model="comment" rows="2" placeholder="Напишите комментарий..."
                                        x-ref="commentInput"
                                        x-on:keydown.enter.prevent="if (!$event.shiftKey) { $wire.storeComment({{ $task->id }}) }"
                                        x-on:comment-added.window="$el.value = ''"></textarea>
                                    <button type="button" class="vm-comment-send-btn" wire:click="storeComment({{ $task->id }})">
                                        <i class="fa fa-paper-plane"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Chat Messages --}}
                            <div class="vm-chat" id="vm-chat-container">
                                @foreach ($comments as $cmt)
                                    @if ($cmt->user->id == auth()->user()->id)
                                        {{-- Current user: right-aligned blue --}}
                                        <div class="vm-chat-msg vm-chat-msg--mine">
                                            <div>
                                                <div class="vm-chat-meta vm-chat-meta--right">
                                                    <span class="vm-chat-time">{{ $cmt->created_at->format('d.m.Y H:i') }}</span>
                                                    <span class="vm-chat-name">{{ $cmt->user->short_name }}</span>
                                                </div>
                                                <div class="vm-chat-bubble vm-chat-bubble--blue">
                                                    {{ $cmt->comment }}
                                                    <button type="button" wire:click.prevent="deleteComment({{ $cmt->id }})" class="vm-chat-delete" title="Удалить">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        {{-- Other user: left-aligned gray --}}
                                        <div class="vm-chat-msg vm-chat-msg--other">
                                            <img class="vm-chat-avatar" src="{{ ($cmt->user->avatar) ? asset('user_image/'.$cmt->user->avatar) : asset('user_image/avatar.jpg') }}" alt="">
                                            <div>
                                                <div class="vm-chat-meta">
                                                    <span class="vm-chat-name">{{ $cmt->user->name }}</span>
                                                    <span class="vm-chat-time">{{ $cmt->created_at->format('d.m.Y H:i') }}</span>
                                                </div>
                                                <div class="vm-chat-bubble vm-chat-bubble--gray">{{ $cmt->comment }}</div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                    </div>

                    {{-- Footer --}}
                    @if (!$task->response && $task->user_id == Auth::user()->id)
                        <div class="vm-footer">
                            <button class="btn vm-btn-submit" wire:click="storeResponse">Завершить задачу</button>
                        </div>
                    @endif

                    @can('evaluate', $task)
                        @if ($task->status == 'Ждет подтверждения')
                            <div class="vm-footer" style="justify-content: flex-end; gap: 10px;">
                                <button class="btn btn-outline-secondary" style="border-radius: 8px;" wire:click="taskRejected({{ $task->id }})">Отменить</button>
                                <button class="btn vm-btn-submit" wire:click="taskConfirmed({{ $task->id }})">Подтвердить</button>
                            </div>
                        @endif
                    @endcan

                    @if ($task->user_id == auth()->user()->id && $task->status == 'Ждет подтверждения')
                        <div class="vm-footer">
                            <button class="btn btn-outline-secondary w-100" style="border-radius: 8px;" wire:click="reSubmit({{ $task->id }})">Отменить отправку</button>
                        </div>
                    @endif

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
                            <form wire:submit="changeUserInfo" method="POST">
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

                            <form wire:submit="updatePassword" method="POST">
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

@script
    <script>
        $wire.on('comment-added', () => {
            // Clear the textarea DOM value
            const textarea = document.querySelector('.vm-comment-input');
            if (textarea) {
                textarea.value = '';
            }

            setTimeout(() => {
                const chat = document.getElementById('vm-chat-container');
                if (chat && chat.lastElementChild) {
                    chat.lastElementChild.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    chat.lastElementChild.style.animation = 'vm-fade-in 0.4s ease';
                }
            }, 100);
        });
    </script>
@endscript
