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
    xoxp-44621983313-81512977463-233691291297-b7574ec09d742a43ee9f0a39280ed923
    <div class="container-fluid" id="recruiting-container">
        <recruiting-process division="{{ $division->abbreviation }}"
                            handle-name="{{ $division->handle->label or 'Ingame Name' }}"></recruiting-process>
    </div>

    <script>
      window.onbeforeunload = function () {
        return 'Are you sure you want to leave this page?';
      };
    </script>
@stop

@section('footer_scripts')
    <script src="{!! asset('/js/recruiting.js?v=4.7') !!}"></script>
@stop