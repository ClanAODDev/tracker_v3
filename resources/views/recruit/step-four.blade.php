@extends('application.base')

@section('content')

    @component ('application.components.division-heading', [$division])
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-large" />
        @endslot
        @slot ('heading')
            <span class="hidden-xs">{{ $division->name }}</span>
            <span class="visible-xs">{{ $division->abbreviation }}</span>
        @endslot
        @slot ('subheading')
            Recruit New Member
        @endslot
    @endcomponent

    <div class="container-fluid">
        @include ('application.components.progress-bar', ['percent' => 80])
        @include ('recruit.partials.member-status-request')
        @include ('recruit.partials.create-welcome-post')

        <a href="{{ route('recruiting.stepFive', $division->abbreviation) }}"  class="btn btn-success pull-right">Finish</a>
        <button class="pull-left btn btn-default" type="button" onclick="history.back()">Back</button>
    </div>

    <script>

    </script>

@stop

@section('footer_scripts')
    <script src="{!! asset('/js/recruiting.js') !!}"></script>
@stop