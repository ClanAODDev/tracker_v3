var Tracker = Tracker || {};

(function ($) {

    Tracker = {

        Setup: function () {
            Tracker.GeneralInit();
            Tracker.SearchMembers();
            Tracker.SearchCollection();

            // misc functionality, visual
            Tracker.AlertHandling();
            Tracker.InitRepeater();
            Tracker.InitTabActivate();
            Tracker.ResetLocality();
        },
        /**
         * Handle member search
         * @constructor
         */
        SearchMembers: function () {
            this.TriggerFilter(document.getElementById("member-search"), this.GetSearchResults, 1000);
            $("#searchclear").click(function () {
                $("div#member-search-results").hide();
                $("#member-search").val('');
                $("#searchclear").css('display', 'none');
            });
        },
        /**
         * Handle repeater fields
         *
         * @constructor
         */
        InitRepeater: function () {
            $(document).ready(function () {
                $('.repeater').repeater();
            });
        },
        /**
         * Handle tab activation on URL navigation
         *
         * @constructor
         */
        InitTabActivate: function () {
            $('.nav-tabs').stickyTabs();
        },
        AlertHandling: function () {
            $('div.alert').not('.alert-important').delay(3000).fadeOut(350);
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
            if ($("#member-search").length) {
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
            }
        },

        /**
         * Search members handle
         *
         * @constructor
         */
        GetSearchResults: function () {
            if ($('#member-search').val()) {
                var name = $('input#member-search').val(),
                    base_url = window.Laravel.appPath;

                $.ajax({
                    url: base_url + '/search/members/' + name,
                    type: 'GET',
                    success: function (response) {
                        $("#searchclear").css('display', 'block');
                        $("div#member-search-results").show();
                        console.log(this.url);
                        $('#member-search-results').html(response);
                    }
                });
            }
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

        ResetLocality: function () {
            $('[data-reset-locality]').click(function () {
                $('[data-locality-entry]').each(function () {

                    var new_string = $(this).find('[data-new-string]'),
                        old_string = $(this).find('[data-old-string]');

                    new_string.val(old_string.val());
                });
            })
        },

        GeneralInit: function () {

            var sparklineCharts = function () {
                $("[recruit-count]").sparkline(
                    $("[recruit-count]").data('count'), {
                        type: 'pie',
                        sliceColors: ['#404652','#f7af3e'],
                        width: '100%',
                        height: 100
                    }
                );

                $("[census-data]").sparkline(
                    $("[census-data]").data('counts'), {
                        type: 'line',
                        lineColor: '#fff',
                        lineWidth: 3,
                        fillColor: '#393D47',
                        height: 80,
                        width: '100%'
                    }
                );
            }

            var sparkResize;

            $(window).resize(function () {
                clearTimeout(sparkResize);
                sparkResize = setTimeout(sparklineCharts, 100);
            });

            sparklineCharts();

            // Handle minimalize left menu
            $('.left-nav-toggle a').on('click', function (event) {
                event.preventDefault();
                $("body").toggleClass("nav-toggle");
                clearTimeout(sparkResize);
                sparkResize = setTimeout(sparklineCharts, 100);
            });

            // Hide all open sub nav menu list
            $('.nav-second').on('show.bs.collapse', function () {
                $('.nav-second.in').collapse('hide');
            })

            // Handle panel toggle
            $('.panel-toggle').on('click', function (event) {
                event.preventDefault();
                var hpanel = $(event.target).closest('div.panel');
                var icon = $(event.target).closest('i');
                var body = hpanel.find('div.panel-body');
                var footer = hpanel.find('div.panel-footer');
                body.slideToggle(300);
                footer.slideToggle(200);

                // Toggle icon from up to down
                icon.toggleClass('fa-chevron-up').toggleClass('fa-chevron-down');
                hpanel.toggleClass('').toggleClass('panel-collapse');
                setTimeout(function () {
                    hpanel.resize();
                    hpanel.find('[id^=map-]').resize();
                }, 50);
            });

            // Handle panel close
            $('.panel-close').on('click', function (event) {
                event.preventDefault();
                var hpanel = $(event.target).closest('div.panel');
                hpanel.remove();
            });

        },

    }

})(jQuery);

Tracker.Setup();

