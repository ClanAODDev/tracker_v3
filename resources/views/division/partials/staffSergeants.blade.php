<div class="panel panel-primary">
    <div class="panel-heading">Assigned Staff Sergeants <span class="pull-right badge">{{ $staffSergeants->count() }}</span></div>

    @forelse ($staffSergeants as $staffSergeant)
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