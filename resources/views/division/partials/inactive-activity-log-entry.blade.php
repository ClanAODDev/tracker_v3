<div class="inactive-activity-item">
    <div class="inactive-activity-icon">
        @if ($activity->name == 'unflagged_member')
            <i class="far fa-flag"></i>
        @elseif ($activity->name == 'flagged_member')
            <i class="fa fa-flag text-warning"></i>
        @else
            <i class="fa fa-trash text-danger"></i>
        @endif
    </div>
    <div class="inactive-activity-content">
        <span class="inactive-activity-user">{{ $activity->user?->name ?? 'Unknown' }}</span>
        @if ($activity->name == 'unflagged_member')
            unflagged
        @elseif ($activity->name == 'flagged_member')
            flagged
        @else
            removed
        @endif
        <span class="inactive-activity-subject">{{ $activity->subject->name }}</span>
    </div>
    <div class="inactive-activity-time">
        {{ $activity->created_at->diffForHumans() }}
    </div>
</div>
