@extends('layouts.main')

@section('main')
    @livewire('research.scraping')
    @include('partials._scrape_modal')
@endsection
