(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
"use strict";

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

var Platoon = Platoon || {};

(function ($) {

    Platoon = {

        setup: function setup() {
            this.handleMembers();
        },

        handleMembers: function handleMembers() {
            var _ref;

            var platoonNum = parseInt($('.platoon-number').text()),
                formattedDate = new Date(),
                d = formattedDate.getDate(),
                m = formattedDate.getMonth() + 1,
                y = formattedDate.getFullYear(),
                nowDate = y + "-" + m + "-" + d,
                selected = new Array();

            $('table.members-table').DataTable({
                "autoWidth": true,
                "sDom": 'T<"clear">tfrip',
                "order": [],
                "columnDefs": [{
                    "targets": 'no-search',
                    "searchable": false
                }, {
                    "targets": 'col-hidden',
                    "visible": false,
                    "searchable": false
                }, {
                    // sort rank by rank id
                    "iDataSort": 0,
                    "aTargets": [3]
                }, {
                    // sort activity by last login date
                    "iDataSort": 1,
                    "aTargets": [5]
                }],
                stateSave: false,
                paging: false,
                "bServerSide": false,
                "drawCallback": function drawCallback(settings) {
                    $("#member-footer").empty();
                    $("#members-table_info").contents().appendTo("#member-footer");
                },

                "oTableTools": {
                    "sRowSelect": "multi",
                    "sSwfPath": "/swf/copy_csv_xls_pdf.swf",
                    "aButtons": [(_ref = {

                        "sExtends": "text",
                        "fnSelect": function fnSelect(nButton, oConfig, nRow) {
                            console.log($(nRow).data('id') + " clicked");
                        }
                    }, _defineProperty(_ref, "sExtends", "collection"), _defineProperty(_ref, "sButtonText", ""), _defineProperty(_ref, "mColumns", "visible"), _defineProperty(_ref, "aButtons", ["select_all", "select_none", {
                        "sExtends": "pdf",
                        "sPdfOrientation": "landscape",
                        "sFileName": "AOD Plt " + platoonNum + "_" + nowDate + ".pdf",
                        "mColumns": "visible"
                    }, {
                        "sExtends": "csv",
                        "sFileName": "AOD Plt " + platoonNum + "_" + nowDate + ".csv",
                        "mColumns": "visible"
                    }]), _defineProperty(_ref, "bSelectedOnly", true), _ref)]
                }
            });

            $(".dataTables_info").remove();
            $(".dataTables_filter input").appendTo("#playerFilter").removeClass('input-sm');
            $("#playerFilter input").attr({
                "placeholder": "Search Players",
                "class": "form-control input-lg"
            });
            $(".dataTables_filter label").remove();

            $(".DTTT_container .DTTT_button").removeClass('DTTT_button').remove();
            $(".DTTT_container").appendTo('.download-area').remove();

            $(".DTTT_container a").addClass('btn btn-xs btn-info tool').attr('title', 'Download table data').text("Export").css('margin-top', '5px').remove();

            $(".no-sort").removeClass("sorting");
        }
    };
})(jQuery);

Platoon.setup();

},{}]},{},[1]);
