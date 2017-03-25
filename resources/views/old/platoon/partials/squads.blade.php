@foreach($squads as $squad)

    <div class="panel panel-default squad">
        <div class="panel-heading wrap-ellipsis">
            <span class="badge pull-right">{{ $squad->members->count() }}</span>
            @can('update', $squad)
                <a href="{{ route('editSquad', [$division->abbreviation, $platoon, $squad]) }}"
                   title="Edit Squad">
                    <i class="fa fa-cog fa-lg"></i>
                </a>
            @endcan

            @if($squad->leader)
                {!! $squad->leader->present()->rankName !!}
            @else
                TBA
            @endif
        </div>

        <div style="height: 250px; max-height: 250px; overflow-y: scroll;">
            <table class="table table-striped table-hover members-table">
                <thead>
                <tr>
                    <th class='col-hidden'><strong>Rank Id</strong></th>
                    <th class='col-hidden'><strong>Last Login Date</strong></th>
                    <th><strong>Member</strong></th>
                    <th class='nosearch text-center'><strong>Rank</strong></th>
                    <th class='text-center hidden-xs hidden-sm'><strong>Joined</strong></th>
                    <th class='text-center'><strong>Last Activity</strong></th>
                    <th class='text-center'>
                        <string>Last Promoted</string>
                    </th>
                </tr>
                </thead>

                <tbody>
                @foreach($squad->members as $member)
                    <tr>
                        <td class="col-hidden">{{ $member->rank_id }}</td>
                        <td class="col-hidden">{{ $member->last_activity }}</td>
                        <td class="">{!! $member->present()->nameWithIcon !!} <a
                                    href="{{ route('member', $member->clan_id) }}"><i
                                        class="fa fa-search text-muted pull-right" title="View profile"></i></a></td>
                        <td class="text-center">{{ $member->rank->abbreviation }}</td>
                        <td class="text-center hidden-xs hidden-sm">{{ $member->join_date }}</td>
                        <td class="text-center">
                            <span class="{{ getActivityClass($member->last_activity, $division) }}">{{ $member->present()->lastActive }}</span>
                        </td>
                        <td class="text-center">{{ $member->last_promoted }}</td>
                    </tr>
                @endforeach
                </tbody>

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
