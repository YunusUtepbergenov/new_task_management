@extends('layouts.main')

@section('main')
    @if (session('password_expired'))
        <div class="alert alert-warning" role="alert">
            <i class="fa fa-exclamation-triangle"></i> {{ __('settings.password_expired_notice') }}
        </div>
    @endif
    @livewire('settings')
@endsection
