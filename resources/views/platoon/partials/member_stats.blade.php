<div class="panel panel-filled hidden-xs hidden-sm m-t-xl">
    <div class="panel-heading">
        <h1 class="m-b-n">
            <i class="pe pe-7s-users pe-lg text-warning"></i> {{ $platoon->members->count() }}
            <small class="slight">Members</small>
        </h1>
    </div>
    <div class="panel-body">
        <canvas id="platoonChart" data-labels="{{ json_encode($activityGraph['labels']) }}"
                data-values="{{ json_encode($activityGraph['values']) }}"
                data-colors="{{ json_encode($activityGraph['colors']) }}"></canvas>
    </div>
</div>

<script>

    var ctx = document.getElementById("platoonChart");

    var myDoughnutChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            datasets: [
                {
                    data: $("#platoonChart").data('values'),
                    backgroundColor: $("#platoonChart").data('colors'),
                    borderWidth: 0,
                }],
            labels: $("#platoonChart").data('labels'),
        },
        options: {
            rotation: 1 * Math.PI,
            circumference: 1 * Math.PI,
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 5,
                    fontColor: '#949ba2'
                },
                label: {
                    fullWidth: false
                }
            }
        }
    });
</script>