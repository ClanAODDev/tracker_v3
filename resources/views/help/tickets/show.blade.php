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
            Viewing Ticket
        @endslot
    @endcomponent

    <div class="container-fluid">


        <h3>Viewing Ticket <code>#{{ $ticket->id }}</code></h3>
        <a href="{{ route('tickets.index') }}" class=""><i class="fa fa-arrow-left"></i> Back to tickets</a>
        <hr>

        <div class="panel panel-filled">
            <div class="panel-body">


                <form action="{{ route('tickets.store') }}" method="post">
                    {{ csrf_field() }}

                    <div class="form-group row">
                        <div class="col-md-4">
                            <label for="created_at">Created at</label>
                            <input type="text" value="{{ $ticket->created_at }}" id="created_at"
                                   class="form-control disabled" disabled>
                        </div>
                        <div class="col-md-4">
                            <label for="updated_at">Updated at</label>
                            <input type="text" value="{{ $ticket->updated_at }}" id="updated_at"
                                   class="form-control disabled" disabled>
                        </div>
                        <div class="col-md-4">
                            <label for="resolved_at">Resolved at</label>
                            <input type="text" value="{{ $ticket->resolved_at ?? '-' }}" id="resolved_at"
                                   class="form-control disabled" disabled>
                        </div>
                    </div>

                    <div class="form-group row">

                        <div class="col-md-4 p-xs">
                            <label for="username">Caller</label>
                            <input type="text" value="{{ $ticket->caller->name }}"
                                   id="username"
                                   class="form-control"
                                   disabled>
                        </div>

                        <div class="col-md-4 p-xs">
                            <label for="username">Assigned to</label>
                            <input type="text" value="{{ $ticket->owner->name ?? '-' }}"
                                   id="username"
                                   class="form-control"
                                   disabled>
                        </div>

                        <div class="col-md-4 p-xs">
                            <label for="username">Ticket Type</label>
                            <input type="text" value="{{ $ticket->type->name }}"
                                   id="username"
                                   class="form-control"
                                   disabled>
                        </div>
                    </div>

                    <hr>

                    <div class="form-group">
                        <label for="description">Ticket body</label>
                        <textarea name="description" id="description" class="form-control" rows="3"
                                  style="resize: vertical">{{ $ticket->description }}</textarea>
                    </div>
                </form>

            </div>
        </div>

        <div class="row">
            <div class="col-md-12">

            </div>
        </div>


        <hr>

        <h3 class="m-t-md">Ticket Activity</h3>
        @if($ticket->activity->count() > 1)
            @include('activity.list', ['activity' => $ticket->activity])
            @else
            <span class="text-muted">No activity recorded</span>
        @endif
    </div>
@stop
