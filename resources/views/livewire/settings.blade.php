<div>
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">Настройки</h3>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="offset-md-1 col-md-10">
            <!-- Avatar Section -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Фото профиля</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="position-relative" style="width: 120px; height: 120px; flex-shrink: 0;">
                            @if ($avatarDataUrl)
                                <img src="{{ $avatarDataUrl }}" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #e9ecef;">
                            @else
                                <img src="{{ $avatarPreview }}" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #e9ecef;">
                            @endif
                        </div>
                        <div class="ml-4">
                            <p class="mb-2 text-muted" style="font-size: 13px;">JPG, JPEG или PNG. Макс 5MB.</p>
                            <div class="d-flex" style="gap: 8px;">
                                <label class="btn btn-primary btn-sm mb-0" style="cursor: pointer;">
                                    <i class="fa fa-upload mr-1"></i> Выбрать фото
                                    <input type="file" wire:model="avatar" accept="image/jpeg,image/png,image/jpg" class="d-none">
                                </label>
                                @if ($avatar)
                                    <button class="btn btn-success btn-sm" wire:click="saveAvatar" wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="saveAvatar"><i class="fa fa-check mr-1"></i> Сохранить</span>
                                        <span wire:loading wire:target="saveAvatar">Сохранение...</span>
                                    </button>
                                @endif
                                @if (Auth::user()->avatar)
                                    <button class="btn btn-outline-danger btn-sm" wire:click="removeAvatar" wire:confirm="Удалить фото профиля?">
                                        <i class="fa fa-trash mr-1"></i> Удалить
                                    </button>
                                @endif
                            </div>
                            <div wire:loading wire:target="avatar" class="mt-2">
                                <small class="text-muted"><i class="fa fa-spinner fa-spin"></i> Загрузка...</small>
                            </div>
                            @error('avatar')
                                <div class="text-danger mt-2" style="font-size: 13px;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Password Section -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Изменить пароль</h4>
                </div>
                <div class="card-body">
                    <form wire:submit="updatePassword">
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label">Прежний пароль</label>
                            <div class="col-lg-9">
                                @error('oldPassword')
                                    <div class="alert alert-danger" style="margin-bottom: 10px;">{{ $message }}</div>
                                @enderror
                                <input type="password" class="form-control" wire:model="oldPassword" autocomplete="off">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label">Новый пароль</label>
                            <div class="col-lg-9">
                                @error('newPassword')
                                    <div class="alert alert-danger" style="margin-bottom: 10px;">{{ $message }}</div>
                                @enderror
                                <input type="password" class="form-control" wire:model="newPassword" autocomplete="off">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label">Подтвердите пароль</label>
                            <div class="col-lg-9">
                                @error('confirmPassword')
                                    <div class="alert alert-danger" style="margin-bottom: 10px;">{{ $message }}</div>
                                @enderror
                                <input type="password" class="form-control" wire:model="confirmPassword" autocomplete="off">
                            </div>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="updatePassword">Изменить</span>
                                <span wire:loading wire:target="updatePassword">Сохранение...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Telegram Section -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0"><i class="fa fa-paper-plane mr-2"></i>Telegram</h4>
                </div>
                <div class="card-body">
                    @if ($telegramLinked)
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="badge badge-success" style="font-size: 14px; padding: 6px 12px;">
                                    <i class="fa fa-check-circle mr-1"></i> Telegram привязан
                                </span>
                            </div>
                            <button class="btn btn-outline-danger btn-sm" wire:click="unlinkTelegram" wire:confirm="Отвязать Telegram от аккаунта?">
                                <i class="fa fa-unlink mr-1"></i> Отвязать
                            </button>
                        </div>
                    @else
                        <p class="text-muted mb-3">Привяжите Telegram для получения уведомлений о задачах, сроках и KPI.</p>

                        @if ($telegramToken)
                            <div class="alert alert-info">
                                <p class="mb-2"><strong>Ваш токен:</strong></p>
                                <code style="font-size: 16px; user-select: all;">{{ $telegramToken }}</code>
                                <p class="mt-2 mb-0 text-muted" style="font-size: 13px;">
                                    Откройте Telegram бот и отправьте: <code>/start {{ $telegramToken }}</code><br>
                                    Токен действителен 10 минут.
                                </p>
                            </div>
                        @endif

                        <button class="btn btn-primary btn-sm" wire:click="generateTelegramToken" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="generateTelegramToken">
                                <i class="fa fa-key mr-1"></i> Сгенерировать токен
                            </span>
                            <span wire:loading wire:target="generateTelegramToken">Генерация...</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@script
    <script>
        $wire.on('avatar-updated', (params) => {
            const url = params.url;
            const $sidebarAvatar = $('.sidebar-user-profile .user-avatar');
            if ($sidebarAvatar.length) {
                $sidebarAvatar.attr('src', url);
            }
        });
    </script>
@endscript
