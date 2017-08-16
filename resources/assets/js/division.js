var Division = Division || {};

(function ($) {

  Division = {

    setup: function () {
      this.initAutocomplete();
      this.initSetup();
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