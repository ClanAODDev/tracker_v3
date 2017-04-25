<h4>Assign members</h4>
<hr />
<p>If there are unassigned members in the division, you can assign them to this platoon by selecting them. Bear in mind that you will still need to create squads, and assign members to those as well.</p>

<div class="row">
    <div class="col-xs-6">
        <div class="panel panel-filled">
            <div class="panel-heading">Unassigned Members</div>
            <div class="panel-body">
                @forelse ($division->unassigned as $member)
                    <li>{{ $member->present()->rankName }}</li>
                @empty
                    <p class="text-muted">No unassigned members available</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-xs-6">
        <div class="panel panel-filled">
            <div class="panel-heading">Current Members</div>
            <div class="panel-body"></div>
        </div>
    </div>
</div>

