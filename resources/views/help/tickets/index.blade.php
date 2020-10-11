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

        <h4>OPEN TICKETS <span class="badge">{{ $openTickets->count() }}</span></h4>
        <div class="row">
            <div class="panel panel-filled">
                <div class="table-responsive">
                    <table class="table table-hover basic-datatable">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Caller</th>
                            <th>Owned By</th>
                            <th>Updated At</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($openTickets as $ticket)
                            <tr>
                                <td>{{ $ticket->id }}</td>
                                <td class="text-info">{{ $ticket->type->name }}</td>
                                <td>{{ $ticket->caller->name }}</td>
                                @if($ticket->owner)
                                    <td class="text-accent">{{ $ticket->owner->name }}</td>
                                @else
                                    <td class="text-muted">--</td>
                                @endif
                                <td>{{ $ticket->updated_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
