@extends('application.base')

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
            Ticket Index
        @endslot
    @endcomponent

    <div class="container-fluid">

        <h3>TICKETS <span class="badge">{{ $tickets->count() }}</span></h3>
        <hr>
        <div class="panel">
            <div class="table-responsive">
                <table class="table table-hover adv-datatable">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th class="text-center">State</th>
                        <th>Type</th>
                        <th>Caller</th>
                        <th>Division</th>
                        <th>Assigned To</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($tickets as $ticket)
                        <tr >
                            <td>{{ $ticket->id }}</td>
                            <td class="text-center">
                                @include('help.tickets.partials.state-box')
                            </td>
                            <td>
                                {{ $ticket->type->name }}
                            </td>
                            <td>{{ $ticket->caller->name }}</td>
                            <td>{{ $ticket->division->name }}</td>
                            @if($ticket->owner)
                                <td class="text-accent">{{ $ticket->owner->name }}</td>
                            @else
                                <td class="text-muted">--</td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop
