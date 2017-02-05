<div class="panel">
    <div class="panel-body">

        <h1 class="m-t-md m-b-xs" style="margin-top: 30px">
            <i class="pe pe-7s-global text-warning"> </i>

            {{ number_format($memberCount) }}

            @if($memberCount < $census->count)
                <span class="slight">
                    <i class="fa fa-play fa-rotate-90 c-white"></i>
                    {{ percent($census->count, $memberCount) }}%
                </span>
            @else
                <span class="slight">
                    <i class="fa fa-play fa-rotate-270 text-warning"></i>
                    {{ percent($census->count, $memberCount) }}%
                </span>
            @endif

        </h1>

        <div class="small">
            <span class="c-white">Total active members</span> in the Angels of Death clan. Percent difference from previous count of {{ $census->count }} on {{ $census->date }}
        </div>
    </div>
</div>