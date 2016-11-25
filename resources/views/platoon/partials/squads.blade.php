@foreach($platoon->squads as $squad)
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                @if ($squad->leader)
                    <strong>{!! $squad->leader->present()->rankName !!}</strong>
                @else
                    <span class="text-muted">No leader assigned</span>
                @endif
            </div>

            @if (count($squad->membersWithoutLeader))
                @foreach($squad->membersWithoutLeader as $member)
                    <a class="list-group-item" href="{{ action('MemberController@show', $member->clan_id) }}">
                        <div class="col-xs-6">{{ $member->present()->rankName }}</div>
                        <div class="col-xs-6 text-center">{{ $member->last_forum_login->diffForHumans() }}</div>
                        <div class="clearfix"></div>
                    </a>
                @endforeach
            @else
                <li class="text-muted list-group-item">No members assigned</li>
            @endif

        </div>
    </div>
@endforeach