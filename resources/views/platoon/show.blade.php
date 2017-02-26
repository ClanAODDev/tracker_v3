@extends('application.base')
@section('content')

    @component ('application.components.division-heading')
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" />
        @endslot
        @slot ('heading')
            {{ $platoon->name }} {{ $division->locality('platoon') }}
        @endslot
        @slot ('subheading')
            {{ $division->name }}
        @endslot
    @endcomponent

    <div class="container-fluid">

        <div class="row margin-top-20">
            <div class="col-md-12">
                @include('platoon.partials.platoon-members')
            </div>

            <div class="col-md-4">
                {{--@include('platoon.partials.member_stats')--}}
            </div>
        </div>

    </div>
@stop

@section('footer_scripts')
    <script src="{!! asset('/js/platoon.js') !!}"></script>
@stop
