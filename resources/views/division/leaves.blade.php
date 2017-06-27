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

        {!! Breadcrumbs::render('leaves-of-absence', $division) !!}

        @if ($expiredLeave)
            <div class="alert alert-warning">
                You have
                <strong>expired leaves of absence</strong>! You should reach out to the member in case an extension is warranted, or remove the expired LOA. Expired leave is marked in red.
            </div>
        @endif
        <p>Leaves of absence are reserved for members who need to take extended leave for extenuating circumstances. It should not be something that is used on the whim. Division leadership should ensure that members are not abusing LOAs.</p>

        <h4 class="m-t-xl">Active Leaves of Absence</h4>

        @if ($membersWithLeave->count())

            <table class="table table-hover basic-datatable">
                <thead>
                <tr>
                    <th class="no-sort"></th>
                    <th>Name</th>
                    <th>End Date</th>
                    <th>Approver</th>
                    <th>Reason</th>
                    <th class="no-sort">Forum Thread</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($membersWithLeave as $member)
                    <tr class="{{ $member->leaveOfAbsence->expired ? 'text-danger' : null }}">
                        <td>
                            <a href="{{ route('member.edit-leave', [$member->clan_id, $member->leaveOfAbsence->id]) }}"
                               class="btn btn-default">
                                <i class="fa fa-search"></i>
                            </a>
                        </td>
                        <td>
                            {{ $member->name }}
                            <span class="text-muted">{{ $member->rank->abbreviation }}</span>
                        </td>
                        <td title="{{ $member->leaveOfAbsence->end_date->diffForHumans() }}">
                            @if($member->leaveOfAbsence->expired)
                                <i class="fa fa-exclamation-triangle text-danger" title="Expired"></i>
                            @endif
                            {{ $member->leaveOfAbsence->end_date->format('Y-m-d') }}
                        </td>
                        <td>
                            @if ($member->leaveOfAbsence->approver)
                                {{ $member->leaveOfAbsence->approver->name }}
                            @else
                                <div class="text-accent">Needs approval</div>
                            @endif
                        </td>
                        <td>
                            {{ ucwords($member->leaveOfAbsence->reason) }}
                        </td>
                        <td>
                            <a target="_blank"
                               href="{{ doForumFunction([$member->leaveOfAbsence->forumthread_id], 'showThread') }}">
                                View Thread <i class="fa fa-external-link text-accent"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <p class="text-muted">No active leaves of absence</p>
        @endif

        <hr class="m-t-xl m-b-xl" />

        @include('application.partials.errors')
        <div class="panel panel-filled panel-c-accent">
            <div class="panel-heading">
                Create Request
            </div>
            <div class="panel-body">
                <form action="{{ route('member.store-leave', $division->abbreviation) }}" method="post">
                    {!! Form::model(App\Note::class, ['method' => 'post', 'route' => ['member.store-leave', $member->clan_id]]) !!}
                    @include('member.forms.create-leave')
                    {!! Form::close() !!}
                </form>
            </div>
        </div>
    </div>
@stop
