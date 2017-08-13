<tr>
    <td>
        @if ($activity->name == 'unflagged_member')
            <i class="fa fa-flag-o"></i> {{ $activity->user->name }} unflagged {{ $activity->subject->name }}
        @elseif ($activity->name == 'flagged_member')
            <i class="fa fa-flag text-danger"></i> {{ $activity->user->name }} flagged {{ $activity->subject->name }} for removal
        @endif
        <span class="badge">{{ $activity->created_at->diffForHumans() }}</span>
    </td>
    <td>
        {{ $activity->created_at->format('Y-m-d') }}
    </td>
</tr>