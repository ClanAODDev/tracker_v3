@foreach($platoon->squads as $squad)
    <div class="col-md-8">
        <div class="panel panel-default">
            <div class="panel-heading">
                @if ($squad->leader)
                    <strong>{!! $squad->leader->specialName !!}</strong>
                @else
                    <span class="text-muted">No leader assigned</span>
                @endif
            </div>

            @if (count($squad->membersWithoutLeader))

                @foreach($squad->membersWithoutLeader($squad->leader_id) as $member)

                        <a class="list-group-item">
                            <div class="col-xs-3">{{ $member->name }}</div>
                            <div class="col-xs-3 text-center">{{ $member->rank->abbreviation }}</div>
                            <div class="col-xs-3 text-center">{{ $member->join_date }}</div>
                            <div class="col-xs-3 text-center">{{ $member->last_forum_login->diffForHumans() }}</div>
                            <div class="clearfix"></div>
                        </a>

                @endforeach

            @else
                <li class="text-muted list-group-item">No members assigned</li>
            @endif

        </div>
    </div>
@endforeach
