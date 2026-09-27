@extends('layouts.main')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/mails.css') }}?v={{ filemtime(public_path('css/mails.css')) }}">
@endsection

@section('main')
    @livewire('mails.mail-report')
@endsection
