@if($rankTimeline->hasHistory)
    <div class="rank-timeline-wrapper">
        <div class="rank-timeline">
            <div class="timeline-track"></div>

            @foreach($rankTimeline->nodes as $index => $node)
                @if($node->type === 'join')
                    <div class="timeline-node timeline-start timeline-{{ $node->position }}" style="--i: {{ $index }}">
                        <div class="timeline-marker marker-join"></div>
                        <div class="timeline-content">
                            <div class="timeline-date">{{ $node->date }}</div>
                            <div class="timeline-label">{{ $node->label }}</div>
                        </div>
                    </div>
                @elseif($node->type === 'consolidated')
                    <div class="timeline-duration-connector" style="--i: {{ $index }}">{{ $rankTimeline->nodes[$index - 1]->duration ?? '' }}</div>
                    <div class="timeline-node timeline-{{ $node->position }}" style="--i: {{ $index }}">
                        <div class="timeline-marker marker-promotion"></div>
                        <div class="timeline-content">
                            <div class="timeline-rank timeline-rank-consolidated">{{ $node->label }}</div>
                            <div class="timeline-date">{{ $node->dateRange }}</div>
                        </div>
                    </div>
                @elseif($node->type === 'promotion')
                    <div class="timeline-duration-connector" style="--i: {{ $index }}">{{ $rankTimeline->nodes[$index - 1]->duration ?? '' }}</div>
                    <div class="timeline-node timeline-{{ $node->position }}" style="--i: {{ $index }}">
                        <div class="timeline-marker marker-promotion"></div>
                        <div class="timeline-content">
                            <div class="timeline-rank">{{ $node->rank }}</div>
                            <div class="timeline-date">{{ $node->date }}</div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

@else
    <p class="text-muted text-center m-t-sm">No rank history available.</p>
@endif
