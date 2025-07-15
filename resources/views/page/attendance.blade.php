@extends('layouts.main')

@section('styles')
    @livewireStyles
@endsection

@section('main')
	<div class="content container-fluid">
		<div class="content container-fluid">
            @livewire('attendance')

@endsection

@section('scripts')
    @livewireScripts
@endsection