<div class="panel panel-filled">
    <div class="panel-heading">Squads</div>
    <div class="panel-body btn-group-vertical center-block">
        @foreach ($platoon->squads as $squad)
            <a class="btn btn-default"
               href="{{ route('squad.show', [$division->abbreviation, $platoon, $squad]) }}">
                {{ $squad->name }} - #{{ $squad->id }}<br />
                <small class="slight">
                    @if ($squad->leader)
                        {{ $squad->leader->present()->rankName }}
                    @else
                        TBA
                    @endif
                </small>
            </a>
        @endforeach
    </div>
</div>