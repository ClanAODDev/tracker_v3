@extends('application.base')

@section('content')

    @component ('application.components.division-heading', [$division])
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-large" />
        @endslot
        @slot ('heading')
            <span class="hidden-xs">Inactive Members</span>
            <span class="visible-xs">{{ $division->abbreviation }}</span>
        @endslot
        @slot ('subheading')
            {{ $division->description }}
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('inactive-members', $division) !!}

        <p>Members listed here have activity that has reached or exceeded the number of days defined by the division leadership. Use this page to attempt to communicate with inactive members, and also to process their removal from the clan.</p>

        <p>{{ $division->name }} division inactivity set to
            <code>{{ $division->settings()->inactivity_days }} days</code>
        </p>

        <hr />

        <div class="panel panel-filled m-b-xl panel-c-info">
            <div class="panel-body">
                <div class="row m-t-xs">
                    <form method="get">
                        <div class="col-md-3">
                            <h4>Filter by {{ $division->locality('platoon') }}</h4>
                        </div>
                        <div class="col-md-4">
                            <select name="platoon" id="platoon" class="form-control">
                                <option value="0" selected>Select a {{ $division->locality('platoon') }}</option>
                                @foreach ($division->platoons as $platoon)
                                    <option value="{{ $platoon->id }}" {{ ($request->platoon == $platoon->id ? 'selected' : null) }}>{{ $platoon->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 text-right">
                            <a href="{{ route('division.inactive-members', $division->abbreviation) }}"
                               class="btn btn-default">Reset</a>
                            <button class="btn btn-success">Submit</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>

        @if (count($inactive))
            <table class="table adv-datatable table-hover">
                <thead>
                <tr>
                    <th>Member Name</th>
                    <th>Last Seen (days ago)</th>
                    <th class="no-sort"></th>
                </tr>
                </thead>
                <tbody class="sortable">
                @foreach ($inactive as $member)
                    <tr>
                        <td>
                            <a href="{{ route('member', $member->clan_id) }}"><i class="fa fa-search"></i></a>
                            {{ $member->name }}
                            <span class="text-muted slight">{{ $member->rank->abbreviation }}</span>
                        </td>
                        <td>
                            <code>{{ $member->last_activity->diffInDays() }}</code>
                        </td>
                        <td class="text-center">
                            @if (! $member->flaggedForInactivity)
                                <a href="#" class="btn btn-warning btn-xs">
                                    <i class="fa fa-flag"></i>
                                    Flag
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <h4><i class="fa fa-times-circle-o text-danger"></i> No Inactive Members</h4>
            <p>Either there are no inactive members, or no members match the criteria you provided.</p>
        @endif

        @foreach ($flagged as $member)
            <li>
                {{ $member->name }}
                @can ('delete', App\Member::class)
                    {{-- TODO - add delete form to inactive members --}}
                    <a class="btn btn-danger"
                       href="{{ route('deleteMember', $member->clan_id) }}">
                        <i class="fa fa-trash text-danger"></i>
                    </a>
                @endcan
            </li>
        @endforeach

    </div>
@stop
