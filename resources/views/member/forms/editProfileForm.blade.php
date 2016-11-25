<?php

use App\Squad, App\Platoon, App\Position;

$division = $member->primaryDivision

?>

<div class="row">
    <div class="col-md-8">
        <div class="well well-lg">

            {{-- Position --}}
            <?php $selectedPosition = ($member->position instanceof Position) ? $member->position->id : null; ?>

            <div class="form-group">
                <label for="position" class="control-label">Position</label>
                <select class="form-control" id="position">
                    <option value="">None</option>
                    @foreach($positions as $position)
                        <option value="{{ $position->id }}"
                                {{ selected($selectedPosition, $position->id) }}
                        >{{ ucwords($division->locality($position->name)) }}</option>
                    @endforeach
                </select>
            </div>
            {{-- end position --}}

            {{-- Platoon --}}
            <?php $selectedPlatoon = ($member->platoon instanceof Platoon) ? $member->platoon->id : null; ?>

            <div class="form-group">
                <label for="platoon" class="control-label">{{ ucwords($division->locality('platoon')) }}</label>
                <select class="form-control" id="select">
                    <option value="">None</option>
                    @foreach($platoons as $platoon)
                        <option value="{{ $platoon->id }}"
                                {{ selected($selectedPlatoon, $platoon->id) }}
                        >{{ $platoon->name }}</option>
                    @endforeach
                </select>
            </div>
            {{-- end platoon --}}

            {{-- Squad --}}
            <?php $selectedSquad = ($member->squad instanceof Squad) ? $member->squad->id : null; ?>

            <div class="form-group">
                <label for="squad" class="control-label">{{ ucwords($division->locality('squad')) }}</label>
                <select class="form-control" id="select">
                    <option value="">None</option>
                    @foreach($squads as $squad)
                        {{ $squadLeader = ( ! empty($squad->leader)) ? $squad->leader->name : "TBA" }}
                        <option value="{{ $squad->id }}"
                                {{ selected($selectedSquad, $squad->id) }}
                        >{{ $squadLeader }} - {{  $platoon->name }}</option>
                    @endforeach
                </select>
            </div>
            {{-- End squad --}}

        </div>
    </div>

    <div class="col-md-4 pull-right">
        @include('member.forms.removalForm')
    </div>

</div>