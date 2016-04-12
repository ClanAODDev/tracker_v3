<div class='panel panel-primary'>
    <div class='panel-heading'>Forum Activity</div>
    <div class='panel-body striped-bg'>
        <div id="canvas-holder" data-stats="{{ ($platoon->forumActivity) ?: null }}">
            <canvas id="chart-area" />
        </div>
    </div>
</div>
