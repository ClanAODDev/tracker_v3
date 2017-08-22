var Division = Division || {};

(function ($) {

  Division = {

    initUnassigned: function () {

      $(function () {

        $('.unassigned').draggable({
          revert: true,
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
})(jQuery);

Division.setup();