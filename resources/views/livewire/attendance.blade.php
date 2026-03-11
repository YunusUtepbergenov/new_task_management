<div>
    <div class="att-page-header">
        <div class="att-page-icon">
            <i class="fa fa-clock-o"></i>
        </div>
        <div>
            <h3 class="att-page-title">Учёт прихода/ухода</h3>
            <p class="att-page-subtitle">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }} &middot; Сотрудники центра</p>
        </div>
    </div>

    <div class="att-card">
        <div class="att-table-wrap">
            <table class="att-table">
                <thead>
                    <tr>
                        <th class="att-th-sticky">Сотрудник</th>
                        @foreach ($dates as $date)
                            <th class="att-th-date {{ in_array($date->dayOfWeek, [6, 0]) ? 'att-th-weekend' : '' }} {{ $date->isToday() ? 'att-th-today' : '' }}">
                                <span class="att-date-day">{{ $date->format('d') }}</span>
                                <span class="att-date-month">{{ $date->translatedFormat('D') }}</span>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataBySector as $sector => $users)
                        <tr class="att-sector-row">
                            <td colspan="{{ count($dates) + 1 }}" class="att-sector-cell">
                                <i class="fa fa-building-o"></i> {{ $sector }}
                            </td>
                        </tr>
                        @foreach ($users as $user)
                            <tr class="att-user-row">
                                <td class="att-td-name">{{ $user['name'] }}</td>
                                @foreach ($dates as $date)
                                    @php
                                        $day = $user['days'][$date->format('Y-m-d')];
                                        $isWeekend = in_array($date->dayOfWeek, [6, 0]);
                                        $isToday = $date->isToday();
                                        $hasData = $day['come'] || $day['leave'];
                                    @endphp
                                    <td class="att-td-day {{ $isWeekend ? 'att-td-weekend' : '' }} {{ $isToday ? 'att-td-today' : '' }}">
                                        @if ($hasData)
                                            <div class="att-time att-time--in">
                                                <i class="fa fa-sign-in"></i>
                                                <span>{{ $day['come'] ? \Carbon\Carbon::parse($day['come'])->format('H:i') : '—' }}</span>
                                            </div>
                                            <div class="att-time att-time--out">
                                                <i class="fa fa-sign-out"></i>
                                                <span>{{ $day['leave'] ? \Carbon\Carbon::parse($day['leave'])->format('H:i') : '—' }}</span>
                                            </div>
                                        @else
                                            <span class="att-empty">—</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
