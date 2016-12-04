<div class="panel panel-primary">
    <div class="panel-heading">General Sergeants <span class="pull-right badge">{{ $generalSergeants->count() }}</span></div>

    @forelse ($generalSergeants as $member)
        <a href="{{ action('MemberController@show', $member->clan_id) }}" class="list-group-item">
            <h4 class="list-group-item-heading">
                <strong>{{ $member->rank->abbreviation }} {{ $member->name }}</strong></h4>
            <p class="list-group-item-text text-muted"><?php echo $member->position->name; ?></p>
        </a>
    @empty
        <li class="list-group-item text-muted">This division has no general sergeants</li>
    @endforelse
</div>