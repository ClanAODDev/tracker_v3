<div class="flot-chart" style="height: 200px; margin-bottom: 30px;">
    <div class="flot-chart-content" id="flot-line-chart"
         data-populations="{{ $populations }}"
         data-weekly-active="{{ $weeklyActive }}"
         data-comments="{{ $comments }}"
    ></div>
</div>

<script>

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

    /*$("#flot-line-chart").bind("plothover", function (event, pos, item) {

            if (item) {
                var count = item.datapoint[1];

                $("#hover-info").html(count)
                    .css({top: item.pageY+5, left: item.pageX+5})
                    .fadeIn(200);
            } else {
                $("#hover-info").hide();
            }

    });*/

    $.plot($("#flot-line-chart"), [data2, data1], chartUsersOptions);

    $(window).resize(function() {
        $.plot($("#flot-line-chart"), [data2, data1], chartUsersOptions);
    });
</script>