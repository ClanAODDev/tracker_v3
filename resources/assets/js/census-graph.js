// Flot charts data and options
var data2 = $("#flot-line-chart").data('populations'),
    data1 = $("#flot-line-chart").data('weekly-active'),
    comments = $("#flot-line-chart").data('comments');

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
            fill: 1,
        }
    },

    grid: {
        tickColor: "#404652",
        borderWidth: 1,
        hoverable: true,
        color: '#000',
        borderColor: '#404652',
    },

    comment: {
        show: true,

        hoverable: false,
    },

    tooltip: {
        show: false
    },

    sidenote: {
        show: false
    },

    comments: comments,

    colors: ["#f7af3e", "#DE9536"]
};

$.plot($("#flot-line-chart"), [data2, data1], chartUsersOptions);

$(window).resize(function () {
    $.plot($("#flot-line-chart"), [data2, data1], chartUsersOptions);
});

$("input[type=checkbox]").change(function (event) {
    var option = {};
    option['comment'] = {show: $(this).is(':checked')};
    $.extend(true, chartUsersOptions, option);
    $.plot($("#flot-line-chart"), [data2, data1], chartUsersOptions);
});