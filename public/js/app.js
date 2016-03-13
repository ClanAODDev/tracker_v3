var Tracker = Tracker || {};

(function ($) {
    Tracker = {

        Setup: function () {
            Tracker.AnimateCounter();
        },

        AnimateCounter: function () {

            $('.count-animated').each(function () {
                var $this = $(this);
                $({Counter: 0}).animate({Counter: $this.text()}, {
                    duration: 3000,
                    easing: "easeOutQuart",
                    step: function () {
                        if ($this.hasClass('percentage')) {
                            $this.text(Tracker.FormatNumber(Math.ceil(this.Counter) + "%"));
                        } else {
                            $this.text(Tracker.FormatNumber(Math.ceil(this.Counter)));
                        }
                    }
                });
            });

        },

        FormatNumber: function (num) {
            return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,")
        }

    }
})(jQuery);

Tracker.Setup();