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

        @if ($errors->count())
            <div class="alert alert-warning" style="cursor: pointer" name="doScrollToErrors">
                There were a few problems with your recruitment. Please review the issues marked in red.
                <i class="fa fa-arrow-circle-right"></i>
            </div>
        @endif

        <form action="{{ route('recruiting.stepTwo', [$division->abbreviation]) }}" method="post">

            {{ csrf_field() }}

            <h3>Getting Started</h3>
            <hr />
            <p>This is an introductory content paragraph for divisions to provide specific instructions regarding a recruitment process. This is a good place to talk about division-specific policies that may make or break interest, ie., division in-game requirements, must join platoon, must wear tags, etc.</p>
            <p>Additionally, recruiters should mention the clan-wide membership requirements:</p>
            <ul class="c-white">
                <li>Maintain minimum forum activity. Inactivity can result in removal from AOD</li>
                <li>Engage on TeamSpeak when playing a game AOD supports</li>
                <li>Strive to be a contributing member of your division</li>
                <li>Always be respectful of other clan members and leadership</li>
            </ul>

            <h3 class="m-t-xl"><i class="fa fa-address-card text-accent" aria-hidden="true"></i> Step 1: Member Data</h3>
            <hr />
            @include ('recruit.forms.member-information')
            @include ('recruit.forms.assignment')

            <button type="submit" class="btn btn-success pull-right">Continue <i class="fa fa-arrow-right"></i></button>
        </form>

    </div>
@stop

@section('footer_scripts')
    <script src="{!! asset('/js/recruiting.js') !!}"></script>
@stop