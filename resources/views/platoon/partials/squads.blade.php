<div class="panel">
    @forelse ($platoon->squads as $squad)
        <a class="list-group-item"
           href="{{ route('squad.show', [$division->slug, $platoon, $squad]) }}">
            {{ $squad->name ?? ordSuffix($loop->iteration) . " Squad" }}<br />
            <span class="slight text-muted">
                @if ($squad->leader)
                    {{ $squad->leader->present()->rankName }}
                @else
                    TBA
                @endif
            </span>
        </a>
    @empty
        <a href="{{ route('createSquad', [$division->slug, $platoon]) }}" class="list-group-item">
            Create Squad
        </a>
    @endforelse

</div>