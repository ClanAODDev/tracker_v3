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
            tickColor: "#404652",
            borderWidth: 1,
            color: "#000",
            borderColor: "#404652"
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
        colors: ["#1bbf89", "#0F83C9", "#f7af3e"]
    };

    $.plot($("#flot-line-chart"), [populationData, discordData], chartUsersOptions);

    $(window).resize(function () {
        $.plot($("#flot-line-chart"), [populationData, discordData], chartUsersOptions);
    });
}

initCensusGraph();
