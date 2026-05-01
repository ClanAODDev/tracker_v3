var Division = Division || {};

(function ($) {
  const csrfToken = $('meta[name=csrf-token]').attr('content');

  Division = {

    initUnassigned() {
      $('.organize-platoons-btn').on('click', (e) => {
        const $btn = $(e.currentTarget);
        const isOrganizing = $('.platoon-assignments-section').hasClass('organize-mode');

        if (!isOrganizing) {
          $btn.html('<i class="fa fa-check"></i> Done');
          $btn.removeClass('btn-accent').addClass('btn-success');
          $('.unassigned-organizer-members').slideDown(200);
          $('.platoon-assignments-section').addClass('organize-mode');
          this.enablePlatoonDragDrop();
        } else {
          $btn.html('<i class="fa fa-arrows-alt"></i> Organize');
          $btn.removeClass('btn-success').addClass('btn-accent');
          $('.unassigned-organizer-members').slideUp(200);
          $('.platoon-assignments-section').removeClass('organize-mode');
        }
      });

      $('.platoon').on('click', (e) => {
        if ($('.platoon-assignments-section').hasClass('organize-mode')) {
          e.preventDefault();
        }
      });
    },

    initScrollToOrganize() {
      $(document).on('click', '.scroll-to-organize', (e) => {
        const $target = $('#platoons');
        if (!$target.length) return;

        e.preventDefault();

        $('html, body').animate({
          scrollTop: $target.offset().top - 20
        }, 400, function() {
          const $organizeBtn = $('.organize-platoons-btn');
          const isOrganizing = $('.platoon-assignments-section').hasClass('organize-mode');

          if (!isOrganizing && $organizeBtn.length) {
            $organizeBtn.trigger('click');
          }

          $target.addClass('highlight-section');
          setTimeout(() => {
            $target.removeClass('highlight-section');
          }, 1500);
        });
      });
    },

    enablePlatoonDragDrop() {
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
          const $platoon = $(this);
          const base_url = window.Laravel.appPath;
          const draggableId = ui.draggable.attr('data-member-id');
          const droppableId = $platoon.attr('data-platoon-id');
          const $draggable = $(ui.draggable);

          $.ajax({
            type: 'POST',
            url: `${base_url}/members/${draggableId}/assign-platoon`,
            data: {
              platoon_id: droppableId,
              _token: csrfToken
            },
            success: () => {
              toastr.success('Member assigned to platoon!');
              $draggable.fadeOut(200, function() {
                $(this).remove();
                if ($('.unassigned-platoon-member').length < 1) {
                  $('.unassigned-organizer').slideUp();
                  $('.platoon-assignments-section').removeClass('organize-mode');
                }
              });
              const $count = $platoon.find('.platoon-stat-badge .fa-users').parent();
              const currentCount = parseInt($count.text().trim()) || 0;
              $count.html(`<i class="fa fa-users"></i> ${currentCount + 1}`);
            },
            error: () => {
              toastr.error('Failed to assign member to platoon');
            }
          });
        }
      });
    },

    initApplicationsModal() {
      const $trigger = $('#open-applications-modal');
      if (!$trigger.length) return;

      $trigger.on('click', (e) => {
        e.preventDefault();
        const params = new URLSearchParams(window.location.search);
        params.set('applications', '1');
        window.history.replaceState(null, '', `?${params.toString()}`);
        $('#applicationsModal').modal('show');
      });

      $('#applicationsModal').on('hidden.bs.modal', () => {
        const params = new URLSearchParams(window.location.search);
        params.delete('applications');
        params.delete('application');
        const qs = params.toString();
        window.history.replaceState(null, '', qs ? `?${qs}` : window.location.pathname);
      });

      const params = new URLSearchParams(window.location.search);
      if (params.has('applications') || params.has('application')) {
        $('#applicationsModal').modal('show');
      }
    },

    setup() {
      this.initAutocomplete();
      this.initPromotionsChart();
      this.initUnassigned();
      this.initScrollToOrganize();
      this.initApplicationsModal();
    },

    initPromotionsChart() {
      const canvas = document.getElementById('promotions-chart');

      if (!canvas || typeof Chart === 'undefined') {
        return;
      }

      const values = JSON.parse(canvas.dataset.values || '[]');
      const labels = JSON.parse(canvas.dataset.labels || '[]');

      if (!values || !labels || values.length === 0) {
        return;
      }

      const styles = getComputedStyle(document.documentElement);
      const themeColors = [
        styles.getPropertyValue('--color-muted').trim() || '#949ba2',
        styles.getPropertyValue('--color-primary').trim() || '#0f83c9',
        styles.getPropertyValue('--color-success').trim() || '#1bbf89',
        styles.getPropertyValue('--color-accent').trim() || '#f7af3e',
        styles.getPropertyValue('--color-info').trim() || '#56c0e0',
        styles.getPropertyValue('--color-danger').trim() || '#db524b'
      ];
      const backgroundColors = values.map((_, i) => themeColors[i % themeColors.length]);

      const gridColor = 'rgba(255,255,255,0.04)';
      const textColor = styles.getPropertyValue('--color-text-light').trim() || '#949ba2';

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
                label: (context) => `${context.parsed.x} promotions`
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

    initAutocomplete() {
      $('#leader').bootcomplete({
        url: `${window.Laravel.appPath}/search-member/`,
        minLength: 3,
        idField: true,
        method: 'POST',
        dataParams: {_token: csrfToken}
      });
    },
  };
})(window.jQuery);

function initDivision() {
    const $ = window.jQuery;
    if (!$ || typeof $.fn.DataTable !== 'function' || typeof $.fn.bootcomplete !== 'function') {
        setTimeout(initDivision, 50);
        return;
    }
    Division.setup();
}

initDivision();
