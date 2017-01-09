(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

var Tracker = Tracker || {};

(function ($) {

    Tracker = {

        Setup: function Setup() {
            Tracker.SearchMembers();
            Tracker.SearchCollection();

            // misc functionality, visual
            Tracker.AnimateCounter();
            Tracker.AlertHandling();
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
        },
        /**
         * Handle repeater fields
         *
         * @constructor
         */
        InitRepeater: function InitRepeater() {
            $(document).ready(function () {
                $('.repeater').repeater();
            });
        },
        /**
         * Handle tab activation on URL navigation
         *
         * @constructor
         */
        InitTabActivate: function InitTabActivate() {
            $('.nav-tabs').stickyTabs();
        },

        AlertHandling: function AlertHandling() {
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
                var name = $('input#member-search').val();

                $.ajax({
                    url: '/search/members/' + name,
                    type: 'GET',
                    success: function success(response) {
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
        AnimateCounter: function AnimateCounter() {
            $('.count-animated').each(function () {
                var $this = $(this);
                $({ Counter: 0 }).animate({ Counter: $this.text() }, {
                    duration: 3000,
                    easing: "easeInOutCirc",
                    step: function step() {
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

        ResetLocality: function ResetLocality() {
            $('[data-reset-locality]').click(function () {
                $('[data-locality-entry]').each(function () {

                    var new_string = $(this).find('[data-new-string]'),
                        old_string = $(this).find('[data-old-string]');

                    new_string.val(old_string.val());
                });
            });
        }

    };
})(jQuery);

Tracker.Setup();

},{}]},{},[1]);
