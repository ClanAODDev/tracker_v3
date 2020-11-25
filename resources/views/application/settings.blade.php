@extends('application.base-tracker')

@section('content')
    @component ('application.components.view-heading')
        @slot ('currentPage')
            v3
        @endslot
        @slot ('icon')
            <img src="{{ asset('images/logo_v2.svg') }}" width="50px"/>
        @endslot
        @slot ('heading')
            AOD Tracker
        @endslot
        @slot ('subheading')
            User Settings
        @endslot
    @endcomponent

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">

                <h4><i class="fas fa-cog text-accent"></i> General</h4>
                <hr>
                <form action="{{ route('user.settings.update') }}" method="POST">
                    {{ csrf_field() }}
                    {{ method_field('PATCH') }}

                    <div class="form-group">
                        <input type='hidden' value='0' name="ticket_notifications">
                        <input id="ticket_notifications"
                               name="ticket_notifications"
                               type="checkbox" {{ checked($settings->ticket_notifications) }} />
                        <label for="ticket_notifications">
                            Receive Discord notifications when your ticket is updated
                        </label>
                    </div>

                    <div class="form-group">
                        <input type='hidden' value='0' name="snow">
                        <input id="snow"
                               name="snow"
                               type="checkbox" {{ checked($settings->snow) }} />
                        <label for="snow">
                            Enable Snow <i class="fas fa-snowflake text-info"></i>
                        </label>
                    </div>

                    <hr>

                    <button class="btn btn-default" type="reset">Reset</button>
                    <button class="btn btn-primary" type="submit">Submit</button>
                </form>
            </div>
        </div>

    </div>
@stop
