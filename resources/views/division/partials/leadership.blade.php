<div class="row">
    @forelse($divisionLeaders as $leader)
        <div class="col-md-4">
            <a href="{{ route('member', $leader->clan_id) }}" class="panel panel-filled panel-c-accent">
                <div class="panel-body">
                    <h4 class="m-b-none">
                        {!! $leader->present()->rankName !!}
                    </h4>
                    <small><?php echo $leader->position->name; ?></small>
                </div>
            </a>
        </div>
    @empty
        <div class="panel panel-filled">
            <div class="panel-body">This division has no assigned leadership</div>
        </div>
    @endforelse
</div>