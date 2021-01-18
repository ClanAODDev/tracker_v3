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
            Create a Admin Ticket
        @endslot
    @endcomponent

    <div class="container-fluid">
        <form action="{{ route('help.tickets.store') }}" method="POST">
            {{ csrf_field() }}

            <div class="row">
                <div class="col-md-12">
                    @include('help.tickets.partials.header')
                    <hr>

                    @if ($type->boilerplate)
                        <h5>Required information</h5>
                        <p>Ensure your ticket includes the following:</p>
                        <pre><code class="language-markdown">{{ $type->boilerplate }}</code></pre>
                        <hr>
                    @endif
                    @include('application.partials.errors')
                    <div class="form-group">
                        <label for="description">Issue description:</label>
                        <textarea name="description" id="description" class="form-control"
                                  style="resize: vertical; min-height: 140px;" rows="10"
                                  required>{{ old('description') }}</textarea>
                        <input type="hidden" value="{{ $type->id }}" name="ticket_type" id="ticket_type">
                        <small class="help-text">Please provide as much detail as possible. Min 25 characters.</small>
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
@endsection
