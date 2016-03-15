<div class="panel panel-primary">
    <div class="panel-heading">Total members</div>
    <div class="panel-body count-detail-big striped-bg">
        <span class="count-animated">{{ $platoon->members->count() }}</span>
    </div>
</div>

<div class='panel panel-primary'>
    <div class='panel-heading'>Forum Activity</div>
    <div class='panel-body striped-bg'>
        <div id="canvas-holder" data-stats="{{ $platoon->forumActivity }}">
            <canvas id="chart-area" />
        </div>
    </div>
</div>
