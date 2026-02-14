@extends('layouts.main')

@section('main')
    @livewire('reports.kpi')
    <iframe id="txtArea1" style="display:none"></iframe>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/js/dataTables.bootstrap4.min.js') }}"></script>
@endsection
