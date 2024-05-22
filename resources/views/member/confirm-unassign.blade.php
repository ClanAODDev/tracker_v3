@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading', ['division' => $division])
        @slot ('heading')
            {!! $member->present()->rankName !!}
        @endslot
        @slot ('subheading')
            {{ $member->position->name ?? "No Position" }}
        @endslot
    @endcomponent

    <div class="container-fluid">

        <h4><i class="fa fa-exclamation-triangle text-danger"></i> Reset Member Assignments</h4>
        <p>You are about to reset this member's assignment information. Are you sure?</p>
<hr />
        <form action="{{ route('member.unassign', $member->clan_id) }}"
              method="post" id="member-reset-form">
            <a href="{{ route('member', $member->getUrlParams()) }}" class="btn btn-default">Cancel</a>
            <button type="submit" class="btn btn-success">Reset Assignments</button>
            {{ csrf_field() }}
        </form>
@endsection


