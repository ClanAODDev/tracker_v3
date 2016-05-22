<div class="panel panel-info">
    <div class="panel-heading">Division Command Staff</div>

    @forelse ($division->leaders as $leader)
        <a href="{{ action('MemberController@show', $leader->clan_id) }}" class="list-group-item">
            <h5 class="pull-right"><i class="fa fa-shield fa-2x text-muted"></i></h5>
            <h4 class="list-group-item-heading">
                <strong>{{ $leader->rank->abbreviation }} {{ $leader->name }}</strong></h4>
            <p class="list-group-item-text text-muted"><?php echo $leader->position->name; ?></p>
        </a>
    @empty
        <li class="list-group-item text-muted">This division has no assigned leadership</li>
    @endforelse
</div>

<div class="panel panel-primary">
    <div class="panel-heading">Assigned Staff Sergeants</div>

    @forelse ($division->staffSergeants as $staffSergeant)
        <a href="{{ action('MemberController@show', $staffSergeant->clan_id) }}" class="list-group-item">
            <h5 class="pull-right"><i class="fa fa-shield fa-2x text-muted"></i></h5>
            <h4 class="list-group-item-heading">
                <strong>{{ $staffSergeant->rank->abbreviation }} {{ $staffSergeant->name }}</strong></h4>
            <p class="list-group-item-text text-muted"><?php echo $staffSergeant->position->name; ?></p>
        </a>
    @empty
        <li class="list-group-item text-muted">No staff sergeants are assigned</li>
    @endforelse
</div>