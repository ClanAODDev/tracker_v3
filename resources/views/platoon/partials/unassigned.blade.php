<div class="panel panel-warning">
    <div class="panel-heading">Unassigned Members</div>
    <div class="panel-body" style="height: 250px; max-height: 250px; overflow-y: scroll;">
        @forelse($unassigned->chunk(ceil($platoon->unassigned->count() / 2)) as $chunk)
            <div class="col-md-6 list-group">
                @foreach($chunk as $member)
                    <a href="#" class="list-group-item wrap-ellipsis">{{ $member->present()->rankName }}</a>
                @endforeach
            </div>
        @empty
            <p class="text-muted">No Unassigned Members</p>
        @endforelse
    </div>
</div>