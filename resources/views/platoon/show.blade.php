@extends('application.base')
@section('content')

    @component ('application.components.division-heading')
        @slot ('icon')
            <a href="{{ route('division', $division->abbreviation) }}">
                <img src="{{ getDivisionIconPath($division->abbreviation) }}" />
            </a>
        @endslot
        @slot ('heading')
            {{ $platoon->name }}
            @include('platoon.partials.edit-platoon-button', ['division' => $division])
        @endslot
        @slot ('subheading')
            {{ $division->name }} Division
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('platoon', $division, $platoon) !!}

        <div class="row">
            <div class="col-md-9">
                <ul class="nav nav-tabs">
                    <li role="presentation" class="active">
                        <a href="#"><i class="fa fa-cube fa-lg"></i></a>
                    </li>
                    <li role="presentation">
                        <a href="{{ route('platoonSquads', [$division->abbreviation, $platoon]) }}">
                            <i class="fa fa-cubes fa-lg"></i>
                        </a>
                    </li>
                </ul>

                <div class="panel panel-filled">
                    @include('platoon.partials.platoon-members')
                </div>
            </div>
            <div class="col-md-3">
                @include('platoon.partials.member_stats')
            </div>
        </div>

    </div>
@stop

@section('footer_scripts')
    <script src="{!! asset('/js/platoon.js') !!}"></script>
@stop
