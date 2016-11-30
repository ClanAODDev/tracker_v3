<div class="panel panel-primary">
    <div class="panel-heading">{{ str_plural($division->locality('platoon')) }}</div>
    @forelse ($platoons as $platoon)
        <a class="list-group-item"
           href="{{ action('PlatoonController@show', [$division->abbreviation, $platoon->id]) }}">
            <h4 class="list-group-item-heading"><strong>{{ $platoon->name }}</strong></h4>
            @if($platoon->leader != 0)
                <p class="list-group-item-text">{{ $platoon->leader->present()->rankName }}</p>
            @else
                <p class="list-group-item-text">Unfilled</p>
            @endif
        </a>
    @empty
        <li class="list-group-item text-muted">No Platoons Found</li>
    @endforelse
</div>
