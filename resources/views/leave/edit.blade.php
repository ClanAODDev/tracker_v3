@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading', ['division' => $division])
        @slot ('heading')
            {!! $member->present()->rankName !!}
            @include('member.partials.member-actions-button', ['member' => $member])
        @endslot
        @slot ('subheading')
            @if ($member->isPending)
                <span class="text-accent"><i class="fa fa-hourglass"></i> Pending member</span>
            @elseif ($member->division_id == 0)
                <span class="text-muted"><i class="fa fa-user-times"></i> Ex-AOD</span>
            @else
                {{ $member->position?->getLabel() ?? "No Position" }}
            @endif
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('member-leave', $member, $division) !!}

        @if ($leave->expired)
            <div class="alert alert-warning">
                This leave of absence has expired! Please reach out to the member to determine if an extension is
                warranted, or revoke the LOA.
            </div>
        @endif

        <h4 class="m-t-xl">Edit Leave Details
            <small class="text-muted text-uppercase">{{ $member->name }}</small>
        </h4>
        <p>Leaves of absence are reserved for members who need to take extended leave for extenuating circumstances. It
            should not be something that is used on the whim. Division leadership should ensure that members are not
            abusing LOAs.</p>

        <p>If an LOA is extended, be sure to update the associated note with the appropriate details if anything has
            changed.</p>

        <div class="panel panel-filled panel-c-info m-t-xl">

            <table class="table">
                <tr>
                    <th>Leave Requested By</th>
                    <th>Leave Approved By</th>
                </tr>
                <tr>
                    <td>
                        {{ $leave->requester->name }}
                    </td>
                    <td>
                        @if ($leave->approver)
                            {{ $leave->approver->name }}
                        @else
                            <div class="text-danger">Not yet approved</div>
                        @endif

                    </td>
                </tr>
            </table>
        </div>

        <div class="m-t-xl">
            <form action="{{ route('leave.update', [$member->clan_id]) }}" method="post">
                <input type="hidden" value="{{ $leave->id }}" name="leave_id"/>
                <input type="hidden" name="requester_id" value="{{ $leave->requester->id }}"/>
                @include('leave.forms.edit-leave')
            </form>
            @if($leave->note && $leave->note->forum_thread_id)
                <a href="{{ doForumFunction([$leave->note->forum_thread_id], 'showThread') }}" target="_blank"
                   class="btn btn-default">View Forum Thread</a>
            @endif
        </div>

        <div class="m-t-xl">
            <label for="note-body">Leave Justification</label>
            <div class="panel panel-filled">
                <div class="panel-body">
                    {{ $leave->note->body ?? "N/A" }}
                </div>
            </div>

            @if($leave->note)
                <p class="m-t-md">Notes are generated separately from leave requests. If you need to make a change to
                    the note associated with this leave request, you can access that below.</p>
                <a href="{{ route('editNote', [$member->clan_id, $leave->note->id]) }}"
                   class="btn btn-accent">Edit Note</a>
            @endif
        </div>

        <div class="m-t-xl">
            <form action="{{ route('leave.delete', [$member->clan_id, $leave->id]) }}" method="post">
                @csrf
                @method('delete')
                @include('leave.forms.delete-leave')
            </form>
        </div>
    </div>

@endsection
