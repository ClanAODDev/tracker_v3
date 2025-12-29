<div class="modal fade" id="activityFeedModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title">
                    <i class="fa fa-history text-accent"></i> Recent Activity
                </h4>
            </div>
            <div class="modal-body">
                <div class="activity-feed">
                    @foreach($recentActivity as $event)
                        @php
                            $member = $event->subject;
                            $memberLink = $member
                                ? '<a href="' . route('member', $member->getUrlParams()) . '">' . e($member->name) . '</a>'
                                : '<span class="text-muted">Unknown</span>';
                        @endphp
                        <div class="activity-feed-item">
                            <span class="activity-feed-icon">
                                <i class="{{ $event->name->feedIcon() }}"></i>
                            </span>
                            <span class="activity-feed-content">
                                {!! $memberLink !!} {{ $event->name->feedDescription() }}
                            </span>
                            <span class="activity-feed-time">{{ $event->created_at->diffForHumans(short: true) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            @if(auth()->user()->isRole(['sr_ldr', 'admin']))
                <div class="modal-footer">
                    <a href="{{ route('filament.mod.resources.activities.index') }}" class="btn btn-default">
                        <i class="fa fa-list"></i> View All Activity
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
