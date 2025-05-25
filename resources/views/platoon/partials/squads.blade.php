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

    @can('update', $platoon)
        <a href="{{ route('filament.mod.resources.platoons.edit', [$platoon]) }}" class="list-group-item">
            <i class="fa fa-plus pull-right text-success"></i> Create {{ $division->locality('squad') }}
        </a>
    @endcan

</div>