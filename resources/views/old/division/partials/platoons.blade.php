<div class="panel panel-primary">
    <div class="panel-heading">
        {{ str_plural($division->locality('platoon')) }} <span class="pull-right badge">{{ $platoons->count() }}</span>
    </div>

    @forelse ($platoons as $platoon)
        <a class="list-group-item"
           href="{{ route('platoon', [$division->abbreviation, $platoon->id]) }}">
            <h5 class="pull-right text-muted big-num count-animated">{{ $platoon->members->count() }}</h5>
            <h4 class="list-group-item-heading"><strong>{{ $platoon->name }}</strong></h4>
            @if(is_object($platoon->leader))
                <p class="list-group-item-text">{{ $platoon->leader->present()->rankName }}</p>
            @else
                <p class="list-group-item-text">Unfilled</p>
            @endif
        </a>
    @empty
        <li class="list-group-item text-muted">No {{ $division->locality('platoon') }} Found</li>
    @endforelse
</div>
