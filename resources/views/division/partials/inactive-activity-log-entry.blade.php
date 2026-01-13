<div class="inactive-activity-item">
    <div class="inactive-activity-icon">
        <i class="{{ $activity->name->feedIcon() }}"></i>
    </div>
    <div class="inactive-activity-content">
        <span class="inactive-activity-user">{{ $activity->user?->name ?? 'Unknown' }}</span>
        @switch($activity->name)
            @case(App\Enums\ActivityType::FLAGGED)
                flagged <span class="inactive-activity-subject">{{ $activity->subject->name }}</span> for inactivity
                @break
            @case(App\Enums\ActivityType::UNFLAGGED)
                unflagged <span class="inactive-activity-subject">{{ $activity->subject->name }}</span>
                @break
            @case(App\Enums\ActivityType::REMOVED)
                removed <span class="inactive-activity-subject">{{ $activity->subject->name }}</span>
                @break
        @endswitch
    </div>
    <div class="inactive-activity-time">
        {{ $activity->created_at->diffForHumans() }}
    </div>
</div>
