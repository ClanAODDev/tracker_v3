<div class='panel panel-primary'>
    <div class='panel-heading'>Forum Activity</div>
    <div class='panel-body striped-bg'>
        <donut-graph url="/v1/api/activity/platoon/{{ $platoon->id }}"></donut-graph>
    </div>
</div>

{{ $platoon->forumActivity }}
