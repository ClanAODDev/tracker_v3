<div class="panel panel-primary">

    <div class="panel-heading">
        {{ ucwords(str_plural($division->locality('platoon'))) }}
    </div>

    <div class="list-group">
        @forelse ($division->platoons as $platoon)
            <a href="{{ action('PlatoonController@show', [$platoon->id]) }}"
               class=" list-group-item">
                <h5 class="pull-right text-muted big-num count-animated">{{ $platoon->members->count() }}</h5>
                <h4 class="list-group-item-heading"><strong>{{ $platoon->name }}</strong></h4>
                <p class="list-group-item-text text-muted">
                    @if ($platoon->leader)
                        {{ $platoon->leader->present()->rankName }}
                    @else
                        TBA
                    @endif
                </p>
            </a>
        @empty
            <li class="list-group-item text-muted">
                No {{ str_plural($division->locality('platoon')) }} currently exist for this division.
            </li>
        @endforelse

    </div>
</div>
