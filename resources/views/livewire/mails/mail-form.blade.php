<form class="mx-drawer" wire:submit="save" role="dialog" aria-modal="true" aria-labelledby="mail-form-title">
    <div class="mx-drawer-head">
        <h3 id="mail-form-title">{{ $documentId ? __('mails.actions.edit') : __('mails.actions.create') }}</h3>
        <button type="button" class="mx-btn mx-btn--icon" wire:click="$parent.closeForm" aria-label="{{ __('mails.actions.close') }}">
            <i class="fa fa-times"></i>
        </button>
    </div>

    <div class="mx-drawer-body">
        <div class="mx-grid">
            <label class="mx-field mx-span-2">{{ __('mails.fields.type') }} *
                <input type="text" class="mx-input" list="mail-types" wire:model="type">
                <datalist id="mail-types">
                    @foreach ($types as $typeOption)
                        <option value="{{ $typeOption }}"></option>
                    @endforeach
                </datalist>
                @error('type') <span class="mx-error">{{ $message }}</span> @enderror
            </label>
            <label class="mx-field">{{ __('mails.fields.document_number') }} *
                <input type="text" class="mx-input" wire:model="document_number">
                @error('document_number') <span class="mx-error">{{ $message }}</span> @enderror
            </label>
            <label class="mx-field">{{ __('mails.fields.document_date') }} *
                <input type="date" class="mx-input" wire:model="document_date">
                @error('document_date') <span class="mx-error">{{ $message }}</span> @enderror
            </label>
            <label class="mx-field mx-span-2">{{ __('mails.fields.title') }} *
                <textarea class="mx-input" rows="3" wire:model="title"></textarea>
                @error('title') <span class="mx-error">{{ $message }}</span> @enderror
            </label>
        </div>

        <div class="mx-field">{{ __('mails.messages.document_files') }}
            <div class="mx-file-grid">
                @foreach ($newFiles as $fileIndex => $newFile)
                    <x-mails.file-card wire:key="new-file-{{ $fileIndex }}"
                        :name="$newFile->getClientOriginalName()"
                        :size="$newFile->getSize()"
                        :remove-action="'removeNewFile('.$fileIndex.')'" />
                @endforeach
                <x-mails.dropzone model="pendingFiles" multiple :title="__('mails.upload.choose_many')" />
            </div>
            @error('pendingFiles.*') <span class="mx-error">{{ $message }}</span> @enderror
            @error('newFiles.*') <span class="mx-error">{{ $message }}</span> @enderror
        </div>

        <div style="display: flex; align-items: center; justify-content: space-between;">
            <h4 class="mx-section-title">{{ __('mails.fields.items') }} · {{ count($items) }} *</h4>
            <button type="button" class="mx-btn mx-btn--link" wire:click="addItem"><i class="fa fa-plus"></i> {{ __('mails.actions.add_item') }}</button>
        </div>
        @error('items') <span class="mx-error">{{ $message }}</span> @enderror

        @foreach ($items as $index => $item)
            <fieldset class="mx-fieldset" wire:key="mail-item-{{ $item['uid'] }}-{{ $index }}">
                <legend>{{ __('mails.fields.item') }} {{ $index + 1 }}</legend>
                <div class="mx-item-grid">
                    <label class="mx-field">{{ __('mails.fields.clause') }}
                        <input type="text" class="mx-input" wire:model="items.{{ $index }}.clause">
                        @error("items.$index.clause") <span class="mx-error">{{ $message }}</span> @enderror
                    </label>
                    <label class="mx-field">{{ __('mails.fields.content') }}
                        <input type="text" class="mx-input" wire:model="items.{{ $index }}.content">
                        @error("items.$index.content") <span class="mx-error">{{ $message }}</span> @enderror
                    </label>
                </div>
                <div class="mx-field">{{ __('mails.fields.main_executor') }} *
                    <x-form.user-picker model="items.{{ $index }}.main_executor_id" :options="$userOptions" :placeholder="__('mails.picker.choose_main')" />
                    @error("items.$index.main_executor_id") <span class="mx-error">{{ $message }}</span> @enderror
                </div>
                <div class="mx-field">{{ __('mails.fields.co_executors') }}
                    <x-form.user-picker model="items.{{ $index }}.co_executor_ids" :options="$userOptions" multiple :placeholder="__('mails.picker.choose_co')">
                        <x-slot:actions>
                            <button type="button" class="mx-picker-action" wire:click="addAllHeads({{ $index }})" @click="close()">
                                <i class="fa fa-users"></i> {{ __('mails.actions.all_heads') }}
                            </button>
                        </x-slot:actions>
                    </x-form.user-picker>
                    @error("items.$index.co_executor_ids.*") <span class="mx-error">{{ $message }}</span> @enderror
                </div>
                <div class="mx-field">{{ __('mails.fields.deadlines') }} *
                    <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                        @foreach ($item['deadlines'] as $deadlineIndex => $deadline)
                            <span class="mx-date" wire:key="mail-item-{{ $item['uid'] }}-deadline-{{ $deadlineIndex }}">
                                <input type="date" wire:model="items.{{ $index }}.deadlines.{{ $deadlineIndex }}.deadline" aria-label="{{ __('mails.fields.deadline') }}">
                                <button type="button" wire:click="removeDeadline({{ $index }}, {{ $deadlineIndex }})" aria-label="{{ __('mails.actions.delete') }}">&times;</button>
                            </span>
                        @endforeach
                        <button type="button" class="mx-btn mx-btn--link" wire:click="addDeadline({{ $index }})">+ {{ __('mails.actions.add_deadline') }}</button>
                        @if (collect($item['deadlines'])->pluck('deadline')->filter()->isNotEmpty())
                            <button type="button" class="mx-btn mx-btn--link" wire:click="repeatMonthly({{ $index }})">+ {{ __('mails.actions.repeat_monthly') }}</button>
                        @endif
                    </div>
                    @error("items.$index.deadlines") <span class="mx-error">{{ $message }}</span> @enderror
                    @foreach ($item['deadlines'] as $deadlineIndex => $deadline)
                        @error("items.$index.deadlines.$deadlineIndex.deadline") <span class="mx-error">{{ $message }}</span> @enderror
                    @endforeach
                </div>
                <div style="text-align: right;">
                    <button type="button" class="mx-btn mx-btn--link" style="color: #dc2626;" wire:click="removeItem({{ $index }})" wire:confirm="{{ __('mails.actions.confirm_remove_item') }}">
                        <i class="fa fa-trash"></i> {{ __('mails.actions.remove_item') }}
                    </button>
                </div>
            </fieldset>
        @endforeach
    </div>

    <div class="mx-drawer-foot">
        <button type="button" class="mx-btn" wire:click="$parent.closeForm">{{ __('mails.actions.cancel') }}</button>
        <button type="submit" class="mx-btn mx-btn--primary" wire:loading.attr="disabled" wire:target="save,newFiles">
            <span wire:loading.remove wire:target="save">{{ __('mails.actions.save') }}</span>
            <span wire:loading wire:target="save"><i class="fa fa-spinner fa-spin"></i></span>
        </button>
    </div>
</form>
