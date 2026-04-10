<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #444; padding: 4px 6px; text-align: center; font-size: 11px; }
        th { background-color: #e2e3e5; font-weight: bold; }
        .sector { background-color: #d1ecf1; font-weight: bold; text-align: left; }
        .name { text-align: left; white-space: nowrap; }
        .weekend { background-color: #fff3cd; }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th colspan="{{ count($dates) + 1 }}">
                    Давомат: {{ $startDate->format('d.m.Y') }} — {{ $endDate->format('d.m.Y') }}
                </th>
            </tr>
            <tr>
                <th>Ф.И.О.</th>
                @foreach ($dates as $date)
                    <th class="{{ in_array($date->dayOfWeek, [6, 0]) ? 'weekend' : '' }}">
                        {{ $date->format('d') }} {{ $date->translatedFormat('D') }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($dataBySector as $sector => $users)
                <tr>
                    <td colspan="{{ count($dates) + 1 }}" class="sector">{{ $sector }}</td>
                </tr>
                @foreach ($users as $user)
                    <tr>
                        <td class="name">{{ $user['name'] }}</td>
                        @foreach ($dates as $date)
                            @php
                                $day = $user['days'][$date->format('Y-m-d')];
                                $come = $day['come'] ? \Carbon\Carbon::parse($day['come'])->format('H:i') : '';
                                $leave = $day['leave'] ? \Carbon\Carbon::parse($day['leave'])->format('H:i') : '';
                                $isWeekend = in_array($date->dayOfWeek, [6, 0]);
                            @endphp
                            <td class="{{ $isWeekend ? 'weekend' : '' }}">
                                @if ($come || $leave)
                                    {{ $come ?: '—' }} / {{ $leave ?: '—' }}
                                @else
                                    —
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</body>
</html>
