@extends('application.base')

@section('content')

    @component ('application.components.division-heading', [$division])
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-large" />
        @endslot
        @slot ('heading')
            Add New Recruit
        @endslot
        @slot ('subheading')
            {{ $division->name }}
        @endslot
    @endcomponent

    <div class="container-fluid" id="recruiting-container">
        <recruiting-process division="{{ $division->abbreviation }}"
                            handle-name="{{ $division->handle->label or 'Ingame Name' }}"></recruiting-process>
    </div>
@stop

@section('footer_scripts')
    <script src="{!! asset('/js/recruiting.js?v=4.61') !!}"></script>
@stop