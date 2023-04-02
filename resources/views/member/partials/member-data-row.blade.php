<tr role="row" class="{{ ($member->leave) ? 'text-muted' : null }}">
    <td class="col-hidden">{{ $member->rank_id }}</td>
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

        {!! $member->present()->nameWithIcon !!}

        <span class="pull-right" title="View Profile">
            <a href="{{ route('member', $member->getUrlParams()) }}" class="btn btn-default btn-xs"><i
                        class="fa fa-search text-accent"></i></a>
        </span>

    </td>
    <td class="text-center">{{ $member->rank->abbreviation }}</td>
    <td class="text-center hidden-xs hidden-sm">{{ $member->join_date }}</td>
    <td class="col-hidden">
                    <span class="{{ getActivityClass($member->last_activity, $division) }}">
                        {{ $member->present()->lastActive($member->last_activity) }}
                    </span>
    </td>
    <td class="text-center">
        @if ($member->tsInvalid)
            <span class="text-danger">MISCONFIGURATION</span>
        @else
            <span class="{{ getActivityClass(Carbon::parse($member->last_ts_activity), $division) }}">
                        {{ $member->present()->lastActive($member->last_ts_activity) }}
                    </span>
        @endif
    </td>
    <td class="col-hidden">{{ $member->last_ts_activity }}</td>
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
</tr>