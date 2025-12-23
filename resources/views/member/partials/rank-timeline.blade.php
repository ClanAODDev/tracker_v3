@if($rankTimeline->hasHistory)
    <div class="rank-timeline-wrapper">
        <div class="rank-timeline">
            <div class="timeline-track"></div>

            @foreach($rankTimeline->nodes as $index => $node)
                @if($node->type === 'join')
                    <div class="timeline-node timeline-start timeline-{{ $node->position }}">
                        <div class="timeline-marker marker-join"></div>
                        <div class="timeline-content">
                            <div class="timeline-date">{{ $node->date }}</div>
                            <div class="timeline-label">{{ $node->label }}</div>
                        </div>
                    </div>
                @elseif($node->type === 'consolidated')
                    <div class="timeline-duration-connector">{{ $rankTimeline->nodes[$index - 1]->duration ?? '' }}</div>
                    <div class="timeline-node timeline-{{ $node->position }}">
                        <div class="timeline-marker marker-promotion"></div>
                        <div class="timeline-content">
                            <div class="timeline-rank timeline-rank-consolidated">{{ $node->label }}</div>
                            <div class="timeline-date">{{ $node->dateRange }}</div>
                        </div>
                    </div>
                @elseif($node->type === 'promotion')
                    <div class="timeline-duration-connector">{{ $rankTimeline->nodes[$index - 1]->duration ?? '' }}</div>
                    <div class="timeline-node timeline-{{ $node->position }}">
                        <div class="timeline-marker marker-promotion"></div>
                        <div class="timeline-content">
                            <div class="timeline-rank">{{ $node->rank }}</div>
                            <div class="timeline-date">{{ $node->date }}</div>
                        </div>
                    </div>
                @elseif($node->type === 'current')
                    <div class="timeline-duration-connector">{{ $rankTimeline->nodes[$index - 1]->duration ?? '' }}</div>
                    <div class="timeline-node timeline-current timeline-{{ $node->position }}">
                        <div class="timeline-marker marker-current"></div>
                        <div class="timeline-content">
                            <div class="timeline-rank current">{{ $node->rank }}</div>
                            <div class="timeline-label">{{ $node->label }}</div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    @if($rankTimeline->historyItems->count() > 1)
        <div class="modal fade" id="rank-history-modal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        <h4 class="modal-title">Rank History</h4>
                    </div>
                    <div class="modal-body">
                        <ul class="rank-history-list">
                            @foreach($rankTimeline->historyItems as $item)
                                @if($item->type === 'join')
                                    <li class="rank-history-item rank-history-join">
                                        <span class="rank-history-date">{{ $item->date }}</span>
                                        <span class="rank-history-label">{{ $item->label }}</span>
                                    </li>
                                @else
                                    <li class="rank-history-item {{ $item->type === 'demotion' ? 'rank-history-demotion' : 'rank-history-promotion' }}">
                                        <span class="rank-history-date">{{ $item->date }}</span>
                                        <span class="rank-history-rank">{{ $item->rank }}</span>
                                        @if($item->type === 'demotion')
                                            <i class="fa fa-arrow-down text-danger" title="Demotion"></i>
                                        @else
                                            <i class="fa fa-arrow-up text-success" title="Promotion"></i>
                                        @endif
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif
@else
    <p class="text-muted text-center m-t-sm">No rank history available.</p>
@endif
