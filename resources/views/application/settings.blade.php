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

                <h4>General</h4>
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

                    <div class="form-group" style="margin-top:50px;">
                        <h4><i class="fas fa-snowflake text-info"></i> Snow</h4>
                        <hr>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="snow" id="no_snow" value="no_snow"
                                    {{ checked(getSnowSetting() === 'no_snow') }}
                            >
                            <label class="form-check-label" for="no_snow">
                                Disable snow <span class="text-muted">(do you hate Christmas?)</span>
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="snow" id="some_snow" value="some_snow"
                                    {{ checked(getSnowSetting() === 'some_snow') }}
                            >
                            <label class="form-check-label" for="some_snow">
                                Some snow <span class="text-muted">(kinda festive)</span>
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="snow" id="all_the_snow"
                                   value="all_the_snow"
                                    {{ checked(getSnowSetting() === 'all_the_snow') }}
                            >
                            <label class="form-check-label" for="all_the_snow">
                                All the snow <span class="text-muted">(really festive - CPU intensive)</span>
                            </label>
                        </div>
                    </div>

                    <hr>

                    <button class="btn btn-default" type="reset">Reset</button>
                    <button class="btn btn-primary" type="submit">Submit</button>
                </form>
            </div>
        </div>

    </div>
@endsection