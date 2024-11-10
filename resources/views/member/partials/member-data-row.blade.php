<tr role="row" class="{{ ($member->leave) ? 'text-muted' : null }}">
    <td class="col-hidden">{{ $member->rank }}</td>
    <td class="col-hidden">{{ $member->last_activity }}</td>
    <td>
        @if (isset($squadView) && $squadView)
            @if ($squad->leader && $squad->leader->clan_id == $member->recruiter_id)
                <strong style="color: magenta;" title="Direct Recruit"><i
                            class="fa fa-asterisk"></i></strong>
            @endif
        @endif
        @if ($member->leave)
            <span style="color: lightslategrey" title="On Leave"><i class="fa fa-asterisk"></i></span>
        @endif
        {!! $member->present()->coloredName !!}
        <span class="pull-right" title="View Profile">
            <a href="{{ route('member', $member->getUrlParams()) }}" class="btn btn-default btn-xs"><i
                        class="fa fa-search text-accent"></i></a>
        </span>
    </td>
    <td class="text-center">{{ $member->rank->getAbbreviation() }}</td>
    <td class="text-center hidden-xs hidden-sm">{{ $member->join_date }}</td>
    <td class="text-center">
                    <span class="{{ getActivityClass($member->last_voice_activity, $division) }}"
                          title="{{$member->last_voice_activity}}">
                        {{ $member->present()->lastActive('last_voice_activity', skipUnits: ['weeks', 'months']) }}
                    </span>
    </td>
    <td class="text-center">{{ $member->last_promoted_at ?? 'Never' }}</td>
    <td class="col-hidden">
        @if ($member->handle)
            @if ($member->handle->url)
                <a href="{{ $member->handle->full_url }}">
                    {{ $member->handle->pivot->value }}
                </a>
            @else
                <code>{{ $member->handle->pivot->value }}</code>
            @endif
        @else
            <span class="text-danger">N/A</span>
        @endif
    </td>
    <td class="col-hidden">
        {{ $member->posts }}
    </td>
    <td class="col-hidden">
        {{ $member->clan_id }}
    </td>
    <td class="col-hidden">{{ $member->last_voice_activity }}</td>
</tr>