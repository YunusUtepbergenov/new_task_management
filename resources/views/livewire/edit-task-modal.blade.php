<div>
    <div class="modal custom-modal fade" id="edit_task" role="dialog" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 620px;">
            <div class="modal-content">
                <div class="vm-header">
                    <div class="vm-header-left">
                        <i class="fa fa-pencil vm-header-icon"></i>
                        <h5 class="vm-header-title">{{ __('tasks.edit_modal_title') }}</h5>
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
                            <span class="vm-section-title">{{ __('tasks.category') }}</span>
                        </div>
                        <div class="form-group mb-3" wire:ignore>
                            <select class="form-control select2" id="edit_score">
                                <option value="" disabled selected>{{ __('tasks.select') }}</option>
                                @foreach ($scoresGrouped as $group => $items)
                                    <optgroup label="{{ $group }}">
                                        @foreach ($items as $type)
                                            <option value="{{ $type['id'] }}">{{ $type['name'] }} ({{ __('tasks.max') }} {{ $type['max_score'] }})</option>
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
                            <span class="vm-section-title">{{ __('tasks.name') }}</span>
                        </div>
                        <div class="form-group mb-3">
                            <input type="text" class="form-control" wire:model="name" placeholder="{{ __('tasks.name_placeholder') }}">
                        </div>
                        @error('name')
                            <div class="text-danger mb-2">{{ $message }}</div>
                        @enderror

                        <div class="vm-section-header">
                            <i class="fa fa-calendar"></i>
                            <span class="vm-section-title">{{ __('tasks.deadline') }}</span>
                        </div>
                        <div class="form-group mb-3">
                            <input type="date" class="form-control" wire:model="deadline">
                        </div>
                        @error('deadline')
                            <div class="text-danger mb-2">{{ $message }}</div>
                        @enderror

                        <div class="vm-section-header">
                            <i class="fa fa-users"></i>
                            <span class="vm-section-title">{{ __('tasks.responsible') }}</span>
                        </div>
                        <div class="form-group mb-3" wire:ignore>
                            <select class="form-control select2" id="edit_users" multiple>
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
                                <span class="vm-section-title">{{ __('tasks.creator') }}</span>
                            </div>
                            <div class="form-group mb-3" wire:ignore>
                                <select class="form-control" id="edit_creator">
                                    @foreach ($creators as $creator)
                                        <option value="{{ $creator['id'] }}">{{ $creator['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="vm-footer">
                    <button class="vm-btn-submit" wire:click="taskUpdate" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="taskUpdate">{{ __('tasks.edit_modal_title') }}</span>
                        <span wire:loading wire:target="taskUpdate">{{ __('tasks.saving') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@script
    <script>
        let pendingFormData = null;
        let orderedEditUserIds = [];

        function initEditSelect2() {
            const $modal = $('#edit_task');
            const $score = $('#edit_score');
            const $users = $('#edit_users');
            const $creator = $('#edit_creator');

            if ($score.length) {
                if ($score.hasClass('select2-hidden-accessible')) {
                    $score.select2('destroy');
                }
                $score.select2({ dropdownParent: $modal, width: '100%' });
                $score.off('change.editScore').on('change.editScore', function () {
                    $wire.$set('scoreId', $(this).val());
                });
            }

            if ($users.length) {
                if ($users.hasClass('select2-hidden-accessible')) {
                    $users.select2('destroy');
                }
                $users.select2({ dropdownParent: $modal, width: '100%', closeOnSelect: false });
                $users.off('select2:select.editUsers').on('select2:select.editUsers', function (e) {
                    orderedEditUserIds.push(e.params.data.id);
                    $wire.$set('userIds', [...orderedEditUserIds]);
                    var $el = $(e.params.data.element);
                    $el.detach();
                    $(this).append($el);
                    $(this).trigger('change.select2');
                });
                $users.off('select2:unselect.editUsers').on('select2:unselect.editUsers', function (e) {
                    orderedEditUserIds = orderedEditUserIds.filter(id => id !== e.params.data.id);
                    $wire.$set('userIds', [...orderedEditUserIds]);
                });
            }

            if ($creator.length) {
                $creator.off('change.editCreator').on('change.editCreator', function () {
                    $wire.$set('creatorId', $(this).val());
                });
            }

            if (pendingFormData) {
                applyFormData(pendingFormData);
                pendingFormData = null;
            }
        }

        function applyFormData(data) {
            const $score = $('#edit_score');
            const $users = $('#edit_users');
            const $creator = $('#edit_creator');

            if ($score.length && data.scoreId) {
                $score.val(data.scoreId).trigger('change.select2');
            }
            if ($users.length && data.userIds) {
                orderedEditUserIds = data.userIds.map(String);
                $users.val(orderedEditUserIds).trigger('change.select2');
            }
            if ($creator.length && data.creatorId) {
                $creator.val(data.creatorId);
            }
        }

        $wire.on('show-edit-modal', () => {
            const $modal = $('#edit_task');

            function openAndInit() {
                $modal.modal('show');
                $modal.one('shown.bs.modal', function () {
                    initEditSelect2();
                });
            }

            if ($modal.hasClass('show') || $modal.hasClass('in')) {
                $modal.modal('hide');
                $modal.one('hidden.bs.modal', () => openAndInit());
            } else {
                openAndInit();
            }
        });

        $wire.on('close-edit-modal', () => {
            $('#edit_task').modal('hide');
            orderedEditUserIds = [];
        });

        $wire.on('edit-form-loaded', (params) => {
            pendingFormData = params[0];
        });
    </script>
@endscript
