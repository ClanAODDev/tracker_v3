@if (count($flaggedMembers) > 0)
    <table class="table table-hover adv-datatable">
        <thead>
        <tr>
            <th>Member</th>
            <th>Last TS Activity</th>
            <th>Last Discord Voice Activity</th>
            <th class="no-sort"></th>
            <th class="no-sort"></th>
        </tr>
        </thead>
        <tbody>
        @foreach ($flaggedMembers as $member)
            <tr>
                <td>
                    <a href="{{ route('member', $member->getUrlParams()) }}"><i class="fa fa-search"></i></a>
                    {{ $member->name }}
                    <span class="text-muted slight">{{ $member->rank->abbreviation }}</span>
                </td>
                <td data-order="{{ $member->last_ts_activity?->timestamp }}">
                    @if ($member->tsInvalid)
                        <code title="Misconfiguration"><span class="text-danger">00000</span></code>
                    @else
                        <code>{{  $member->present()->lastActive('last_ts_activity', skipUnits: ['weeks', 'months']) }}</code>
                    @endif
                </td>
                <td data-order="{{ $member->last_voice_activity?->timestamp }}">
                    <code>{{ $member->present()->lastActive('last_voice_activity', skipUnits: ['weeks','months']) }}</code>

                </td>
                <td>
                    @can ('update', $member)
                        <a href="{{ route('member.unflag-inactive', $member->clan_id) . "#flagged" }}"
                           class="btn btn-warning btn-sm">
                            <i class="fa fa-flag"></i>
                            Unflag
                        </a>
                    @endcan
                </td>
                <td>
                    <div class="btn-group-xs">
                        @can ('delete', $member)
                            <form action="{{ route('member.drop-for-inactivity', [$member->clan_id]) }}" method="post">
                                @method('delete')
                                @csrf
                                <input type="hidden" value="Member removed for inactivity" name="removal_reason"/>
                                <button type="submit" class="btn btn-danger btn-sm remove-member"
                                        data-member-id="{{ $member->clan_id }}">
                                    <i class="fa fa-trash text-danger"></i> Remove
                                </button>
                            </form>
                        @endcan
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <p>There are currently no members flagged for removal.</p>
@endif


