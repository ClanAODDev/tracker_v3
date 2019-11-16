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

        <h3>ADMIN TICKETS <span class="badge">{{ $tickets->count() }}</span></h3>
        <hr>
        <div class="panel">
            <div class="table-responsive">
                <table class="table table-hover adv-datatable">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Status</th>
                        <th>Type</th>
                        <th>Caller</th>
                        <th>Assigned To</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($tickets as $ticket)
                        <tr>
                            <td>{{ $ticket->id }}</td>
                            <td class="text-center">
                                @include('help.tickets.partials.state-box')
                            </td>
                            <td>
                                {{ $ticket->type->name }}
                            </td>
                            <td>{{ $ticket->caller->name }}</td>
                            @if($ticket->owner)
                                <td class="text-accent">{{ $ticket->owner->name }}</td>
                            @else
                                <td class="text-muted">--</td>
                            @endif

                            <td>
                                <form action="{{ route('tickets.own', $ticket) }}" class="btn-group clearfix"
                                      method="post">

                                    @if (auth()->user()->isRole(['admin']) && $ticket->owner_id != auth()->id())
                                        {{ csrf_field() }}
                                        <button type="submit" class="btn btn-default btn-sm"
                                                @click="return toastr.confirm('Are you sure');">Own to me
                                        </button>
                                    @endif
                                    <a href="{{ route('tickets.show', $ticket) }}"
                                       class="btn btn-default btn-sm">View</a>
                                </form>

                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop
