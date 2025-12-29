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
                    @foreach($recentActivity as $group)
                        @php
                            $type = $group['type'];
                            $events = $group['events'];
                            $count = $events->count();
                            $isGrouped = $count > 1;
                        @endphp
                        <div class="activity-feed-item">
                            <span class="activity-feed-icon">
                                <i class="{{ $type->feedIcon() }}"></i>
                            </span>
                            <span class="activity-feed-content">
                                @if($isGrouped)
                                    <a href="#" class="activity-group-toggle" data-toggle="collapse" data-target="#activity-group-{{ $loop->index }}">
                                        <strong>{{ $count }}</strong>&nbsp;members {{ $type->feedDescription() }}
                                        <i class="fa fa-chevron-down fa-xs"></i>
                                    </a>
                                    <div id="activity-group-{{ $loop->index }}" class="collapse activity-group-members">
                                        @foreach($events as $event)
                                            @php
                                                $member = $event->subject;
                                            @endphp
                                            <div class="activity-group-member">
                                                @if($member)
                                                    <a href="{{ route('member', $member->getUrlParams()) }}">{{ $member->name }}</a>
                                                @else
                                                    <span class="text-muted">Unknown</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    @php
                                        $member = $events->first()->subject;
                                        $memberLink = $member
                                            ? '<a href="' . route('member', $member->getUrlParams()) . '">' . e($member->name) . '</a>'
                                            : '<span class="text-muted">Unknown</span>';
                                    @endphp
                                    {!! $memberLink !!} {{ $type->feedDescription() }}
                                @endif
                            </span>
                            <span class="activity-feed-time">{{ $group['created_at']->diffForHumans(short: true) }}</span>
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
