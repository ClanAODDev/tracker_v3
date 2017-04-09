(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
"use strict";

// Flot charts data and options
var data2 = $("#flot-line-chart").data('populations'),
    data1 = $("#flot-line-chart").data('weekly-active'),
    comments = $("#flot-line-chart").data('comments');

var chartUsersOptions = {

    series: {

        points: {
            show: true,
            radius: 2,
            symbol: "circle"
        },

        splines: {
            show: true,
            tension: 0.4,
            lineWidth: 1,
            fill: 1
        }
    },

    grid: {
        tickColor: "#404652",
        borderWidth: 1,
        hoverable: true,
        color: '#000',
        borderColor: '#404652'
    },

    comment: {
        show: true,

        hoverable: false
    },

    tooltip: {
        show: false
    },

    sidenote: {
        show: false
    },

    xaxis: {
        axisLabel: 'Weeks'
    },

    comments: comments,

    colors: ["#f7af3e", "#DE9536"]
};

$.plot($("#flot-line-chart"), [data2, data1], chartUsersOptions);

$(window).resize(function () {
    $.plot($("#flot-line-chart"), [data2, data1], chartUsersOptions);
});

$("input[type=checkbox]").change(function (event) {
    var option = {};
    option['comment'] = { show: $(this).is(':checked') };
    $.extend(true, chartUsersOptions, option);
    $.plot($("#flot-line-chart"), [data2, data1], chartUsersOptions);
});

},{}]},{},[1]);
