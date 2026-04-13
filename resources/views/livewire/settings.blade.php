<div>
    <div class="settings-page-header">
        <div class="settings-page-icon">
            <i class="fa fa-cog"></i>
        </div>
        <div>
            <h3 class="settings-page-title">{{ __('settings.page_title') }}</h3>
            <p class="settings-page-subtitle">{{ __('settings.page_subtitle') }}</p>
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
                                alt="{{ __('settings.photo_alt') }}"
                                class="settings-avatar-img"
                            >
                            <input type="file" wire:model="avatar" accept="image/jpeg,image/png,image/jpg" class="d-none" x-ref="avatarInput">
                            <div class="settings-avatar-overlay" x-on:click="$refs.avatarInput.click()">
                                <i class="fa fa-camera"></i>
                                <span>{{ __('settings.change_photo') }}</span>
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
                                <span wire:loading.remove wire:target="saveAvatar"><i class="fa fa-check"></i> {{ __('settings.save_photo') }}</span>
                                <span wire:loading wire:target="saveAvatar"><i class="fa fa-spinner fa-spin"></i> {{ __('settings.saving') }}</span>
                            </button>
                            <button class="btn settings-btn settings-btn--ghost" wire:click="$set('avatar', null)">
                                {{ __('settings.cancel') }}
                            </button>
                        @endif
                        @if (!$avatar && Auth::user()->avatar)
                            <button class="btn settings-btn settings-btn--danger-ghost" wire:click="removeAvatar" wire:confirm="{{ __('settings.delete_photo_confirm') }}">
                                <i class="fa fa-trash-o"></i> {{ __('settings.delete_photo') }}
                            </button>
                        @endif
                    </div>

                    @error('avatar')
                        <div class="settings-error mt-2">
                            <i class="fa fa-exclamation-circle"></i> {{ $message }}
                        </div>
                    @enderror
                    <p class="settings-avatar-hint">{!! __('settings.photo_hint') !!}</p>
                </div>
            </div>

            {{-- Telegram Section --}}
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-icon settings-card-icon--telegram">
                        <i class="fa fa-paper-plane"></i>
                    </div>
                    <div>
                        <h5 class="settings-card-title">{{ __('settings.telegram') }}</h5>
                        <p class="settings-card-subtitle">{{ __('settings.telegram_subtitle') }}</p>
                    </div>
                    @if ($telegramLinked)
                        <span class="settings-badge settings-badge--success">
                            <i class="fa fa-check-circle"></i> {{ __('settings.linked') }}
                        </span>
                    @else
                        <span class="settings-badge settings-badge--muted">
                            <i class="fa fa-unlink"></i> {{ __('settings.not_linked') }}
                        </span>
                    @endif
                </div>
                <div class="settings-card-body">
                    @if ($telegramLinked)
                        <div class="settings-telegram-linked">
                            <div class="settings-telegram-status">
                                <i class="fa fa-check-circle" style="color: #22c55e; font-size: 20px;"></i>
                                <span>{{ __('settings.telegram_linked_msg') }}</span>
                            </div>
                            <button class="btn settings-btn settings-btn--danger-ghost" wire:click="unlinkTelegram" wire:confirm="{{ __('settings.unlink_confirm') }}">
                                <i class="fa fa-unlink"></i> {{ __('settings.unlink') }}
                            </button>
                        </div>
                    @else
                        @if ($telegramToken)
                            <div class="settings-telegram-token">
                                <div class="settings-telegram-token-header">
                                    <i class="fa fa-key" style="color: var(--sidebar-active-bg);"></i>
                                    <span>{{ __('settings.your_token') }}</span>
                                </div>
                                <div class="settings-telegram-code">
                                    <span class="settings-token-text">{{ $telegramToken }}</span>
                                    <button type="button" class="settings-copy-btn" wire:click="$dispatch('copy-token')" title="Copy">
                                        <i class="fa fa-copy"></i>
                                    </button>
                                </div>
                                <div class="settings-telegram-steps">
                                    <div class="settings-telegram-step">
                                        <span class="settings-step-num">1</span>
                                        <span>{!! __('settings.step_open_bot') !!} <a href="https://t.me/ijro_cerr_uz_bot" target="_blank" class="settings-link">@ijro_cerr_uz_bot</a></span>
                                    </div>
                                    <div class="settings-telegram-step">
                                        <span class="settings-step-num">2</span>
                                        <span>{!! __('settings.step_press_start') !!}</span>
                                    </div>
                                    <div class="settings-telegram-step">
                                        <span class="settings-step-num">3</span>
                                        <span>{{ __('settings.step_send_token') }} <code>{{ $telegramToken }}</code></span>
                                    </div>
                                </div>
                                <p class="settings-telegram-hint"><i class="fa fa-clock-o"></i> {{ __('settings.token_valid') }}</p>
                            </div>
                        @else
                            <p class="settings-telegram-desc">{{ __('settings.link_description') }} <a href="https://t.me/ijro_cerr_uz_bot" target="_blank" class="settings-link">@ijro_cerr_uz_bot</a>.</p>
                        @endif
                        <button class="btn settings-btn settings-btn--primary" wire:click="generateTelegramToken" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="generateTelegramToken">
                                <i class="fa fa-key"></i> {{ $telegramToken ? __('settings.new_token') : __('settings.generate_token') }}
                            </span>
                            <span wire:loading wire:target="generateTelegramToken"><i class="fa fa-spinner fa-spin"></i> {{ __('settings.generating') }}</span>
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
                        <h5 class="settings-card-title">{{ __('settings.contacts') }}</h5>
                        <p class="settings-card-subtitle">{{ __('settings.contacts_subtitle') }}</p>
                    </div>
                </div>
                <div class="settings-card-body">
                    <form wire:submit="updateContactInfo">
                        <div class="settings-form-row">
                            <div class="settings-form-group">
                                <label class="settings-label">{{ __('settings.phone') }}</label>
                                <div class="settings-input-wrap">
                                    <i class="fa fa-phone settings-input-icon"></i>
                                    <input type="text" class="form-control settings-input" wire:model="phone" placeholder="{{ __('settings.phone_placeholder') }}">
                                </div>
                                @error('phone')
                                    <div class="settings-error"><i class="fa fa-exclamation-circle"></i> {{ $message }}</div>
                                @enderror
                            </div>
                            <div class="settings-form-group">
                                <label class="settings-label">{{ __('settings.internal') }}</label>
                                <div class="settings-input-wrap">
                                    <i class="fa fa-hashtag settings-input-icon"></i>
                                    <input type="text" class="form-control settings-input" wire:model="internal" placeholder="{{ __('settings.internal_placeholder') }}">
                                </div>
                                @error('internal')
                                    <div class="settings-error"><i class="fa fa-exclamation-circle"></i> {{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="settings-card-footer">
                            <button type="submit" class="btn settings-btn settings-btn--primary" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="updateContactInfo">{{ __('settings.save') }}</span>
                                <span wire:loading wire:target="updateContactInfo"><i class="fa fa-spinner fa-spin"></i> {{ __('settings.saving') }}</span>
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
                        <h5 class="settings-card-title">{{ __('settings.security') }}</h5>
                        <p class="settings-card-subtitle">{{ __('settings.security_subtitle') }}</p>
                    </div>
                </div>
                <div class="settings-card-body">
                    <form wire:submit="updatePassword">
                        <div class="settings-form-group">
                            <label class="settings-label">{{ __('settings.old_password') }}</label>
                            <div class="settings-input-wrap">
                                <i class="fa fa-key settings-input-icon"></i>
                                <input type="password" class="form-control settings-input" wire:model="oldPassword" placeholder="{{ __('settings.old_password_placeholder') }}" autocomplete="off">
                            </div>
                            @error('oldPassword')
                                <div class="settings-error"><i class="fa fa-exclamation-circle"></i> {{ $message }}</div>
                            @enderror
                        </div>
                        <div class="settings-form-row">
                            <div class="settings-form-group">
                                <label class="settings-label">{{ __('settings.new_password') }}</label>
                                <div class="settings-input-wrap">
                                    <i class="fa fa-shield settings-input-icon"></i>
                                    <input type="password" class="form-control settings-input" wire:model="newPassword" placeholder="{{ __('settings.new_password_placeholder') }}" autocomplete="off">
                                </div>
                                @error('newPassword')
                                    <div class="settings-error"><i class="fa fa-exclamation-circle"></i> {{ $message }}</div>
                                @enderror
                            </div>
                            <div class="settings-form-group">
                                <label class="settings-label">{{ __('settings.confirm_password') }}</label>
                                <div class="settings-input-wrap">
                                    <i class="fa fa-shield settings-input-icon"></i>
                                    <input type="password" class="form-control settings-input" wire:model="confirmPassword" placeholder="{{ __('settings.confirm_password_placeholder') }}" autocomplete="off">
                                </div>
                                @error('confirmPassword')
                                    <div class="settings-error"><i class="fa fa-exclamation-circle"></i> {{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="settings-card-footer">
                            <button type="submit" class="btn settings-btn settings-btn--primary" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="updatePassword">{{ __('settings.change_password') }}</span>
                                <span wire:loading wire:target="updatePassword"><i class="fa fa-spinner fa-spin"></i> {{ __('settings.saving') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Language Section --}}
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-icon settings-card-icon--blue">
                        <i class="fa fa-language"></i>
                    </div>
                    <div>
                        <h5 class="settings-card-title">{{ __('settings.language') }}</h5>
                        <p class="settings-card-subtitle">{{ __('settings.language_subtitle') }}</p>
                    </div>
                </div>
                <div class="settings-card-body">
                    <div class="settings-form-row">
                        <div class="settings-form-group">
                            <label class="settings-label">{{ __('settings.interface_language') }}</label>
                            <select class="form-control settings-input" wire:model="locale" wire:change="updateLocale">
                                <option value="ru">Русский</option>
                                <option value="uz">Ўзбекча</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@script
    <script>
        Livewire.on('copy-token', () => {
            var text = document.querySelector('.settings-token-text');
            if (!text) return;
            var range = document.createRange();
            range.selectNodeContents(text);
            var sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
            document.execCommand('copy');
            sel.removeAllRanges();

            var btn = document.querySelector('.settings-copy-btn');
            btn.innerHTML = '<i class="fa fa-check" style="color: #22c55e;"></i>';
            setTimeout(function() {
                btn.innerHTML = '<i class="fa fa-copy"></i>';
            }, 2000);
        });

        $wire.on('avatar-updated', (params) => {
            const url = params.url;
            const $sidebarAvatar = $('.sidebar-user-profile .user-avatar');
            if ($sidebarAvatar.length) {
                $sidebarAvatar.attr('src', url);
            }
        });
    </script>
@endscript
