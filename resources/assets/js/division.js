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
      this.initPromotionsChart();
      this.initUnassigned();
      this.initScrollToOrganize();
    },

    initPromotionsChart: function () {
      var canvas = document.getElementById('promotions-chart');

      if (!canvas || typeof Chart === 'undefined') {
        return;
      }

      var values = JSON.parse(canvas.dataset.values || '[]');
      var labels = JSON.parse(canvas.dataset.labels || '[]');

      if (!values || !labels || values.length === 0) {
        return;
      }

      var styles = getComputedStyle(document.documentElement);
      var themeColors = [
        styles.getPropertyValue('--color-muted').trim() || '#949ba2',
        styles.getPropertyValue('--color-primary').trim() || '#0f83c9',
        styles.getPropertyValue('--color-success').trim() || '#1bbf89',
        styles.getPropertyValue('--color-accent').trim() || '#f7af3e',
        styles.getPropertyValue('--color-info').trim() || '#56c0e0',
        styles.getPropertyValue('--color-danger').trim() || '#db524b'
      ];
      var backgroundColors = values.map(function(_, i) {
        return themeColors[i % themeColors.length];
      });

      var gridColor = 'rgba(255,255,255,0.04)';
      var textColor = styles.getPropertyValue('--color-text-light').trim() || '#949ba2';

      new Chart(canvas, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{
            data: values,
            backgroundColor: backgroundColors,
            borderWidth: 0,
            barThickness: 20
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
              usePointStyle: true,
              boxPadding: 6,
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
              grid: {
                color: gridColor
              },
              ticks: {
                stepSize: 1,
                color: textColor
              }
            },
            y: {
              grid: {
                display: false
              },
              ticks: {
                color: textColor
              }
            }
          }
        }
      });
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

function initDivision() {
    var $ = window.jQuery;
    if (!$ || typeof $.fn.DataTable !== 'function' || typeof $.fn.bootcomplete !== 'function') {
        setTimeout(initDivision, 50);
        return;
    }
    Division.setup();
}

initDivision();