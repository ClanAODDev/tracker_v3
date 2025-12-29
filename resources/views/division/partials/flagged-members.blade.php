@if (count($flaggedMembers) > 0)
    <div class="table-responsive">
        <table class="table inactive-table flagged-table">
            <thead>
            <tr>
                <th class="inactive-bulk-col" style="display: none;"><input type="checkbox" class="flagged-select-all" title="Select All"></th>
                <th>Member</th>
                <th>Last Voice Activity</th>
                <th>Reminded</th>
                <th>{{ $division->locality('platoon') }} / Squad</th>
                <th class="text-right">Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($flaggedMembers as $member)
                <tr>
                    <td class="inactive-bulk-col" style="display: none;"><input type="checkbox" class="flagged-member-checkbox" value="{{ $member->clan_id }}"></td>
                    <td>
                        <a href="{{ route('member', $member->getUrlParams()) }}" class="inactive-member-link">
                            <span class="inactive-member-name">{{ $member->name }}</span>
                            <span class="inactive-member-rank">{{ $member->rank->getAbbreviation() }}</span>
                        </a>
                    </td>
                    <td data-order="{{ $member->last_voice_activity?->timestamp ?? 0 }}">
                        <span class="inactive-time">
                            {{ $member->present()->lastActive('last_voice_activity', skipUnits: ['weeks','months']) }}
                        </span>
                    </td>
                    <td data-order="{{ $member->last_activity_reminder_at?->timestamp ?? 0 }}">
                        @if($member->last_activity_reminder_at)
                            <span class="inactive-time" title="{{ $member->last_activity_reminder_at->format('M j, Y') }}">
                                {{ $member->last_activity_reminder_at->diffForHumans(short: true) }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
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
                            @can('flag-inactive', $member)
                                <a href="{{ route('member.unflag-inactive', $member->clan_id) }}"
                                   class="btn btn-sm btn-warning"
                                   title="Unflag Member">
                                    <i class="fa fa-flag"></i> Unflag
                                </a>
                            @endcan
                            @can('separate', $member)
                                <form action="{{ route('member.drop-for-inactivity', [$member->clan_id]) }}"
                                      method="post"
                                      class="d-inline">
                                    @method('delete')
                                    @csrf
                                    <input type="hidden" value="Member removed for inactivity" name="removal_reason"/>
                                    <button type="submit"
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure you want to remove {{ addslashes($member->name) }} from AOD?')">
                                        <i class="fa fa-trash"></i> Remove
                                    </button>
                                </form>
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
        <i class="fa fa-flag-o"></i>
        <h4>No Flagged Members</h4>
        <p>There are currently no members flagged for removal.</p>
    </div>
@endif
