<div class="panel panel-filled text-center">
    <div class="panel-heading">Squads</div>
    <div class="panel-body btn-group-vertical">
        @foreach ($platoon->squads as $squad)
            <a class="btn btn-default"
               href="{{ route('squad.show', [$division->abbreviation, $platoon, $squad]) }}">
                {{ $squad->name }}
            </a>
        @endforeach
    </div>
</div>