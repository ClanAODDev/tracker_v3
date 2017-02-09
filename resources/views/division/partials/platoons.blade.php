@forelse ($platoons as $platoon)
    <a href="{{ route('platoon', [$division->abbreviation, $platoon->id]) }}"
       class="panel panel-filled">
        <div class="panel-body">
            <h4 class="m-b-none">
                {{ $platoon->name }}
                <label class="label label-accent pull-right">{{ $platoon->members->count() }}</label>
            </h4>

            @if(is_object($platoon->leader))
                <p class="list-group-item-text">{{ $platoon->leader->present()->rankName }}</p>
            @else
                <p class="list-group-item-text">Unfilled</p>
            @endif
        </div>
    </a>
@empty
    <div class="panel panel-filled text-muted">
        <div class="panel-body">
            No {{ $division->locality('platoon') }} Found
        </div>
    </div>
@endforelse

