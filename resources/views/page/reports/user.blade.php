@extends('layouts.main')

@section('main')
    @livewire('reports.user', ['userId' => $user_id, 'start' => $start, 'end'=> $end])
@endsection
