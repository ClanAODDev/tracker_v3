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
            Create a Help Ticket
        @endslot
    @endcomponent

    <div class="container-fluid">
        <form action="{{ route('help.tickets.store') }}" method="POST">
            {{ csrf_field() }}

            <div class="row">
                <div class="col-md-12">

                    @include('application.partials.errors')

                    <h4>Create an Admin Ticket</h4>
                    <p>Submit a ticket only if you require assistance from the <strong>MSGT+</strong> team. If you have
                        questions regarding the use of this form, please use the Admin discord channel. </p>
                    <hr>
                    <div class="form-group">
                        <label for="ticket_type">Reason for ticket</label>
                        <select name="ticket_type" id="ticket_type" class="form-control" required>
                            <option hidden disabled selected value> -- Select a type --</option>
                            @foreach (\App\Models\TicketType::all() as $ticketType)
                                <option value="{{ $ticketType->id }}">{{ $ticketType->name }}
                                    - {{ $ticketType->description }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="description">Issue description:</label>
                        <textarea name="description" id="description" cols="30" rows="10" class="form-control"
                                  style="resize: vertical" required></textarea>
                        <small class="help-text">Please provide as much detail as possible</small>
                    </div>
                </div>
            </div>

            <hr>


            <div class="text-right">
                <button type="reset" class="btn btn-default">Reset form</button>
                <button type="submit" class="btn btn-success">Create Ticket</button>
            </div>
        </form>
    </div>
@stop
