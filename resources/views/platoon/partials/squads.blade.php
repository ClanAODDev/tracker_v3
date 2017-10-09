<div class="panel">
    @foreach ($platoon->squads as $squad)
        <a class="list-group-item"
           href="{{ route('squad.show', [$division->abbreviation, $platoon, $squad]) }}">
            {{ str_limit($squad->name or "Squad" . $loop->iteration, 23) }}<br />
            <span class="slight text-muted">
                @if ($squad->leader)
                    {{ $squad->leader->present()->rankName }}
                @else
                    TBA
                @endif
            </span>
        </a>
    @endforeach

</div>