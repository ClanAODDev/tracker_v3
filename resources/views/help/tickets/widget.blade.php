@extends('application.base-tracker')

@section('content')
    <div class="container-fluid">
        <div id="ticket-widget-container"></div>
    </div>
@endsection

@section('footer_scripts')
    @vite(['resources/assets/js/tickets.js'])
@endsection
