<div class="panel panel-primary">

    {{-- locality --}}
    <div class="panel-heading">Platoons</div>
    <div class="list-group">
        @if (count($division->platoons))
            @foreach ($division->platoons as $platoon)
                <a href="{{ action('PlatoonController@show', [$platoon->id]) }}"
                   class=" list-group-item">
                    <h5 class="pull-right text-muted big-num count-animated">{{ $platoon->members->count() }}</h5>
                    <h4 class="list-group-item-heading"><strong>{{ $platoon->name }}</strong></h4>
                    <p class="list-group-item-text text-muted">
                        @if ($platoon->leader)
                            {{ $platoon->leader->rankName }}
                        @else
                            TBA
                        @endif
                    </p>
                </a>
            @endforeach
        @else
            <li class="list-group-item text-muted">No platoons currently exist for this division.</li>
        @endif

    </div>
</div>
