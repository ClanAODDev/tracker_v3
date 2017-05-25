@extends('application.base')

@section('content')

    @component ('application.components.view-heading')
        @slot ('currentPage')
            Recruiting
        @endslot
        @slot ('icon')
            <i class="pe page-header-icon pe-7s-users"></i>
        @endslot
        @slot ('heading')
            AOD Tracker
        @endslot
        @slot ('subheading')
            Add a new member to AOD
        @endslot
    @endcomponent

    <div class="container-fluid">
        <h3>Select a division</h3>
        <p>To start the recruiting process, select the division you wish to add a new recruit for. Keep in mind that each division's recruiting process is unique to that division, so ensure you communicate with them before processing a recruit for a division that is not your own.</p>

        <hr />

        <div class="row">
            @foreach ($divisions as $abbreviation => $name)
                <div class="col-sm-4">
                    <a href="{{ route('recruiting.form', [$abbreviation]) }}" class="panel panel-filled">
                        <div class="panel-body">
                            <img src="{{ getDivisionIconPath($abbreviation) }}"
                                 class="division-icon-small" />
                            {{ $name }}
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

    </div>
@stop