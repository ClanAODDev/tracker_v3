@foreach($squads as $squad)

    <div class="panel panel-primary squad">
        <div class="panel-heading wrap-ellipsis">
            <span class="badge pull-right">{{ $squad->members->count() }}</span>
            @if($squad->leader)
                {!! $squad->leader->present()->rankName !!}
            @else
                TBA
            @endif
        </div>

        <div class="panel-body" style="height: 250px; max-height: 250px; overflow-y: scroll;">
            <table class="table table-striped">
                <tr>
                    <th>Name</th>
                    <th>Rank</th>
                    <th>Joined</th>
                    <th>Last Activity</th>
                    <th>Last Promoted</th>
                </tr>


                @foreach($squad->members as $member)
                    <tr>
                        <td>{{ $member->name }}</td>
                        <td>{{ $member->rank->name }}</td>
                        <td>{{ $member->join_date }}</td>
                        <td>
                            <span class="{{ getActivityClass($member->last_activity, $division) }}">{{ $member->present()->lastActive }}</span>
                        </td>
                        <td>{{ $member->last_promoted }}</td>
                    </tr>
                @endforeach



                {{--@foreach($chunk as $member)--}}
                {{--@if($squad->leader && $member->clan_id != $squad->leader_id)--}}
                {{--<a href="{{ route('member', $member->clan_id) }}"--}}
                {{--class="list-group-item wrap-ellipsis">--}}
                {{--<small>{!! $member->present()->rankName !!}</small>--}}
                {{--</a>--}}
                {{--@endif--}}


            </table>

        </div>

    </div>
@endforeach