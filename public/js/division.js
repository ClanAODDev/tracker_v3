/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*****************************************!*\
  !*** ./resources/assets/js/division.js ***!
  \*****************************************/
var Division = Division || {};
(function ($) {
  Division = {
    initUnassigned: function initUnassigned() {
      $(function () {
        $('.unassigned').draggable({
          revert: true
        });

        // $('.squad').droppable({
        //   hoverClass: 'panel-c-success',
        //   greedy: true,
        //   drop: function (event, ui) {
        //     alert('asdf');
        //   }
        // });

        $('.platoon').droppable({
          hoverClass: 'panel-c-success',
          drop: function drop(event, ui) {
            var platoon = $(this),
              base_url = window.Laravel.appPath,
              draggableId = ui.draggable.attr('data-member-id'),
              droppableId = platoon.attr('data-platoon-id');
            $.ajax({
              type: 'POST',
              url: base_url + '/members/' + draggableId + '/assign-platoon',
              data: {
                platoon_id: droppableId,
                _token: $('meta[name=csrf-token]').attr('content')
              },
              success: function success(response) {
                toastr.success('Member was assigned to platoon #' + droppableId);
                $(ui.draggable).remove();
                if ($('.unassigned').length < 1) {
                  $('.unassigned-container').slideUp();
                }
              }
            });
          }
        });
      });
    },
    setup: function setup() {
      this.initAutocomplete();
      this.initSetup();
      this.initUnassigned();
    },
    initSetup: function initSetup() {
      var ctx = $('.promotions-chart');
      if (ctx.length) {
        var myDoughnutChart = new Chart(ctx, {
          type: 'doughnut',
          data: {
            datasets: [{
              data: ctx.data('values'),
              borderWidth: 0,
              backgroundColor: ['#949ba2', '#0f83c9', '#5fbb60', '#6da21f', '#56c0e0', '#db524b']
            }],
            labels: ctx.data('labels')
          },
          options: {
            legend: {
              position: 'bottom',
              labels: {
                boxWidth: 5,
                fontColor: '#949ba2'
              },
              label: {
                fullWidth: true
              }
            }
          }
        });
      }
    },
    initAutocomplete: function initAutocomplete() {
      $('#leader').bootcomplete({
        url: window.Laravel.appPath + '/search-member/',
        minLength: 3,
        idField: true,
        method: 'POST',
        dataParams: {
          _token: $('meta[name=csrf-token]').attr('content')
        }
      });
    }
  };
})(jQuery);
Division.setup();
/******/ })()
;