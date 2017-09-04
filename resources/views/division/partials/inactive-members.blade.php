@if (count($inactiveMembers))
    <div class="table-responsive">
        <table class="table adv-datatable table-hover">
            <thead>
            <tr>
                <th>Member Name</th>
                <th>Last Forum Activity
                    <small class="slight">Days</small>
                </th>
                <th>Last TS Activity
                    <small class="slight">Days</small>
                </th>
                <th>Squad</th>
                <th class="no-sort"></th>
                <th class="no-sort"></th>
            </tr>
            </thead>
            <tbody class="sortable">
            @foreach ($inactiveMembers as $member)
                <tr>
                    <td>
                        <a href="{{ route('member', $member->getUrlParams()) }}"><i class="fa fa-search"></i></a>
                        {{ $member->name }}
                        <span class="text-muted slight">{{ $member->rank->abbreviation }}</span>
                    </td>
                    <td>
                        <code>{{ $member->last_activity->diffInDays() }}</code>
                    </td>
                    <td>
                        @if ($member->tsInvalid)
                            <code title="Misconfiguration"><span class="text-danger">00000</span></code>
                        @else
                            <code>{!! Carbon::parse($member->last_ts_activity)->diffInDays() !!}</code>
                        @endif
                    </td>
                    <td>{{ $member->squad->name or "Untitled" }}</td>
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
    </div>
@else
    <h4><i class="fa fa-times-circle-o text-danger"></i> No Inactive Members</h4>
    <p>Either there are no inactive members, or no members match the criteria you provided.</p>
@endif