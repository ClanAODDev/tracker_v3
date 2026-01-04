import { getThemeColors, getBaseChartOptions, getGridScaleConfig, registerChartForThemeUpdates } from './chart-utils.js';

function initRetentionGraph() {
    var $ = window.jQuery;

    if (!$ || typeof Chart === 'undefined') {
        setTimeout(initRetentionGraph, 50);
        return;
    }

    $('#preset-select').on('change', function () {
        var months = parseInt($(this).val());
        if (!months) return;

        var end = new Date();
        var start = new Date();
        start.setMonth(start.getMonth() - months);
        start.setDate(1);

        end.setMonth(end.getMonth() + 1);
        end.setDate(0);

        $('#start').val(start.toISOString().split('T')[0]);
        $('#end').val(end.toISOString().split('T')[0]);

        $(this).closest('form').submit();
    });

    var canvas = document.getElementById('retention-chart');
    if (!canvas) {
        return;
    }

    var recruitsData = JSON.parse(canvas.dataset.recruits || '[]');
    var removalsData = JSON.parse(canvas.dataset.removals || '[]');
    var populationData = JSON.parse(canvas.dataset.population || '[]');

    if (!recruitsData.length) {
        return;
    }

    var colors = getThemeColors();
    var baseOptions = getBaseChartOptions(colors);
    var scaleConfig = getGridScaleConfig(colors);

    var labels = recruitsData.map(function(point) {
        return point[0];
    });

    var recruitsValues = recruitsData.map(function(point) {
        return point[1];
    });

    var removalsValues = removalsData.map(function(point) {
        return point[1];
    });

    var populationValues = populationData.map(function(point) {
        return point[1];
    });

    var chart = new Chart(canvas, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Recruited',
                    data: recruitsValues,
                    borderColor: colors.success,
                    backgroundColor: colors.success + '1A',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 2,
                    pointHoverRadius: 4
                },
                {
                    label: 'Removed',
                    data: removalsValues,
                    borderColor: colors.danger,
                    backgroundColor: colors.danger + '1A',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 2,
                    pointHoverRadius: 4
                },
                {
                    label: 'Population',
                    data: populationValues,
                    borderColor: colors.primary,
                    backgroundColor: colors.primary + '1A',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 2,
                    pointHoverRadius: 4
                }
            ]
        },
        options: {
            ...baseOptions,
            plugins: {
                ...baseOptions.plugins,
                tooltip: {
                    ...baseOptions.plugins.tooltip,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y;
                        }
                    }
                }
            },
            scales: {
                x: scaleConfig,
                y: {
                    ...scaleConfig,
                    beginAtZero: true
                }
            }
        }
    });

    registerChartForThemeUpdates(chart, function(c, newColors) {
        c.data.datasets[0].borderColor = newColors.success;
        c.data.datasets[0].backgroundColor = newColors.success + '1A';
        c.data.datasets[1].borderColor = newColors.danger;
        c.data.datasets[1].backgroundColor = newColors.danger + '1A';
        c.data.datasets[2].borderColor = newColors.primary;
        c.data.datasets[2].backgroundColor = newColors.primary + '1A';
        c.options.plugins.legend.labels.color = newColors.text;
        c.options.scales.x.grid.color = newColors.grid;
        c.options.scales.x.ticks.color = newColors.text;
        c.options.scales.y.grid.color = newColors.grid;
        c.options.scales.y.ticks.color = newColors.text;
        c.update();
    });
}

initRetentionGraph();
