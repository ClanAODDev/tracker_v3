<div class="census-chart-container">
    <div class="census-chart-header">
        <h4 class="census-chart-title">
            <i class="fa fa-chart-line"></i> Census History
        </h4>
    </div>
    <div class="census-chart-body">
        <div class="chart-wrapper" style="height: 300px;">
            <canvas id="census-chart"
                 data-populations="{{ $populations }}"
                 data-weekly-discord="{{ $weeklyDiscordActive }}"
            ></canvas>
        </div>
    </div>
</div>
