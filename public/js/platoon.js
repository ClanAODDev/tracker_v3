(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
"use strict";

var Platoon = Platoon || {};

(function ($) {

    Platoon = {

        setup: function setup() {
            this.handleMembers();
        },

        handleMembers: function handleMembers() {

            var platoonNum = parseInt($('.platoon-number').text()),
                formattedDate = new Date(),
                d = formattedDate.getDate(),
                m = formattedDate.getMonth() + 1,
                y = formattedDate.getFullYear(),
                nowDate = y + "-" + m + "-" + d,
                selected = new Array();

            /**
             * Handle platoons, squads
             */
            $('table.members-table').DataTable({
                autoWidth: true, bInfo: false,
                columnDefs: [{
                    targets: 'no-search', searchable: false
                }, {
                    targets: 'col-hidden', visible: false, searchable: false
                }, {
                    // sort rank by rank id
                    "iDataSort": 0, "aTargets": [3]
                }, {
                    // sort activity by last login date
                    "iDataSort": 1, "aTargets": [5]
                }],
                stateSave: true, paging: false
            });

            $(".dataTables_filter input").appendTo("#playerFilter").removeClass('input-sm');

            $("#playerFilter input").attr({
                "placeholder": "Search Players",
                "class": "form-control"
            });

            $(".dataTables_filter label").remove();

            $(".no-sort").removeClass("sorting");

            // omit leader field if using TBA
            $("#is_tba").click(function () {
                toggleTBA();
            });

            toggleTBA();

            function toggleTBA() {
                if ($('#is_tba').is(':checked')) {
                    $("#leader_id, #leader").prop("disabled", true).val('');
                } else {
                    $("#leader_id, #leader").prop("disabled", false);
                }
            }

            // handle leader assignment completion for
            // platoons and squads
            $('#leader').bootcomplete({
                url: window.Laravel.appPath + '/search-leader/',
                minLength: 3,
                idField: true,
                method: 'POST',
                dataParams: { _token: $('meta[name=csrf-token]').attr('content') }
            });
        }
    };
})(jQuery);

Platoon.setup();

},{}]},{},[1]);
