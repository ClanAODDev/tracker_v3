var Division = Division || {};

(function ($) {

  Division = {

    setup: function () {
      this.initAutocomplete();
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