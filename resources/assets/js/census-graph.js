import { getThemeColors, getBaseChartOptions, getGridScaleConfig, registerChartForThemeUpdates } from './chart-utils.js';

function initCensusGraph() {
    if (typeof Chart === 'undefined') {
        setTimeout(initCensusGraph, 50);
        return;
    }

    var canvas = document.getElementById('census-chart');
    if (!canvas) {
        return;
    }

    var populationData = JSON.parse(canvas.dataset.populations || '[]');
    var discordData = JSON.parse(canvas.dataset.weeklyDiscord || '[]');

    if (!populationData.length || !discordData.length) {
        return;
    }

    var colors = getThemeColors();
    var baseOptions = getBaseChartOptions(colors);
    var scaleConfig = getGridScaleConfig(colors);

    var labels = populationData.map(function(point) {
        return new Date(point[0]);
    });

    var populationValues = populationData.map(function(point) {
        return point[1];
    });

    var discordValues = discordData.map(function(point) {
        return point[1];
    });

    var chart = new Chart(canvas, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Population',
                    data: populationValues,
                    borderColor: colors.success,
                    backgroundColor: colors.success + '1A',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 2,
                    pointHoverRadius: 4
                },
                {
                    label: 'Discord Active',
                    data: discordValues,
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
                        title: function(context) {
                            var date = context[0].parsed.x;
                            return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: '2-digit' });
                        },
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y + ' members';
                        }
                    }
                }
            },
            scales: {
                x: {
                    ...scaleConfig,
                    type: 'time',
                    time: {
                        unit: 'day',
                        displayFormats: {
                            day: 'MM/dd/yy'
                        }
                    }
                },
                y: {
                    ...scaleConfig,
                    beginAtZero: false
                }
            }
        }
    });

    registerChartForThemeUpdates(chart, function(c, newColors) {
        c.data.datasets[0].borderColor = newColors.success;
        c.data.datasets[0].backgroundColor = newColors.success + '1A';
        c.data.datasets[1].borderColor = newColors.primary;
        c.data.datasets[1].backgroundColor = newColors.primary + '1A';
        c.options.plugins.legend.labels.color = newColors.text;
        c.options.scales.x.grid.color = newColors.grid;
        c.options.scales.x.ticks.color = newColors.text;
        c.options.scales.y.grid.color = newColors.grid;
        c.options.scales.y.ticks.color = newColors.text;
        c.update();
    });
}

initCensusGraph();
