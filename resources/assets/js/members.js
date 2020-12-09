let Platoon = Platoon || {};

(function ($) {

  Platoon = {

    setup: function () {
      this.handleMemberList();
      this.handleForumActivityChart();
    },


    handleForumActivityChart: function () {

      var ctx = $('.forum-activity-chart');

      var myDoughnutChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
          datasets: [
            {
              data: ctx.data('values'),
              backgroundColor: ctx.data('colors'),
              borderWidth: 0,
            }],
          labels: ctx.data('labels'),
        },
        options: {
          rotation: 1 * Math.PI,
          circumference: 1 * Math.PI,
          legend: {
            position: 'bottom',
            labels: {
              boxWidth: 5,
              fontColor: '#949ba2'
            },
            label: {
              fullWidth: false
            }
          }
        }
      });
    },
    handleMemberList: function () {

      var platoonNum = parseInt($('.platoon-number').text()),
        formattedDate = new Date(),
        d = formattedDate.getDate(),
        m = (formattedDate.getMonth() + 1),
        y = formattedDate.getFullYear(),
        nowDate = y + '-' + m + '-' + d,
        selected = new Array();

      /**
       * Handle platoons, squads
       */
      $('table.members').DataTable({
        bInfo: false, autoWidth: true,
        columnDefs: [{
          targets: 'no-search', searchable: false
        }, {
          targets: 'col-hidden', visible: false, searchable: false
        }, {
          // sort rank by rank id
          'iDataSort': 2, 'aTargets': [1]
        }
        ],
        stateSave: false, paging: false,
      });

      $('.dataTables_filter input').appendTo('#playerFilter').removeClass('input-sm');

      $('#playerFilter input').attr({
        'placeholder': 'Search Players',
        'class': 'form-control'
      });

      $('.dataTables_filter label').remove();

      $('.no-sort').removeClass('sorting');

      // omit leader field if using TBA
      $('#is_tba').click(function () {
        toggleTBA();
      });

      toggleTBA();

      function toggleTBA () {
        if ($('#is_tba').is(':checked')) {
          $('#leader_id, #leader').prop('disabled', true).val('');
        } else {
          $('#leader_id, #leader').prop('disabled', false);
        }
      }

    },
  };
})(jQuery);

Platoon.setup();