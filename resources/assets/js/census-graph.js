function initCensusGraph() {
    const $ = window.jQuery;

    if (!$ || typeof $.plot !== 'function') {
        setTimeout(initCensusGraph, 50);
        return;
    }

    var $chart = $("#flot-line-chart");
    if (!$chart.length) {
        return;
    }

    var populationData = $chart.data("populations"),
        discordData = $chart.data("weekly-discord");

    if (!populationData || !discordData) {
        return;
    }

    var styles = getComputedStyle(document.documentElement);
    var gridColor = styles.getPropertyValue('--color-bg-panel').trim() || '#404652';
    var successColor = styles.getPropertyValue('--color-success').trim() || '#1bbf89';
    var primaryColor = styles.getPropertyValue('--color-primary').trim() || '#0F83C9';
    var accentColor = styles.getPropertyValue('--color-accent').trim() || '#f7af3e';

    var chartUsersOptions = {
        series: {
            points: {
                show: true,
                radius: 2,
                symbol: "circle"
            },
            splines: {
                show: true,
                tension: 0.4,
                lineWidth: 1,
                fill: 0.10
            }
        },
        grid: {
            tickColor: gridColor,
            borderWidth: 1,
            color: "#000",
            borderColor: gridColor
        },
        tooltip: false,
        tooltippage: {
            show: true,
            content: "%x - %y members"
        },
        xaxis: {
            mode: "time",
            timeformat: "%m/%d/%y"
        },
        colors: [successColor, primaryColor, accentColor]
    };

    $.plot($("#flot-line-chart"), [populationData, discordData], chartUsersOptions);

    $(window).resize(function () {
        $.plot($("#flot-line-chart"), [populationData, discordData], chartUsersOptions);
    });
}

initCensusGraph();
