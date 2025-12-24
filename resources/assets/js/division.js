var Division = Division || {};

(function ($) {

  Division = {

    initUnassigned: function () {

      $(function () {

        $('.unassigned').draggable({
          revert: true,
        });

        $('.platoon').droppable({
          hoverClass: 'panel-c-success',
          drop: function (event, ui) {

            let platoon = $(this),
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
              success: function (response) {
                toastr.success('Member was assigned to platoon #' + droppableId);
                $(ui.draggable).remove();
                if ($('.unassigned').length < 1) {
                  $('.unassigned-container').slideUp();
                }
              },
            });
          }
        });
      });
    },
    setup: function () {
      this.initAutocomplete();
      this.initSetup();
      this.initUnassigned();
      this.initPopulationChart();
    },

    initPopulationChart: function () {
      var canvas = document.getElementById('population-chart');
      if (!canvas) return;

      var ctx = canvas.getContext('2d');
      var labels = JSON.parse(canvas.dataset.labels || '[]');
      var population = JSON.parse(canvas.dataset.population || '[]');
      var voice = JSON.parse(canvas.dataset.voice || '[]');

      if (labels.length === 0) return;

      var maxPop = Math.max(...population);
      var minPop = Math.min(...population);

      new Chart(ctx, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [
            {
              label: 'Members',
              data: population,
              borderColor: '#5bc0de',
              backgroundColor: 'rgba(91, 192, 222, 0.1)',
              borderWidth: 2,
              fill: true,
              tension: 0.3,
              pointRadius: 3,
              pointHoverRadius: 6,
              pointBackgroundColor: '#5bc0de',
              pointBorderColor: '#fff',
              pointBorderWidth: 2,
            },
            {
              label: 'Voice Active',
              data: voice,
              borderColor: '#1bbf89',
              backgroundColor: 'rgba(27, 191, 137, 0.1)',
              borderWidth: 2,
              fill: true,
              tension: 0.3,
              pointRadius: 2,
              pointHoverRadius: 5,
              pointBackgroundColor: '#1bbf89',
              pointBorderColor: '#fff',
              pointBorderWidth: 2,
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: {
            intersect: false,
            mode: 'index'
          },
          plugins: {
            legend: {
              display: true,
              position: 'top',
              align: 'end',
              labels: {
                boxWidth: 12,
                padding: 15,
                color: 'rgba(255, 255, 255, 0.7)',
                font: { size: 11 }
              }
            },
            tooltip: {
              backgroundColor: 'rgba(0, 0, 0, 0.8)',
              titleColor: '#fff',
              bodyColor: '#fff',
              padding: 12,
              displayColors: true,
              callbacks: {
                label: function(context) {
                  return context.dataset.label + ': ' + context.parsed.y;
                }
              }
            }
          },
          scales: {
            x: {
              grid: { color: 'rgba(255, 255, 255, 0.05)' },
              ticks: {
                color: 'rgba(255, 255, 255, 0.5)',
                font: { size: 10 },
                maxRotation: 45,
                minRotation: 0
              }
            },
            y: {
              beginAtZero: false,
              suggestedMin: minPop - Math.round(minPop * 0.1),
              grid: { color: 'rgba(255, 255, 255, 0.05)' },
              ticks: {
                color: 'rgba(255, 255, 255, 0.5)',
                font: { size: 10 }
              }
            }
          }
        }
      });
    },

    initSetup: function () {
      var ctx = $('.promotions-chart');

      if (ctx.length) {
        var myDoughnutChart = new Chart(ctx, {
          type: 'doughnut',
          data: {
            datasets: [
              {
                data: ctx.data('values'),
                borderWidth: 0,
                backgroundColor: [
                  '#949ba2', '#0f83c9', '#1bbf89', '#f7af3e', '#56c0e0', '#db524b'
                ]
              }],
            labels: ctx.data('labels'),
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

    initAutocomplete: function () {

      $('#leader').bootcomplete({
        url: window.Laravel.appPath + '/search-member/',
        minLength: 3,
        idField: true,
        method: 'POST',
        dataParams: {_token: $('meta[name=csrf-token]').attr('content')}
      });

    },
  };
})(window.jQuery);

Division.setup();