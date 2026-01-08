@php
    $visibleMemberTags = $member->tags->filter(fn ($tag) => $tag->isVisibleTo());
@endphp
<tr role="row" class="{{ ($member->leave) ? 'text-muted' : null }}">
    <td class="bulk-select-col"><input type="checkbox" class="member-checkbox" value="{{ $member->clan_id }}" data-parttimer="{{ isset($division) && $member->division_id !== $division->id ? '1' : '0' }}"></td>
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
        @if (isset($division) && $member->division_id !== $division->id)
            <span class="text-info" title="Part-Timer (Primary: {{ $member->division?->name ?? 'None' }})"><i
                        class="fa fa-clock"></i></span>
        @endif
        <span class="pull-right" title="View Profile">
            <a href="{{ route('member', $member->getUrlParams()) }}" class="btn btn-accent btn-xs"><i
                        class="fa fa-search"></i></a>
        </span>
    </td>
    <td class="text-center hidden-xs">{{ $member->rank->getAbbreviation() }}</td>
    @if(isset($platoon))
        <td class="text-center hidden-xs hidden-sm">
            @if($member->squad)
                <a href="{{ route('squad.show', [$division->slug, $platoon, $member->squad]) }}" class="text-muted">
                    {{ $member->squad->name ?? 'Untitled' }}
                </a>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
    @else
        <td class="text-center hidden-xs hidden-sm">
            @if($member->platoon)
                <a href="{{ route('platoon', [$division->slug, $member->platoon]) }}" class="text-muted">
                    {{ $member->platoon->name ?? 'Untitled' }}
                </a>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
    @endif
    <td class="text-center hidden-xs hidden-sm">{{ $member->join_date }}</td>
    <td class="text-center hidden-xs">
                    <span class="{{ getActivityClass($member->last_voice_activity, $division) }}"
                          title="{{$member->last_voice_activity}}">
                        {{ $member->present()->lastActive('last_voice_activity', skipUnits: ['weeks', 'months']) }}
                    </span>
    </td>
    <td class="text-center hidden-xs">{{ $member->last_promoted_at ?? 'Never' }}</td>
    @if(!auth()->user()->isRole('member'))
        @php
            $remindedToday = $member->last_activity_reminder_at?->isToday();
        @endphp
        <td class="text-center hidden-xs hidden-sm">
            <button type="button"
                    class="btn btn-xs activity-reminder-toggle {{ $remindedToday ? 'btn-default' : 'btn-success' }}"
                    data-member-id="{{ $member->clan_id }}"
                    title="{{ $member->last_activity_reminder_at ? 'Reminded ' . $member->last_activity_reminder_at->diffForHumans() : 'Not reminded' }}"
                    {{ $remindedToday ? 'disabled' : '' }}>
                <i class="fa fa-bell"></i>
                @if($member->last_activity_reminder_at)
                    <span class="reminded-date">{{ $member->last_activity_reminder_at->format('n/j/y') }}</span>
                @endif
            </button>
        </td>
    @endif
    <td class="hidden-xs hidden-sm table-tags-cell">
        @foreach($visibleMemberTags as $tag)
            <span class="badge table-tag tag-visibility-{{ $tag->visibility->value }}"
                  title="{{ $tag->division?->name ?? 'Global' }}">{{ $tag->name }}</span>
        @endforeach
    </td>
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
    <td class="col-hidden">{{ $member->tags->pluck('id')->join(',') }}</td>
    @if(!auth()->user()->isRole('member'))
        <td class="col-hidden">{{ $member->last_activity_reminder_at?->format('Y-m-d') ?? '0000-00-00' }}</td>
    @endif
</tr>