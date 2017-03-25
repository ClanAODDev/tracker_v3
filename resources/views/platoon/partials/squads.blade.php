@forelse($squads as $squad)

    <div class="m-b-xxl">

        <div class="panel squad">
            <div class="panel-body">
                <h5 class="text-center">
                    <span class="pull-left">
                        @if($squad->leader)
                            {!! $squad->leader->present()->rankName !!}
                        @else
                            Leader TBA
                        @endif
                    </span>

                    <strong>
                        {{ ($squad->name) ?: $division->locality('squad') . " " . $loop->index }}
                    </strong>

                    <span class="pull-right">
                       @can('update', $squad)
                            <a href="{{ route('editSquad', [$division->abbreviation, $platoon, $squad]) }}"
                               title="Edit {{ $division->locality('squad') }}"
                               class="btn btn-default btn-sm">
                            <i class="fa fa-wrench"></i>
                        </a>
                        @endcan
                    </span>
                </h5>
            </div>

            <div class="table-responsive">

                <table class="table table-hover members-table">
                    <thead>
                    <tr>
                        <th class='col-hidden'><strong>Rank Id</strong></th>
                        <th class='col-hidden'><strong>Last Login Date</strong></th>
                        <th><strong>Member</strong></th>
                        <th class='nosearch text-center'><strong>Rank</strong></th>
                        <th class='text-center hidden-xs hidden-sm'><strong>Joined</strong></th>
                        <th class='text-center'><strong>Last Activity</strong></th>
                        <th class='text-center'>
                            <string>Last Promoted</string>
                        </th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($squad->members as $member)
                        <tr>
                            <td class="col-hidden">{{ $member->rank_id }}</td>
                            <td class="col-hidden">{{ $member->last_activity }}</td>
                            <td class="">{!! $member->present()->nameWithIcon !!} <a
                                        href="{{ route('member', $member->clan_id) }}"><i
                                            class="fa fa-search text-muted pull-right" title="View profile"></i></a>
                            </td>
                            <td class="text-center">{{ $member->rank->abbreviation }}</td>
                            <td class="text-center hidden-xs hidden-sm">{{ $member->join_date }}</td>
                            <td class="text-center">
                                <span class="{{ getActivityClass($member->last_activity, $division) }}">{{ $member->present()->lastActive }}</span>
                            </td>
                            <td class="text-center">{{ $member->last_promoted }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@empty
    <div class="panel-body">
        <div class="row">
            <div class="col-xs-10">
                <h4>No squads</h4>
                <p>No squads exist. Do you want to create one?
                </p>
            </div>
            <div class="col-xs-2">
                <a href="{{ route('createSquad', [$division->abbreviation, $platoon]) }}"
                   class="btn btn-default pull-right text-uppercase">
                    <i class="fa fa-plus"></i> New
                </a>
            </div>
        </div>

    </div>
@endforelse
