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
        <h4>Ticket <strong>#{{ $ticket->id }}</strong> Details</h4>
        <hr>

        <div class="row">
            <div class="col-md-6">
                Caller: {{ $ticket->caller->name }}
            </div>
            <div class="col-md-6">
                Created: {{ $ticket->created_at }}
            </div>
            
            <div class="col-md-12">

            </div>
        </div>

        <label for="description">Ticket Description</label>
        <textarea name="description" id="description" cols="30" rows="10"
                  style="resize: vertical" class="form-control">{{ $ticket->description }}</textarea>
    </div>
@stop
