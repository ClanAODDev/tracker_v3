@if (count($inactiveMembers))
    <table class="table adv-datatable table-hover">
        <thead>
        <tr>
            <th>Member Name</th>
            <th>Last Seen
                <small class="text-muted">Days</small>
            </th>
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
                <td>
                    <code>{{ $member->last_activity->diffInDays() }}</code>
                </td>
                <td class="text-center">
                    @can ('update', $member)
                        <a href="{{ route('member.flag-inactive', $member->clan_id) }}"
                           class="btn btn-warning">
                            <i class="fa fa-flag btn-sm"></i>
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