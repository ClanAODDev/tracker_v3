@if (count(($type === 'discord') ? $inactiveDiscordMembers : $inactiveTSMembers))
    <div class="table-responsive">
        <table class="table adv-datatable table-hover">
            <thead>
            <tr>
                <th>Member Name</th>
                <th>Last Discord Voice Activity</th>
                <th>Discord State</th>
                <th>Squad</th>
                <th class="no-sort"></th>
                <th class="no-sort"></th>
            </tr>
            </thead>
            <tbody class="sortable">
            @foreach (($type === 'discord') ? $inactiveDiscordMembers : $inactiveTSMembers as $member)
                <tr>
                    <td>
                        <a href="{{ route('member', $member->getUrlParams()) }}"><i class="fa fa-search"></i></a>
                        {{ $member->name }}
                        <span class="text-muted slight">{{ $member->rank->getAbbreviation() }}</span>
                    </td>
                    <td data-order="{{ $member->last_voice_activity->timestamp ?? null }}">
                        <code>{{ $member->present()->lastActive('last_voice_activity', skipUnits: ['weeks','months']) }}</code>
                    </td>
                    <td title="{{ $member->last_voice_status?->getDescription() }}">
                        <code>{{ $member->last_voice_status?->getLabel() ?? 'Unknown' }}</code>
                    </td>
                    <td>{{ $member->squad->name ?? "Untitled" }}</td>
                    <td>
                        <a href="{{ doForumFunction([$member->clan_id,], 'pm') }}" target="_blank"
                           class="btn btn-default btn-sm">Forum PM</a>
                    </td>
                    <td class="text-center">
                        {{-- @TODO: create separate permission for inactive flagging --}}
                        @can ('update', $member)
                            <a href="{{ route('member.flag-inactive', $member->clan_id) }}"
                               class="btn btn-warning btn-sm">
                                <i class="fa fa-flag"></i>
                                Flag
                            </a>
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