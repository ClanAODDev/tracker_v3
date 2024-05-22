@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading', ['division' => $division])
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
@endsection

{{--
@section('footer_scripts')
    <script src="{!! asset('/js/division-reports.js') !!}"></script>
@endsection
--}}

{{--
{!! "<" . Str::slug($division->name) . ">" !!}
<h4>Loading report...</h4>
If you are seeing this message, the division report either does not exist, or did not loaded properly.
{!! "</" . Str::slug($division->name) . ">" !!}
--}}