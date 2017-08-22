@extends('application.base')

@section('content')

    @component ('application.components.division-heading')
        @slot ('icon')
            @if ($division)
                <img src="{{ getDivisionIconPath($division->abbreviation) }}"
                     class="division-icon-large" />
            @else
                <img src="{{ asset('images/logo_v2.svg') }}" width="50px" style="opacity: .2;" />
            @endif
        @endslot
        @slot ('heading')
            {!! $member->present()->rankName !!}
        @endslot
        @slot ('subheading')
            {{ $member->position->name or "No Position" }}
        @endslot
    @endcomponent

    <div class="container-fluid">

        <h4><i class="fa fa-exclamation-triangle text-danger"></i> Reset Member Assignments</h4>
        <p>You are about to reset this member's {{ $division->locality('platoon') }} and {{ $division->locality('squad') }} assignment information. Are you sure?</p>
<hr />
        <form action="{{ route('member.unassign', $member->clan_id) }}"
              method="post" id="member-reset-form">
            <a href="{{ route('member', $member->clan_id) }}" class="btn btn-default">Cancel</a>
            <button type="submit" class="btn btn-success">Reset Assignments</button>
            {{ csrf_field() }}
        </form>
@stop


