(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
"use strict";

var Tracker = Tracker || {};

(function ($) {

    Tracker = {

        Setup: function Setup() {
            Tracker.GeneralInit();
            Tracker.SearchMembers();
            Tracker.SearchCollection();

            // misc functionality, visual
            Tracker.InitRepeater();
            Tracker.InitTabActivate();
            Tracker.ResetLocality();
        },
        /**
         * Handle member search
         * @constructor
         */
        SearchMembers: function SearchMembers() {
            this.TriggerFilter(document.getElementById("member-search"), this.GetSearchResults, 1000);
            $("#searchclear").click(function () {
                $("section.search-results").addClass('closed').removeClass('open');
                $("#member-search").val('');
                $("#searchclear").css('display', 'none');
            });
        },
        /**
         * Handle repeater fields
         *
         * @constructor
         */
        InitRepeater: function InitRepeater() {
            $('.repeater').repeater({
                isFirstItemUndeletable: true
            });
        },
        /**
         * Handle tab activation on URL navigation
         *
         * @constructor
         */
        InitTabActivate: function InitTabActivate() {
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
        TriggerFilter: function TriggerFilter(textArea, callback, delay) {
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
        GetSearchResults: function GetSearchResults() {
            if ($('#member-search').val()) {
                var name = $('input#member-search').val(),
                    base_url = window.Laravel.appPath;

                $.ajax({
                    url: base_url + '/search/members/' + name,
                    type: 'GET',
                    success: function success(response) {
                        $("#searchclear").css('display', 'block');
                        $('section.search-results').html(response);
                        $("section.search-results").addClass('open').removeClass('closed');
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
        FormatNumber: function FormatNumber(num) {
            return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
        },

        /**
         * Filter a collection of items
         *
         * @constructor
         */
        SearchCollection: function SearchCollection() {
            $('#search-collection').keyup(function () {
                var value = $(this).val(),
                    exp = new RegExp('^' + value, 'i'),
                    items = ".collection .collection-item";
                $(items).each(function () {
                    // toggle items that don't meet criteria
                    var isMatch = exp.test($(this).text());
                    $(this).toggle(isMatch);
                });
            });
        },

        ResetLocality: function ResetLocality() {
            $('[data-reset-locality]').click(function () {
                $('[data-locality-entry]').each(function () {

                    var new_string = $(this).find('[data-new-string]'),
                        old_string = $(this).find('[data-old-string]');

                    if (new_string.val() !== old_string.val()) {
                        new_string.effect('highlight', 1000);
                        new_string.val(old_string.val());
                    }
                });
            });
        },

        GeneralInit: function GeneralInit() {

            var clipboard = new Clipboard('.copy-to-clipboard');

            clipboard.on('success', function (e) {
                toastr.success('Copied!');
                e.clearSelection();
            });

            $('table.basic-datatable').DataTable({
                paging: false,
                bFilter: false,
                bInfo: false,
                order: [],
                columnDefs: [{ targets: 'no-sort', orderable: false }]
            });

            $('table.adv-datatable').DataTable({
                order: [],
                columnDefs: [{ targets: 'no-sort', orderable: false }]
            });

            var sparklineCharts = function sparklineCharts() {
                $("[census-data]").sparkline($("[census-data]").data('counts'), {
                    type: 'line',
                    lineColor: '#fff',
                    lineWidth: 3,
                    fillColor: '#404652',
                    height: 50,
                    width: '100%'
                });

                $(".census-pie").each(function () {
                    $(this).sparkline($(this).data('counts'), {
                        type: 'pie',
                        sliceColors: ['#404652', '#f7af3e']
                    });
                });

                $('[census-data]').bind('sparklineClick', function (ev) {
                    var sparkline = ev.sparklines[0],
                        region = sparkline.getCurrentRegionFields();
                    console.log("Clicked on x=" + region.x + " y=" + region.y);
                });
            };

            var sparkResize = void 0;

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
        }

    };
})(jQuery);

Tracker.Setup();

},{}]},{},[1]);

//# sourceMappingURL=main.js.map
