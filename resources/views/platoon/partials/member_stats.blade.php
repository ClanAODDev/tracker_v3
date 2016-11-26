<div class="panel panel-primary">
    <div class="panel-heading">Total Members</div>
    <div class="panel-body count-detail-big striped-bg">
        <span class="count-animated">{{ $platoon->activeMembers->count() }}</span>
    </div>
</div>

<div class="panel panel-primary">
    <div class="panel-heading">Forum Activity</div>
    <div class="panel-body striped-bg">
        {!! $activityGraph->render() !!}
    </div>
</div>
