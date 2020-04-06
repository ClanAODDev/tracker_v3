@extends('application.base')

@section('content')

    @component ('application.components.view-heading')
        @slot ('currentPage')
            Leadership Training
        @endslot
        @slot ('icon')
            <img src="{{ asset('images/logo_v2.svg') }}" width="50px"/>
        @endslot
        @slot ('heading')
            AOD Tracker
        @endslot
        @slot ('subheading')
            Training Module
        @endslot
    @endcomponent

    <div class="container-fluid" id="training-container">
        <p>Please select the rank you wish to conduct training for:</p>

        <a class=" btn btn-primary" href="{{ route('training.sgt') }}#sgt-duties">SGT</a>
        <a class="disabled btn btn-primary" href="{{ route('training.ssgt') }}" disabled>SSGT</a>
        <a class="disabled btn btn-primary" href="{{ route('training.msgt') }}" disabled>MSGT</a>
    </div>
@stop

@section('footer_scripts')
    <script>
        $('.nav-item').click(function () {
            $(this).find('.fa-check-circle').removeClass('text-muted').addClass('text-success');
        })
    </script>
@stop