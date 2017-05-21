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
        @include ('application.components.progress-bar', ['percent' => 100])

        <h4><i class="fa fa-check-circle-o text-success"></i> Complete</h4>

        <p>Your recruitment has been successfully completed!</p>

        <a href="{{ route('home') }}" type="button" class="btn btn-success m-t-lg">Go Home</a>
    </div>

@stop

@section('footer_scripts')
    <script src="{!! asset('/js/recruiting.js') !!}"></script>
@stop