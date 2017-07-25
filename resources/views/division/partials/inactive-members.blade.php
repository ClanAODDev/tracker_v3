@if (count($inactive))
    <table class="table adv-datatable table-hover">
        <thead>
        <tr>
            <th>Member Name</th>
            <th>Last Seen <small class="text-muted">Days ago</small></th>
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
                    <a href="#" class="btn btn-warning btn-xs">
                        <i class="fa fa-flag"></i>
                        Flag
                    </a>

                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <h4><i class="fa fa-times-circle-o text-danger"></i> No Inactive Members</h4>
    <p>Either there are no inactive members, or no members match the criteria you provided.</p>
@endif