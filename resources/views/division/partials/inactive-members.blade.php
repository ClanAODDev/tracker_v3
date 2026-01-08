@if (count(($type === 'discord') ? $inactiveDiscordMembers : $inactiveTSMembers))
    <div class="table-responsive">
        <table class="table inactive-table">
            <thead>
            <tr>
                <th class="inactive-bulk-col"><input type="checkbox" class="inactive-select-all" title="Select All"></th>
                <th>Member</th>
                <th>Last Voice Activity</th>
                <th>Reminded</th>
                <th>Status</th>
                <th>{{ $division->locality('platoon') }} / Squad</th>
                <th class="text-right">Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach (($type === 'discord') ? $inactiveDiscordMembers : $inactiveTSMembers as $member)
                @php
                    $daysSince = $member->last_voice_activity ? $member->last_voice_activity->diffInDays(now()) : null;
                    $severityClass = '';
                    if ($daysSince === null || $daysSince >= $division->settings()->inactivity_days * 2) {
                        $severityClass = 'inactive-row--severe';
                    } elseif ($daysSince >= $division->settings()->inactivity_days * 1.5) {
                        $severityClass = 'inactive-row--warning';
                    }
                    $remindedToday = $member->last_activity_reminder_at?->isToday();
                @endphp
                <tr class="{{ $severityClass }}">
                    <td class="inactive-bulk-col"><input type="checkbox" class="inactive-member-checkbox" value="{{ $member->clan_id }}"></td>
                    <td>
                        <a href="{{ route('member', $member->getUrlParams()) }}" class="inactive-member-link">
                            <span class="inactive-member-name">{{ $member->name }}</span>
                            <span class="inactive-member-rank">{{ $member->rank->getAbbreviation() }}</span>
                        </a>
                    </td>
                    <td data-order="{{ $member->last_voice_activity->timestamp ?? 0 }}">
                        <span class="inactive-time">
                            {{ $member->present()->lastActive('last_voice_activity', skipUnits: ['weeks','months']) }}
                        </span>
                    </td>
                    <td data-order="{{ $member->last_activity_reminder_at?->format('Y-m-d') ?? '0000-00-00' }}">
                        <button type="button"
                                class="btn btn-sm activity-reminder-toggle {{ $remindedToday ? 'btn-default' : 'btn-success' }}"
                                data-member-id="{{ $member->clan_id }}"
                                title="{{ $member->last_activity_reminder_at ? 'Reminded ' . $member->last_activity_reminder_at->diffForHumans() : 'Not reminded' }}"
                                {{ $remindedToday ? 'disabled' : '' }}>
                            <i class="fa fa-bell"></i>
                            @if($member->last_activity_reminder_at)
                                <span class="reminded-date">{{ $member->last_activity_reminder_at->format('n/j/y') }}</span>
                            @endif
                        </button>
                    </td>
                    <td>
                        <span class="inactive-status" title="{{ $member->last_voice_status?->getDescription() }}">
                            {{ $member->last_voice_status?->getLabel() ?? 'Unknown' }}
                        </span>
                    </td>
                    <td>
                        <span class="inactive-unit">
                            {{ $member->platoon->name ?? 'Unassigned' }}
                            @if($member->squad)
                                / {{ $member->squad->name }}
                            @endif
                        </span>
                    </td>
                    <td class="text-right">
                        <div class="inactive-actions">
                            <a href="{{ doForumFunction([$member->clan_id,], 'pm') }}"
                               target="_blank"
                               class="btn btn-sm btn-default"
                               title="Send Forum PM">
                                <i class="fa fa-envelope"></i>
                            </a>
                            @can('flag-inactive', \App\Models\Member::class)
                                <a href="{{ route('member.flag-inactive', $member->clan_id) }}"
                                   class="btn btn-sm btn-warning"
                                   title="Flag for Removal">
                                    <i class="fa fa-flag"></i>
                                </a>
                            @endcan
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="inactive-empty">
        <i class="fa fa-check-circle"></i>
        <h4>No Inactive Members</h4>
        <p>All members are within the activity threshold, or no members match the selected filter.</p>
    </div>
@endif
