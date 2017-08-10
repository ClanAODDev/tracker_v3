@if (count($inactiveMembers))
    <table class="table adv-datatable table-hover">
        <thead>
        <tr>
            <th>Member Name</th>
            <th>Last Forum Activity <small class="slight">Days</small></th>
            <th>Last TS Activity <small class="slight">Days</small></th>
            <th class="no-sort"></th>
            <th class="no-sort"></th>
        </tr>
        </thead>
        <tbody class="sortable">
        @foreach ($inactiveMembers as $member)
            <tr>
                <td>
                    <a href="{{ route('member', $member->clan_id) }}"><i class="fa fa-search"></i></a>
                    {{ $member->name }}
                    <span class="text-muted slight">{{ $member->rank->abbreviation }}</span>
                </td>
                <td class="text-center">
                    <code>{{ $member->last_activity->diffInDays() }}</code>
                </td>
                <td class="text-center">
                    <code>{!! $member->last_ts_activity !!}</code>
                </td>
                <td>
                    <a href="{{ doForumFunction([$member->clan_id,], 'pm') }}" target="_blank"
                       class="btn btn-default btn-sm">Forum PM</a>
                </td>
                <td class="text-center">
                    @can ('update', $member)
                        <a href="{{ route('member.flag-inactive', $member->clan_id) }}"
                           class="btn btn-warning btn-sm">
                            <i class="fa fa-flag"></i>
                            Flag
                        </a>
                    @else
                        <span class="text-muted">No available actions</span>
                    @endcan
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <h4><i class="fa fa-times-circle-o text-danger"></i> No Inactive Members</h4>
    <p>Either there are no inactiveMembers members, or no members match the criteria you provided.</p>
@endif