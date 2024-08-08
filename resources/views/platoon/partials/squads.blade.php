<div class="panel">
    @foreach ($platoon->squads as $squad)
        <a class="list-group-item"
           href="{{ route('squad.show', [$division->slug, $platoon, $squad]) }}">
            {{ $squad->name ?? ordSuffix($loop->iteration) . " Squad" }}<br/>
            <span class="slight text-muted">
                @if ($squad->leader)
                    {{ $squad->leader->present()->rankName }}
                @else
                    TBA
                @endif
            </span>
        </a>
    @endforeach

    <a href="{{ route('createSquad', [$division->slug, $platoon]) }}" class="list-group-item">
        <i class="fa fa-plus pull-right text-success"></i> Create Squad
    </a>

</div>