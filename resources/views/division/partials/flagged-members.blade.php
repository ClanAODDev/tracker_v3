@if (count($flaggedMembers) > 0)
    <table class="table table-hover adv-datatable">
        <thead>
        <tr>
            <th>Member</th>
            <th>
                {{ $inactivityMetric === 'last_voice_activity' ? 'Last Discord Voice Activity' : 'Last TS Activity' }}
                <small class="text-muted">Days</small></th>
            </th>
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
                <td>
                    @if ($inactivityMetric === 'last_ts_activity')
                        @if ($member->tsInvalid)
                            <code title="Misconfiguration"><span class="text-danger">00000</span></code>
                        @else
                            <code>{{  $member->present()->lastActive('last_ts_activity', skipUnits: ['weeks', 'months']) }}</code>
                        @endif
                    @elseif($inactivityMetric === 'last_voice_activity')
                        <code>{{ $member->present()->lastActive('last_voice_activity', skipUnits: ['weeks','months']) }}</code>
                    @endif
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
                            {!! Form::model($member, ['method' => 'delete', 'route' => ['member.drop-for-inactivity', $member->clan_id]]) !!}
                            <input type="hidden" value="Member removed for inactivity" name="removal_reason" />
                            <button type="submit" class="btn btn-danger btn-sm remove-member"
                                    data-member-id="{{ $member->clan_id }}">
                                <i class="fa fa-trash text-danger"></i> Remove
                            </button>
                            {!! Form::close() !!}
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


