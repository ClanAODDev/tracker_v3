$(function() {
    // platoon forum activity stats
    var json = $("#canvas-holder").attr('data-stats');
    var donutData = $.parseJSON(json);
    var ctx = document.getElementById("chart-area").getContext("2d");
    window.myDonut = new Chart(ctx).Doughnut(donutData, {
        animationEasing: "easeInOutQuint",
        animationSteps: 75,
        percentageInnerCutout: 50,
        animateScale: true,
        responsive: true
    });
});
