@props(['deadline'])

{{-- "Sent/closed on time" or "sent/closed N days late", once the deadline is sent or closed and the date is known. --}}
@php
    $lateDays = $deadline->lateDays();
    $verb = $deadline->status === \App\Models\MailDeadline::STATUS_DONE ? 'closed' : 'sent';
@endphp

@if ($lateDays !== null)
    <span {{ $attributes->class(['mx-closed', 'mx-closed--late' => $lateDays > 0]) }}>
        <i class="fa {{ $lateDays ? 'fa-clock-o' : 'fa-check' }}" aria-hidden="true"></i>
        {{ $lateDays ? __("mails.messages.{$verb}_late", ['days' => $lateDays]) : __("mails.messages.{$verb}_on_time") }}
    </span>
@endif
