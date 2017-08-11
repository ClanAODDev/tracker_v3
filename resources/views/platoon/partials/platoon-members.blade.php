<div class='panel-body border-bottom'>
    <div id='playerFilter'></div>
</div>
<div class="table-responsive">

    <table class='table table-hover members-table'>
        <thead>
        <tr>
            <th class='col-hidden'><strong>Rank Id</strong></th>
            <th class='col-hidden'><strong>Last Login Date</strong></th>
            <th><strong>Member</strong></th>
            <th class='no-search text-center'><strong>Rank</strong></th>
            <th class='text-center hidden-xs hidden-sm'><strong>Joined</strong></th>
            <th class='text-center'><strong>Forum Activity</strong></th>
            <th class='text-center no-sort'><strong>TS Activity</strong></th>
            <th class='text-center'>
                <string>Last Promoted</string>
            </th>
        </tr>
        </thead>

        <tbody>

        @foreach($members as $member)
            <tr role="row" style="cursor:pointer;"
                onclick="window.location.href = '{{ route('member', $member->clan_id) }}';">
                <td class="col-hidden">{{ $member->rank_id }}</td>
                <td class="col-hidden">{{ $member->last_activity }}</td>
                <td>{!! $member->present()->nameWithIcon !!}</td>
                <td class="text-center">{{ $member->rank->abbreviation }}</td>
                <td class="text-center hidden-xs hidden-sm">{{ $member->join_date }}</td>
                <td class="text-center">
                    <span class="{{ getActivityClass($member->last_activity, $division) }}">{{ $member->present()->lastActive }}</span>
                </td>
                <td class="text-center">
                    @if ($member->tsInvalid)
                        <span class="text-danger">{{ $member->tsInvalid }}</span>
                    @else
                        {{ Carbon::parse($member->last_ts_activity)->diffForHumans() }}
                    @endif
                </td>
                <td class="text-center">{{ $member->last_promoted }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

