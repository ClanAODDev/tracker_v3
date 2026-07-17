@if(isset($division['rank_change']) && $division['rank_change'] !== 0)
    @if($division['rank_change'] > 0)
        <span class="leaderboard-movement leaderboard-movement--up" title="Up from #{{ $division['previous_rank'] }}">
            <i class="fa fa-caret-up"></i>{{ $division['rank_change'] }}
        </span>
    @else
        <span class="leaderboard-movement leaderboard-movement--down" title="Down from #{{ $division['previous_rank'] }}">
            <i class="fa fa-caret-down"></i>{{ abs($division['rank_change']) }}
        </span>
    @endif
@elseif(isset($division['previous_rank']))
    <span class="leaderboard-movement leaderboard-movement--unchanged" title="Unchanged">
        <i class="fa fa-minus"></i>
    </span>
@endif
