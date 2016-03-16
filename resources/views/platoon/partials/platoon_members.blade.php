
@if (count($platoon->squads->count()))

    <div class="panel panel-info">

        <div class="panel-heading">Squads</div>

        @foreach($platoon->squads as $squad)
            <a class="list-group-item">
                {{ $squad->id }}
            </a>
        @endforeach

    </div>
@endif


<div class="panel panel-primary">

    <div class='panel-heading'>
        <div class='download-area hidden-xs'></div>
        Members (last 30 days)<span></span>
    </div>

    <div class='panel-body border-bottom'>
        <div id='playerFilter'></div>
        <div class="table-responsive">

            <table class='table table-striped table-hover' id='members-table'>
                <thead>
                <tr>
                    <th class='col-hidden'><strong>Rank Id</strong></th>
                    <th class='col-hidden'><strong>Last Login Date</strong></th>
                    <th><strong>Member</strong></th>
                    <th class='nosearch text-center hidden-xs hidden-sm'><strong>Rank</strong></th>
                    <th class='text-center hidden-xs hidden-sm'><strong>Joined</strong></th>
                    <th class='text-center'><strong>Forum Activity</strong></th>
                </tr>
                </thead>

                <tbody>

                @foreach($platoon->members as $member)

                    <tr role="row" data-id="{{ $member->clan_id }}">
                        <td class="col-hidden">{{ $member->rank_id }}</td>
                        <td class="col-hidden">{{ $member->last_forum_login }}</td>
                        <td class="">{!! $member->specialName !!}</td>
                        <td class="text-center">{{ $member->rank->abbreviation }}</td>
                        <td class="text-center">{{ $member->join_date }}</td>
                        <td class="text-center">{{ $member->last_forum_login->diffForHumans() }}</td>
                    </tr>

                @endforeach

                </tbody>

            </table>

        </div>
    </div>

    <div class='panel-footer text-muted text-center' id='member-footer'></div>

</div>
