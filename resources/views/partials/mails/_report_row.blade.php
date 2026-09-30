{{-- One row of the report page table. Expects $row, $isTotal, $number (non-total rows), $isEmployee, $statusColumns, $share, $tab and $person. --}}
@php
    $cell = function (int $count, string $status, bool $pastDue): array {
        $dot = match ($status) {
            'pending' => $pastDue ? 'late' : 'pending',
            default => $status,
        };

        return [$count, $dot];
    };
@endphp
<tr @class(['mx-grid-total' => $isTotal])
    @unless ($isTotal)
        wire:key="{{ $tab }}-row-{{ $row['person'] ?? $row['name'] }}"
        x-show="! query || @js(mb_strtolower($row['name'])).includes(query.toLowerCase())"
    @endunless
>
    @if ($isTotal)
        <td></td>
        <td>{{ __('mails.report.total') }}</td>
    @else
        <td class="mx-muted">{{ $number }}</td>
        <td>
            @if ($isEmployee)
                <button type="button" class="mx-grid-name mx-grid-person {{ $person === $row['person'] ? 'is-active' : '' }}" wire:click="showPerson('{{ $row['person'] }}')" title="{{ __('mails.report.open_tasks') }}">
                    <span class="mx-avatar mx-avatar--sm {{ $row['is_group'] ? 'mx-avatar--group' : '' }}">{{ $row['initials'] }}</span>
                    <span>{{ $row['name'] }}</span>
                    @if ($row['left'])
                        <small class="mx-muted">{{ __('mails.fields.left') }}</small>
                    @endif
                    <i class="fa fa-angle-right mx-grid-person-arrow" aria-hidden="true"></i>
                </button>
            @else
                <span class="mx-grid-name" title="{{ $row['name'] }}"><span>{{ $row['name'] }}</span></span>
            @endif
        </td>
    @endif

    <td class="mx-n mx-bl">{{ $row['required'] ?: '–' }}</td>

    <td class="mx-n mx-bl mx-grid-part">{{ $row['not_due'] ?: '–' }}</td>
    @foreach ($statusColumns as $status)
        @php([$count, $dot] = $cell($row['not_due_statuses'][$status], $status, false))
        <td class="mx-n">
            @if ($count)
                <span class="mx-grid-dot"><i class="mx-dot mx-dot--{{ $dot }}"></i>{{ $count }}</span>
            @else
                <span class="mx-grid-zero">–</span>
            @endif
        </td>
    @endforeach

    <td class="mx-n mx-bl mx-grid-part">
        @if ($row['past_due'])
            <span class="mx-grid-past">{{ $row['past_due'] }}</span><span class="mx-grid-pct">{{ $share($row) }}%</span>
        @else
            <span class="mx-grid-zero">–</span>
        @endif
    </td>
    @foreach ($statusColumns as $status)
        @php([$count, $dot] = $cell($row['past_due_statuses'][$status], $status, true))
        <td class="mx-n">
            @if ($count)
                <span class="mx-grid-dot"><i class="mx-dot mx-dot--{{ $dot }}"></i>{{ $count }}</span>
            @else
                <span class="mx-grid-zero">–</span>
            @endif
        </td>
    @endforeach

    <td class="mx-n mx-bl">
        @if ($row['closed'])
            <span class="mx-grid-closed">{{ $row['closed'] }}</span>
        @else
            <span class="mx-grid-zero">–</span>
        @endif
    </td>
</tr>
