/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*********************************************!*\
  !*** ./resources/assets/js/census-graph.js ***!
  \*********************************************/
// Flot charts data and options
var data2 = $('#flot-line-chart').data('populations'),
  data3 = $('#flot-line-chart').data('weekly-ts');
// data1 = $('#flot-line-chart').data('weekly-active'),
// comments = $('#flot-line-chart').data('comments');

var chartUsersOptions = {
  series: {
    points: {
      show: true,
      radius: 2,
      symbol: 'circle'
    },
    splines: {
      show: true,
      tension: 0.4,
      lineWidth: 1,
      fill: .10
    }
  },
  grid: {
    tickColor: '#404652',
    borderWidth: 1,
    color: '#000',
    borderColor: '#404652'
  },
  comment: {
    show: true
  },
  tooltip: false,
  tooltippage: {
    show: true,
    content: '%x - %y members'
  },
  xaxis: {
    mode: 'time',
    timeformat: '%m/%d/%y'
  },
  colors: ['#5fbb60', '#0F83C9', '#6da21f']
};
$.plot($('#flot-line-chart'), [data2, data3], chartUsersOptions);
$(window).resize(function () {
  $.plot($('#flot-line-chart'), [data2, data3], chartUsersOptions);
});
/******/ })()
;