@extends('application.base')

@section('content')

    @component ('application.components.division-heading')
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-large" />
        @endslot
        @slot ('heading')
            Ingame Reporting
        @endslot
        @slot ('subheading')
            {{ $division->name }} Division
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('ingame-report', $division) !!}

        <div id="report-container">
            @include('division.partials.ingame-report-partial')
        </div>
    </div>
@stop

{{--
@section('footer_scripts')
    <script src="{!! asset('/js/division-reports.js') !!}"></script>
@stop
--}}

{{--
{!! "<" . str_slug($division->name) . ">" !!}
<h4>Loading report...</h4>
If you are seeing this message, the division report either does not exist, or did not loaded properly.
{!! "</" . str_slug($division->name) . ">" !!}
--}}