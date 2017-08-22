@if (count($division->unnassigned))
    <div class="panel panel-c-accent panel-filled unassigned-container">
        <div class="panel-body">
            <h5>Unassigned members</h5>
            @foreach ($division->unassigned as $member)
                <div class="unassigned badge" data-member-id="{{ $member->clan_id }}"
                     style="cursor: move">
                    {{ $member->present()->rankName }}
                </div>
            @endforeach
        </div>
    </div>
@endif