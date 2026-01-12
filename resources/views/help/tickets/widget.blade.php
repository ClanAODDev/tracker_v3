@extends('application.base-tracker')

@section('content')
    <div class="container-fluid">
        <div id="ticket-widget-container"
             @if(isset($initialTicketId)) data-ticket-id="{{ $initialTicketId }}" @endif
             @if(isset($initialView)) data-initial-view="{{ $initialView }}" @endif
        ></div>
    </div>
@endsection

@section('footer_scripts')
    @vite(['resources/assets/js/tickets.js'])
@endsection
