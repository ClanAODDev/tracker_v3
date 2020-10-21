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

        <a href="?filter[state]=new" class="btn btn-rounded btn-default {{ request()->input('filter.state') == 'new' ? 'active' : '' }}">New ({{ $newCount }})</a>
        <a href="?filter[state]=resolved" class="btn btn-rounded btn-default {{ request()->input('filter.state') == 'resolved' ? 'active' : '' }}">Resolved ({{ $resolvedCount }})</a>
        <a href="?filter[state]=assigned" class="btn btn-rounded btn-default {{ request()->input('filter.state') == 'assigned' ? 'active' : '' }}">Assigned ({{ $assignedCount }})</a>

        <form action="">

            <div class="row">

                <div class="col-md-12">
                    <div class="panel panel-filled">
                        <div class="panel-heading">
                            <div class="panel-tools">
                                <a class="panel-toggle"><i class="fa fa-chevron-up"></i></a>
                            </div>
                            Filter tickets
                        </div>
                        <div class="panel-body">
                            <div class="row">

                            </div>
                            <div class="text-right">
                                <a href="{{ route('help.tickets.index') }}" class="btn btn-default">Reset</a>
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>

        @if(request('filter') && is_array(request('filter')))
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-c-accent panel-filled">
                        <div class="panel-body">
                            <h5>Active filters</h5>

                            @foreach (request('filter') as $attribute => $filter)
                                <span class="badge">{{ ucwords($attribute) . " = " . ucwords($filter) }}</span>
                            @endforeacH

                        </div>
                    </div>
                </div>
            </div>
        @endif

        <h4>TICKETS <span class="badge">{{ $tickets->count() }}</span></h4>
        <hr>

        @unless($tickets->count())
            <p class="text-muted">No tickets match the provided criteria.</p>
        @else

            <div class="panel panel-filled">
                <div class="table-responsive">
                    <table class="table table-hover basic-datatable">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Caller</th>
                            <th>State</th>
                            <th>Owned By</th>
                            <th>Updated At</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($tickets->get() as $ticket)
                            <tr>
                                <td>{{ $ticket->id }}</td>
                                <td class="text-info">{{ $ticket->ticket_type->name }}</td>
                                <td>{{ $ticket->caller->name }}</td>
                                <td>
                                    @if ($ticket->state == 'new')

                                        <a title="Show only {{ $ticket->state }} tickets"
                                           href="{{ route('help.tickets.index') . "?filter[state]={$ticket->state}" }}"
                                           class="label label-info text-uppercase">{{ $ticket->state }}</a>

                                    @elseif ($ticket->state == 'assigned')

                                        <a title="Show only {{ $ticket->state }} tickets"
                                           href="{{ route('help.tickets.index')  . "?filter[state]={$ticket->state}"}}"
                                           class="label label-warning text-uppercase">{{ $ticket->state }}</a>

                                    @else ($ticket->state == 'resolved')

                                        <a title="Show only {{ $ticket->state }} tickets"
                                           href="{{ route('help.tickets.index') . "?filter[state]={$ticket->state}"}}"
                                           class="label label-success text-uppercase">{{ $ticket->state }}</a>

                                    @endif
                                </td>
                                <td>
                                    @if($ticket->owner)
                                        {{ $ticket->owner->name }}
                                    @else
                                        <span class="text-muted">--</span>
                                    @endif
                                </td>
                                <td>{{ $ticket->updated_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>


        @endunless
    </div>
@stop
