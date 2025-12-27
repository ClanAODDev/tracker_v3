var Division = Division || {};

(function ($) {

  Division = {

    initUnassigned: function () {
      var self = this;

      $('.organize-platoons-btn').on('click', function() {
        var $btn = $(this);
        var isOrganizing = $('.platoon-assignments-section').hasClass('organize-mode');

        if (!isOrganizing) {
          $btn.html('<i class="fa fa-check"></i> Done');
          $btn.removeClass('btn-accent').addClass('btn-success');
          $('.unassigned-organizer-members').slideDown(200);
          $('.platoon-assignments-section').addClass('organize-mode');
          self.enablePlatoonDragDrop();
        } else {
          $btn.html('<i class="fa fa-arrows-alt"></i> Organize');
          $btn.removeClass('btn-success').addClass('btn-accent');
          $('.unassigned-organizer-members').slideUp(200);
          $('.platoon-assignments-section').removeClass('organize-mode');
        }
      });

      $('.platoon').on('click', function(e) {
        if ($('.platoon-assignments-section').hasClass('organize-mode')) {
          e.preventDefault();
        }
      });
    },

    initScrollToOrganize: function () {
      var self = this;

      $(document).on('click', '.scroll-to-organize', function(e) {
        var $target = $('#platoons');
        if (!$target.length) return;

        e.preventDefault();

        $('html, body').animate({
          scrollTop: $target.offset().top - 20
        }, 400, function() {
          var $organizeBtn = $('.organize-platoons-btn');
          var isOrganizing = $('.platoon-assignments-section').hasClass('organize-mode');

          if (!isOrganizing && $organizeBtn.length) {
            $organizeBtn.trigger('click');
          }

          $target.addClass('highlight-section');
          setTimeout(function() {
            $target.removeClass('highlight-section');
          }, 1500);
        });
      });
    },

    enablePlatoonDragDrop: function () {
      var self = this;

      if ($('.unassigned-platoon-member').data('ui-draggable')) {
        return;
      }

      $('.unassigned-platoon-member').draggable({
        revert: true,
        revertDuration: 200,
        zIndex: 1000,
        cursor: 'grabbing',
        helper: 'clone',
        appendTo: 'body'
      });

      $('.platoon').droppable({
        hoverClass: 'panel-c-success',
        drop: function (event, ui) {
          var $platoon = $(this);
          var base_url = window.Laravel.appPath;
          var draggableId = ui.draggable.attr('data-member-id');
          var droppableId = $platoon.attr('data-platoon-id');

          $.ajax({
            type: 'POST',
            url: base_url + '/members/' + draggableId + '/assign-platoon',
            data: {
              platoon_id: droppableId,
              _token: $('meta[name=csrf-token]').attr('content')
            },
            success: function (response) {
              toastr.success('Member assigned to platoon!');
              $(ui.draggable).fadeOut(200, function() {
                $(this).remove();
                if ($('.unassigned-platoon-member').length < 1) {
                  $('.unassigned-organizer').slideUp();
                  $('.platoon-assignments-section').removeClass('organize-mode');
                }
              });
              var $count = $platoon.find('.platoon-stat-badge .fa-users').parent();
              var currentCount = parseInt($count.text().trim()) || 0;
              $count.html('<i class="fa fa-users"></i> ' + (currentCount + 1));
            },
            error: function () {
              toastr.error('Failed to assign member to platoon');
            }
          });
        }
      });
    },
    setup: function () {
      this.initAutocomplete();
      this.initSetup();
      this.initUnassigned();
      this.initScrollToOrganize();
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

      var styles = getComputedStyle(document.documentElement);
      var infoColor = styles.getPropertyValue('--color-info').trim() || '#5bc0de';
      var successColor = styles.getPropertyValue('--color-success').trim() || '#1bbf89';

      new Chart(ctx, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [
            {
              label: 'Members',
              data: population,
              borderColor: infoColor,
              backgroundColor: infoColor + '1a',
              borderWidth: 2,
              fill: true,
              tension: 0.3,
              pointRadius: 3,
              pointHoverRadius: 6,
              pointBackgroundColor: infoColor,
              pointBorderColor: '#fff',
              pointBorderWidth: 2,
            },
            {
              label: 'Voice Active',
              data: voice,
              borderColor: successColor,
              backgroundColor: successColor + '1a',
              borderWidth: 2,
              fill: true,
              tension: 0.3,
              pointRadius: 2,
              pointHoverRadius: 5,
              pointBackgroundColor: successColor,
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
        var colors = ['#949ba2', '#0f83c9', '#1bbf89', '#f7af3e', '#56c0e0', '#db524b'];
        var values = ctx.data('values');
        var labels = ctx.data('labels');

        new Chart(ctx, {
          type: 'bar',
          data: {
            labels: labels,
            datasets: [{
              label: 'Promotions',
              data: values,
              backgroundColor: labels.map(function(_, i) {
                return colors[i % colors.length];
              }),
              borderWidth: 0,
              borderRadius: 4,
              barThickness: 24
            }]
          },
          options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: false
              },
              tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                titleColor: '#fff',
                bodyColor: '#fff',
                padding: 10,
                callbacks: {
                  label: function(context) {
                    return context.parsed.x + ' promotions';
                  }
                }
              }
            },
            scales: {
              x: {
                beginAtZero: true,
                grid: { color: 'rgba(255, 255, 255, 0.05)' },
                ticks: {
                  color: 'rgba(255, 255, 255, 0.5)',
                  font: { size: 11 },
                  stepSize: 1
                }
              },
              y: {
                grid: { display: false },
                ticks: {
                  color: 'rgba(255, 255, 255, 0.7)',
                  font: { size: 12 }
                }
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