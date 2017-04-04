<div class="col-md-6">
    {{-- Position --}}
    <div class="form-group">
        <label for="position" class="control-label">Position</label>
        <select class="form-control" id="position">
            <option value="">None</option>
            @foreach ($positions as $positionId => $positionName)
                <option value="{{ $positionId }}"
                        {{ selected($member->position->id, $positionId) }}
                >{{ ucwords($positionName) }}
                </option>
            @endforeach
        </select>
    </div>
    {{-- end position --}}

    {{-- Platoon --}}
    <div class="form-group">
        <label for="platoon" class="control-label">{{ ucwords($division->locality('platoon')) }}</label>
        <select class="form-control" id="select">
            <option value="">None</option>
            @foreach($division->platoons as $platoon)
                <option value="{{ $platoon->id }}"
                        {{ selected($member->platoon or null, $platoon->id) }}
                >{{ $platoon->name }}
                </option>
            @endforeach
        </select>
    </div>
    {{-- end platoon --}}

    {{-- Squad --}}
    <div class="form-group">
        <label for="squad" class="control-label">{{ ucwords($division->locality('squad')) }}</label>
        <select class="form-control" id="select">
            <option value="">None</option>
            @foreach($division->squads as $squad)
                <option value="{{ $squad->id }}"
                        {{ selected($member->squad or null, $squad->id) }}
                >{{ $squad->name }} - {{ $squad->platoon->name }}
                </option>
            @endforeach
        </select>
    </div>
    {{-- End squad --}}

</div>
<div class="col-md-6"></div>
