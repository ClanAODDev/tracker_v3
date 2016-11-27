<div class="row">

    @if (count($squads))
        @foreach($squads as $squad)
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">

                    </div>

                    @forelse($squad->members as $member)
                        <a class="list-group-item" href="{{ action('MemberController@show', $member->clan_id) }}">
                            <div class="col-xs-6">{!! $member->present()->nameWithIcon !!}</div>
                            <div class="col-xs-6 text-center">{{ $member->last_forum_login->diffForHumans() }}</div>
                            <div class="clearfix"></div>
                        </a>
                    @empty
                        <li class="text-muted list-group-item">No members assigned</li>
                    @endforelse

                </div>
            </div>
        @endforeach
    @endif
</div>