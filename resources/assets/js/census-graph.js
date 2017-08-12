// Flot charts data and options
var data2 = $('#flot-line-chart').data('populations'),
  data3 = $('#flot-line-chart').data('weekly-ts'),
  data1 = $('#flot-line-chart').data('weekly-active'),
  comments = $('#flot-line-chart').data('comments');

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
      fill: .10,
    }
  },

  grid: {
    tickColor: '#404652',
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

  xaxis: {
    axisLabel: 'Weeks'
  },

  comments: comments,

  colors: ['#0F83C9', '#1bbf89', '#f7af3e']
};

$.plot($('#flot-line-chart'), [data1, data2, data3], chartUsersOptions);

$(window).resize(function () {
  $.plot($('#flot-line-chart'), [data1, data2, data3], chartUsersOptions);
});

$('input[type=checkbox]').change(function (event) {
  var option = {};
  option['comment'] = {show: $(this).is(':checked')};
  $.extend(true, chartUsersOptions, option);
  $.plot($('#flot-line-chart'), [data1, data2, data3], chartUsersOptions);
});