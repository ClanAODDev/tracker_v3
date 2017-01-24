<div class="panel panel-warning">
    <div class="panel-heading">Unassigned Members <span
                class="badge pull-right">{{ $platoon->unassigned->count() }}</span></div>
    <div class="panel-body">
        @if($platoon->unassigned->count())
            <p>There are <code>{{ $platoon->unassigned->count() }}</code> unassigned members. Do you wish to assign them?</p>
            <button class="btn btn-default">Manage Squads</button>
        @else
            <p>There are no unassigned members.</p>
        @endif
    </div>
</div>