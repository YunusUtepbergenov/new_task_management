@props(['model', 'multiple' => false, 'compact' => false, 'title' => null])

{{--
    Drag & drop file zone bound to a Livewire upload property. Files upload as soon as they are
    chosen or dropped; progress comes from Livewire's upload events on the hidden input.
--}}
<div {{ $attributes->class(['mx-dropzone', 'mx-dropzone--compact' => $compact]) }}
     x-data="{ dragging: false, uploading: false, progress: 0 }"
     :class="{ 'is-dragging': dragging, 'is-uploading': uploading }"
     @dragover.prevent="dragging = true"
     @dragleave.prevent="dragging = false"
     @drop.prevent="dragging = false; $refs.input.files = $event.dataTransfer.files; $refs.input.dispatchEvent(new Event('change', { bubbles: true }))"
     x-on:livewire-upload-start="uploading = true; progress = 0"
     x-on:livewire-upload-progress="progress = $event.detail.progress"
     x-on:livewire-upload-finish="uploading = false"
     x-on:livewire-upload-cancel="uploading = false"
     x-on:livewire-upload-error="uploading = false">
    <label class="mx-dropzone-body">
        <span class="mx-dropzone-icon" aria-hidden="true">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V4M7 9l5-5 5 5"/><path d="M20 16.5V18a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-1.5"/></svg>
        </span>
        <span class="mx-dropzone-text">
            <span x-show="! uploading">
                <strong>{{ $title ?? __('mails.upload.choose') }}</strong>
                <span>{{ __('mails.upload.or_drop') }}</span>
            </span>
            <span x-show="uploading" style="display: none;"><strong>{{ __('mails.upload.uploading') }}</strong> <span x-text="progress + '%'"></span></span>
            @unless ($compact)
                <small>{{ __('mails.upload.hint') }}</small>
            @endunless
        </span>
        <input type="file" x-ref="input" wire:model="{{ $model }}" @if ($multiple) multiple @endif>
    </label>
    <span class="mx-dropzone-progress" x-show="uploading" style="display: none;"><span :style="`width: ${progress}%`"></span></span>
</div>
