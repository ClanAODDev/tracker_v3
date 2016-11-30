<div class="panel panel-info">
    <div class="panel-heading">Division Command Staff</div>
    @forelse($divisionLeaders as $leader)
        <a href="{{ action('MemberController@show', $leader->clan_id) }}" class="list-group-item">
            <h5 class="pull-right"><i class="fa fa-shield fa-2x text-muted"></i></h5>
            <h4 class="list-group-item-heading">
                <strong>{!! $leader->present()->rankName !!}</strong></h4>
            <p class="list-group-item-text text-muted"><?php echo $leader->position->name; ?></p>
        </a>
    @empty
        <li class="list-group-item text-muted">This division has no assigned leadership</li>
    @endforelse
</div>

<div class="row">
    <div class="col-md-6">
        @include('division.partials.generalSergeants')
    </div>
    <div class="col-md-6">
        @include('division.partials.staffSergeants')
    </div>
</div>