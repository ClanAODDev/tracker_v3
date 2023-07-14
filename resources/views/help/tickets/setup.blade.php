@extends('application.base-tracker')

@section('content')

    @component ('application.components.view-heading')
        @slot ('currentPage')
            Admin Support
        @endslot
        @slot ('icon')
            <i class="pe page-header-icon pe-7s-help2"></i>
        @endslot
        @slot ('heading')
            AOD Tracker
        @endslot
        @slot ('subheading')
            Create a Help Ticket
        @endslot
    @endcomponent

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">

                @include('help.tickets.partials.header')

                <hr>
                <div class="row">
                    @foreach ($ticketTypes as $ticketType)
                        <div class="col-md-3">
                            <a href="{{ route('help.tickets.create') . '?type=' . $ticketType->slug }}"
                               class="panel panel-filled" style="height: 140px; ">
                                <div class="panel-heading"><strong>{{ $ticketType->name }}</strong></div>
                                <div class="panel-body">{{ $ticketType->description }}</div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
