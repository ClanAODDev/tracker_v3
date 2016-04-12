var Tracker = Tracker || {};

(function ($) {

    Tracker = {

        Setup: function () {

            Tracker.SearchMembers();
            Tracker.AnimateCounter();
            Tracker.SearchCollection();

        },

        /**
         * Handle member search
         * @constructor
         */
        SearchMembers: function () {
            this.TriggerFilter(document.getElementById("member-search"), this.GetSearchResults, 1000);
        },

        /**
         * Textarea event listener
         *
         * @param textArea
         * @param callback
         * @param delay
         * @constructor
         */
        TriggerFilter: function (textArea, callback, delay) {
            var timer = null;
            textArea.onkeypress = function () {
                if (timer) {
                    window.clearTimeout(timer);
                }
                timer = window.setTimeout(function () {
                    timer = null;
                    callback();
                }, delay);
            };
            textArea = null;
        },

        /**
         * Search members handle
         *
         * @constructor
         */
        GetSearchResults: function () {
            if ($('#member-search').val()) {
                var name = $('input#member-search').val();

                $.ajax({
                    url: '/members/search/' + name,
                    type: 'GET',
                    success: function (response) {
                        $('#member-search-results').html(response);
                    }
                });
            }
        },

        /**
         * Animate counter areas
         *
         * @constructor
         */
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

        /**
         * Format a human readable number
         *
         * @param num
         * @returns {string}
         * @constructor
         */
        FormatNumber: function (num) {
            return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,")
        },

        /**
         * Filter a collection of items
         *
         * @constructor
         */
        SearchCollection: function () {
            $('#search-collection').keyup(function () {
                var value = $(this).val();
                // case insensitive search
                var exp = new RegExp('^' + value, 'i');
                var items = ".collection .collection-item";
                $(items).each(function () {
                    // toggle items that don't meet criteria
                    var isMatch = exp.test($(this).text());
                    $(this).toggle(isMatch);
                });
            });
        },

    }

})(jQuery);

Tracker.Setup();

