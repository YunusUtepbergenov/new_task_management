@props(['name', 'size' => null, 'meta' => null, 'href' => null, 'removeAction' => null, 'confirm' => null])

@php
    $kind = \App\Models\MailFile::kindFor($name);
    $extension = strtoupper(pathinfo($name, PATHINFO_EXTENSION)) ?: 'FILE';
    $details = collect([\App\Models\MailFile::formatSize($size), $meta])->filter()->join(' · ');
@endphp

<div {{ $attributes->class(['mx-file-card']) }}>
    <span class="mx-file-badge mx-file-badge--{{ $kind }}" aria-hidden="true">{{ \Illuminate\Support\Str::limit($extension, 4, '') }}</span>
    <span class="mx-file-info">
        @if ($href)
            <a href="{{ $href }}" class="mx-file-name" title="{{ $name }}">{{ $name }}</a>
        @else
            <span class="mx-file-name" title="{{ $name }}">{{ $name }}</span>
        @endif
        @if ($details)
            <span class="mx-file-meta">{{ $details }}</span>
        @endif
    </span>
    @if ($href)
        <a href="{{ $href }}" class="mx-icon-btn" title="{{ __('mails.upload.download') }}" aria-label="{{ __('mails.upload.download') }}"><i class="fa fa-download"></i></a>
    @endif
    @if ($removeAction)
        <button type="button" class="mx-icon-btn mx-icon-btn--danger" wire:click="{{ $removeAction }}" @if ($confirm) wire:confirm="{{ $confirm }}" @endif title="{{ __('mails.actions.delete') }}" aria-label="{{ __('mails.actions.delete') }}"><i class="fa fa-trash-o"></i></button>
    @endif
</div>
