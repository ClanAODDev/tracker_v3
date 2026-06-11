import { getThemeColors, getBaseChartOptions, registerChartForThemeUpdates } from './chart-utils.js';

var PALETTE = [
    '#0F83C9', '#56c0e0', '#1bbf89', '#f7af3e',
    '#db524b', '#9b59b6', '#e67e22', '#1abc9c',
    '#3498db', '#e74c3c', '#2ecc71', '#f39c12',
];

function buildSegmentColors(count, originalIndex, colors) {
    return Array.from({ length: count }, function(_, i) {
        if (i === originalIndex) return colors.success;
        return PALETTE[i % PALETTE.length];
    });
}

function initTransferGraph() {
    var $ = window.jQuery;

    if (!$ || typeof Chart === 'undefined') {
        setTimeout(initTransferGraph, 50);
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

    var canvas = document.getElementById('transfer-chart');
    if (!canvas) return;

    var labels = JSON.parse(canvas.dataset.labels || '[]');
    var counts = JSON.parse(canvas.dataset.counts || '[]');

    if (!labels.length) return;

    var colors = getThemeColors();
    var baseOptions = getBaseChartOptions(colors);
    var originalIndex = parseInt(canvas.dataset.originalIndex);
    var total = counts.reduce(function(sum, v) { return sum + v; }, 0);

    var backgroundColors = buildSegmentColors(labels.length, originalIndex, colors);

    var chart = new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: counts,
                backgroundColor: backgroundColors,
                borderColor: 'transparent',
                borderWidth: 0,
                hoverOffset: 6,
            }]
        },
        options: {
            ...baseOptions,
            interaction: {
                mode: 'nearest',
                intersect: true,
            },
            plugins: {
                ...baseOptions.plugins,
                legend: {
                    ...baseOptions.plugins.legend,
                    position: 'right',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            var pct = total > 0 ? Math.round(context.parsed / total * 100) : 0;
                            return ' ' + context.parsed + ' members (' + pct + '%)';
                        }
                    }
                }
            },
        }
    });

    registerChartForThemeUpdates(chart, function(c, newColors) {
        var newBg = buildSegmentColors(labels.length, originalIndex, newColors);
        c.data.datasets[0].backgroundColor = newBg;
        c.options.plugins.legend.labels.color = newColors.text;
        c.update();
    });
}

initTransferGraph();
