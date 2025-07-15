<div>
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"> Учёт прихода/ухода сотрудников</h3>
            </div>
        </div>
    </div>

    <div class="table-responsive" id="employeeTable">
        <table class="table table-bordered table-sm">
            <thead class="thead-light">
                <tr>
                    <th>Сотрудник</th>
                    @foreach ($dates as $date)
                        <th class="{{ in_array($date->dayOfWeek, [6, 0]) ? 'text-danger' : '' }}">{{ $date->format('d M') }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($dataBySector as $sector => $users)
                    <tr>
                        <th class="sector_name thead-light" colspan="{{ count($dates) + 1 }}">
                            {{ $sector }}
                        </th>
                    </tr>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user['name'] }}</td>
                            @foreach ($dates as $date)
                                @php
                                    $day = $user['days'][$date->format('Y-m-d')];
                                @endphp
                                <td class="{{ in_array($date->dayOfWeek, [6, 0]) ? 'bg-holiday' : '' }}">
                                    @if ($day['come'] || $day['leave'])
                                        <div><small><i class="las la-door-open bg-success" style="color: green; font-size:16px"></i> {{ $day['come'] ?? '-' }}</small></div>
                                        <div><small><i class="las la-door-closed bg-warning" style="color: green; font-size:16px"></i> {{ $day['leave'] ?? '-' }}</small></div>
                                    @else
                                        <small>-</small>
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
