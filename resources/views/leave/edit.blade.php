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
            {{ $division->description }}
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('member-leave', $member, $division) !!}

        @if ($leave->expired)
            <div class="alert alert-warning">
                This leave of absence has expired! Please reach out to the member to determine if an extension is warranted, or revoke the LOA.
            </div>
        @endif

        <h4 class="m-t-xl">Edit Leave Details
            <small class="text-muted text-uppercase">{{ $member->name }}</small>
        </h4>
        <p>Leaves of absence are reserved for members who need to take extended leave for extenuating circumstances. It should not be something that is used on the whim. Division leadership should ensure that members are not abusing LOAs.</p>

        <p>If an LOA is extended, be sure to update the associated note with the appropriate details if anything has changed.</p>

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
            {!! Form::model($leave, ['method' => 'patch', 'route' => ['leave.update', $member->clan_id]]) !!}
            <input type="hidden" value="{{ $leave->id }}" name="leave_id" />
            <input type="hidden" name="requester_id" value="{{ $leave->requester->id }}" />
            @include('leave.forms.edit-leave')
            {!! Form::close() !!}
            <a href="{{ doForumFunction([$leave->note->forum_thread_id], 'showThread') }}" target="_blank"
               class="btn btn-default">View Forum Thread</a>
        </div>

        <div class="m-t-xl">
            <label for="note-body">Leave Justification</label>
            <textarea name="note-body" id="note-body" rows="4" class="form-control"
                      disabled>{{ $leave->note->body }}</textarea>
            <p class="m-t-md">Notes are generated separately from leave requests. If you need to make a change to the note associated with this leave request, you can access that below.</p>
            <a href="{{ route('editNote', [$member->clan_id, $leave->note->id]) }}"
               class="btn btn-accent">Edit Note</a>
        </div>

        <div class="m-t-xl">
            {!! Form::model($leave, ['method' => 'delete', 'route' => ['leave.delete', $member->clan_id, $leave->id]]) !!}
            @include('leave.forms.delete-leave')
            {!! Form::close() !!}
        </div>
    </div>

@stop
