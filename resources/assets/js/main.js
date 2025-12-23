var Tracker = Tracker || {};

(function ($) {

    Tracker = {

        Setup: function () {
            Tracker.GeneralInit();
            Tracker.SearchMembers();
            Tracker.SearchCollection();

            // misc functionality, visual
            Tracker.InitRepeater();
            Tracker.InitTabActivate();
        },
        /**
         * Handle member search
         * @constructor
         */
        SearchMembers: function () {
            this.TriggerFilter(document.getElementById('member-search'), this.GetSearchResults, 1000);
            $('#searchclear').click(function () {
                $('section.search-results').addClass('closed').removeClass('open');
                $('#member-search').val('');
                $('#searchclear').css('display', 'none');
            });
        },
        /**
         * Handle repeater fields
         *
         * @constructor
         */
        InitRepeater: function () {
            $('.repeater').repeater({
                isFirstItemUndeletable: true,
            });
        },
        /**
         * Handle tab activation on URL navigation
         *
         * @constructor
         */
        InitTabActivate: function () {
            $('.nav-tabs').stickyTabs();

            // handle sparklines that aren't visible on the dom initially
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                $.sparkline_display_visible();
            });
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

            if (!textArea || !$('#member-search').length) return; // Ensure textArea exists

            var triggerSearch = function () {
                if (!textArea) return; // Ensure textArea still exists

                $('.results-loader').removeClass('hidden');

                if (timer) {
                    window.clearTimeout(timer);
                }

                timer = window.setTimeout(function () {
                    timer = null;
                    if (!textArea || textArea.value.trim() === "") {
                        $('.results-loader').addClass('hidden'); // Hide loader if empty
                        return; // Do not trigger search if the field is empty
                    }
                    callback();
                }, delay);
            };

            textArea.addEventListener('keydown', triggerSearch);
            textArea.addEventListener('paste', function () {
                setTimeout(triggerSearch, 0); // Ensures pasted text is processed first
            });

            textArea.addEventListener('input', function () {
                if (!textArea) return;
                if (textArea.value.trim() === "") {
                    $('.results-loader').addClass('hidden'); // Hide loader if empty
                }
            });
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
                        window.scrollTo(0, 0);
                        $('.results-loader').addClass('hidden');
                        $('#searchclear').css('display', 'block');
                        $('section.search-results').html(response);
                        $('section.search-results').addClass('open').removeClass('closed');
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
            return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
        },

        /**
         * Filter a collection of items
         *
         * @constructor
         */
        SearchCollection: function () {
            $('#search-collection').keyup(function () {
                let value = $(this).val(),
                    exp = new RegExp('^' + value, 'i'),
                    items = '.collection .collection-item';
                $(items).each(function () {
                    // toggle items that don't meet criteria
                    let isMatch = exp.test($(this).text());
                    $(this).toggle(isMatch);

                });
            });
        },

        smoothScroll: function () {
            $('.smooth-scroll').click(function (e) {
                e.preventDefault();
                var targetId = $(this).attr('href');
                var top = $(targetId).offset().top - 90;
                $('html, body').stop().animate({scrollTop: top}, 750);
                window.location.hash = $.attr(this, 'href').substr(1);
            });

        },

        GeneralInit: function () {

            // handle primary nav collapse
            $('.left-nav-toggle a').click(function () {
                if ($('body').hasClass('nav-toggle')) {
                    $.get(window.Laravel.appPath + '/primary-nav/decollapse');
                } else {
                    $.get(window.Laravel.appPath + '/primary-nav/collapse');
                }
            });

            // Only enable if the document has a long scroll bar
            // Note the window height + offset
            if (($(window).height() + 100) < $(document).height()) {
                $('#top-link-block').removeClass('hidden').affix({
                    // how far to scroll down before link "slides" into view
                    offset: {top: 100}
                });
            }

            this.smoothScroll();

            var clipboard = new Clipboard('.copy-to-clipboard');

            clipboard.on('success', function (e) {
                toastr.success('Copied!');
                e.clearSelection();
            });

            var basicDatatable = $('table.basic-datatable').DataTable({
                paging: false,
                bFilter: false,
                stateSave: true,
                bInfo: false,
                order: [],
                columnDefs: [
                    {targets: 'no-sort', orderable: false}
                ],
                select: {
                    style: 'os',
                    selector: 'td:first-child',
                },
            });

            var advDataTable = $('table.adv-datatable').DataTable({
                order: [],
                columnDefs: [
                    {targets: 'no-sort', orderable: false}
                ]
            });

            if ($('.for-pm-selection').length) {
                // handle PM selection
                basicDatatable.on("select", function (e, t, a, d) {
                    let l = basicDatatable.rows($(".selected")).data().toArray().map(function (e) {
                        return e[4]
                    });
                    if (l.length >= 2) {
                        $("#selected-data").show(),
                            $("#selected-data .status-text").text("With selected (" + l.length + ")"),
                            $("#pm-member-data").val(l);
                    }
                });
            }

            var sparklineCharts = function () {
                $('[census-data]').each(function () {
                    var $el = $(this);
                    $el.sparkline($el.data('counts'), {
                        type: 'line',
                        lineColor: '#fff',
                        lineWidth: 2,
                        fillColor: '#404652',
                        height: 50,
                        width: '100%'
                    });

                    if ($el.data('weekly-voice')) {
                        $el.sparkline($el.data('weekly-voice'), {
                            type: 'line',
                            lineColor: '#56C0E0',
                            lineWidth: 2,
                            fillColor: 'rgba(86, 192, 224, 0.3)',
                            composite: true
                        });
                    }
                });

                $('.census-pie').each(function () {
                    $(this).sparkline(
                        $(this).data('counts'), {
                            type: 'pie',
                            sliceColors: $(this).data('colors')
                        }
                    );
                });
            };

            let sparkResize;

            $(window).resize(function () {
                clearTimeout(sparkResize);
                sparkResize = setTimeout(sparklineCharts, 100);
            });

            sparklineCharts();

            // Handle minimalize left menu
            $('.left-nav-toggle a').on('click', function (event) {
                event.preventDefault();
                $('body').toggleClass('nav-toggle');
                clearTimeout(sparkResize);
                sparkResize = setTimeout(sparklineCharts, 100);
            });

            // Hide all open sub nav menu list
            $('.nav-second').on('show.bs.collapse', function () {
                $('.nav-second.in').collapse('hide');
            });

            // Handle panel toggle
            $('.panel-toggle').on('click', function (event) {
                event.preventDefault();
                var hpanel = $(event.target).closest('div.panel');
                var icon = $(event.target).closest('i.toggle-icon');
                var iconNotLinked = $(event.target).find('i.toggle-icon');
                var body = hpanel.find('div.panel-body');
                var footer = hpanel.find('div.panel-footer');
                body.slideToggle(300);
                footer.slideToggle(200);

                // Toggle icon from up to down
                icon.toggleClass('fa-chevron-up').toggleClass('fa-chevron-down');
                iconNotLinked.toggleClass('fa-chevron-up').toggleClass('fa-chevron-down');
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

            $('.search-member').bootcomplete({
                url: window.Laravel.appPath + '/search-member/',
                minLength: 3,
                idField: true,
                method: 'POST',
                dataParams: {_token: $('meta[name=csrf-token]').attr('content')}
            });

        },

    };

})(window.jQuery);

Tracker.Setup();

