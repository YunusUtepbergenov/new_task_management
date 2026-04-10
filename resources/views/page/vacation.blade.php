@extends('layouts.main')

@section('main')
    @php
        $months = __('employees.months');
        $monthIcons = [
            1 => 'fa-snowflake-o', 2 => 'fa-snowflake-o', 3 => 'fa-leaf',
            4 => 'fa-leaf', 5 => 'fa-sun-o', 6 => 'fa-sun-o',
            7 => 'fa-sun-o', 8 => 'fa-sun-o', 9 => 'fa-leaf',
            10 => 'fa-leaf', 11 => 'fa-snowflake-o', 12 => 'fa-snowflake-o'
        ];
    @endphp

    <div class="content container-fluid">
        <div class="vac-page-header">
            <div class="vac-page-icon">
                <i class="fa fa-calendar-check-o"></i>
            </div>
            <div>
                <h3 class="vac-page-title">{{ __('employees.vacation_title') }}</h3>
                <p class="vac-page-subtitle">{{ date('Y') }} {{ __('employees.year') }} &middot; {{ count($vacations) }} {{ __('employees.months_planned') }}</p>
            </div>
        </div>

        <div class="vac-grid">
            @foreach ($vacations as $vacation)
                @php
                    $isCurrent = now()->month == $vacation->month;
                    $isPast = now()->month > $vacation->month;
                @endphp
                <div class="vac-card {{ $isCurrent ? 'vac-card--current' : '' }} {{ $isPast ? 'vac-card--past' : '' }}">
                    <div class="vac-card-header">
                        <div class="vac-month-icon {{ $isCurrent ? 'vac-month-icon--current' : '' }}">
                            <i class="fa {{ $monthIcons[$vacation->month] }}"></i>
                        </div>
                        <div>
                            <span class="vac-month-name">{{ $months[$vacation->month] }}</span>
                            @if ($isCurrent)
                                <span class="vac-badge-current">{{ __('employees.current') }}</span>
                            @endif
                        </div>
                        <span class="vac-user-count">
                            <i class="fa fa-users"></i> {{ $vacation->users->count() }}
                        </span>
                    </div>
                    <div class="vac-card-body">
                        @foreach ($vacation->users as $user)
                            <div class="vac-person">
                                <img class="vac-person-avatar" src="{{ $user->avatar ? asset('user_image/'.$user->avatar) : asset('user_image/avatar.jpg') }}" alt="">
                                <div class="vac-person-info">
                                    <span class="vac-person-name">{{ $user->short_name ?? $user->name }}</span>
                                    <span class="vac-person-role">{{ $user->role->name }}</span>
                                </div>
                                <span class="vac-person-period">
                                    @php
                                        $date = \Carbon\Carbon::parse($user->join_date);
                                    @endphp
                                    {{ $date->year(now()->year - 1)->format('d.m.Y') }} — {{ $date->year(now()->year)->format('d.m.Y') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
