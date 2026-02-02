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
    initApplicationsModal: function () {
      var self = this;
      var $trigger = $('#open-applications-modal');
      if (!$trigger.length) return;

      var url = $trigger.data('url');
      var loaded = false;

      function openModal() {
        var $modal = $('#applicationsModal');
        $modal.modal('show');

        if (!loaded) {
          self.loadApplications(url);
          loaded = true;
        }
      }

      $trigger.on('click', function (e) {
        e.preventDefault();
        var params = new URLSearchParams(window.location.search);
        params.set('applications', '1');
        window.history.replaceState(null, '', '?' + params.toString());
        openModal();
      });

      $('#applicationsModal').on('hidden.bs.modal', function () {
        var params = new URLSearchParams(window.location.search);
        params.delete('applications');
        var qs = params.toString();
        window.history.replaceState(null, '', qs ? '?' + qs : window.location.pathname);
      });

      if (new URLSearchParams(window.location.search).has('applications')) {
        openModal();
      }
    },

    loadApplications: function (url) {
      var $loading = $('#applications-loading');
      var $content = $('#applications-content');
      var $empty = $('#applications-empty');
      var $list = $('#applications-list');
      var $detail = $('#applications-detail');

      $.ajax({
        url: url,
        type: 'GET',
        success: function (data) {
          $loading.addClass('hidden');

          if (!data.applications || !data.applications.length) {
            $empty.removeClass('hidden');
            return;
          }

          var apps = data.applications;

          apps.forEach(function (app, index) {
            $list.append(
              '<a href="#" class="list-group-item application-item' + (index === 0 ? ' active' : '') + '" data-index="' + index + '">' +
                '<strong>' + $('<span>').text(app.discord_username).html() + '</strong>' +
                '<span class="text-muted pull-right" style="font-size: 12px;">' + $('<span>').text(app.created_at).html() + '</span>' +
              '</a>'
            );

            var html = '<div class="panel panel-filled application-modal-detail' + (index !== 0 ? ' hidden' : '') + '" data-index="' + index + '" style="border-radius: 8px;">' +
              '<div class="panel-body">' +
                '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.06);">' +
                  '<strong style="font-size: 16px;">' + $('<span>').text(app.discord_username).html() + '</strong>' +
                  '<span class="text-muted" style="font-size: 12px;">' + $('<span>').text(app.created_at).html() + '</span>' +
                '</div>';

            app.responses.forEach(function (r) {
              html += '<div style="margin-bottom: 14px;">' +
                '<div class="text-muted" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">' + $('<span>').text(r.label).html() + '</div>' +
                '<div style="margin-top: 2px;">' + $('<span>').text(r.value).html() + '</div>' +
              '</div>';
            });

            html += '</div></div>';
            $detail.append(html);
          });

          $list.on('click', '.application-item', function (e) {
            e.preventDefault();
            var idx = $(this).data('index');
            $list.find('.application-item').removeClass('active');
            $(this).addClass('active');
            $detail.find('.application-modal-detail').addClass('hidden');
            $detail.find('.application-modal-detail[data-index="' + idx + '"]').removeClass('hidden');
          });

          $content.removeClass('hidden');
        },
        error: function () {
          $loading.html('<span class="text-danger"><i class="fa fa-exclamation-triangle"></i> Failed to load applications.</span>');
        }
      });
    },

    setup: function () {
      this.initAutocomplete();
      this.initPromotionsChart();
      this.initUnassigned();
      this.initScrollToOrganize();
      this.initApplicationsModal();
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