const $ = window.jQuery;
var populationData = $("#flot-line-chart").data("populations"),
    discordData = $("#flot-line-chart").data("weekly-discord");
// data1 = $('#flot-line-chart').data('weekly-active'),
// comments = $('#flot-line-chart').data('comments');

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
