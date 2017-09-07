<tr>
    <td>
        @if ($activity->name == 'unflagged_member')
            <i class="fa fa-flag-o"></i> {{ $activity->user->name }} unflagged {{ $activity->subject->name }}
        @elseif ($activity->name == 'flagged_member')
            <i class="fa fa-flag text-warning"></i> {{ $activity->user->name }} flagged {{ $activity->subject->name }} for removal
        @else
            <i class="fa fa-trash text-danger"></i> {{ $activity->user->name }} removed {{ $activity->subject->name }}
        @endif
        <span class="badge text-muted">{{ $activity->created_at->diffForHumans() }}</span>
    </td>
    <td>
        {{ $activity->created_at->format('Y-m-d') }}
    </td>
</tr>