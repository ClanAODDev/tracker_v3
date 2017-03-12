<div class="row">
    @forelse($divisionLeaders as $leader)
        <div class="col-md-4">
            <a href="{{ route('member', $leader->clan_id) }}" class="panel panel-filled panel-c-accent">
                <div class="panel-body">
                    <h4 class="m-b-none">
                        {!! $leader->present()->rankName !!}
                        <span class="pull-right"><i class="pe pe-2x pe-7s-shield"></i></span>
                    </h4>
                    <small><?php echo $leader->position->name; ?></small>
                </div>
            </a>
        </div>
    @empty
        <div class="col-md-12">
            <div class="panel panel-filled panel-c-danger text-muted">
                <div class="panel-body">
                    <h4 class="m-b-none text-uppercase">
                        No leadership assigned
                    </h4>
                </div>
            </div>
        </div>
    @endforelse
</div>