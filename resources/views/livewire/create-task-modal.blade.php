<div>
    <div class="modal custom-modal fade" id="create_task" role="dialog" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 620px;">
            <div class="modal-content">
                <div class="vm-header">
                    <div class="vm-header-left">
                        <i class="fa fa-plus vm-header-icon"></i>
                        <h5 class="vm-header-title">Новая задача</h5>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body" style="padding: 0;">
                    <div class="vm-section">
                        @if ($errorMsg)
                            <div class="alert alert-danger mb-3">{{ $errorMsg }}</div>
                        @endif

                        <div class="vm-section-header">
                            <i class="fa fa-list-alt"></i>
                            <span class="vm-section-title">Категория</span>
                        </div>
                        <div class="form-group mb-3" wire:ignore>
                            <select class="form-control select2" id="create_score">
                                <option value="" disabled selected>Выберите</option>
                                @foreach ($scoresGrouped as $group => $items)
                                    <optgroup label="{{ $group }}">
                                        @foreach ($items as $type)
                                            <option value="{{ $type['id'] }}">{{ $type['name'] }} (Макс: {{ $type['max_score'] }})</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        @error('scoreId')
                            <div class="text-danger mb-2">{{ $message }}</div>
                        @enderror

                        <div class="vm-section-header">
                            <i class="fa fa-pencil-square-o"></i>
                            <span class="vm-section-title">Название</span>
                        </div>
                        <div class="form-group mb-3">
                            <input type="text" class="form-control" wire:model="name" placeholder="Введите название задачи">
                        </div>
                        @error('name')
                            <div class="text-danger mb-2">{{ $message }}</div>
                        @enderror

                        <div class="vm-section-header">
                            <i class="fa fa-paperclip"></i>
                            <span class="vm-section-title">Файлы (Макс: 5 МБ)</span>
                        </div>
                        <div class="form-group mb-3"
                             x-data="{ dragging: false }"
                             x-on:dragover.prevent="dragging = true"
                             x-on:dragleave.prevent="dragging = false"
                             x-on:drop.prevent="dragging = false; $refs.createFileInput.files = $event.dataTransfer.files; $refs.createFileInput.dispatchEvent(new Event('change'));">
                            <div class="vm-dropzone" :class="{ 'vm-dropzone--active': dragging }" x-on:click="$refs.createFileInput.click()">
                                <input type="file" wire:model="files" class="vm-dropzone-input" x-ref="createFileInput" multiple>
                                <div class="vm-dropzone-content" wire:loading.remove wire:target="files">
                                    <i class="fa fa-cloud-upload vm-dropzone-icon"></i>
                                    <span class="vm-dropzone-text">Нажмите или перетащите файлы</span>
                                    <span class="vm-dropzone-hint">PDF, DOC, XLS, JPG до 5 МБ</span>
                                </div>
                                <div wire:loading wire:target="files" style="width: 100%;">
                                    <div class="vm-upload-progress">
                                        <div class="vm-progress-bar">
                                            <div class="vm-progress-bar-fill"></div>
                                        </div>
                                        <span style="font-size: 12px; color: var(--text-secondary);">Загрузка файлов...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if (!empty($files))
                            @foreach ($files as $index => $file)
                                <div class="vm-uploaded-file" wire:key="file-{{ $index }}">
                                    <i class="fa fa-check-circle" style="color: #22c55e; font-size: 18px; flex-shrink: 0;"></i>
                                    <span>{{ Str::limit($file->getClientOriginalName(), 25) }}</span>
                                    <button type="button" class="vm-upload-remove" wire:click.stop="removeFile({{ $index }})" title="Удалить файл">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                            @endforeach
                        @endif
                        @error('files.*')
                            <div class="text-danger mb-2">{{ $message }}</div>
                        @enderror

                        <div class="vm-section-header">
                            <i class="fa fa-calendar"></i>
                            <span class="vm-section-title">Срок</span>
                        </div>
                        <div class="form-group mb-3">
                            <input type="date" class="form-control" wire:model="deadline">
                        </div>
                        @error('deadline')
                            <div class="text-danger mb-2">{{ $message }}</div>
                        @enderror

                        <div class="vm-section-header">
                            <i class="fa fa-users"></i>
                            <span class="vm-section-title">Ответственный</span>
                        </div>
                        <div class="form-group mb-3" wire:ignore>
                            <select class="form-control select2" id="create_users" multiple>
                                @foreach ($filteredSectors as $sector)
                                    <optgroup label="{{ $sector['name'] }}">
                                        @foreach ($sector['users'] as $user)
                                            <option value="{{ $user['id'] }}">{{ $user['name'] }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        @error('userIds')
                            <div class="text-danger mb-2">{{ $message }}</div>
                        @enderror

                        @if (count($creators) > 1)
                            <div class="vm-section-header">
                                <i class="fa fa-user"></i>
                                <span class="vm-section-title">Постановщик</span>
                            </div>
                            <div class="form-group mb-3" wire:ignore>
                                <select class="form-control" id="create_creator">
                                    @foreach ($creators as $creator)
                                        <option value="{{ $creator['id'] }}">{{ $creator['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="vm-footer">
                    <button class="vm-btn-submit" wire:click="taskStore" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="taskStore">Поставить Задачу</span>
                        <span wire:loading wire:target="taskStore">Создание...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@script
    <script>
        let orderedUserIds = [];

        function initCreateSelect2() {
            const $modal = $('#create_task');
            const $score = $('#create_score');
            const $users = $('#create_users');
            const $creator = $('#create_creator');

            if ($score.length) {
                if ($score.hasClass('select2-hidden-accessible')) {
                    $score.select2('destroy');
                }
                $score.select2({ dropdownParent: $modal, width: '100%' });
                $score.off('change.createScore').on('change.createScore', function () {
                    $wire.$set('scoreId', $(this).val());
                });
            }

            if ($users.length) {
                if ($users.hasClass('select2-hidden-accessible')) {
                    $users.select2('destroy');
                }
                $users.select2({ dropdownParent: $modal, width: '100%' });
                $users.off('select2:select.createUsers').on('select2:select.createUsers', function (e) {
                    orderedUserIds.push(e.params.data.id);
                    $wire.$set('userIds', [...orderedUserIds]);
                    var $el = $(e.params.data.element);
                    $el.detach();
                    $(this).append($el);
                    $(this).trigger('change.select2');
                });
                $users.off('select2:unselect.createUsers').on('select2:unselect.createUsers', function (e) {
                    orderedUserIds = orderedUserIds.filter(id => id !== e.params.data.id);
                    $wire.$set('userIds', [...orderedUserIds]);
                });
            }

            if ($creator.length) {
                $creator.off('change.createCreator').on('change.createCreator', function () {
                    $wire.$set('creatorId', $(this).val());
                });
            }
        }

        $wire.on('show-create-modal', () => {
            const $modal = $('#create_task');

            function openAndInit() {
                $modal.modal('show');
                $modal.one('shown.bs.modal', function () {
                    orderedUserIds = [];
                    initCreateSelect2();
                    $('#create_score').val(null).trigger('change.select2');
                    $('#create_users').val(null).trigger('change.select2');
                });
            }

            if ($modal.hasClass('show') || $modal.hasClass('in')) {
                $modal.modal('hide');
                $modal.one('hidden.bs.modal', () => openAndInit());
            } else {
                openAndInit();
            }
        });

        $wire.on('close-create-modal', () => {
            $('#create_task').modal('hide');
            orderedUserIds = [];
        });
    </script>
@endscript
