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
        <h4><i class="fa fa-pencil-square-o"></i> Step 2: Member Agreement</h4>
        <hr />

    </div>

@stop

@section('footer_scripts')
    <script src="{!! asset('/js/recruiting.js') !!}"></script>
@stop