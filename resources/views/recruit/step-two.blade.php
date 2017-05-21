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

        <form action="{{ route('recruiting.stepThree', [$division->abbreviation]) }}" method="post" id="member-information">
            {{ csrf_field() }}
            <input type="hidden" name="member-id" value="{{ $request['member-id'] }}">
            <input type="hidden" name="forum-name" value="{{ $request['forum-name'] }}">
            <input type="hidden" name="ingame-name" value="{{ $request['ingame-name'] }}">
            <input type="hidden" name="platoon" value="{{ $request['platoon'] }}">
            <input type="hidden" name="squad" value="{{ $request['squad'] }}">
            <input type="hidden" name="division-id" value="{{ $request->division->id }}">
        </form>

        <p>AOD members are required to read and reply to a handful of threads posts in the AOD community forums. Your division may have additional threads that you require new members to reply to.</p>
        <button class="btn btn-default refresh-button" name="doThreadCheck">
            <i class="fa fa-refresh fa-spin text-info"></i> <span class="status">Loading...</span>
        </button>

        <div class="thread-results"></div>
        <hr />

        <button class="pull-right continue-btn btn btn-success" type="button">
            Continue
        </button>

    </div>

@stop

@section('footer_scripts')
    <script src="{!! asset('/js/recruiting.js') !!}"></script>
@stop