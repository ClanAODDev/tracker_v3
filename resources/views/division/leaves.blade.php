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
        <p>Leaves of absence are reserved for members who need to take extended leave for extenuating circumstances. It should not be something that is used on the whim. Division leadership should ensure that members are not abusing LOAs.</p>

        <h4 class="m-t-xl">Active Leaves of Absence</h4>

        @if ($membersWithLeave->count())

            <table class="table table-striped basic-datatable">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>End Date</th>
                    <th>Reason</th>
                    <th>Note</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($membersWithLeave as $member)
                    <tr>
                        <td>
                            {{ $member->name }}
                            <span class="text-muted">{{ $member->rank->abbreviation }}</span>
                        </td>
                        <td>
                            <div class="{{ $member->leaveOfAbsence->expired ? 'text-danger' : null }}">
                                {{ $member->leaveOfAbsence->end_date->format('Y-m-d') }}
                                <span class="text-muted">
                                    {{ $member->leaveOfAbsence->end_date->diffForHumans() }}
                                </span>
                            </div>
                        </td>
                        <td>
                            {{ $member->leaveOfAbsence->type or "None" }}
                        </td>
                        <td>
                            {{ $member->leaveOfAbsence->note->body }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <p class="text-muted">No active leaves of absence</p>
        @endif
    </div>
@stop
