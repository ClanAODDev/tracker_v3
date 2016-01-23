var json2 = $("#canvas2").attr('data-stats');
var lineData = $.parseJSON(json2);
var ctx2 = document.getElementById("chart2").getContext("2d");
window.myLine = new Chart(ctx2).Bar(lineData);


var json3 = $("#canvas3").attr('data-stats');
var barData = $.parseJSON(json3);
var ctx3 = document.getElementById("chart3").getContext("2d");
window.myBar = new Chart(ctx3).Line(barData);




