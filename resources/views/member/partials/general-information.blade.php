<div class="panel panel-filled">
    <div class="list-group-item">
        <span class="c-white">AOD Forums</span> <span
                class="pull-right">{{ $member->last_activity->diffForHumans() }}</span>
    </div>

    <div class="list-group-item">
        <span class="c-white">AOD Teamspeak</span> <span
                class="pull-right">{{ $member->last_activity->diffForHumans() }}</span>
    </div>

    <div class="list-group-item">
        <span class="c-white">Joined</span> <span
                class="pull-right">{{ $member->join_date }}</span>
    </div>

    <div class="list-group-item">
        <span class="c-white">Last promoted</span> <span
                class="pull-right">{{ $member->last_promoted }}</span>
    </div>
</div>