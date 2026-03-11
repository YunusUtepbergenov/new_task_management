<div>
    <div class="settings-page-header">
        <div class="settings-page-icon">
            <i class="fa fa-cog"></i>
        </div>
        <div>
            <h3 class="settings-page-title">Настройки</h3>
            <p class="settings-page-subtitle">Управление профилем и учетной записью</p>
        </div>
    </div>

    <div class="settings-layout">
        {{-- Left Column: Profile --}}
        <div class="settings-col-left">
            <div class="settings-profile-card">
                <div class="settings-profile-banner"></div>
                <div class="settings-profile-body">
                    <div class="settings-avatar-area"
                         x-data="{ dragging: false }"
                         x-on:dragover.prevent="dragging = true"
                         x-on:dragleave.prevent="dragging = false"
                         x-on:drop.prevent="dragging = false; $refs.avatarInput.files = $event.dataTransfer.files; $refs.avatarInput.dispatchEvent(new Event('change'));">

                        <div class="settings-avatar-wrapper" :class="{ 'settings-avatar-dragging': dragging }">
                            <img
                                src="{{ $avatarDataUrl ?: $avatarPreview }}"
                                alt="Фото профиля"
                                class="settings-avatar-img"
                            >
                            <input type="file" wire:model="avatar" accept="image/jpeg,image/png,image/jpg" class="d-none" x-ref="avatarInput">
                            <div class="settings-avatar-overlay" x-on:click="$refs.avatarInput.click()">
                                <i class="fa fa-camera"></i>
                                <span>Изменить</span>
                            </div>
                            <div wire:loading wire:target="avatar" class="settings-avatar-loading">
                                <div class="settings-avatar-spinner"></div>
                            </div>
                        </div>
                    </div>

                    <div class="settings-profile-info">
                        <h4 class="settings-profile-name">{{ Auth::user()->name }}</h4>
                        <p class="settings-profile-role">{{ Auth::user()->role->name }} &middot; {{ Auth::user()->sector->name }}</p>
                        @if (Auth::user()->email)
                            <span class="settings-profile-email"><i class="fa fa-envelope-o"></i> {{ Auth::user()->email }}</span>
                        @endif
                    </div>

                    <div class="settings-avatar-actions">
                        @if ($avatar)
                            <button class="btn settings-btn settings-btn--save" wire:click="saveAvatar" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="saveAvatar"><i class="fa fa-check"></i> Сохранить фото</span>
                                <span wire:loading wire:target="saveAvatar"><i class="fa fa-spinner fa-spin"></i> Сохранение...</span>
                            </button>
                            <button class="btn settings-btn settings-btn--ghost" wire:click="$set('avatar', null)">
                                Отмена
                            </button>
                        @endif
                        @if (!$avatar && Auth::user()->avatar)
                            <button class="btn settings-btn settings-btn--danger-ghost" wire:click="removeAvatar" wire:confirm="Удалить фото профиля?">
                                <i class="fa fa-trash-o"></i> Удалить фото
                            </button>
                        @endif
                    </div>

                    @error('avatar')
                        <div class="settings-error mt-2">
                            <i class="fa fa-exclamation-circle"></i> {{ $message }}
                        </div>
                    @enderror
                    <p class="settings-avatar-hint">JPG, JPEG или PNG &middot; Макс 5 МБ</p>
                </div>
            </div>

            {{-- Telegram Section --}}
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-icon settings-card-icon--telegram">
                        <i class="fa fa-paper-plane"></i>
                    </div>
                    <div>
                        <h5 class="settings-card-title">Telegram</h5>
                        <p class="settings-card-subtitle">Уведомления о задачах</p>
                    </div>
                    @if ($telegramLinked)
                        <span class="settings-badge settings-badge--success">
                            <i class="fa fa-check-circle"></i> Привязан
                        </span>
                    @else
                        <span class="settings-badge settings-badge--muted">
                            <i class="fa fa-unlink"></i> Не привязан
                        </span>
                    @endif
                </div>
                <div class="settings-card-body">
                    @if ($telegramLinked)
                        <div class="settings-telegram-linked">
                            <div class="settings-telegram-status">
                                <i class="fa fa-check-circle" style="color: #22c55e; font-size: 20px;"></i>
                                <span>Ваш Telegram аккаунт привязан. Вы получаете уведомления.</span>
                            </div>
                            <button class="btn settings-btn settings-btn--danger-ghost" wire:click="unlinkTelegram" wire:confirm="Отвязать Telegram от аккаунта?">
                                <i class="fa fa-unlink"></i> Отвязать
                            </button>
                        </div>
                    @else
                        @if ($telegramToken)
                            <div class="settings-telegram-token">
                                <div class="settings-telegram-token-header">
                                    <i class="fa fa-key" style="color: var(--sidebar-active-bg);"></i>
                                    <span>Ваш токен:</span>
                                </div>
                                <code class="settings-telegram-code">{{ $telegramToken }}</code>
                                <div class="settings-telegram-steps">
                                    <div class="settings-telegram-step">
                                        <span class="settings-step-num">1</span>
                                        <span>Откройте <a href="https://telegram.com/ijro_cerr_uz_bot" target="_blank" class="settings-link">Telegram бот</a></span>
                                    </div>
                                    <div class="settings-telegram-step">
                                        <span class="settings-step-num">2</span>
                                        <span>Отправьте: <code>/start {{ $telegramToken }}</code></span>
                                    </div>
                                </div>
                                <p class="settings-telegram-hint"><i class="fa fa-clock-o"></i> Токен действителен 10 минут</p>
                            </div>
                        @else
                            <p class="settings-telegram-desc">Привяжите Telegram для получения уведомлений.</p>
                        @endif
                        <button class="btn settings-btn settings-btn--primary" wire:click="generateTelegramToken" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="generateTelegramToken">
                                <i class="fa fa-key"></i> {{ $telegramToken ? 'Новый токен' : 'Сгенерировать токен' }}
                            </span>
                            <span wire:loading wire:target="generateTelegramToken"><i class="fa fa-spinner fa-spin"></i> Генерация...</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right Column: Contact + Security --}}
        <div class="settings-col-right">
            {{-- Contact Info Section --}}
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-icon settings-card-icon--green">
                        <i class="fa fa-phone"></i>
                    </div>
                    <div>
                        <h5 class="settings-card-title">Контакты</h5>
                        <p class="settings-card-subtitle">Номер телефона и внутренний номер</p>
                    </div>
                </div>
                <div class="settings-card-body">
                    <form wire:submit="updateContactInfo">
                        <div class="settings-form-row">
                            <div class="settings-form-group">
                                <label class="settings-label">Номер телефона</label>
                                <div class="settings-input-wrap">
                                    <i class="fa fa-phone settings-input-icon"></i>
                                    <input type="text" class="form-control settings-input" wire:model="phone" placeholder="+998 XX XXX XX XX">
                                </div>
                                @error('phone')
                                    <div class="settings-error"><i class="fa fa-exclamation-circle"></i> {{ $message }}</div>
                                @enderror
                            </div>
                            <div class="settings-form-group">
                                <label class="settings-label">Внутренний номер</label>
                                <div class="settings-input-wrap">
                                    <i class="fa fa-hashtag settings-input-icon"></i>
                                    <input type="text" class="form-control settings-input" wire:model="internal" placeholder="Например: 123">
                                </div>
                                @error('internal')
                                    <div class="settings-error"><i class="fa fa-exclamation-circle"></i> {{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="settings-card-footer">
                            <button type="submit" class="btn settings-btn settings-btn--primary" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="updateContactInfo">Сохранить</span>
                                <span wire:loading wire:target="updateContactInfo"><i class="fa fa-spinner fa-spin"></i> Сохранение...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Password Section --}}
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-icon settings-card-icon--blue">
                        <i class="fa fa-lock"></i>
                    </div>
                    <div>
                        <h5 class="settings-card-title">Безопасность</h5>
                        <p class="settings-card-subtitle">Изменить пароль учетной записи</p>
                    </div>
                </div>
                <div class="settings-card-body">
                    <form wire:submit="updatePassword">
                        <div class="settings-form-group">
                            <label class="settings-label">Прежний пароль</label>
                            <div class="settings-input-wrap">
                                <i class="fa fa-key settings-input-icon"></i>
                                <input type="password" class="form-control settings-input" wire:model="oldPassword" placeholder="Введите текущий пароль" autocomplete="off">
                            </div>
                            @error('oldPassword')
                                <div class="settings-error"><i class="fa fa-exclamation-circle"></i> {{ $message }}</div>
                            @enderror
                        </div>
                        <div class="settings-form-row">
                            <div class="settings-form-group">
                                <label class="settings-label">Новый пароль</label>
                                <div class="settings-input-wrap">
                                    <i class="fa fa-shield settings-input-icon"></i>
                                    <input type="password" class="form-control settings-input" wire:model="newPassword" placeholder="Минимум 6 символов" autocomplete="off">
                                </div>
                                @error('newPassword')
                                    <div class="settings-error"><i class="fa fa-exclamation-circle"></i> {{ $message }}</div>
                                @enderror
                            </div>
                            <div class="settings-form-group">
                                <label class="settings-label">Подтвердите пароль</label>
                                <div class="settings-input-wrap">
                                    <i class="fa fa-shield settings-input-icon"></i>
                                    <input type="password" class="form-control settings-input" wire:model="confirmPassword" placeholder="Повторите новый пароль" autocomplete="off">
                                </div>
                                @error('confirmPassword')
                                    <div class="settings-error"><i class="fa fa-exclamation-circle"></i> {{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="settings-card-footer">
                            <button type="submit" class="btn settings-btn settings-btn--primary" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="updatePassword">Изменить пароль</span>
                                <span wire:loading wire:target="updatePassword"><i class="fa fa-spinner fa-spin"></i> Сохранение...</span>
                            </button>
                        </div>
                    </form>
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
