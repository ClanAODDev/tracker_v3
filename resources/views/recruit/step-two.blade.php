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
        @include ('application.components.progress-bar', ['percent' => 40])

        <h4><i class="fa fa-pencil-square-o text-accent"></i> Step 2: Member Agreement</h4>
        <hr />

        <form action="{{ route('recruiting.stepThree', [$division->abbreviation]) }}" method="post" id="member-information">
            {{ csrf_field() }}
            <input type="hidden" name="member_id" value="{{ $request['member_id'] }}">
            <input type="hidden" name="forum_name" value="{{ $request['forum_name'] }}">
            <input type="hidden" name="ingame_name" value="{{ $request['ingame_name'] }}">
            <input type="hidden" name="platoon" value="{{ $request['platoon'] }}">
            <input type="hidden" name="squad" value="{{ $request['squad'] }}">
            <input type="hidden" name="division_id" value="{{ $request->division->id }}">
            <input type="hidden" name="is_testing" value="{{ $isTesting }}">
        </form>

        <p>AOD members are required to read and reply to a handful of threads posts in the AOD community forums. Your division may have additional threads that you require new members to reply to.</p>

        <p>Searching threads for posts by member <code>{{ $request['member_id'] }}</code></p>

        <button class="btn btn-default refresh-button" onclick="window.Recruiting.handleThreadCheck()">
            <i class="fa fa-refresh fa-spin text-info"></i> <span class="status">Loading...</span>
        </button>

        <div class="thread-results"></div>
        <hr />

        <button class="pull-right step-two-submit btn btn-success" type="button">Continue</button>
        <button class="pull-left btn btn-default" type="button" onclick="history.back()">Back</button>

    </div>

@stop

@section('footer_scripts')
    <script src="{!! asset('/js/recruiting.js') !!}" type="text/javascript"></script>
    <script>window.Recruiting.handleThreadCheck()</script>
@stop